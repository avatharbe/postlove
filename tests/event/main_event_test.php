<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Stanislav Atanasov
* @copyright (c) 2026 Avathar.be
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace avathar\postlove\tests\event;

/**
* Tests for the main_listener event subscriber.
*
* Verifies that the listener correctly subscribes to the phpBB core events
* needed for postlove functionality: permission registration, post data
* prefetch, post row modification, user setup, memberlist profile view,
* and cleanup after post/user deletion.
*
* Fixture: tests/event/fixtures/users.xml
* Contains 4 topics, 4 posts, 6 likes, and 2 users to provide a
* realistic dataset for the listener under test.
*
* @group event
*/

class main_event extends \phpbb_database_test_case
{
	protected $listener;

	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	protected static function setup_extensions()
	{
		return array('avathar/postlove');
	}

	protected $db;

	/**
	* Get data set fixtures
	*/
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/users.xml');
	}

	/**
	* Set up the test environment with all dependencies needed by main_listener.
	*
	* Mocks created:
	* - auth: permission checks (acl_get, acl_getf)
	* - config: empty config object for postlove settings
	* - db: test DBAL backed by the XML fixture data
	* - template: mock template for assign_vars/assign_block_vars assertions
	* - user: mock user object for user_id and session data
	* - language: mock language object for lang() string lookups
	* - controller_helper: mock helper for route generation (disables constructor
	*   to avoid requiring the full phpBB DI container)
	*/
	public function setUp(): void
	{
		parent::setUp();
		// Setup Auth
		$this->auth = $this->createMock('\phpbb\auth\auth');

		//Setup Config
		$this->config = new \phpbb\config\config(array());

		// Setup DB
		$this->db = $this->new_dbal();

		// Setup template
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		// Setup User
		$this->user = $this->createMock('\phpbb\user');

		// Setup Language
		$this->language = $this->createMock('\phpbb\language\language');

		// Setup Controller
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	* Instantiate the main_listener with all mocked dependencies.
	*
	* Uses the table name 'phpbb_posts_likes' matching the test DB fixture.
	*/
	protected function set_listener()
	{
		$this->listener = new \avathar\postlove\event\main_listener(
			$this->auth,
			$this->config,
			$this->db,
			$this->template,
			$this->user,
			$this->language,
			$this->controller_helper,
			'phpbb_posts_likes'
		);
	}

	/**
	* Verify that main_listener subscribes to exactly the expected phpBB core events.
	*
	* The required events are:
	* - core.permissions: register the u_postlove permission
	* - core.viewtopic_modify_post_data: prefetch like data for all posts in the topic
	* - core.viewtopic_modify_post_row: inject like count and button into each post row
	* - core.user_setup: load the postlove language file
	* - core.memberlist_view_profile: show likes given/received on user profiles
	* - core.delete_posts_after: clean up likes when posts are deleted
	* - core.delete_user_after: clean up likes when a user account is removed
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.permissions',
			'core.viewtopic_modify_post_data',
			'core.viewtopic_modify_post_row',
			'core.user_setup',
			'core.memberlist_view_profile',
			'core.delete_posts_after',
			'core.delete_user_after',
		), array_keys(\avathar\postlove\event\main_listener::getSubscribedEvents()));
	}
}
