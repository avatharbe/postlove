<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\notification;

/**
 * Notification type for post likes.
 *
 * Generates an in-board notification when a user likes another user's post.
 * The notification shows the liker's username and links to the liked post.
 *
 * Deduplication: item_id = post_id, so each liked post generates a unique
 * notification. item_parent_id = requester_id (the liker), used for deletion
 * when a like is removed.
 *
 * Email notifications are not supported (get_email_template returns false).
 * Users can disable this notification type in UCP -> Board preferences.
 *
 * Dependencies are injected via setter methods (set_config, set_user_loader)
 * called from services.yml, since notification types extend the base class
 * which has its own constructor.
 */
class postlove extends \phpbb\notification\type\base
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.postlove';
	}

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_POST_LOVE',
	);

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Inject the config service (called from services.yml).
	 *
	 * @param \phpbb\config\config $config
	 */
	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Inject the user_loader service (called from services.yml).
	 *
	 * @param \phpbb\user_loader $user_loader
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	* Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	*
	* @return bool True/False whether or not this is available to the user
	*/
	public function is_available()
	{
		return true;
	}

	/**
	 * Get the id of the liked post
	 *
	 * @param array $data The data for the like
	 * @return int
	 */
	public static function get_item_id($data)
	{
		return (int) $data['post_id'];
	}

	/**
	 * Get the id of the liker
	 *
	 * @param array $data The data for the like
	 * @return int
	 */
	public static function get_item_parent_id($data)
	{
		return (int) $data['requester_id'];
	}

	/**
	 * Find the users who should receive this notification.
	 *
	 * Returns the post author (user_id) as the sole recipient, filtered
	 * through check_user_notification_options() to respect UCP preferences.
	 * The liker (requester_id) is excluded by the notifyhelper before this
	 * method is called (self-notifications are prevented there).
	 *
	 * @param array $data    Notification data (user_id = post author, requester_id = liker)
	 * @param array $options Options including 'ignore_users'
	 * @return array Users to notify, filtered by their notification preferences
	 */
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'			=> array(),
		), $options);
		$users = array(
			$data['user_id']	=> 0,
		);
		$this->user_loader->load_users(array_keys($users));

		return $this->check_user_notification_options(array_keys($users), $options);
	}

	/**
	 * Get the avatar of the user who liked the post (the liker).
	 *
	 * @return string HTML for the avatar image
	 */
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('requester_id'), false, true);
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('requester_id'), 'no_profile');
		return $this->language->lang('NOTIFICATION_POSTLOVE_ADD', $username);
	}

	/**
	 * Get the HTML formatted reference of the notification
	 *
	 * @return string
	 */
	public function get_reference()
	{
		return censor_text($this->get_data('post_subject'));
	}

	/**
	 * Email notifications are not supported for post likes.
	 *
	 * @return false
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * No email template variables (email not supported).
	 *
	 * @return array Empty array
	 */
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p=". $this->get_data('post_id') . '#p' . $this->get_data('post_id'));
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->get_data('requester_id'));
	}

	/**
	 * Prepare notification data for database insertion.
	 *
	 * Stores the liker (requester_id), post author (user_id), post_id,
	 * topic_id, and post_subject so they can be retrieved later for
	 * display, URL generation, and deletion.
	 *
	 * @param array $data            Notification data from notifyhelper::notify()
	 * @param array $pre_create_data Data from pre_create_insert_array()
	 * @return array Data ready for database insertion
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('requester_id', $data['requester_id']);
		$this->set_data('user_id', $data['user_id']);
		$this->set_data('post_id', $data['post_id']);
		$this->set_data('topic_id', $data['topic_id']);
		$this->set_data('post_subject', $data['post_subject']);

		return parent::create_insert_array($data, $pre_create_data);
	}
}
