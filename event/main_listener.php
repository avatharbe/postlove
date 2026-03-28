<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Main event listener for the Post Love extension.
 *
 * Handles the core like functionality on viewtopic pages:
 * - Registers the u_postlove permission
 * - Batch-prefetches like data for all posts on a page (avoids N+1 queries)
 * - Injects the heart button, like count, and tooltip into each post row
 * - Shows likes given/received counters in user mini profiles
 * - Adds a love list link to member profile pages
 * - Cleans up orphan likes when posts or users are deleted
 */
class main_listener implements EventSubscriberInterface
{
	protected \phpbb\auth\auth $auth;
	protected \phpbb\config\config $config;
	protected \phpbb\db\driver\driver_interface $db;
	protected \phpbb\template\template $template;
	protected \phpbb\user $user;
	protected \phpbb\language\language $language;
	protected \phpbb\controller\helper $helper;
	protected string $loves_table;

	/** @var array Prefetched likers per post_id: [post_id => [user_id => username]] */
	protected array $post_likers = [];

	/** @var array Prefetched "likes given" count per user_id */
	protected array $user_likes_given = [];

	/** @var array Prefetched "likes received" count per user_id */
	protected array $user_likes_received = [];

	public static function getSubscribedEvents()
	{
		return array(
			'core.permissions'					=> 'add_permissions',
			'core.viewtopic_modify_post_data'	=> 'prefetch_likes',
			'core.viewtopic_modify_post_row'	=> 'modify_post_row',
			'core.user_setup'					=> 'load_language_on_setup',
			'core.memberlist_view_profile'		=> 'user_profile_likes',
			'core.delete_posts_after'			=> 'clean_posts_after',
			'core.delete_user_after'			=> 'clean_users_after',
		);
	}

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user,
	\phpbb\language\language $language,
	\phpbb\controller\helper $helper,
	$loves_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->language = $language;
		$this->helper = $helper;
		$this->loves_table = $loves_table;
	}

	/**
	 * Load the postlove language file on every page.
	 *
	 * @param \phpbb\event\data $event The core.user_setup event
	 */
	public function load_language_on_setup($event)
	{
		$this->language->add_lang('postlove', 'avathar/postlove');
	}

	/**
	 * Register the u_postlove permission in phpBB's ACL system.
	 *
	 * @param \phpbb\event\data $event The core.permissions event
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_postlove'] = array('lang' => 'ACL_U_POSTLOVE', 'cat' => 'misc');
		$event['permissions'] = $permissions;
	}

	/**
	 * Prefetch all like data for posts on the current viewtopic page.
	 *
	 * Runs 1-3 batch queries to populate class-level caches:
	 * - $post_likers: all likers per post (for tooltip + current user check)
	 * - $user_likes_given: total likes given per poster (for mini profile)
	 * - $user_likes_received: total likes received per poster (for mini profile)
	 *
	 * This replaces the original N+1 approach (3 queries per post) with at most
	 * 3 queries total regardless of how many posts are on the page.
	 *
	 * @param \phpbb\event\data $event The core.viewtopic_modify_post_data event
	 *        Contains 'post_list' (array of post IDs) and 'rowset' (post data)
	 */
	public function prefetch_likes($event)
	{
		$post_list = $event['post_list'];
		if (empty($post_list))
		{
			return;
		}

		// Query 1: all likers for all posts on this page
		$sql = 'SELECT pl.post_id, pl.user_id, u.username
			FROM ' . $this->loves_table . ' pl
			JOIN ' . USERS_TABLE . ' u ON u.user_id = pl.user_id
			WHERE ' . $this->db->sql_in_set('pl.post_id', $post_list) . '
			ORDER BY pl.liketime ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->post_likers[(int) $row['post_id']][(int) $row['user_id']] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		// Collect unique poster user_ids from the rowset
		$user_ids = [];
		foreach ($event['rowset'] as $row)
		{
			if ($row['user_id'] != ANONYMOUS)
			{
				$user_ids[] = (int) $row['user_id'];
			}
		}
		$user_ids = array_unique($user_ids);

		if (empty($user_ids))
		{
			return;
		}

		// Query 2: likes given count per user
		if ($this->config['postlove_show_likes'])
		{
			$sql = 'SELECT user_id, COUNT(post_id) as cnt
				FROM ' . $this->loves_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . '
				GROUP BY user_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->user_likes_given[(int) $row['user_id']] = (int) $row['cnt'];
			}
			$this->db->sql_freeresult($result);
		}

		// Query 3: likes received count per user
		if ($this->config['postlove_show_liked'])
		{
			$sql = 'SELECT liked_user_id, COUNT(post_id) as cnt
				FROM ' . $this->loves_table . '
				WHERE ' . $this->db->sql_in_set('liked_user_id', $user_ids) . '
				GROUP BY liked_user_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->user_likes_received[(int) $row['liked_user_id']] = (int) $row['cnt'];
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	 * Inject heart button, like count, and tooltip into each post row.
	 *
	 * Uses prefetched data from prefetch_likes(). Sets template variables:
	 * - POST_LIKERS: tooltip text ("post liked by: user1, user2")
	 * - POST_LIKERS_COUNT: number of likes on this post
	 * - POST_LIKE_CLASS: 'liked' (filled heart) or 'like' (outline heart)
	 * - POST_LIKE_URL: AJAX toggle URL
	 * - ACTION_ON_CLICK: tooltip action text
	 * - DISABLE: set to 1 if user cannot like (no permission or own post)
	 * - USER_LIKES: likes given count for the poster (mini profile)
	 * - USER_LIKED: likes received count for the poster (mini profile)
	 *
	 * Respects the pf_postlove_hide custom profile field (user opt-out).
	 *
	 * @param \phpbb\event\data $event The core.viewtopic_modify_post_row event
	 */
	public function modify_post_row($event)
	{
		$this->user->get_profile_fields($this->user->data['user_id']);
		if (!(isset($this->user->profile_fields['pf_postlove_hide']) && $this->user->profile_fields['pf_postlove_hide']))
		{
			$post_id = (int) $event['row']['post_id'];
			$likers = $this->post_likers[$post_id] ?? [];
			$current_user_has_liked = isset($likers[$this->user->data['user_id']]);

			$post_row = $event['post_row'];
			if (!empty($likers))
			{
				$post_likers = implode(', ', $likers);
				$post_row['POST_LIKERS'] = $this->language->lang('LIKED_BY') . $post_likers;
				$post_row['POST_LIKERS_COUNT'] = count($likers);
				$post_row['POST_LIKE_CLASS'] = $current_user_has_liked ? 'liked' : 'like';
				$post_row['ACTION_ON_CLICK'] = $current_user_has_liked ? $this->language->lang('CLICK_TO_UNLIKE') : $this->language->lang('CLICK_TO_LIKE');
			}
			else
			{
				$post_row['POST_LIKERS_COUNT'] = '0';
				$post_row['POST_LIKE_CLASS'] = 'like';
				$post_row['ACTION_ON_CLICK'] = $this->language->lang('CLICK_TO_LIKE');
			}
			$post_row['POST_LIKE_URL'] = $this->helper->route('avathar_postlove_control', array('action' => 'toggle', 'post' => $post_id));
			$event['post_row'] = $post_row;

			$this->template->assign_var('SHOW_BUTTON', $this->config['postlove_show_button']);
			$this->template->assign_var('SHOW_USER_LIKES', $this->config['postlove_show_likes']);
			$this->template->assign_var('SHOW_USER_LIKED', $this->config['postlove_show_liked']);
			$this->template->assign_var('IS_POSTROW', '1');

			if (!$this->config['postlove_author_like'] && $event['poster_id'] == $this->user->data['user_id'])
			{
				$post_row = $event['post_row'];
				$post_row['DISABLE'] = 1;
				$post_row['ACTION_ON_CLICK'] = $this->language->lang('CANT_LIKE_OWN_POST');
				$event['post_row'] = $post_row;
			}
			if (!$this->auth->acl_get('u_postlove'))
			{
				$post_row = $event['post_row'];
				$post_row['DISABLE'] = 1;
				$post_row['ACTION_ON_CLICK'] = $this->language->lang('LOGIN_TO_LIKE_POST');
				$event['post_row'] = $post_row;
			}
		}

		// Show likes given/received in mini profile (using prefetched data)
		if ($event['row']['user_id'] != ANONYMOUS)
		{
			$poster_id = (int) $event['row']['user_id'];
			if ($this->config['postlove_show_likes'])
			{
				$post_row = $event['post_row'];
				$post_row['USER_LIKES'] = $this->user_likes_given[$poster_id] ?? 0;
				$event['post_row'] = $post_row;
			}
			if ($this->config['postlove_show_liked'])
			{
				$post_row = $event['post_row'];
				$post_row['USER_LIKED'] = $this->user_likes_received[$poster_id] ?? 0;
				$event['post_row'] = $post_row;
			}
		}
	}

	/**
	 * Add the love list link to the member profile statistics section.
	 *
	 * Sets the POSTLOVE_STATS template var with the URL to the user's love
	 * list page, opened as a modal popup via jQuery modal.
	 *
	 * @param \phpbb\event\data $event The core.memberlist_view_profile event
	 */
	public function user_profile_likes($event)
	{
		$this->user->get_profile_fields($this->user->data['user_id']);
		if (!(isset($this->user->profile_fields['pf_postlove_hide']) && $this->user->profile_fields['pf_postlove_hide']))
		{
			$this->template->assign_var('POSTLOVE_STATS', $this->helper->route('avathar_postlove_list', array('user_id' => $event['member']['user_id'])) . '?short=1');
		}
	}

	/**
	 * Remove likes referencing permanently deleted posts.
	 *
	 * @param \phpbb\event\data $event The core.delete_posts_after event
	 */
	public function clean_posts_after($event)
	{
		$sql = 'DELETE FROM ' . $this->loves_table . ' WHERE ' . $this->db->sql_in_set('post_id', $event['post_ids']);
		$this->db->sql_query($sql);
	}

	/**
	 * Remove likes given by permanently deleted users.
	 *
	 * @param \phpbb\event\data $event The core.delete_user_after event
	 */
	public function clean_users_after($event)
	{
		$sql = 'DELETE FROM ' . $this->loves_table . ' WHERE ' . $this->db->sql_in_set('user_id', $event['user_ids']);
		$this->db->sql_query($sql);
	}
}
