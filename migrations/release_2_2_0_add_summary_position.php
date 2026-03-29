<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

/**
 * Adds the postlove_summary_position config entry for controlling
 * whether the most liked posts summary appears above or below the forum list.
 */
class release_2_2_0_add_summary_position extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_0_0',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['postlove_summary_position', 0]],
		];
	}
}
