<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2018 v12mike
* @copyright (c) 2026 Avathar.be
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace avathar\postlove\tests\summaryevent;

/**
* Tests for the summary_listener (most-liked-posts summary panels).
*
* Verifies that the summary panels on the board index page and viewforum page
* correctly query and display the most-liked posts, respecting forum read
* permissions and configurable time period filters (ever, this year, this month,
* this week, today).
*
* Fixture: tests/event/fixtures/summary_data.xml
* Contains 2 forums, 3 topics, 5 posts, and 6 likes with timestamps carefully
* chosen so that period-based filtering is deterministic. Likes span from
* timestamp 1000 to 5000, with the test clock fixed at 1500000000 (July 2017)
* to make year/month/week/today boundaries predictable.
*
* The template mock uses willReturnCallback() to capture assign_block_vars calls
* in order, replacing the deprecated $this->at() PHPUnit matcher for PHPUnit 9+
* compatibility.
*
* @group event
*/

class summary_event extends \phpbb_database_test_case
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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/summary_data.xml');
	}

	/**
	* Set up the test environment with all dependencies for summary_listener.
	*
	* Several objects are assigned to PHP globals ($auth, $config, $user, etc.)
	* because phpBB core functions like get_username_string() and
	* get_complete_topic_tracking() read from globals directly.
	*
	* Mocks and services:
	* - auth: controls forum read permissions (acl_getf) per test case
	* - config: empty config; each test sets the postlove_*_most_liked_* values
	* - db: test DBAL with summary_data.xml fixture
	* - dispatcher/phpbb_dispatcher: mock event dispatcher for cache and content_visibility
	* - cache: real cache service backed by a dummy driver (purged each run)
	* - request: mock returning empty strings (needed by append_sid in topic tracking)
	* - template: mock to assert assign_vars and assign_block_vars calls
	* - user: mock user with data array preset (user_id=2, registered, high lastmark
	*   so all topics appear as read); format_date returns the raw timestamp for
	*   easy assertion. Assigned to $user global for get_username_string().
	* - content_visibility: real instance wired to test DB, controls post/topic visibility
	* - language: mock that returns the first argument unchanged (language key passthrough)
	*/
	public function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher, $user, $config, $auth, $cache, $request;

		parent::setUp();
		// Setup Auth
		$this->auth = $this->createMock('\phpbb\auth\auth');
		$auth = $this->auth;

		//Setup Config
		$this->config = new \phpbb\config\config(array());
		$config = $this->config;

		// Setup DB
		$this->db = $this->new_dbal();

		$this->dispatcher = new \phpbb_mock_event_dispatcher();
		$phpbb_dispatcher = $this->dispatcher;

		//Setup Cache
		$this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), $this->config, $this->db, $this->dispatcher, $phpbb_root_path, $phpEx);
		$cache = $this->cache;

		// Setup Request (needed by get_complete_topic_tracking -> append_sid)
		$request = $this->createMock('\phpbb\request\request');
		$request->method('variable')
			->willReturn('');

		// Setup template
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		// Setup User
		$this->user = $this->createMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime',
			));
		$this->user->data = array(
			'user_id' => 2,
			'is_bot' => false,
			'is_registered' => true,
			'user_type' => 0,
			'user_lastmark' => 9999999999,
		);
		$this->user->profile_fields = array();
		$this->user->method('format_date')
			->will($this->returnArgument(0));
		$user = $this->user;

		$this->content_visibility = new \phpbb\content_visibility($this->auth, $this->config, $this->dispatcher, $this->db, $this->user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->method('lang')
			->will($this->returnArgument(0));
	}

	/**
	* Instantiate summary_listener with all dependencies.
	*
	* The last constructor argument (1500000000 = 2017-07-14 02:40:00 UTC) is
	* a fixed "now" timestamp used for period calculations instead of time().
	* This makes the year/month/week/today boundary filters deterministic:
	* - "this year" = likes since 2017-01-01
	* - "this month" = likes since 2017-07-01
	* - "this week" = likes since 2017-07-10 (Monday)
	* - "today" = likes since 2017-07-14 00:00
	*/
	protected function set_listener()
	{
		$this->listener = new \avathar\postlove\event\summary_listener(
			$this->auth,
			$this->config,
			$this->cache,
			$this->content_visibility,
			$this->db,
			$this->dispatcher,
			$this->template,
			$this->user,
			$this->language,
			'/',
			'.php',
			'phpbb_',
			1500000000 // test liketime
		);
	}

	/**
	* Verify that summary_listener subscribes to exactly the four events it needs:
	* - core.index_modify_page_title: render the summary panel on the board index
	* - core.viewforum_modify_page_title: render the summary panel on viewforum
	* - core.viewforum_modify_topics_data: inject like counts into topic data
	* - core.viewforum_modify_topicrow: add like counts to individual topic rows
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.index_modify_page_title',
			'core.viewforum_modify_page_title',
			'core.viewforum_modify_topics_data',
			'core.viewforum_modify_topicrow',
		), array_keys(\avathar\postlove\event\summary_listener::getSubscribedEvents()));
	}

	/**
	* Data provider for test_index_modify_page_title.
	*
	* Test matrix (all on the board index, which shows posts across all readable forums):
	*
	* 'show all'              - registered user, "ever" limit=10, both forums readable
	*                           => 5 most-liked posts across both forums
	* 'show all only Forum 1' - registered user, "ever" limit=10, only forum 1 readable
	*                           => 4 posts (forum 2 post excluded by permission)
	* 'anonymous user'        - user_id=1 (ANONYMOUS), all periods enabled with limit=1,
	*                           only forum 1 readable => 3 posts (one per period: ever,
	*                           this_year, this_month); username renders as <a> link
	*                           instead of <span> since anonymous users see profile links
	* 'only this year'        - only postlove_liked_this_year=10, forum 1 readable
	*                           => 3 posts with likes in current year + 1 duplicate
	*                           from the "ever" bucket that also qualifies
	* 'only this month'       - only postlove_liked_this_month=10 => 2 posts
	* 'only this week'        - only postlove_liked_this_week=10 => 2 posts
	* 'only today'            - only postlove_liked_today=10 => 1 post
	* 'none at all'           - all period limits=0 => 0 results, empty summary
	*
	* Each entry provides: user_id, five period limits, permission array,
	* expected template vars, expected block_vars count, expected block_vars data.
	*/
	public function data_index_modify_page_title()
	{
		return array(
			'show all' => array(
				2, // user_id
				10, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
						2 => array('f_read' => 1), // can view forum 2
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 5,
				),
				5, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=4#p4',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '4000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=2&amp;t=2&amp;p=2#p2',
						'U_FORUM'   		=> '/viewforum..php?f=2',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '2000',
						'TOPIC_TITLE'   	=> 'test 2',
						'FORUM_NAME'		=> 'Forum 2',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),	
					),
				),
			'show all only in Forum 1' => array(
				2, // user_id
				10, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 4,
				),
				4, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=4#p4',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '4000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'anonymous user' => array(
				1, // user_id
				1, // postlove_liked_ever
				1, // postlove_liked_this_year
				1, // postlove_liked_this_month
				1, // postlove_liked_this_week
				1, // postlove_liked_today
				array(
					1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 3,
					),
				3, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this year' => array(
				2, // user_id
				0, // postlove_liked_ever
				10, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 3,
				),
				3, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this month' => array(
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				10, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 2,
				),
				2, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this week' => array(
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				10, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 2,
				),
				2, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_WEEK',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_WEEK',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only today' => array(
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				10, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 1,
				),
				1, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_TODAY',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'none at all' => array(
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
						2 => array('f_read' => 1), // can view forum 2
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 0,
				),
				0, // count
				array(),
				),
			);
	}

	/**
	* Test that the index page summary panel renders the correct most-liked posts.
	*
	* For each data provider case, this test:
	* 1. Configures the postlove_index_most_liked_* config values
	* 2. Sets the user ID and forum read permissions
	* 3. Dispatches the core.index_modify_page_title event through a real dispatcher
	* 4. Asserts that template->assign_vars receives the correct summary count
	* 5. Asserts that template->assign_block_vars is called the expected number of
	*    times with the correct post data (topic URL, forum URL, username, like count, period)
	*
	* Uses willReturnCallback on assign_block_vars to capture calls in order,
	* replacing the deprecated $this->at() matcher for PHPUnit 9+ compatibility.
	*
	* @dataProvider data_index_modify_page_title
	*/
	public function test_index_modify_page_title($user_id,
												 $postlove_liked_ever,
												 $postlove_liked_this_year,
												 $postlove_liked_this_month,
												 $postlove_liked_this_week,
												 $postlove_liked_today,
												 $permissions,
												 $expected1,
												 $count,
												 $expected2)
	{
		$this->config['postlove_index_most_liked_ever'] = $postlove_liked_ever;
		$this->config['postlove_index_most_liked_this_year'] = $postlove_liked_this_year;
		$this->config['postlove_index_most_liked_this_month'] = $postlove_liked_this_month;
		$this->config['postlove_index_most_liked_this_week'] = $postlove_liked_this_week;
		$this->config['postlove_index_most_liked_today'] = $postlove_liked_today;
		$this->user->data['user_id'] = $user_id;
		$tmp = $permissions;
		$this->auth->expects($this->any())
			->method('acl_getf')
			->willreturn($permissions);
		$this->auth->expects($this->any())
			->method('acl_get')
			->with('u_postlove_summary')
			->willReturn(true);

		$event_data = array();
		$event = new \phpbb\event\data(compact($event_data));
		$this->set_listener();
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.index_modify_page_title', array($this->listener, 'index_page_summary'));
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($expected1);
		$block_vars_calls = [];
		$this->template->expects($this->exactly($count))
			->method('assign_block_vars')
			->willReturnCallback(function ($block, $data) use (&$block_vars_calls) {
				$block_vars_calls[] = [$block, $data];
			});

		$dispatcher->dispatch('core.index_modify_page_title', $event);

		for ($i = 0; $i < $count; $i++) {
			$this->assertEquals('most_liked_posts', $block_vars_calls[$i][0]);
			$this->assertEquals($expected2[$i], $block_vars_calls[$i][1]);
		}
	}

	/**
	* Data provider for test_viewforum_modify_page_title.
	*
	* Same test matrix as data_index_modify_page_title but scoped to a single
	* forum (forum_id=1). The viewforum summary only shows posts belonging to
	* the viewed forum and its sub-forums, so forum 2 posts never appear
	* regardless of read permissions. Each entry adds forum_id as the first
	* parameter compared to the index data provider.
	*
	* Cases: 'show all', 'show all only in Forum 1', 'anonymous user',
	* 'only this year', 'only this month', 'only this week', 'only today',
	* 'none at all'.
	*/
	public function data_viewforum_modify_page_title()
	{
		return array(
			'show all' => array(
				1, //forum_id
				2, // user_id
				10, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
						2 => array('f_read' => 1), // can view forum 2
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 4,
				),
				4, // count (only forum 1 posts, no sub-forums)
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=4#p4',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '4000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'show all only in Forum 1' => array(
				1, //forum_id
				2, // user_id
				10, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 4,
				),
				4, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=4#p4',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '4000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'anonymous user' => array(
				1, //forum_id
				1, // user_id
				1, // postlove_liked_ever
				1, // postlove_liked_this_year
				1, // postlove_liked_this_month
				1, // postlove_liked_this_week
				1, // postlove_liked_today
				array(
					1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 3,
					),
				3, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_EVER',
						'LIKES_IN_PERIOD'   => 2,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=3" style="color: #blue;" class="username-coloured">Test user 3</a>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this year' => array(
				1, //forum_id
				2, // user_id
				0, // postlove_liked_ever
				10, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 3,
				),
				3, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=3&amp;p=5#p5',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '5000',
						'TOPIC_TITLE'   	=> 'test 3',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_YEAR',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this month' => array(
				1, //forum_id
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				10, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 2,
				),
				2, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_MONTH',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only this week' => array(
				1, //forum_id
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				10, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 2,
				),
				2, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=3#p3',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '3000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_WEEK',
						'LIKES_IN_PERIOD'   => 1,
						),
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_THIS_WEEK',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'only today' => array(
				1, //forum_id
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				10, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 1,
				),
				1, // count
				array(
					array(
						'U_TOPIC'   		=> '/viewtopic..php?f=1&amp;t=1&amp;p=1#p1',
						'U_FORUM'   		=> '/viewforum..php?f=1',
						'S_UNREAD'  		=> false,
						'USERNAME_FULL' 	=> '<span style="color: #blue;" class="username-coloured">Test user 3</span>',
						'POST_TIME' 		=> '1000',
						'TOPIC_TITLE'   	=> 'test 1',
						'FORUM_NAME'		=> 'Forum 1',
						'POST_LIKES_IN_PERIOD'  => 'LIKES_TODAY',
						'LIKES_IN_PERIOD'   => 1,
						),
					),
				),
			'none at all' => array(
				1, //forum_id
				2, // user_id
				0, // postlove_liked_ever
				0, // postlove_liked_this_year
				0, // postlove_liked_this_month
				0, // postlove_liked_this_week
				0, // postlove_liked_today
				array(
						1 => array('f_read' => 1), // can view forum 1
						2 => array('f_read' => 1), // can view forum 2
					), // user permissions
				array(
					'S_MOSTLIKEDSUMMARYCOUNT'	=> 0,
				),
				0, // count
				array(),
				),
			);
	}

	/**
	* Test that the viewforum summary panel renders correctly for a single forum.
	*
	* Similar to test_index_modify_page_title but dispatches
	* core.viewforum_modify_page_title and passes forum_id and forum_data
	* (with left_id/right_id for sub-forum tree bounds) in the event.
	* Uses postlove_forum_most_liked_* config keys instead of the index variants.
	*
	* @dataProvider data_viewforum_modify_page_title
	*/
	public function test_viewforum_modify_page_title($forum_id,
													 $user_id,
													 $postlove_liked_ever,
													 $postlove_liked_this_year,
													 $postlove_liked_this_month,
													 $postlove_liked_this_week,
													 $postlove_liked_today,
													 $permissions,
													 $expected1,
													 $count,
													 $expected2)
	{
		$this->config['postlove_forum_most_liked_ever'] = $postlove_liked_ever;
		$this->config['postlove_forum_most_liked_this_year'] = $postlove_liked_this_year;
		$this->config['postlove_forum_most_liked_this_month'] = $postlove_liked_this_month;
		$this->config['postlove_forum_most_liked_this_week'] = $postlove_liked_this_week;
		$this->config['postlove_forum_most_liked_today'] = $postlove_liked_today;
		$this->user->data['user_id'] = $user_id;
		$tmp = $permissions;
		$this->auth->expects($this->any())
			->method('acl_getf')
			->willreturn($permissions);
		$this->auth->expects($this->any())
			->method('acl_get')
			->with('u_postlove_summary')
			->willReturn(true);

		$forum_data = array(
			'forum_id' => $forum_id,
			'left_id' => 1,
			'right_id' => 2,
		);
		$event_data = array('forum_id', 'forum_data');
		$event = new \phpbb\event\data(compact($event_data));
		$this->set_listener();
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewforum_modify_page_title', array($this->listener, 'forum_page_summary'));
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($expected1);
		$block_vars_calls = [];
		$this->template->expects($this->exactly($count))
			->method('assign_block_vars')
			->willReturnCallback(function ($block, $data) use (&$block_vars_calls) {
				$block_vars_calls[] = [$block, $data];
			});

		$dispatcher->dispatch('core.viewforum_modify_page_title', $event);

		for ($i = 0; $i < $count; $i++) {
			$this->assertEquals('most_liked_posts', $block_vars_calls[$i][0]);
			$this->assertEquals($expected2[$i], $block_vars_calls[$i][1]);
		}
	}
}

