<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

/**
 * Explicitly denies u_postlove for the GUESTS group.
 *
 * All anonymous visitors share user_id 1 (ANONYMOUS), so allowing
 * guests to like posts causes a broken toggle (each guest overwrites
 * the previous guest's like/unlike). This migration ensures the
 * permission is never granted to the Guests group.
 */
class release_2_2_0_deny_guest_like extends \phpbb\db\migration\migration
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
			['permission.permission_unset', ['GUESTS', 'u_postlove', 'group']],
		];
	}
}
