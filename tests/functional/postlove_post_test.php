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
* End-to-end functional tests for the postlove like feature on viewtopic.
*
* Tests the full like lifecycle: creating content, toggling likes via the AJAX
* endpoint, and verifying that like counts display correctly on the page in
* both inline mode (CSS class .postlove) and button mode (CSS class .postlove-li).
* Also tests guest visibility, guest like prevention, ACP-driven profile
* counter toggles (likes given/received), and the love list page.
*
* @group functional
*/
class postlove_post_test extends postlove_base
{
	protected $post2 = array();

	/**
	* Test the full like/unlike cycle in both display modes (inline and button).
	*
	* Steps:
	* 1. Log in and create a topic with a reply post
	* 2. Switch to inline mode (button_mode=0) and verify the .postlove span exists
	* 3. Toggle a like on the reply via AJAX, reload, and verify "1" appears
	* 4. Toggle unlike via AJAX (removes the like)
	* 5. Switch to button mode (button_mode=1) and verify the .postlove-li span exists
	* 6. Toggle like again, reload, and verify "1" appears in .postlove-count
	* 7. Log out
	*/
	public function test_post()
	{
		$this->login();

		// Test creating topic and post to test
		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test [b]post[/b] posted by the testing framework.');

        $this->set_button_mode(0);
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		//Do we see the static?
		$class = $crawler->filter('#p' . $post2['post_id'])->filter('.postlove')->filter('span')->attr('class');

		//toggle like
		$crw1 = self::request('GET', 'app.php/postlove/toggle/' . $post2['post_id'], array(), array(), array('CONTENT_TYPE'	=> 'application/json'));

		//reload page and test ...
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('1', $crawler->filter('#p' . $post2['post_id'])->filter('.postlove')->text());

		//toggle like
		$crw1 = self::request('GET', 'app.php/postlove/toggle/' . $post2['post_id'], array(), array(), array('CONTENT_TYPE'	=> 'application/json'));

        $this->set_button_mode(1);
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		//Do we see the static?
		$class = $crawler->filter('#p' . $post2['post_id'])->filter('.postlove-li')->filter('span')->attr('class');

		//toggle like
		$crw1 = self::request('GET', 'app.php/postlove/toggle/' . $post2['post_id'], array(), array(), array('CONTENT_TYPE'	=> 'application/json'));

		//reload page and test ...
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertStringContainsString('1', $crawler->filter('#p' . $post2['post_id'])->filter('.postlove-count')->text());

		$this->logout();
	}

	/**
	* Test that guests (not logged in) can see like counts on posts.
	*
	* Verifies in both inline mode and button mode that the like count
	* from test_post (which left a like on post 3) is visible to an
	* unauthenticated visitor.
	*/
	public function test_guest_see_loves()
	{
        $this->set_button_mode(0);
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1', $crawler->filter('#p3')->filter('.postlove')->text());

        $this->set_button_mode(1);

		//reload page and test ...
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1', $crawler->filter('#p3')->filter('.postlove-count')->text());
	}
	
	/**
	* Test that guests cannot toggle likes.
	*
	* Attempts to toggle a like via AJAX as a guest (not logged in),
	* then verifies the like count remains unchanged (still "1").
	* The controller should reject the request because the guest user
	* does not have the u_postlove permission (guests are blocked since 2.2.0).
	*/
	public function test_guests_cannot_like()
	{
		$crw1 = self::request('GET', 'app.php/postlove/toggle/3', array(), array(), array('CONTENT_TYPE'	=> 'application/json'));

        $this->set_button_mode(0);
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1', $crawler->filter('#p3')->filter('.postlove')->text());
		
	}
	/**
	* Test the ACP toggles for showing likes given/received counters on profiles.
	*
	* Exercises four configurations via the ACP form:
	* 1. Both counters disabled (default) => neither .liked_info nor .like_info present
	* 2. Show likes received only (postlove_show_likes=1, show_liked=0)
	*    => .liked_info visible with count, .like_info absent
	* 3. Show likes given only (postlove_show_likes=0, show_liked=1)
	*    => .like_info visible with count, .liked_info absent
	* 4. Both counters enabled => both .liked_info and .like_info visible
	*
	* Each step logs into ACP, submits the settings form, then verifies
	* the viewtopic page reflects the change in the post profile area.
	*/
	public function test_show_likes_given()
	{
		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.liked_info')->count());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.like_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('avathar/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-avathar-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 1,
			'poslove[postlove_show_liked]'	=> 0,
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString('Changes saved!', $crawler->text());
		$this->logout();

		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.liked_info')->parents()->text());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.like_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('avathar/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-avathar-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 0,
			'poslove[postlove_show_liked]'	=> 1,
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString('Changes saved!', $crawler->text());
		$this->logout();

		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.like_info')->parents()->text());
		$this->assertEquals(0,  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.liked_info')->count());
		$this->logout();
		
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('avathar/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-avathar-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form->setValues(array(
			'poslove[postlove_show_likes]'	=> 1,
			'poslove[postlove_show_liked]'	=> 1,
		));
		$crawler = self::submit($form);
		$this->assertStringContainsString('Changes saved!', $crawler->text());
		$this->logout();

		$this->login();
		$crawler = self::request('GET', "viewtopic.php?t=2&sid={$this->sid}");
		$this->assertStringContainsString('1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.like_info')->parents()->text());
		$this->assertStringContainsString('1',  $crawler->filter('.post')->eq(0)->filter('.inner')->filter('.postprofile')->filter('.profile-custom-field')->filter('.liked_info')->parents()->text());
		$this->logout();
	}

	/**
	* Test the love list page (app.php/postlove/{user_id}).
	*
	* Logs in, loads the love list for user 2, and verifies that exactly
	* one liked post appears in the list (rendered as a topiclist item).
	*/
	public function test_show_list()
	{
		$this->login();
		$this->add_lang_ext('avathar/postlove', 'postlove');
	
		$crawler = self::request('GET', "app.php/postlove/2?sid={$this->sid}");
		//$this->assertStringContainsString('zzazaza', $crawler->text());
		$this->assertEquals(1, $crawler->filter('.inner')->filter('.topiclist')->filter('ul')->filter('li')->count());
	}
}
