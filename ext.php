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
* Extension class for board announcements
*/

class ext extends \phpbb\extension\base
{
	const MIN_PHP_VERSION = '8.1.0';
	const MIN_PHPBB_VERSION = '3.3.0';

	/**
	* Check whether the extension can be enabled.
	*
	* @return bool|array True if enableable, or an array of error language keys otherwise
	*/
	public function is_enableable()
	{
		$errors = [];

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$errors[] = 'This extension requires PHP ' . self::MIN_PHP_VERSION . ' or higher. You are running PHP ' . PHP_VERSION . '.';
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '<'))
		{
			$errors[] = 'This extension requires phpBB ' . self::MIN_PHPBB_VERSION . ' or higher. You are running phpBB ' . PHPBB_VERSION . '.';
		}

		return empty($errors) ? true : $errors;
	}

	/**
	* Single enable step that installs any included migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Enable board rules notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('avathar.postlove.notification.type.postlove');
				return 'notifications';

			break;

			default:

				// Run parent enable step method
				return parent::enable_step($old_state);

			break;
		}
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Disable board rules notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('avathar.postlove.notification.type.postlove');
				return 'notifications';

			break;

			default:

				// Run parent disable step method
				return parent::disable_step($old_state);

			break;
		}
	}

	/**
	* Single purge step that reverts any included and installed migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				/**
				* @todo Remove this try/catch condition once purge_notifications is fixed
				* in the core to work with disabled extensions without fatal errors.
				* https://tracker.phpbb.com/browse/PHPBB3-12435
				*/
				try
				{
					// Purge board rules notifications
					$phpbb_notifications = $this->container->get('notification_manager');
					$phpbb_notifications->purge_notifications('avathar.postlove.notification.type.postlove');
				}
				catch (\phpbb\notification\exception $e)
				{
					// continue
				}

				return 'notifications';

			break;

			default:

				// Run parent purge step method
				return parent::purge_step($old_state);

			break;
		}
	}
}
