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
 * The summary query uses raw SQL with subqueries for performance (aggregating
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

	protected \phpbb\auth\auth $auth;
	protected \phpbb\config\config $config;
	protected \phpbb\cache\service $cache;
	protected \phpbb\content_visibility $content_visibility;
	protected \phpbb\db\driver\driver_interface $db;
	protected \phpbb\event\dispatcher_interface $dispatcher;
	protected \phpbb\template\template $template;
	protected \phpbb\user $user;
	protected \phpbb\language\language $language;
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
				// ony add forums that are visible to this user
				if ($forum_read_ary[$forum_id]['f_read'] == 1)
				{
					$forum_ary[] = $forum_data['forum_id'];
				}
			}
			$this->db->sql_freeresult($result);

			// prune any duplicates
			$forum_ary = array_unique($forum_ary);
		}
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
	function build_summary_array($forum_ary, $page_type)
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
	function topposts_of_period($forum_ary, $howmany, $period_start_time, $period_name, $post_list)
	{
		if ($howmany == 0)
		{
			// configuration says we don't need to look for any in this period
			return $post_list;
		}

		// find all the visible, liked posts in the given period
		$sql = 'SELECT '. USERS_TABLE . '.user_id, '. USERS_TABLE . '.username, '. USERS_TABLE . '.user_colour,
			' . TOPICS_TABLE . '.topic_title, ' . TOPICS_TABLE . '.forum_id, ' . TOPICS_TABLE . '.topic_id,
			most_liked_posts.post_id, most_liked_posts.post_time, ' . TOPICS_TABLE . '.topic_type,
			' . FORUMS_TABLE . '.forum_name, sum_likes
			FROM (
				SELECT ' . POSTS_TABLE . '.forum_id, ' . POSTS_TABLE . '.post_id, ' . POSTS_TABLE . '.post_time, ' . POSTS_TABLE . '.topic_id, ' . POSTS_TABLE . '.poster_id, sum_likes
				FROM(
					SELECT post_id AS post, COUNT(*) AS sum_likes
					FROM ' . $this->table_prefix . 'posts_likes
						WHERE ' . $this->table_prefix . 'posts_likes.liketime > ' . $period_start_time . '
						AND post_id NOT IN (' . implode(",", $post_list) . ')
						GROUP BY post_id
					) AS liked_posts
			LEFT JOIN ' . POSTS_TABLE .   ' ON post = post_id
			WHERE  ' . $this->content_visibility->get_forums_visibility_sql('post', $forum_ary, POSTS_TABLE .'.') . '
			)AS most_liked_posts
		LEFT JOIN ' . TOPICS_TABLE .  ' ON most_liked_posts.topic_id = '  . TOPICS_TABLE . '.topic_id
		LEFT JOIN ' . USERS_TABLE .   ' ON most_liked_posts.poster_id = ' . USERS_TABLE .  '.user_id
		LEFT JOIN ' . FORUMS_TABLE .  ' ON ' . TOPICS_TABLE . '.forum_id = '  . FORUMS_TABLE . '.forum_id
		WHERE topic_status <> ' . ITEM_MOVED . '
		ORDER BY sum_likes DESC, post_time DESC';

		// cache the query to reduce load on server
		// the same query is run for all users with the same set of forum permissions
		// Note: cache is cleared each time a user adds or removes a like in the database
		$result = $this->db->sql_query_limit($sql, $howmany, 0, (self::SECONDS_PER_HOUR * 12) - 1);

		$forums = $topic_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
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

		$this->db->sql_rowseek(0, $result);
		while ($row = $this->db->sql_fetchrow($result))
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
		$this->db->sql_freeresult($result);
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
	 * @param \phpbb\event\data $event The core.viewforum_modify_topicrow event
	 *        Contains 'row' (raw topic data) and 'topic_row' (template data)
	 */
	public function inject_topic_like_count($event)
	{
		$topic_id = (int) $event['row']['topic_id'];
		$count = $this->topic_like_counts[$topic_id] ?? 0;

		$topic_row = $event['topic_row'];
		$topic_row['TOPIC_LIKE_COUNT'] = $count;
		$event['topic_row'] = $topic_row;
	}
}
