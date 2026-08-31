<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove;

/**
* Extension class for custom enable/disable/purge actions.
*
* Enables and disables the postlove notification type alongside the extension
* itself, and enforces the PHP and phpBB version requirements before the
* extension can be enabled.
*/

class ext extends \phpbb\extension\base
{
	/**
	* Canonical extension version. Kept here rather than in phpbb_config, matching
	* the convention in the sibling extensions (recenttopics, bbguild). Bump this
	* together with composer.json and the README changelog.
	*/
	const POSTLOVE_VERSION = '2.2.6';
	const MIN_PHP_VERSION = '8.1.0';
	const MIN_PHPBB_VERSION = '3.3.0';

	/**
	* Check whether the extension can be enabled.
	*
	* @return bool|array True if enableable, or an array of error language keys otherwise
	*/
	public function is_enableable(): bool|array
	{
		$errors = [];

		/** @var \phpbb\language\language $language */
		$language = $this->container->get('language');
		$language->add_lang('postlove', 'avathar/postlove');

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$errors[] = $language->lang('POSTLOVE_PHP_VERSION_FAIL', self::MIN_PHP_VERSION, PHP_VERSION);
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '<'))
		{
			$errors[] = $language->lang('POSTLOVE_PHPBB_VERSION_FAIL', self::MIN_PHPBB_VERSION, PHPBB_VERSION);
		}

		return empty($errors) ? true : $errors;
	}

	/**
	* Single enable step that installs any included migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return string|bool The next step's state, or false once the parent reports finished
	*/
	public function enable_step($old_state): string|bool
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Enable postlove notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('avathar.postlove.notification.type.postlove');
				return 'notifications';

			default:

				// Run parent enable step method
				return parent::enable_step($old_state);
		}
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return string|bool The next step's state, or false once the parent reports finished
	*/
	public function disable_step($old_state): string|bool
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Disable postlove notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('avathar.postlove.notification.type.postlove');
				return 'notifications';

			default:

				// Run parent disable step method
				return parent::disable_step($old_state);
		}
	}

	/**
	* Single purge step that reverts any included and installed migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return string|bool The next step's state, or false once the parent reports finished
	*/
	public function purge_step($old_state): string|bool
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Purge postlove notifications. No try/catch: purge_notifications()
				// swallows the exception thrown when the type was never registered,
				// fixed in phpBB 3.1.0-b4 (PHPBB3-12435), and this extension requires
				// 3.3.0 or higher.
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('avathar.postlove.notification.type.postlove');

				return 'notifications';

			default:

				// Run parent purge step method
				return parent::purge_step($old_state);
		}
	}
}
