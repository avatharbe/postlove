<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

class release_2_1_0_add_permissions extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_1_0_rename_namespace',
		];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_postlove', true]],
			['permission.permission_set', ['REGISTERED', 'u_postlove', 'group']],
			['permission.permission_set', ['REGISTERED_COPPA', 'u_postlove', 'group']],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_postlove', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_postlove', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_postlove', 'group']],
		];
	}
}
