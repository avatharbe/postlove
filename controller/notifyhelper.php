<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\controller;

use Symfony\Component\DependencyInjection\Container;

/**
 * Notification helper for like/unlike events.
 *
 * Creates phpBB notifications when a post is liked and removes them
 * when the like is undone. Self-likes (liker == poster) are silently
 * ignored to avoid notifying users about their own actions.
 */
class notifyhelper
{
	protected \phpbb\config\config $config;
	protected \phpbb\db\driver\driver_interface $db;
	protected \phpbb\request\request $request;
	protected \phpbb\template\template $template;
	protected \phpbb\user $user;
	protected Container $phpbb_container;
	protected string $root_path;
	protected string $php_ext;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, Container $phpbb_container, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_container = $phpbb_container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Create or remove a like notification.
	 *
	 * @param string $type         'add' to create a notification, 'remove' to delete it
	 * @param int    $topic_id     Topic containing the liked post
	 * @param int    $post_id      The liked post ID
	 * @param string $post_subject Subject of the liked post (shown in notification)
	 * @param int    $poster_user  User ID of the post author (notification recipient)
	 * @param int    $liker_user   User ID who liked/unliked the post
	 */
	public function notify($type, $topic_id, $post_id, $post_subject, $poster_user, $liker_user)
	{
		$notification_data = array(
			'topic_id'	=> (int) $topic_id,
			'post_id'	=> (int) $post_id,
			'post_subject'	=>	$post_subject,
			'user_id'	=> (int) $poster_user,
			'requester_id'	=> (int) $liker_user,
		);

		$phpbb_notifications = $this->phpbb_container->get('notification_manager');
		if ($notification_data['requester_id'] != $notification_data['user_id'])
		{
			switch ($type)
			{
				case 'add':
					$phpbb_notifications->add_notifications('avathar.postlove.notification.type.postlove', $notification_data);
				break;
				case 'remove':
					$notifications = $phpbb_notifications->get_item_type_class('avathar.postlove.notification.type.postlove');
					$phpbb_notifications->delete_notifications('avathar.postlove.notification.type.postlove', $notifications->get_item_id($notification_data));
				break;
			}
		}
	}
}
