<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the like button and most-liked-posts summary opt-outs to
 * UCP > Board preferences > Edit global settings, reading and writing the
 * user_postlove_hide / user_postlove_hide_sum columns.
 *
 * Replaces the postlove_hide custom profile field (removed in
 * release_2_2_7_remove_hide_cpf), which lived on the Profile tab instead and
 * bundled both under one switch (#55).
 */
class ucp_listener implements EventSubscriberInterface
{
	protected \phpbb\request\request $request;
	protected \phpbb\template\template $template;
	protected \phpbb\user $user;

	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	public static function getSubscribedEvents()
	{
		return array(
			'core.ucp_prefs_view_data'			=> 'ucp_prefs_get_data',
			'core.ucp_prefs_view_update_data'	=> 'ucp_prefs_set_data',
		);
	}

	/**
	 * Collect the submitted (or current) values into $event['data'] for
	 * ucp_prefs_set_data(), then, on a plain page view, assign the template
	 * vars the radio buttons in ucp_prefs_view_radio_buttons_append.html read.
	 *
	 * @param \phpbb\event\data $event The core.ucp_prefs_view_data event
	 */
	public function ucp_prefs_get_data($event)
	{
		$data = array_merge($event['data'], array(
			'postlove_hide'		=> $this->request->variable('postlove_hide', (int) $this->user->data['user_postlove_hide']),
			'postlove_hide_sum'	=> $this->request->variable('postlove_hide_sum', (int) $this->user->data['user_postlove_hide_sum']),
		));

		if (!$event['submit'])
		{
			$this->template->assign_vars(array(
				'S_POSTLOVE_HIDE'		=> $data['postlove_hide'],
				'S_POSTLOVE_HIDE_SUM'	=> $data['postlove_hide_sum'],
			));
		}

		$event['data'] = $data;
	}

	/**
	 * Fold the collected values into the UPDATE phpBB runs on the users
	 * table.
	 *
	 * @param \phpbb\event\data $event The core.ucp_prefs_view_update_data event
	 */
	public function ucp_prefs_set_data($event)
	{
		$event['sql_ary'] = array_merge($event['sql_ary'], array(
			'user_postlove_hide'		=> (int) $event['data']['postlove_hide'],
			'user_postlove_hide_sum'	=> (int) $event['data']['postlove_hide_sum'],
		));
	}
}
