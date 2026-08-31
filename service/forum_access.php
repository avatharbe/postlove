<?php
/**
 *
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace avathar\postlove\service;

use phpbb\db\driver\driver_interface;
use phpbb\user;

/**
 * Drops password protected forums the current viewer has not unlocked from
 * a forum_id set.
 *
 * f_read is independent of the forum password; phpBB gates it separately,
 * via login_forum_box() and phpbb_forums_access. Mirrors the forum_password
 * / FORUMS_ACCESS_TABLE check in search.php.
 */
class forum_access
{
	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/**
	 * @param driver_interface $db
	 * @param user             $user
	 */
	public function __construct(driver_interface $db, user $user)
	{
		$this->db = $db;
		$this->user = $user;
	}

	/**
	 * Remove password protected forums the current session has not unlocked.
	 *
	 * @param  array $forum_ids Candidate forum IDs (e.g. from acl_getf('f_read'))
	 * @return array Forum IDs from $forum_ids that are not password locked for this session
	 */
	public function drop_locked(array $forum_ids): array
	{
		if (empty($forum_ids))
		{
			return $forum_ids;
		}

		$sql = 'SELECT f.forum_id
			FROM ' . FORUMS_TABLE . ' f
			LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa
				ON (fa.forum_id = f.forum_id
					AND fa.session_id = '" . $this->db->sql_escape($this->user->session_id) . "')
			WHERE " . $this->db->sql_in_set('f.forum_id', $forum_ids) . "
				AND f.forum_password <> ''
				AND fa.user_id IS NULL";
		$result = $this->db->sql_query($sql);
		$locked = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$locked[] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		return empty($locked) ? $forum_ids : array_values(array_diff($forum_ids, $locked));
	}
}
