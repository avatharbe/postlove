<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

/**
 * Adds the u_postlove_summary permission to gate visibility of the
 * "most liked posts" summary panels by user group.
 */
class release_2_2_0_add_summary_permission extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_1_0_add_permissions',
		];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_postlove_summary', true]],
			['permission.permission_set', ['REGISTERED', 'u_postlove_summary', 'group']],
			['permission.permission_set', ['REGISTERED_COPPA', 'u_postlove_summary', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_postlove_summary', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_postlove_summary', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_postlove_summary', 'group']],
		];
	}
}
