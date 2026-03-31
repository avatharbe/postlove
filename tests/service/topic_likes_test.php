<?php
/**
 *
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace avathar\postlove\tests\service;

/**
 * Tests for the topic_likes public service API.
 *
 * Verifies that get_topic_like_counts() correctly aggregates like counts
 * across all posts belonging to each requested topic.
 *
 * Fixture: tests/event/fixtures/summary_data.xml
 * Topics and their expected like counts:
 *   topic 1 — posts 1, 3, 4 → post 1 liked by users 4 and 7 (2), post 3 by user 4 (1),
 *              post 4 by user 5 (1) = 4 likes total
 *   topic 2 — post 2 → liked by user 4 (1) = 1 like total
 *   topic 3 — post 5 → liked by user 6 (1) = 1 like total
 *
 * @group service
 */
class topic_likes_test extends \phpbb_database_test_case
{
	/** @var \avathar\postlove\service\topic_likes */
	protected $service;

	protected static function setup_extensions()
	{
		return array('avathar/postlove');
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../event/fixtures/summary_data.xml');
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->service = new \avathar\postlove\service\topic_likes(
			$this->new_dbal(),
			'phpbb_posts_likes'
		);
	}

	/**
	 * Empty input must return an empty array without touching the database.
	 */
	public function test_empty_input()
	{
		$result = $this->service->get_topic_like_counts([]);
		$this->assertSame([], $result);
	}

	/**
	 * Counts are correctly aggregated across all posts in each topic.
	 *
	 * topic 1: 4 likes (posts 1×2, 3×1, 4×1)
	 * topic 2: 1 like  (post 2×1)
	 * topic 3: 1 like  (post 5×1)
	 */
	public function test_counts()
	{
		$result = $this->service->get_topic_like_counts([1, 2, 3]);
		$this->assertSame([1 => 4, 2 => 1, 3 => 1], $result);
	}

	/**
	 * A topic ID with no likes is absent from the result; it is not returned as 0.
	 */
	public function test_missing_topic_absent()
	{
		$result = $this->service->get_topic_like_counts([2, 99]);
		$this->assertSame([2 => 1], $result);
		$this->assertArrayNotHasKey(99, $result);
	}
}
