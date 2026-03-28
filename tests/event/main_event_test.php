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
	* Setup test environment
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
	* Create our controller
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
	* Test the event listener is subscribing events
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
