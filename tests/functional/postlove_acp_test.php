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
* Functional test for the postlove ACP (Admin Control Panel) module.
*
* Verifies that the ACP configuration page for Post Love loads correctly
* and displays the expected settings form with proper language keys.
*
* @group functional
*/
class postlove_acp_test extends postlove_base
{

	/**
	* Test that the ACP postlove configuration page loads and contains
	* the expected form fields.
	*
	* Steps:
	* 1. Log in as admin
	* 2. Load the extension's language file (info_acp_postlove)
	* 3. Request the ACP module page
	* 4. Assert that the POSTLOVE_SHOW_LIKES and POSTLOVE_SHOW_LIKED
	*    language strings appear on the page (confirming the form rendered)
	*/
	public function test_acp_pages()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('avathar/postlove', 'info_acp_postlove');

		$crawler = self::request('GET', 'adm/index.php?i=-avathar-postlove-acp-acp_postlove_module&mode=main&sid=' . $this->sid);
		$this->assertContainsLang('POSTLOVE_SHOW_LIKES', $crawler->text());
		$this->assertContainsLang('POSTLOVE_SHOW_LIKED', $crawler->text());
	}
}