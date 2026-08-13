<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Drops two obsolete config entries that no code has ever read.
 *
 * Both were added back when this was anavaro/postlove:
 *
 * - postlove_version (release_1_0_0) — the extension version in phpbb_config,
 *   never read and never kept current, so every board still carries it pinned
 *   at '1.0.0'. The canonical version now lives in ext::POSTLOVE_VERSION,
 *   matching the convention in the sibling extensions (recenttopics, bbguild).
 * - postlove_installed_theme (release_1_1_0) — never read by anything, and
 *   never given a default anyone could change.
 *
 * Boards inherited from the original author reach the current chain via
 * release_2_1_0_rename_namespace, which rewrites their old migration rows and
 * so marks those two migrations as already run. Their rows would survive every
 * upgrade; removing them here in update_data() is what actually cleans them up,
 * because the migrator's own reversal of those migrations only runs on purge.
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

	// No revert_data(). On purge the migrator auto-reverses update_data(), so
	// these two config.remove steps briefly re-add both keys with an empty value.
	// That is harmless: migrations revert newest first, so the chain then reaches
	// release_1_1_0 and release_1_0_0, whose own reversals remove them again and
	// leave the config table clean.
}
