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

namespace avathar\postlove\tests\controller;

/**
* Tests for the lovelist controller (user's liked posts page).
*
* Verifies that the /postlove/{user_id} endpoint correctly queries and
* displays the posts a user has liked, respecting per-forum read permissions.
* Posts in forums the viewer cannot read are excluded from the result set.
*
* Fixture: tests/controller/fixtures/users.xml
* Contains 4 topics spread across 4 forums, 4 posts, and 6 likes.
* The fixture uses forums 1-3 (forum 4 has no likes) so permission arrays
* control which forums' liked posts are visible to the viewer.
*
* @group controller
*/

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';

class controller_lovelist_test extends \phpbb_database_test_case
{
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
	* Set up all dependencies for the lovelist controller.
	*
	* Mocks and services:
	* - db: test DBAL backed by the controller fixtures XML
	* - phpbb_dispatcher (global): mock event dispatcher required by phpBB internals
	* - user: mock user; user_id and is_registered are set per test in get_controller()
	* - language: mock returning the language key itself (passthrough) for assertions
	* - controller_helper: mock with render() returning a plain Response containing
	*   the template filename and status code, so tests can verify HTTP status
	* - auth: mock for acl_getf('f_read') permission checks, configured per test
	* - user_loader: real user_loader backed by the test DB (loads usernames for display)
	* - template: mock to assert assign_block_vars call count (one per visible liked post)
	* - pagination: mock (constructor disabled); pagination logic is not under test
	* - request: mock request object for controller dependencies
	*/
	public function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher;

		parent::setUp();
		$this->db = $this->new_dbal();

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$this->user = $this->createMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->method('lang')
			->will($this->returnArgument(0));

		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller_helper->expects($this->any())
			->method('render')
			->willReturnCallback(function ($template_file, $page_title = '', $status_code = 200, $display_online_list = false) {
				return new \Symfony\Component\HttpFoundation\Response($template_file, $status_code);
			});

		$this->auth = $this->createMock('\phpbb\auth\auth');

		$this->user_loader = new \phpbb\user_loader($this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		// Mock the template
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$this->pagination = $this->getMockBuilder('\phpbb\pagination')->disableOriginalConstructor()
			->getMock();

		$this->request = $this->createMock('\phpbb\request\request');
	}
	/**
	* Smoke test: verify that the posts_likes table exists in the test database.
	*
	* This confirms the extension's migration ran correctly and the table
	* is available for the controller queries that follow.
	*/
	public function test_install()
	{
		$db_tools = new \phpbb\db\tools\tools($this->db);
		$this->assertTrue($db_tools->sql_table_exists('phpbb_posts_likes'));
	}

	/**
	* Create a lovelist controller configured for a specific test scenario.
	*
	* Sets the user identity, configures forum read permissions via auth mock,
	* and sets an expectation on the template mock for the exact number of
	* assign_block_vars calls (one per visible liked post).
	*
	* @param int   $user_id        The viewing user's ID
	* @param bool  $is_registered  Whether the user is registered
	* @param int   $expected       Expected number of assign_block_vars calls
	* @param array $perm_ary       Forum permission array (forum_id => ['f_read' => bool])
	* @return \avathar\postlove\controller\lovelist The configured controller
	*/
	protected function get_controller($user_id, $is_registered, $expected, $perm_ary)
	{
		$this->user->data['user_id'] = $user_id;
		$this->user->data['is_registered'] = $is_registered;

		$this->auth->expects($this->any())
			->method('acl_getf')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValue($perm_ary));

		$this->template->expects($this->exactly($expected))
			->method('assign_block_vars');

		$controller = new \avathar\postlove\controller\lovelist(
			$this->user,
			$this->language,
			$this->controller_helper,
			$this->db,
			$this->auth,
			$this->user_loader,
			$this->template,
			$this->pagination,
			$this->request,
			'phpbb_posts_likes',
			'./',
			'php'
		);

		return $controller;
	}

	/**
	* Data provider for test_controller.
	*
	* Tests how forum read permissions filter the visible liked posts.
	* Each case provides: user_id, is_registered, requester_id (whose loves to view),
	* expected block_vars count, and the forum permission array.
	*
	* 'normal'     - All 3 forums readable => 6 liked posts visible (all likes)
	* 'test_forum' - Forums 1+2 readable, forum 3 restricted => 5 visible
	*                (likes on posts in forum 3 are hidden)
	* 'test2'      - Only forum 1 readable, forums 2+3 restricted => 1 visible
	*                (only likes on forum 1 posts shown)
	*/
	public function controller_data()
	{
		return array(
			'normal' => array(
				1, // User Id
				true, // Is user registered
				1, // Request Id
				6, // Expected
				array(
					1 => array(
						'f_read'	=> true,
					),
					2 => array(
						'f_read'	=> true,
					),
					3 => array(
						'f_read'	=> true,
					),
				)
			),
			'test_forum' => array(
				2, // User Id
				true, // Is user registered
				1, // Request Id
				5, // Expected
				array(
					1 => array(
						'f_read'	=> true,
					),
					2 => array(
						'f_read'	=> true,
					),
					3 => array(
						'f_read'	=> false,
					),
				)
			),
			'test2' => array(
				1, // User Id
				true, // Is user registered
				3, // Requestor Id
				1, // Expected
				array(
					1 => array(
						'f_read'	=> true,
					),
					2 => array(
						'f_read'	=> false,
					),
					3 => array(
						'f_read'	=> false,
					),
				)
			),
		);
	}

	/**
	 * Test the lovelist controller with varying forum permissions.
	 *
	 * Calls controller->base($requester_id, false) to render the love list
	 * for the given user, then verifies:
	 * 1. The response is a Symfony Response
	 * 2. HTTP status is 200
	 * 3. The template mock received exactly $expected assign_block_vars calls
	 *    (asserted via the expects() set up in get_controller)
	 *
	 * @dataProvider controller_data
	 */
	public function test_controller($user_id, $is_registered, $requester_id, $expected, $perm_ary)
	{
		$controller = $this->get_controller($user_id, $is_registered, $expected, $perm_ary);
		$response = $controller->base($requester_id, false);
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('200', $response->getStatusCode());
	}
}
