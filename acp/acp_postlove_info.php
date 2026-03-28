<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\acp;

/**
* @package module_install
*/

class acp_postlove_info
{
	function module()
	{
		return array(
			'filename'	=> 'avathar\postlove\acp\acp_postlove_module',
			'title'		=> 'ACP_POSTLOVE', // define in the lang/xx/acp/common.php language file
			'version'	=> '2.0.0-a2',
			'modes'		=> array(
				'main'		=> array(
					'title'		=> 'ACP_POSTLOVE',
					'auth' 		=> 'ext_avathar/postlove && acl_a_user',
					'cat'		=> array('ACP_POSTLOVE_GRP')
				),
			),
		);
	}
}
