<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Removes the postlove_hide custom profile field, added in
 * release_1_2_0_create_cpf, now that release_2_2_7_ucp_prefs_columns has
 * copied its value onto user_postlove_hide and the UCP listener reads from
 * there instead.
 */
class release_2_2_7_remove_hide_cpf extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_7_ucp_prefs_columns',
		];
	}

	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_postlove_hide');
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'profile_fields_data'	=> [
					'pf_postlove_hide',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'profile_fields_data'	=> [
					'pf_postlove_hide'	=> ['UINT:2', 0],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'remove_cpf']]],
		];
	}

	public function remove_cpf()
	{
		$sql = "SELECT field_id FROM " . PROFILE_FIELDS_TABLE . " WHERE field_name = 'postlove_hide'";
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);

		if (!$field_id)
		{
			return;
		}

		$sql = 'DELETE FROM ' . PROFILE_FIELDS_TABLE . ' WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . PROFILE_LANG_TABLE . ' WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . ' WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);
	}

	// No revert_data(): recreating the CPF's field/lang rows on downgrade
	// would need the exact field_type/lang data from release_1_2_0_create_cpf,
	// and reverting this far back is not a supported path.
}
