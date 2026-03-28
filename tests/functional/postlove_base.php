<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\tests\functional;

/**
* Base class for postlove functional tests.
*
* Extends phpbb_functional_test_case, which provides a live phpBB installation
* with a real database, HTTP client, and session management. Subclasses inherit
* helper methods for toggling postlove configuration settings via direct SQL
* updates and cache purges.
*
* All functional tests run against the installed board, so the extension must
* be enabled and its migrations applied before these tests execute.
*
* @group functional
*/
class postlove_base extends \phpbb_functional_test_case
{
	/** @inheritdoc */
	protected static function setup_extensions()
	{
		return array('avathar/postlove');
	}

	/** @inheritdoc */
	public function setUp(): void
	{
		parent::setUp();
	}

	/**
	* Enable CSS usage for the postlove extension (postlove_use_css = 1).
	*/
	public function force_allow_postlove()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_use_css'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}

	/**
	* Enable the "show likes received" counter on user profiles
	* (postlove_show_likes = 1).
	*/
	public function show_likes()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_show_likes'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}
	/**
	* Enable the "show likes given" counter on user profiles
	* (postlove_show_liked = 1).
	*/
	public function show_liked()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_show_liked'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}

	/**
	* Toggle between button mode and inline mode for the like control.
	*
	* Button mode (1): like control appears as a post button (.postlove-li)
	* Inline mode (0): like control appears inline in the post (.postlove)
	*
	* @param int $enable_button 1 for button mode, 0 for inline mode
	*/
	public function set_button_mode($enable_button)
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = $enable_button
			WHERE config_name = 'postlove_show_button'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}
	
	/**
	* Look up a topic ID by its title in the database.
	*
	* @param string $topic_title The exact topic title to search for
	* @return string The topic_id
	*/
	public function get_topic_id($topic_title)
	{
		$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_title = \'' . $topic_title . '\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		return $row['topic_id'];
	}
}