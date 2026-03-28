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
* Tests for the ajaxify controller (AJAX like/unlike toggle endpoint).
*
* Verifies the /postlove/toggle/{post_id} endpoint correctly handles:
* - Permission checks: users without the u_postlove permission get an error
* - Self-like prevention: when postlove_author_like is disabled, authors cannot
*   like their own posts
* - Non-existent posts: toggling a post that does not exist returns an error
* - Like action: toggling a post the user has not liked adds a like (toggle_action=add)
* - Unlike action: toggling a post the user has already liked removes it (toggle_action=remove)
*
* Fixture: tests/controller/fixtures/users.xml
* Contains 4 topics, 4 posts (all by poster_id=1), and 6 existing likes.
*
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
	* Set up all dependencies required by the ajaxify controller.
	*
	* Mocks and services:
	* - auth: mock for acl_get('u_postlove') permission check; configured per
	*   test case in get_controller()
	* - db: test DBAL backed by the controller fixtures XML
	* - config: empty config; postlove_author_like is set per test in get_controller()
	* - user: mock user; user_id is set per test in get_controller()
	* - language: mock language for error message lookups
	* - dispatcher: mock event dispatcher for the cache service
	* - cache: real cache service with dummy driver, purged to avoid stale data
	*   between tests (needed because the controller updates post like counts
	*   which phpBB caches)
	* - notifyhelper: mock notification helper (constructor disabled); the
	*   controller calls it on successful like/unlike but we don't verify
	*   notification behavior here
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

		$this->dispatcher = new \phpbb_mock_event_dispatcher();

		$this->cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\dummy(),
			$this->config,
			$this->db,
			$this->dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		$this->cache->purge();

		// Setup notifyhelper
		$this->notifyhelper = $this->getMockBuilder('\avathar\postlove\controller\notifyhelper')->disableOriginalConstructor()
			->getMock();
	}

	/**
	* Create an ajaxify controller instance configured for a specific test scenario.
	*
	* @param int  $user_id              The acting user's ID
	* @param bool $has_permission        Whether the user has the u_postlove ACL permission
	* @param bool $postlove_author_like  Whether post authors are allowed to like their own posts
	* @return \avathar\postlove\controller\ajaxify The configured controller
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
	* Data provider for test_ajaxify_controller.
	*
	* All posts in the fixture are authored by poster_id=1. The fixture has
	* existing likes: user 2 has liked posts 1 and 2.
	*
	* Test matrix (user_id, has_permission, author_like_allowed, post_id, expected JSON):
	*
	* 'no_permission'      - User 1, no u_postlove permission => error
	*                        (permission gate rejects the request)
	* 'user_cant_like_own' - User 1 (=poster), has permission, but author_like=false,
	*                        post 4 => error (self-like prevention)
	* 'no_such_post'       - User 1, has permission, post 5 does not exist => error
	* 'user_can_like'      - User 1 (=poster), has permission, author_like=true,
	*                        post 4 => add (author can like own post when allowed)
	* 'like'               - User 2, post 3 (no existing like) => add
	* 'unlike'             - User 2, post 1 (already liked in fixture) => remove
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
	 * Test the ajaxify controller's toggle action for each scenario.
	 *
	 * Calls controller->base('toggle', $post_id) and verifies:
	 * 1. The response is a Symfony JsonResponse (always, even on error)
	 * 2. HTTP status is 200
	 * 3. The response body contains the expected JSON fragment:
	 *    - '{"error":1}' for permission/validation failures
	 *    - '"toggle_action":"add"' when a new like is inserted
	 *    - '"toggle_action":"remove"' when an existing like is deleted
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
