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
	protected \phpbb\request\request $request;
	protected notifyhelper $notifyhelper;
	protected string $likes_table;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\language\language $language, \phpbb\cache\service $cache,
								\phpbb\request\request $request, \avathar\postlove\controller\notifyhelper $notifyhelper,
								$likes_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->language = $language;
		$this->cache = $cache;
		$this->request = $request;
		$this->notifyhelper = $notifyhelper;
		$this->likes_table = $likes_table;
	}

	/**
	 * Handle the like toggle action.
	 *
	 * @param string $action The action to perform ('toggle')
	 * @param int    $post   The post ID to like/unlike
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @throws \phpbb\exception\http_exception 404 if $action is not a known action
	 */
	public function base ($action, $post)
	{
		switch ($action)
		{
			case 'toggle':
				if ($this->user->data['user_id'] == ANONYMOUS || !$this->auth->acl_get('u_postlove'))
				{
					return new \Symfony\Component\HttpFoundation\JsonResponse(array(
						'error'	=> 1
					));
				}
				else
				{
					// The toggle is a state-changing GET with no payload, so without a
					// link hash an <img src> on any page silently likes or unlikes the
					// post for every logged-in visitor holding u_postlove. Checked here,
					// ahead of the lookup, so nothing is read or written before the
					// request is shown to have originated from the board.
					if (!check_link_hash($this->request->variable('hash', ''), 'postlove_' . (int) $post))
					{
						return new \Symfony\Component\HttpFoundation\JsonResponse(array(
							'error'	=> 1
						));
					}

					//get state for the like
					$sql_array = array(
						'SELECT'	=> 'pl.liketime as liketime, pl.user_id as liker_id, p.forum_id as forum_id, p.topic_id as topic_id, p.poster_id as poster, p.post_subject as post_subject',
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

					// u_postlove is a global permission and $post comes straight from the
					// URL, so the post's forum has to be checked separately. Without this
					// the toggle would write a like against a post in a forum the caller
					// cannot read, notify its author, and hand back its liker list.
					if (!$this->auth->acl_get('f_read', (int) $row['forum_id']))
					{
						return new \Symfony\Component\HttpFoundation\JsonResponse(array(
							'error'	=> 1
						));
					}

					if (!$row['liketime'])
					{
						// No record for this user loving this post yet — insert one.
						// topic_id, post_subject and poster (aliased poster_id) are already in $row.
						$insert_data = array(
							'post_id'		=> (int) $post,
							'user_id'		=> (int) $this->user->data['user_id'],
							'type'			=> 'post',
							'liketime'		=> time(),
							'liked_user_id'	=> (int) $row['poster'],
						);
						$sql = 'INSERT INTO ' . $this->likes_table . ' ' . $this->db->sql_build_array('INSERT', $insert_data);
						$this->db->sql_query($sql);
						$this->cache->destroy('sql', $this->likes_table);
						$this->notifyhelper->notify('add', (int) $row['topic_id'], (int) $post, $row['post_subject'], (int) $row['poster'], (int) $this->user->data['user_id']);
						return new \Symfony\Component\HttpFoundation\JsonResponse(array(
							'toggle_action'	=> 'add',
							'toggle_post'	=> $post,
							'toggle_title'	=> $this->language->lang('CLICK_TO_UNLIKE'),
							'toggle_likers'	=> $this->get_likers_string((int) $post),
						));
					}

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
			break;
		}
		// 'toggle' is the only known action, but {action} carries no route
		// requirement, so any segment reaches this method. Returning an int here
		// left the kernel with nothing to render — "The controller must return a
		// response (0 given)", an untranslated framework string served as HTTP 500.
		throw new \phpbb\exception\http_exception(404, 'PAGE_NOT_FOUND');
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
			WHERE pl.post_id = ' . (int) $post_id . '
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
