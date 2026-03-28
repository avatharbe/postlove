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
* @group functional
*/
class postlove_acp_test extends postlove_base
{

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