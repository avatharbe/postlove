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
 * ACP module info class.
 *
 * Registers the Post Love ACP module under the ACP_POSTLOVE_GRP category.
 * Requires the extension to be enabled and the acl_a_user permission.
 */
class acp_postlove_info
{
	/**
	 * Return the module definition for phpBB's ACP module system.
	 *
	 * @return array Module metadata (filename, title, version, modes)
	 */
	function module()
	{
		return array(
			'filename'	=> 'avathar\postlove\acp\acp_postlove_module',
			'title'		=> 'ACP_POSTLOVE',
			'version'	=> '2.1.0',
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
