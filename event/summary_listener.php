<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2018 v12mike
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for "most liked posts" summary panels and viewforum heart counts.
 *
 * Displays configurable summary panels of the most liked posts on the board
 * index and viewforum pages, broken down by period (today, this week, this
 * month, this year, all time). Also handles per-topic like count display
 * on the viewforum topic list.
 *
 * The summary query uses SQL with subqueries for performance (aggregating
 * likes, joining posts/topics/users/forums, filtering by content visibility).
 * Results are cached for 12 hours to reduce database load.
 *
 * Users can opt out via the pf_postlove_hide custom profile field.
 * Bots are excluded automatically.
 */
class summary_listener implements EventSubscriberInterface
{
	private const SECONDS_PER_MINUTE = 60;
	private const SECONDS_PER_HOUR = self::SECONDS_PER_MINUTE * 60;
	private const SECONDS_PER_DAY = self::SECONDS_PER_HOUR * 24;

	/**
	 * How many aggregate rows to fetch per requested post, per page. The
	 * visibility filter runs after the aggregate, so over-fetching leaves room
	 * for posts it drops.
	 */
	private const AGGREGATE_OVERFETCH = 10;

	/**
	 * Upper bound on pages topposts_of_period() will fetch from its board-wide
	 * aggregate; bounds the worst case for a viewer who can read few of the
	 * period's highest-liked posts, rather than paging the whole period.
	 */
	private const MAX_AGGREGATE_ROUNDS = 5;

	protected \phpbb\auth\auth $auth;
	protected \phpbb\config\config $config;
	protected \phpbb\cache\service $cache;
	protected \phpbb\content_visibility $content_visibility;
	protected \phpbb\db\driver\driver_interface $db;
	protected \phpbb\event\dispatcher_interface $dispatcher;
	protected \phpbb\template\template $template;
	protected \phpbb\user $user;
	protected \phpbb\language\language $language;
	protected \avathar\postlove\service\forum_access $forum_access;
	protected string $root_path;
	protected string $php_ext;
	protected string $table_prefix;
	protected int $test_time;

	/** @var array Prefetched like counts per topic_id */
	protected array $topic_like_counts = [];

	public function __construct(\phpbb\auth\auth $auth,
								\phpbb\config\config $config,
								\phpbb\cache\service $cache,
								\phpbb\content_visibility $content_visibility,
								\phpbb\db\driver\driver_interface $db,
								\phpbb\event\dispatcher_interface $dispatcher,
								\phpbb\template\template $template,
								\phpbb\user $user,
								\phpbb\language\language $language,
								\avathar\postlove\service\forum_access $forum_access,
								$phpbb_root_path,
								$php_ext,
								$table_prefix,
								$test_time = 0) /* optional parameter only used for unit tests */
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->cache = $cache;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->template = $template;
		$this->user = $user;
		$this->language = $language;
		$this->forum_access = $forum_access;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->test_time = $test_time;
	}

	public static function getSubscribedEvents()
	{
		return array(
			'core.index_modify_page_title'			=> 'index_page_summary',
			'core.viewforum_modify_page_title'		=> 'forum_page_summary',
			'core.viewforum_modify_topics_data'		=> 'prefetch_topic_likes',
			'core.viewforum_modify_topicrow'		=> 'inject_topic_like_count',
		);
	}

	/**
	 * Build the most-liked-posts summary for the board index page.
	 *
	 * Queries across all forums the user has read access to.
	 * Skipped for bots and users who opted out via pf_postlove_hide.
	 *
	 * @param \phpbb\event\data $event The core.index_modify_page_title event
	 */
	public function  index_page_summary($event)
	{
		// first check that this user wants to see Post Like
		$this->user->get_profile_fields($this->user->data['user_id']);
		if ($this->user->data['is_bot'] || // bots dont want to see this
			!$this->auth->acl_get('u_postlove_summary') || // user group not allowed to see summary
			(isset($this->user->profile_fields['pf_postlove_hide']) && $this->user->profile_fields['pf_postlove_hide']) // user doesnt want
			)
		{
			return;
		}

		// get array of fora permissions
		$forum_read_ary = array();
		$forum_read_ary = $this->auth->acl_getf('f_read');

		$forum_ary = array();
		// build an array of forum_ids that this user may read
		foreach ($forum_read_ary as $forum_id => $allowed)
		{
			if ($allowed['f_read'])
			{
				$forum_ary[] = (int) $forum_id;
			}
		}

		// prune any duplicates
		$forum_ary = array_unique($forum_ary);

		// f_read is granted independently of a forum password; drop forums the
		// viewer's session has not unlocked, or this panel leaks topic titles,
		// subjects, authors and links from password protected forums.
		$forum_ary = $this->forum_access->drop_locked($forum_ary);

		if (!count($forum_ary))
		{
			// no need to look any further
			return;
		}

		$this->build_summary_array($forum_ary, 'index');
	}

	/**
	 * Build the most-liked-posts summary for a specific forum page.
	 *
	 * Queries the current forum plus any direct child sub-forums the user
	 * has read access to. Uses forum_data left_id/right_id to detect
	 * whether the forum has sub-forums.
	 *
	 * @param \phpbb\event\data $event The core.viewforum_modify_page_title event
	 *        Contains 'forum_id' and 'forum_data' (with left_id/right_id)
	 */
	public function  forum_page_summary($event)
	{
		// first check that this user wants to see Post Like
		$this->user->get_profile_fields($this->user->data['user_id']);
		if ($this->user->data['is_bot'] || // we dont want bots to see summaries
			 !$this->auth->acl_get('u_postlove_summary') || // user group not allowed to see summary
			 (isset($this->user->profile_fields['pf_postlove_hide']) && $this->user->profile_fields['pf_postlove_hide']) // user doesnt want
			)
		{
			return;
		}

		$forum_ary = array();
		$forum_id = $event['forum_id'];
		$forum_ary[] = $forum_id;

		// if there are sub-forums, we need to include them
		if ($event['forum_data']['left_id'] != $event['forum_data']['right_id'] - 1)
		{
			$forum_read_ary = $this->auth->acl_getf('f_read');

			$sql = 'SELECT f.forum_id
				FROM ' . FORUMS_TABLE . " f
				WHERE f.parent_id = $forum_id"; // direct children only, not recursive

			$result = $this->db->sql_query($sql);
			while ($forum_data = $this->db->sql_fetchrow($result))
			{
				// Only add child forums that are visible to this user. acl_getf() only
				// returns forums the user has ACL rows for, so a sub-forum created
				// without copying permissions is absent from the array entirely —
				// default it to "not readable" rather than indexing into null.
				if (($forum_read_ary[$forum_data['forum_id']]['f_read'] ?? 0) == 1)
				{
					$forum_ary[] = $forum_data['forum_id'];
				}
			}
			$this->db->sql_freeresult($result);

			// prune any duplicates
			$forum_ary = array_unique($forum_ary);
		}

		// The current forum already passed core's password gate to be viewable
		// here, but a sub-forum can carry its own separate password the viewer
		// has not unlocked; drop any locked forum before it reaches the query.
		$forum_ary = $this->forum_access->drop_locked($forum_ary);

		$this->build_summary_array($forum_ary, 'forum');
	}


	/**
	 * Build the summary across all configured time periods.
	 *
	 * Calls topposts_of_period() for each period (ever, year, month, week, today)
	 * based on the ACP config settings. Each call excludes posts already shown
	 * in prior periods to avoid duplicates.
	 *
	 * Sets S_MOSTLIKEDSUMMARYCOUNT template var (total posts across all periods).
	 *
	 * @param array  $forum_ary  Forum IDs to include in the query
	 * @param string $page_type  'index' or 'forum' (determines which config keys to use)
	 */
	protected function build_summary_array($forum_ary, $page_type)
	{

		$post_list = array();
		$post_list[] = '0'; // Seed value so NOT IN clause is never empty

		// build the array of most liked posts
		$day_begin_time = (int) floor(($this->test_time ? $this->test_time : time()) / self::SECONDS_PER_DAY) * self::SECONDS_PER_DAY;
		$post_list = $this->topposts_of_period($forum_ary, $this->config['postlove_' . $page_type . '_most_liked_ever'],		2,										'LIKES_EVER',		$post_list);
		$post_list = $this->topposts_of_period($forum_ary, $this->config['postlove_' . $page_type . '_most_liked_this_year'],	$day_begin_time - self::SECONDS_PER_DAY * 366, 'LIKES_THIS_YEAR',	$post_list);
		$post_list = $this->topposts_of_period($forum_ary, $this->config['postlove_' . $page_type . '_most_liked_this_month'],	$day_begin_time - self::SECONDS_PER_DAY * 31,	'LIKES_THIS_MONTH', $post_list);
		$post_list = $this->topposts_of_period($forum_ary, $this->config['postlove_' . $page_type . '_most_liked_this_week'],	$day_begin_time - self::SECONDS_PER_DAY * 7,	'LIKES_THIS_WEEK',	$post_list);
		$post_list = $this->topposts_of_period($forum_ary, $this->config['postlove_' . $page_type . '_most_liked_today'],		$day_begin_time - self::SECONDS_PER_DAY,		'LIKES_TODAY',		$post_list);

		$this->template->assign_vars(array(
			'S_MOSTLIKEDSUMMARYCOUNT'	=>  count($post_list) - 1,
			'S_POSTLOVE_SUMMARY_BELOW'	=>  (int) $this->config['postlove_summary_position'],
			));
	}

	/**
	 * Query the top liked posts for a specific time period.
	 *
	 * Uses a raw SQL query with subqueries to:
	 * 1. Aggregate like counts per post within the period (inner subquery)
	 * 2. Join with posts table and filter by content visibility (middle subquery)
	 * 3. Join with topics, users, forums for display data (outer query)
	 *
	 * Results are cached for 12 hours. Posts already shown in prior periods
	 * (passed via $post_list) are excluded via NOT IN.
	 *
	 * Fires the avathar.postlove.modify_summary_tpl_ary event to allow
	 * other extensions to modify the template data before rendering.
	 *
	 * @param array  $forum_ary        Forum IDs to include
	 * @param int    $howmany          Max posts to show for this period (0 = skip)
	 * @param int    $period_start_time Unix timestamp for the start of the period
	 * @param string $period_name      Language key for the period label (e.g. 'LIKES_TODAY')
	 * @param array  $post_list        Post IDs already shown (excluded from results)
	 * @return array Updated post_list with newly shown post IDs appended
	 */
	protected function topposts_of_period($forum_ary, $howmany, $period_start_time, $period_name, $post_list)
	{
		if ($howmany == 0)
		{
			// configuration says we don't need to look for any in this period
			return $post_list;
		}

		// Deliberately two statements rather than one nested query. phpBB's MSSQL
		// driver applies a row limit by regex-injecting TOP into every SELECT it
		// finds, with no count argument, so running the nested form through
		// sql_query_limit() truncated the inner aggregate before the outer
		// ORDER BY sum_likes DESC could apply — the panel then showed arbitrary
		// posts rather than the most liked ones, silently, because TOP without
		// ORDER BY in a subquery is valid T-SQL.
		//
		// Both queries below are a single SELECT, so nothing can be rewritten in
		// the wrong place. The aggregate is deliberately board wide with no
		// forum filter, so its 12 hour cache is shared by every viewer;
		// visibility and the viewer's forum set are applied afterwards, on the
		// display query.
		//
		// Being board wide, a single page can be entirely posts a given viewer
		// can't read; page through the aggregate, applying the display query to
		// each page, until enough visible posts are found or the period is
		// exhausted, bounded by MAX_AGGREGATE_ROUNDS.
		$page_size = $howmany * self::AGGREGATE_OVERFETCH;
		$rowset = array();

		for ($round = 0; $round < self::MAX_AGGREGATE_ROUNDS; $round++)
		{
			// 1. Aggregate the likes for the period; post_id is a tiebreaker on
			// otherwise-equal sum_likes, keeping the paging stable across rounds.
			$sql = 'SELECT post_id AS post, COUNT(*) AS sum_likes
				FROM ' . $this->table_prefix . 'posts_likes
				WHERE liketime > ' . (int) $period_start_time . '
					AND post_id NOT IN (' . implode(',', $post_list) . ')
				GROUP BY post_id
				ORDER BY sum_likes DESC, post_id ASC';
			$result = $this->db->sql_query_limit($sql, $page_size, $round * $page_size, (self::SECONDS_PER_HOUR * 12) - 1);

			$sum_likes = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sum_likes[(int) $row['post']] = (int) $row['sum_likes'];
			}
			$this->db->sql_freeresult($result);

			if (empty($sum_likes))
			{
				// Exhausted the period; a further page can only be empty too.
				break;
			}

			// 2. Fetch display data for this page, applying content visibility
			// and the viewer's forum set here. Not cached, unlike the
			// aggregate above: this query does not reference posts_likes, so
			// cache->destroy('sql', $likes_table) on a like toggle could never
			// invalidate it, and it is a keyed lookup of a few post ids.
			$sql = 'SELECT u.user_id, u.username, u.user_colour,
				t.topic_title, t.forum_id, t.topic_id, t.topic_type,
				p.post_id, p.post_time,
				f.forum_name
				FROM ' . POSTS_TABLE . ' p
				LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id
				LEFT JOIN ' . USERS_TABLE . ' u ON p.poster_id = u.user_id
				LEFT JOIN ' . FORUMS_TABLE . ' f ON t.forum_id = f.forum_id
				WHERE ' . $this->db->sql_in_set('p.post_id', array_keys($sum_likes)) . '
					AND ' . $this->content_visibility->get_forums_visibility_sql('post', $forum_ary, 'p.') . '
					AND t.topic_status <> ' . ITEM_MOVED;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$row['sum_likes'] = $sum_likes[(int) $row['post_id']];
				$rowset[] = $row;
			}
			$this->db->sql_freeresult($result);

			if (count($rowset) >= $howmany || count($sum_likes) < $page_size)
			{
				// Enough visible posts, or the aggregate came up short of a full
				// page; a further page would be empty too.
				break;
			}
		}

		// Order and trim in PHP. The ordering has to happen after the visibility
		// filter has removed rows, and doing it here keeps both statements to a
		// single SELECT each.
		usort($rowset, function ($a, $b)
		{
			return ($b['sum_likes'] <=> $a['sum_likes']) ?: ((int) $b['post_time'] <=> (int) $a['post_time']);
		});
		$rowset = array_slice($rowset, 0, $howmany);

		$forums = $topic_ids = array();
		foreach ($rowset as $row)
		{
			$post_list[] = $row['post_id'];
			$topic_ids[] = $row['topic_id'];
			$forums[$row['forum_id']][] = $row['topic_id'];
		}

		// Get topic tracking
		$topic_tracking_info = array();
		foreach ($forums as $forum_id => $topic_id)
		{
			$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $topic_id);
		}

		foreach ($rowset as $row)
		{
			$topic_id = $row['topic_id'];
			$forum_id = $row['forum_id'];
			$forum_name = $row['forum_name'];

			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;
			$view_post_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);
			$forum_name_url = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']);
			$topic_title = censor_text($row['topic_title']);
			$is_guest = ($row['user_id'] == ANONYMOUS) ? true : false;

			$tpl_ary = array(
				'U_TOPIC'   		=> $view_post_url,
				'U_FORUM'   		=> $forum_name_url,
				'S_UNREAD'  		=> $post_unread,
				'USERNAME_FULL' 	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'POST_TIME' 		=> $this->user->format_date($row['post_time']),
				'TOPIC_TITLE'   	=> $topic_title,
				'FORUM_NAME'		=> $forum_name,
				'POST_LIKES_IN_PERIOD'  => $this->language->lang($period_name, $row['sum_likes'] +0),
				'LIKES_IN_PERIOD'   => $row['sum_likes'] + 0,
			);
			/**
			* Modify the topic data before it is assigned to the template
			*
			* @event avathar.postlove.modify_summary_tpl_ary
			* @var  array   row 		Array with topic data
			* @var  array   tpl_ary 	Template block array with topic data
			* @since 2.2.2
			*/
			$vars = array('row', 'tpl_ary');
			extract($this->dispatcher->trigger_event('avathar.postlove.modify_summary_tpl_ary', compact($vars)));

			$this->template->assign_block_vars('most_liked_posts', $tpl_ary);
		}

		return $post_list;
	}

	/**
	 * Prefetch total like counts for all topics on the current viewforum page.
	 *
	 * Runs a single aggregate query joining posts_likes with posts to get
	 * the total like count per topic. Results are stored in $topic_like_counts
	 * and read by inject_topic_like_count() for each topic row.
	 *
	 * @param \phpbb\event\data $event The core.viewforum_modify_topics_data event
	 *        Contains 'topic_list' (array of topic IDs on the current page)
	 */
	public function prefetch_topic_likes($event)
	{
		$topic_list = $event['topic_list'];
		if (empty($topic_list))
		{
			return;
		}

		$sql = 'SELECT p.topic_id, COUNT(pl.post_id) AS like_count
			FROM ' . $this->table_prefix . 'posts_likes pl
			JOIN ' . POSTS_TABLE . ' p ON p.post_id = pl.post_id
			WHERE ' . $this->db->sql_in_set('p.topic_id', $topic_list) . '
			GROUP BY p.topic_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->topic_like_counts[(int) $row['topic_id']] = (int) $row['like_count'];
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Inject the like count into each topic row on the viewforum page.
	 *
	 * Reads from the prefetched $topic_like_counts array and sets
	 * TOPIC_LIKE_COUNT in the topic row template data. The template
	 * (topiclist_row_append.html) shows a heart icon + count when > 0.
	 *
	 * Gated like the two most-liked summary panels: bots, missing
	 * u_postlove_summary, and pf_postlove_hide all leave TOPIC_LIKE_COUNT
	 * unset, which the template's IF already treats as falsy.
	 *
	 * @param \phpbb\event\data $event The core.viewforum_modify_topicrow event
	 *        Contains 'row' (raw topic data) and 'topic_row' (template data)
	 */
	public function inject_topic_like_count($event)
	{
		$this->user->get_profile_fields($this->user->data['user_id']);
		if ($this->user->data['is_bot'] ||
			!$this->auth->acl_get('u_postlove_summary') ||
			(isset($this->user->profile_fields['pf_postlove_hide']) && $this->user->profile_fields['pf_postlove_hide']))
		{
			return;
		}

		$topic_id = (int) $event['row']['topic_id'];
		$count = $this->topic_like_counts[$topic_id] ?? 0;

		$topic_row = $event['topic_row'];
		$topic_row['TOPIC_LIKE_COUNT'] = $count;
		$event['topic_row'] = $topic_row;
	}
}
