<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * release_1_1_0 defaulted postlove_author_like to 1 (self-liking allowed).
 * Most like/reaction systems (Reddit, Facebook, Discord) disallow liking
 * your own content, since it's the one count that's trivial to game. Flips
 * the default to 0, only where a board still holds the original default —
 * an admin who deliberately kept it at 1 keeps their value.
 */
class release_2_2_7_disallow_self_like_default extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_7_symmetric_summary_defaults',
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'fix_author_like_default']]],
		];
	}

	public function fix_author_like_default()
	{
		if ((int) $this->config['postlove_author_like'] === 1)
		{
			$this->config->set('postlove_author_like', 0);
		}
	}

	// No revert_data(): can't reliably distinguish "still holds the old
	// default" from "admin set it back to 1 on purpose" after the fact.
}
