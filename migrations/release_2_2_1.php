<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
namespace avathar\postlove\migrations;

/**
 * Version marker for release 2.2.1.
 *
 * Intentionally empty: 2.2.1 only added the is_enableable() requirement check
 * (#29) and changed no schema or data. The migration exists so the release is
 * recorded in phpbb_migrations and so later migrations have a stable link in
 * the dependency chain. Do not delete it — boards that already ran it would be
 * left with an unresolvable dependency on their next update.
 */
class release_2_2_1 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_0_deny_guest_like',
		];
	}

	public function update_data()
	{
		return [];
	}
}
