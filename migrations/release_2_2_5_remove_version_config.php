<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Drops the obsolete postlove_version config entry.
 *
 * release_1_0_0 added postlove_version to phpbb_config back when this was
 * anavaro/postlove. Nothing has ever read it and no later migration has kept it
 * current, so every board still carries it pinned at '1.0.0'. Boards inherited
 * from the original author reach the current chain via
 * release_2_1_0_rename_namespace, which rewrites their old migration rows and
 * so marks release_1_0_0 as already run — leaving the stale row to survive
 * every upgrade. Removing it here is what actually cleans those boards up.
 *
 * The canonical version now lives in ext::POSTLOVE_VERSION, matching the
 * convention in the sibling extensions (recenttopics, bbguild).
 */
class release_2_2_5_remove_version_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_4',
		];
	}

	public function effectively_installed()
	{
		return !isset($this->config['postlove_version']);
	}

	public function update_data()
	{
		return [
			['config.remove', ['postlove_version']],
		];
	}

	/**
	 * Deliberately empty. Re-adding postlove_version on revert would restore the
	 * exact row this migration exists to remove; the purge path is already
	 * covered by release_1_0_0::revert_data(), which removes the key (a harmless
	 * no-op DELETE once this migration has run).
	 */
	public function revert_data()
	{
		return [];
	}
}
