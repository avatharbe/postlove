<?php
/**
 *
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace avathar\postlove\service;

use phpbb\db\driver\driver_interface;

/**
 * Public service for querying aggregated like counts.
 *
 * Other extensions can consume this service via optional DI
 * (@?avathar.postlove.topic_likes) to display like counts
 * without querying the posts_likes table directly.
 */
class topic_likes
{
	/** @var driver_interface */
	protected $db;

	/** @var string */
	protected $likes_table;

	/**
	 * @param driver_interface $db
	 * @param string           $likes_table
	 */
	public function __construct(driver_interface $db, string $likes_table)
	{
		$this->db = $db;
		$this->likes_table = $likes_table;
	}

	/**
	 * Get aggregated like counts for a set of topics.
	 *
	 * @param  array $topic_ids Array of topic IDs
	 * @return array Associative array [topic_id => like_count]
	 */
	public function get_topic_like_counts(array $topic_ids): array
	{
		if (empty($topic_ids))
		{
			return [];
		}

		$sql = 'SELECT p.topic_id, COUNT(l.post_id) AS like_count
			FROM ' . POSTS_TABLE . ' p
			INNER JOIN ' . $this->likes_table . ' l ON (l.post_id = p.post_id)
			WHERE ' . $this->db->sql_in_set('p.topic_id', $topic_ids) . '
			GROUP BY p.topic_id';
		$result = $this->db->sql_query($sql);

		$counts = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[(int) $row['topic_id']] = (int) $row['like_count'];
		}
		$this->db->sql_freeresult($result);

		return $counts;
	}
}
