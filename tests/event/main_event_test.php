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

	/**
	* Count all rows currently in phpbb_posts_likes.
	*/
	protected function count_likes(): int
	{
		$result = $this->db->sql_query('SELECT COUNT(*) AS cnt FROM phpbb_posts_likes');
		$cnt = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		return $cnt;
	}

	/**
	* Data provider for test_clean_posts_after.
	*
	* Fixture has 6 likes spread across posts 1–3:
	*   post 1: liked by users 3, 2, 4  (3 rows)
	*   post 2: liked by users 3, 2     (2 rows)
	*   post 3: liked by user  3        (1 row)
	*
	* 'one post'      - delete post 1 only       => 3 rows remain (posts 2+3)
	* 'two posts'     - delete posts 1 and 2     => 1 row  remains (post 3)
	* 'no match'      - delete non-existent post => 6 rows remain (nothing deleted)
	*/
	public function clean_posts_data(): array
	{
		return array(
			'one post'  => array([1],     3),
			'two posts' => array([1, 2],  1),
			'no match'  => array([99],    6),
		);
	}

	/**
	* Verify that clean_posts_after removes all likes referencing the deleted post IDs.
	*
	* @dataProvider clean_posts_data
	*/
	public function test_clean_posts_after(array $post_ids, int $expected_remaining): void
	{
		$this->set_listener();
		$event = new \phpbb\event\data(array('post_ids' => $post_ids));
		$this->listener->clean_posts_after($event);
		$this->assertSame($expected_remaining, $this->count_likes());
	}

	/**
	* Data provider for test_clean_users_after.
	*
	* Fixture has 6 likes; the user_id column records who gave each like:
	*   user 3: liked posts 1, 2, 3  (3 rows)
	*   user 2: liked posts 1, 2     (2 rows)
	*   user 4: liked post  1        (1 row)
	*
	* 'one user'   - delete user 3 only       => 3 rows remain (users 2+4)
	* 'two users'  - delete users 2 and 3     => 1 row  remains (user 4 / post 1)
	* 'no match'   - delete non-existent user => 6 rows remain (nothing deleted)
	*/
	public function clean_users_data(): array
	{
		return array(
			'one user'  => array([3],     3),
			'two users' => array([2, 3],  1),
			'no match'  => array([99],    6),
		);
	}

	/**
	* Verify that clean_users_after removes all likes given by the deleted user IDs.
	*
	* @dataProvider clean_users_data
	*/
	public function test_clean_users_after(array $user_ids, int $expected_remaining): void
	{
		$this->set_listener();
		$event = new \phpbb\event\data(array('user_ids' => $user_ids));
		$this->listener->clean_users_after($event);
		$this->assertSame($expected_remaining, $this->count_likes());
	}

	/**
	* Verify that prefetch_likes populates the three internal caches via batch queries.
	*
	* Fixture phpbb_posts_likes:
	*   post 1 liked by users 3, 2, 4 — user 4 absent from phpbb_users, excluded by JOIN
	*   post 2 liked by users 3, 2
	*   post 3 liked by user 3
	*   all rows have liked_user_id=3 (the poster)
	*
	* rowset contains posts 1-3 all by user_id=3, so user_ids=[3] for the stats queries.
	*/
	public function test_prefetch_likes(): void
	{
		$this->set_listener();
		$this->config['postlove_show_likes'] = 1;
		$this->config['postlove_show_liked'] = 1;
		$this->user->data = ['user_id' => 5];

		$this->listener->prefetch_likes(new \phpbb\event\data([
			'post_list' => [1, 2, 3],
			'rowset'    => [
				1 => ['user_id' => 3],
				2 => ['user_id' => 3],
				3 => ['user_id' => 3],
			],
		]));

		$ref = new \ReflectionClass($this->listener);

		$likers_prop = $ref->getProperty('post_likers');
		$likers_prop->setAccessible(true);
		$post_likers = $likers_prop->getValue($this->listener);

		// post 1: users 3 and 2 via JOIN (user 4 has no phpbb_users row)
		$this->assertCount(2,            $post_likers[1]);
		$this->assertSame('Test user',   $post_likers[1][3]);
		$this->assertSame('Test user 2', $post_likers[1][2]);
		// post 2: users 3 and 2
		$this->assertCount(2, $post_likers[2]);
		// post 3: user 3 only
		$this->assertCount(1, $post_likers[3]);

		$given_prop = $ref->getProperty('user_likes_given');
		$given_prop->setAccessible(true);
		// user 3 gave likes on posts 1, 2, 3 = 3
		$this->assertSame(3, $given_prop->getValue($this->listener)[3]);

		$received_prop = $ref->getProperty('user_likes_received');
		$received_prop->setAccessible(true);
		// all 6 fixture rows have liked_user_id=3
		$this->assertSame(6, $received_prop->getValue($this->listener)[3]);
	}

	/**
	* Post 1 has two likers (users 3 and 2). Viewer (user 5) has permission
	* and has not liked. Expected: count=2, class='like', URL set, no DISABLE.
	*/
	public function test_modify_post_row_counts(): void
	{
		$this->set_listener();
		$this->config['postlove_show_likes']  = 0;
		$this->config['postlove_show_liked']  = 0;
		$this->config['postlove_show_button'] = 0;
		$this->config['postlove_author_like'] = 1;
		$this->user->data           = ['user_id' => 5];
		$this->user->profile_fields = [];

		$this->language->method('lang')->willReturnArgument(0);
		$this->controller_helper->method('route')->willReturn('/postlove/toggle/1');
		$this->auth->method('acl_get')->willReturn(true);

		$this->listener->prefetch_likes(new \phpbb\event\data([
			'post_list' => [1],
			'rowset'    => [1 => ['user_id' => 3]],
		]));

		$event = new \phpbb\event\data([
			'row'       => ['post_id' => 1, 'user_id' => 3],
			'poster_id' => 3,
			'post_row'  => [],
		]);
		$this->listener->modify_post_row($event);
		$post_row = $event['post_row'];

		$this->assertSame(2,                    $post_row['POST_LIKERS_COUNT']);
		$this->assertSame('like',               $post_row['POST_LIKE_CLASS']);
		$this->assertSame('/postlove/toggle/1', $post_row['POST_LIKE_URL']);
		$this->assertArrayNotHasKey('DISABLE',  $post_row);
	}

	/**
	* Viewer (user 2) is in the likers for post 1.
	* Expected: POST_LIKE_CLASS='liked', ACTION_ON_CLICK='CLICK_TO_UNLIKE'.
	*/
	public function test_modify_post_row_current_user_liked(): void
	{
		$this->set_listener();
		$this->config['postlove_show_likes']  = 0;
		$this->config['postlove_show_liked']  = 0;
		$this->config['postlove_show_button'] = 0;
		$this->config['postlove_author_like'] = 1;
		$this->user->data           = ['user_id' => 2]; // user 2 liked post 1 in fixture
		$this->user->profile_fields = [];

		$this->language->method('lang')->willReturnArgument(0);
		$this->controller_helper->method('route')->willReturn('/postlove/toggle/1');
		$this->auth->method('acl_get')->willReturn(true);

		$this->listener->prefetch_likes(new \phpbb\event\data([
			'post_list' => [1],
			'rowset'    => [1 => ['user_id' => 3]],
		]));

		$event = new \phpbb\event\data([
			'row'       => ['post_id' => 1, 'user_id' => 3],
			'poster_id' => 3,
			'post_row'  => [],
		]);
		$this->listener->modify_post_row($event);
		$post_row = $event['post_row'];

		$this->assertSame('liked',           $post_row['POST_LIKE_CLASS']);
		$this->assertSame('CLICK_TO_UNLIKE', $post_row['ACTION_ON_CLICK']);
	}

	/**
	* Viewer lacks u_postlove permission.
	* Expected: DISABLE=1, ACTION_ON_CLICK='LOGIN_TO_LIKE_POST'.
	*/
	public function test_modify_post_row_no_permission(): void
	{
		$this->set_listener();
		$this->config['postlove_show_likes']  = 0;
		$this->config['postlove_show_liked']  = 0;
		$this->config['postlove_show_button'] = 0;
		$this->config['postlove_author_like'] = 1;
		$this->user->data           = ['user_id' => 5];
		$this->user->profile_fields = [];

		$this->language->method('lang')->willReturnArgument(0);
		$this->controller_helper->method('route')->willReturn('/postlove/toggle/1');
		$this->auth->method('acl_get')->with('u_postlove')->willReturn(false);

		$this->listener->prefetch_likes(new \phpbb\event\data([
			'post_list' => [1],
			'rowset'    => [1 => ['user_id' => 3]],
		]));

		$event = new \phpbb\event\data([
			'row'       => ['post_id' => 1, 'user_id' => 3],
			'poster_id' => 3,
			'post_row'  => [],
		]);
		$this->listener->modify_post_row($event);
		$post_row = $event['post_row'];

		$this->assertSame(1,                    $post_row['DISABLE']);
		$this->assertSame('LOGIN_TO_LIKE_POST', $post_row['ACTION_ON_CLICK']);
	}

	/**
	* postlove_author_like=0 and viewer is the post's author.
	* The self-like check fires (CANT_LIKE_OWN_POST); the anonymous/permission
	* check does not fire (user has permission and is not anonymous).
	* Expected: DISABLE=1, ACTION_ON_CLICK='CANT_LIKE_OWN_POST'.
	*/
	public function test_modify_post_row_self_like_disabled(): void
	{
		$this->set_listener();
		$this->config['postlove_show_likes']  = 0;
		$this->config['postlove_show_liked']  = 0;
		$this->config['postlove_show_button'] = 0;
		$this->config['postlove_author_like'] = 0; // self-like disabled
		$this->user->data           = ['user_id' => 3]; // same as poster_id
		$this->user->profile_fields = [];

		$this->language->method('lang')->willReturnArgument(0);
		$this->controller_helper->method('route')->willReturn('/postlove/toggle/1');
		$this->auth->method('acl_get')->willReturn(true);

		$this->listener->prefetch_likes(new \phpbb\event\data([
			'post_list' => [1],
			'rowset'    => [1 => ['user_id' => 3]],
		]));

		$event = new \phpbb\event\data([
			'row'       => ['post_id' => 1, 'user_id' => 3],
			'poster_id' => 3,
			'post_row'  => [],
		]);
		$this->listener->modify_post_row($event);
		$post_row = $event['post_row'];

		$this->assertSame(1,                    $post_row['DISABLE']);
		$this->assertSame('CANT_LIKE_OWN_POST', $post_row['ACTION_ON_CLICK']);
	}
}
