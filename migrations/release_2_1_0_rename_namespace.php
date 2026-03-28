<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

class release_2_1_0_rename_namespace extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_0_0_add_liked_user_id',
		];
	}

	public function effectively_installed()
	{
		// If there are no old-namespace migration records, nothing to do
		$sql = 'SELECT COUNT(*) AS cnt
			FROM ' . $this->table_prefix . "migrations
			WHERE migration_name LIKE '%anavaro%postlove%'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count === 0;
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'rename_migrations']]],
			['custom', [[$this, 'rename_modules']]],
		];
	}

	public function rename_migrations()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "migrations
			SET migration_name = REPLACE(migration_name, 'anavaro', 'avathar')
			WHERE migration_name LIKE '%anavaro%postlove%'";
		$this->db->sql_query($sql);
	}

	public function rename_modules()
	{
		// Update ACP module class reference
		$sql = 'UPDATE ' . $this->table_prefix . "modules
			SET module_class = REPLACE(module_class, 'anavaro', 'avathar'),
				module_basename = REPLACE(module_basename, 'anavaro', 'avathar'),
				module_auth = REPLACE(module_auth, 'anavaro', 'avathar')
			WHERE module_basename LIKE '%anavaro%postlove%'";
		$this->db->sql_query($sql);
	}
}
