<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016 v12mike
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
* Adds the postlove_show_button config entry for button display mode.
*/

class release_1_2_0 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array(
			'\avathar\postlove\migrations\release_1_1_0',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('postlove_index_most_liked_today', 0)),
			array('config.add', array('postlove_index_most_liked_this_week', 2)),
			array('config.add', array('postlove_index_most_liked_this_month', 1)),
			array('config.add', array('postlove_index_most_liked_this_year', 1)),
			array('config.add', array('postlove_index_most_liked_ever', 0)),
			array('config.add', array('postlove_forum_most_liked_today', 0)),
			array('config.add', array('postlove_forum_most_liked_this_week', 1)),
			array('config.add', array('postlove_forum_most_liked_this_month', 1)),
			array('config.add', array('postlove_forum_most_liked_this_year', 1)),
			array('config.add', array('postlove_forum_most_liked_ever', 1)),
			array('config.add', array('postlove_show_button', 1)),
		);
	}

	// No revert_data(): the migrator already reverses update_data() on purge
	// (\phpbb\db\migrator::revert() merges helper::reverse_update_data() with
	// whatever revert_data() returns), so every key added above is removed
	// automatically. The explicit list that used to live here duplicated those
	// ten removals and added an eleventh for postlove_summary_query_cache_seconds,
	// a key no migration has ever added.
}
