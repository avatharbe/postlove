<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Drops two obsolete config entries that no code has ever read:
 * postlove_version and postlove_installed_theme. The canonical version now
 * lives in ext::POSTLOVE_VERSION.
 */
class release_2_2_5_remove_dead_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_4',
		];
	}

	public function effectively_installed()
	{
		return !isset($this->config['postlove_version'])
			&& !isset($this->config['postlove_installed_theme']);
	}

	public function update_data()
	{
		return [
			['config.remove', ['postlove_version']],
			['config.remove', ['postlove_installed_theme']],
		];
	}

	// No revert_data(): purge briefly re-adds both keys, but release_1_1_0 and
	// release_1_0_0's own reversals remove them again right after.
}
