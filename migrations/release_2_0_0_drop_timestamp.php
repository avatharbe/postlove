<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 v12mike
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
* Drops the legacy timestamp column after data migration to liketime.
*/
class release_2_0_0_drop_timestamp extends \phpbb\db\migration\profilefield_base_migration
{
	public static function depends_on()
	{
		return array(
			'\avathar\postlove\migrations\release_2_0_0',
		);
	}


	public function update_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'posts_likes'	=> array(
					'timestamp',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'posts_likes'	=> array(
					'timestamp' => array('VCHAR:32', 0),
				),
			),
		);
	}

	public function update_data()
	{
		return array();
	}
}
