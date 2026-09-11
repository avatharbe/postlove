<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Splits user_postlove_hide and user_postlove_hide_sum further: each still
 * bundled two unrelated UI elements under one toggle (the like button with
 * the profile's own "Likes" link; the summary panels with the topic-list
 * like count). Adds user_postlove_hide_profile (the "Likes" link) and
 * user_postlove_hide_topics (the topic-list count) so all four are
 * independent. Both default to 0 ("No"), same as the other two.
 */
class release_2_2_7_split_hide_prefs extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_7_remove_hide_cpf',
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_postlove_hide_profile'	=> ['UINT:2', 0],
					'user_postlove_hide_topics'	=> ['UINT:2', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_postlove_hide_profile',
					'user_postlove_hide_topics',
				],
			],
		];
	}
}
