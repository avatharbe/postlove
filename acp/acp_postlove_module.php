<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2013 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\acp;

/**
 * ACP module for Post Love settings.
 *
 * Provides the admin interface for configuring:
 * - Mini profile counters (likes given / likes received)
 * - Post button display mode
 * - Author self-like permission
 * - Most-liked summary periods (today/week/month/year/ever) for index and forum pages
 * - Orphan like cleanup (removes likes referencing deleted posts/users)
 * - Thanks for Posts import tool
 *
 * Note: phpBB ACP modules are not DI services — they are instantiated directly
 * by phpBB's module system. Services are obtained from $phpbb_container.
 */
class acp_postlove_module
{
	public string $tpl_name;
	public string $page_title;
	public string $u_action;

	/**
	 * Main ACP module handler.
	 *
	 * Processes form submissions (settings save, orphan cleanup, thanks import)
	 * and renders the settings template with current config values.
	 *
	 * @param int    $id   Module ID
	 * @param string $mode Module mode ('main')
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\config\config $config */
		$config = $phpbb_container->get('config');
		/** @var \phpbb\db\driver\driver_interface $db */
		$db = $phpbb_container->get('dbal.conn');
		/** @var \phpbb\template\template $template */
		$template = $phpbb_container->get('template');
		/** @var \phpbb\request\request $request */
		$request = $phpbb_container->get('request');
		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		$likes_table = $phpbb_container->getParameter('tables.avathar.postlove');

		$this->tpl_name = 'acp_postlove';
		$this->page_title = 'ACP_POSTLOVE';

		if ($request->is_set_post('submit'))
		{
			$postlove = $request->variable('poslove', array('' => ''));
			foreach ($postlove as $key => $var)
			{
				$config->set($key, $var);
			}
			trigger_error($language->lang('CONFIRM_MESSAGE', $this->u_action));
		}

		if ($request->variable('clean', false))
		{
			if (confirm_box(true))
			{
				// Clean post loves that reference deleted posts
				$sql_ary = array(
					'SELECT'	=> 'pl.post_id as post_id',
					'FROM'		=> array($likes_table => 'pl'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(POSTS_TABLE => 'p'),
							'ON'	=> 'pl.post_id = p.post_id'
						)
					),
					'WHERE'	=> 'p.post_id IS NULL'
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$delete_post_likes = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_post_likes[] = $row['post_id'];
				}
				$db->sql_freeresult($result);
				if (!empty($delete_post_likes))
				{
					$sql = 'DELETE FROM ' . $likes_table . ' WHERE ' . $db->sql_in_set('post_id', $delete_post_likes);
					$db->sql_query($sql);
				}

				// Clean post loves that reference deleted users
				$sql_ary = array(
					'SELECT'	=> 'pl.user_id as user_id',
					'FROM'		=> array($likes_table => 'pl'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(USERS_TABLE => 'u'),
							'ON'	=> 'pl.user_id = u.user_id'
						)
					),
					'WHERE'	=> 'u.user_id IS NULL'
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$delete_user_likes = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_user_likes[] = $row['user_id'];
				}
				$db->sql_freeresult($result);
				if (!empty($delete_user_likes))
				{
					$sql = 'DELETE FROM ' . $likes_table . ' WHERE ' . $db->sql_in_set('user_id', $delete_user_likes);
					$db->sql_query($sql);
				}
			}
			else
			{
				confirm_box(false, $language->lang('CONFIRM_OPERATION'), build_hidden_fields(array('clean' => true)));
			}
		}

		if ($request->variable('import', false))
		{
			if (confirm_box(true))
			{
				// Import thanks from the Thanks for Posts extension
				$sql = 'INSERT INTO ' . $likes_table . ' (post_id, user_id, liked_user_id, liketime)
					SELECT t.post_id, t.user_id, t.poster_id as liked_user_id, t.thanks_time as liketime
					FROM ' . $phpbb_container->getParameter('core.table_prefix') . 'thanks AS t
					LEFT JOIN ' . $likes_table . ' as l
					ON t.user_id = l.user_id
					AND t.post_id = l.post_id
					WHERE l.post_id IS NULL';
				$db->sql_query($sql);
			}
			else
			{
				confirm_box(false, $language->lang('CONFIRM_OPERATION'), build_hidden_fields(array('import' => true)));
			}
		}

		// Check for Thanks for Posts data available to import
		$thanks_to_convert = 0;
		/** @var \phpbb\db\tools\tools_interface $db_tools */
		$db_tools = $phpbb_container->get('dbal.tools');
		$thanks_table = $phpbb_container->getParameter('core.table_prefix') . 'thanks';
		if ($db_tools->sql_table_exists($thanks_table))
		{
			$sql = 'SELECT COUNT(t.thanks_time) as item_count
				FROM ' . $thanks_table . ' AS t
				LEFT JOIN ' . $likes_table . ' as l
				ON t.user_id = l.user_id
				AND t.post_id = l.post_id
				WHERE l.post_id IS NULL';

			$result = $db->sql_query($sql);
			$thanks_to_convert = (int) $db->sql_fetchfield('item_count');
			$db->sql_freeresult($result);
		}

		$template->assign_vars(array(
			'POST_LIKES'	=> ($config['postlove_show_likes'] == 1),
			'POST_LIKED'	=> ($config['postlove_show_liked'] == 1),
			'AUTHOR_LIKE'	=> ($config['postlove_author_like'] == 1),
			'SHOW_BUTTON'	=> ($config['postlove_show_button'] == 1),
			'INDEX_HOWMANY_TODAY'		=> $config['postlove_index_most_liked_today'],
			'INDEX_HOWMANY_THIS_WEEK'	=> $config['postlove_index_most_liked_this_week'],
			'INDEX_HOWMANY_THIS_MONTH'	=> $config['postlove_index_most_liked_this_month'],
			'INDEX_HOWMANY_THIS_YEAR'	=> $config['postlove_index_most_liked_this_year'],
			'INDEX_HOWMANY_EVER'		=> $config['postlove_index_most_liked_ever'],
			'FORUM_HOWMANY_TODAY'		=> $config['postlove_forum_most_liked_today'],
			'FORUM_HOWMANY_THIS_WEEK'	=> $config['postlove_forum_most_liked_this_week'],
			'FORUM_HOWMANY_THIS_MONTH'	=> $config['postlove_forum_most_liked_this_month'],
			'FORUM_HOWMANY_THIS_YEAR'	=> $config['postlove_forum_most_liked_this_year'],
			'FORUM_HOWMANY_EVER'		=> $config['postlove_forum_most_liked_ever'],
			'THANKS_TO_CONVERT'			=> $thanks_to_convert,
		));
	}
}
