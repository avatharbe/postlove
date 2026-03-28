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
* @group controller
*/

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';

class controller_ajaxify_test extends \phpbb_database_test_case
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
	* Setup test environment
	*/
	public function setUp(): void
	{
		parent::setUp();
		global $phpbb_root_path, $phpEx;

		// Setup Auth
		$this->auth = $this->createMock('\phpbb\auth\auth');

		// Setup DB
		$this->db = $this->new_dbal();

		//Setup Config
		$this->config = new \phpbb\config\config(array());

		// Setup User
		$this->user = $this->createMock('\phpbb\user');

		// Setup Language
		$this->language = $this->createMock('\phpbb\language\language');

		$this->cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\dummy(),
			$this->config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$this->cache->purge();

		// Setup notifyhelper
		$this->notifyhelper = $this->getMockBuilder('\avathar\postlove\controller\notifyhelper')->disableOriginalConstructor()
			->getMock();
	}

	/**
	* Create our controller
	*/
	protected function get_controller($user_id, $has_permission, $postlove_author_like)
	{
		$this->user->data['user_id'] = $user_id;
		$this->config['postlove_author_like'] = $postlove_author_like;

		$this->auth->method('acl_get')
			->with('u_postlove')
			->willReturn($has_permission);

		return new \avathar\postlove\controller\ajaxify(
			$this->auth,
			$this->config,
			$this->db,
			$this->user,
			$this->language,
			$this->cache,
			$this->notifyhelper,
			'phpbb_posts_likes'
		);
	}

	/**
	* Test data for the test_ajaxify_controller test
	*
	* @return array Test data
	*/
	public function controller_ajaxify_data()
	{
		return array(
			'no_permission'	=> array(
				1, // user_id
				false, // has u_postlove permission
				true, // Allow author to like
				1, // post ID
				'{"error":1}'
			),
			'user_cant_like_own'	=> array(
				1, // user_id
				true, // has u_postlove permission
				false, // Allow author to like
				4, // post ID
				'{"error":1}'
			),
			'no_such_post'	=> array(
				1, // user_id
				true, // has u_postlove permission
				true, // Allow author to like
				5, // post ID
				'{"error":1}'
			),
			'user_can_like'	=> array(
				1, // user_id
				true, // has u_postlove permission
				true, // Allow author to like
				4, // post ID
				'"toggle_action":"add"'
			),
			'like'	=> array(
				2, // user_id
				true, // has u_postlove permission
				true, // Allow author to like
				3, // post ID
				'"toggle_action":"add"'
			),
			'unlike'	=> array(
				2, // user_id
				true, // has u_postlove permission
				true, // Allow author to like
				1, // post ID
				'"toggle_action":"remove"'
			),
		);
	}

	/**
	 * Test the controller
	 *
	 * @dataProvider controller_ajaxify_data
	 */
	public function test_ajaxify_controller($user_id, $has_permission, $postlove_author_like, $post_id, $expected)
	{
		$controller = $this->get_controller($user_id, $has_permission, $postlove_author_like);
		$response = $controller->base('toggle', $post_id);
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString($expected, $response->getContent());
	}
}
