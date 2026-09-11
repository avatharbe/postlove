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
 * Adds four independent like-visibility opt-outs to
 * UCP > Board preferences > Edit global settings, reading and writing the
 * user_postlove_hide* columns: the like button, the "Likes" link on your
 * own profile, the topic-list like count, and the most-liked-posts summary
 * panels.
 *
 * Replaces the postlove_hide custom profile field (removed in
 * release_2_2_7_remove_hide_cpf), which lived on the Profile tab instead and
 * bundled all four under one switch (#55).
 */
class ucp_listener implements EventSubscriberInterface
{
	/** Form field name => users table column name, for the four toggles. */
	private const FIELDS = array(
		'postlove_hide'			=> 'user_postlove_hide',
		'postlove_hide_profile'	=> 'user_postlove_hide_profile',
		'postlove_hide_topics'		=> 'user_postlove_hide_topics',
		'postlove_hide_sum'		=> 'user_postlove_hide_sum',
	);

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
		$data = $event['data'];
		$template_vars = array();

		foreach (self::FIELDS as $field_name => $column_name)
		{
			$data[$field_name] = $this->request->variable($field_name, (int) $this->user->data[$column_name]);
			$template_vars['S_' . strtoupper($field_name)] = $data[$field_name];
		}

		if (!$event['submit'])
		{
			$this->template->assign_vars($template_vars);
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
		$sql_ary = $event['sql_ary'];

		foreach (self::FIELDS as $field_name => $column_name)
		{
			$sql_ary[$column_name] = (int) $event['data'][$field_name];
		}

		$event['sql_ary'] = $sql_ary;
	}
}
