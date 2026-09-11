<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * release_1_2_0's index/forum summary period defaults were asymmetric with
 * no apparent reason: this-week showed 2 posts on the index but only 1 per
 * forum, and ever was off on the index but on for individual forums, while
 * today/this-month/this-year were already symmetric. Brings the two odd
 * ones in line with the rest (1 post shown, same as forum's existing
 * defaults), only where a board still holds the original default — an
 * admin who deliberately set either to something else keeps their value.
 */
class release_2_2_7_symmetric_summary_defaults extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_7_split_hide_prefs',
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'fix_index_defaults']]],
		];
	}

	public function fix_index_defaults()
	{
		if ((int) $this->config['postlove_index_most_liked_this_week'] === 2)
		{
			$this->config->set('postlove_index_most_liked_this_week', 1);
		}

		if ((int) $this->config['postlove_index_most_liked_ever'] === 0)
		{
			$this->config->set('postlove_index_most_liked_ever', 1);
		}
	}

	// No revert_data(): can't reliably distinguish "still holds the old
	// default" from "admin set it back to the same value on purpose" after
	// the fact.
}
