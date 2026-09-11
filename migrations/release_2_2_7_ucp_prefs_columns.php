<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Adds user_postlove_hide and user_postlove_hide_sum columns to the users
 * table, and copies any existing pf_postlove_hide custom profile field value
 * across before release_2_2_7_remove_hide_cpf drops it.
 *
 * Moves the opt-out from a Custom Profile Field on the Profile tab (#55) to
 * two independent toggles under UCP > Board preferences > Edit global
 * settings, alongside the extension's split of button vs. summary
 * visibility. postlove_hide_sum has no prior CPF data to carry over — the
 * split never shipped in a released version.
 */
class release_2_2_7_ucp_prefs_columns extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_5_remove_dead_config',
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_postlove_hide'		=> ['UINT:2', 0],
					'user_postlove_hide_sum'	=> ['UINT:2', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_postlove_hide',
					'user_postlove_hide_sum',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'copy_hide_preference']]],
		];
	}

	/**
	 * Page through users with the CPF enabled, copying it to the new column.
	 * The CPF column still exists at this point: this migration runs before
	 * release_2_2_7_remove_hide_cpf drops it.
	 *
	 * @param int|null $start Paging offset from the previous call
	 * @return int|null Next offset, or null once done
	 */
	public function copy_hide_preference($start)
	{
		$start = (int) $start;
		$limit = 500;
		$rows_done = 0;

		$sql = 'SELECT user_id
			FROM ' . $this->table_prefix . "profile_fields_data
			WHERE pf_postlove_hide <> 0
			ORDER BY user_id ASC";
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_postlove_hide = 1
				WHERE user_id = ' . (int) $row['user_id'];
			$this->db->sql_query($sql);
			$rows_done++;
		}
		$this->db->sql_freeresult($result);

		if ($rows_done < $limit)
		{
			return;
		}

		return $start + $limit;
	}
}
