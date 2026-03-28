<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
* Registers the ACP module and adds the postlove_author_like config entry.
*/

class release_1_1_0 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array(
			'\avathar\postlove\migrations\release_1_0_1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('postlove_installed_theme', 'default')),
			array('config.add', array('postlove_author_like', 1)),
			array('config.remove', array('postlove_use_css')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_POSTLOVE_GRP'
			)),
			array('module.add', array(
				'acp',
				'ACP_POSTLOVE_GRP',
				array(
					'module_basename'	=> '\avathar\postlove\acp\acp_postlove_module',
					'module_mode'		=> array('main'),
					'module_auth'        => 'ext_avathar/postlove && acl_a_user',
				)
			)),
		);
	}
}
