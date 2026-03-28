<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\controller;

/**
 * AJAX controller for the like/unlike toggle.
 *
 * Handles the POST /postlove/toggle/{post_id} route. Returns a JsonResponse
 * with toggle_action (add/remove), toggle_title (tooltip text), and
 * toggle_likers (updated likers string) on success, or {error: 1} on failure.
 *
 * Permission checks:
 * - User must have the u_postlove ACL permission
 * - Author self-like is controlled by the postlove_author_like config setting
 * - Post must exist
 */
class ajaxify
{
	protected \phpbb\auth\auth $auth;
	protected \phpbb\config\config $config;
	protected \phpbb\db\driver\driver_interface $db;
	protected \phpbb\user $user;
	protected \phpbb\language\language $language;
	protected \phpbb\cache\service $cache;
	protected notifyhelper $notifyhelper;
	protected string $likes_table;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\language\language $language, \phpbb\cache\service $cache, \avathar\postlove\controller\notifyhelper $notifyhelper,
								$likes_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->language = $language;
		$this->cache = $cache;
		$this->notifyhelper = $notifyhelper;
		$this->likes_table = $likes_table;
	}

	/**
	 * Handle the like toggle action.
	 *
	 * @param string $action The action to perform ('toggle')
	 * @param int    $post   The post ID to like/unlike
	 * @return \Symfony\Component\HttpFoundation\JsonResponse|int
	 */
	public function base ($action, $post)
	{
		switch ($action)
		{
			case 'toggle':
				if (!$this->auth->acl_get('u_postlove'))
				{
					return new \Symfony\Component\HttpFoundation\JsonResponse(array(
						'error'	=> 1
					));
				}
				else
				{
					//get state for the like
					$sql_array = array(
						'SELECT'	=> 'pl.liketime as liketime, pl.user_id as liker_id, p.topic_id as topic_id, p.poster_id as poster, p.post_subject as post_subject',
						'FROM'	=> array(
							POSTS_TABLE	=> 'p',
						),
						'LEFT_JOIN'	=> array(
							array(
								'FROM'	=> array($this->likes_table	=> 'pl'),
								'ON'	=> 'pl.post_id = p.post_id AND pl.user_id = ' . $this->user->data['user_id']
							),
						),
						'WHERE'	=> 'p.post_id = ' . (int) $post
					);
					$sql = $this->db->sql_build_query('SELECT', $sql_array);
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
					if (!$row || (!$this->config['postlove_author_like'] && $row['poster'] == $this->user->data['user_id']))
					{
						return new \Symfony\Component\HttpFoundation\JsonResponse(array(
							'error'	=> 1
						));
					}

					else
					{
						if (!$row['liketime'])
						{
							//so we don't have record for this user loving this post ... give some love!
							$sql = 'SELECT topic_id, poster_id, post_subject FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post;
							$result = $this->db->sql_query($sql);
							$row1 = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							$sql = 'INSERT INTO ' . $this->likes_table . ' (post_id, user_id, type, liketime, liked_user_id) VALUES (' . (int) $post . ', ' . $this->user->data['user_id'] . ', \'post\', ' . time() . ', ' . $row1['poster_id'] . ')';
							$result = $this->db->sql_query($sql);
							$this->db->sql_freeresult($result);
							$this->cache->destroy('sql', $this->likes_table);
							$this->notifyhelper->notify('add', $row1['topic_id'], (int) $post, $row1['post_subject'], $row1['poster_id'] , $this->user->data['user_id']);
							return new \Symfony\Component\HttpFoundation\JsonResponse(array(
								'toggle_action'	=> 'add',
								'toggle_post'	=> $post,
								'toggle_title'	=> $this->language->lang('CLICK_TO_UNLIKE'),
								'toggle_likers'	=> $this->get_likers_string((int) $post),
							));
						}
						else
						{
							//so we have a record ... and the user don't love it anymore!
							$sql = 'DELETE FROM ' . $this->likes_table . ' WHERE post_id = ' . (int) $post . ' AND user_id = ' . (int) $this->user->data['user_id'];
							$result = $this->db->sql_query($sql);
							$this->db->sql_freeresult($result);
							$this->cache->destroy('sql', $this->likes_table);
							$this->notifyhelper->notify('remove', $row['topic_id'], (int) $post, $row['post_subject'], $row['poster'], $this->user->data['user_id']);
							return new \Symfony\Component\HttpFoundation\JsonResponse(array(
								'toggle_action' => 'remove',
								'toggle_post'	=> $post,
								'toggle_likers'	=> $this->get_likers_string((int) $post),
								'toggle_title'	=> $this->language->lang('CLICK_TO_LIKE'),
							));
						}
					}
				}
			break;
		}
		// Fallback for unhandled actions
		return 0;
	}

	/**
	 * Build the "liked by: user1, user2" tooltip string for a post.
	 *
	 * @param int $post_id The post ID
	 * @return string The formatted likers string, or empty if no likes
	 */
	protected function get_likers_string(int $post_id): string
	{
		$sql = 'SELECT u.username
			FROM ' . $this->likes_table . ' pl
			JOIN ' . USERS_TABLE . ' u ON u.user_id = pl.user_id
			WHERE pl.post_id = ' . $post_id . '
			ORDER BY pl.liketime ASC';
		$result = $this->db->sql_query($sql);
		$likers = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$likers[] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		if (empty($likers))
		{
			return '';
		}
		return $this->language->lang('LIKED_BY') . implode(', ', $likers);
	}
}
