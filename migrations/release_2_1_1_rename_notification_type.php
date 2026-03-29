<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

class release_2_1_1_rename_notification_type extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_1_0_rename_namespace',
		];
	}

	public function effectively_installed()
	{
		$sql = 'SELECT COUNT(*) AS cnt
			FROM ' . $this->table_prefix . "notification_types
			WHERE notification_type_name = 'notification.type.postlove'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count === 0;
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'rename_notification_type']]],
		];
	}

	public function rename_notification_type()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "notification_types
			SET notification_type_name = 'avathar.postlove.notification.type.postlove'
			WHERE notification_type_name = 'notification.type.postlove'";
		$this->db->sql_query($sql);
	}
}
