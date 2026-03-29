<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'POSTLOVE_CONTROL'				=> 'Post like',
	'POSTLOVE_SHOW_LIKES'			=> 'Show how many posts a user has liked',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Display the total number of posts a user has liked in their profile area on each post.',
	'POSTLOVE_SHOW_LIKED'			=> 'Show how many likes a user has received',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Display the total number of likes a user\'s posts have received in their profile area on each post.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'From here you can change some Post Love settings',
	'CONFIRM_MESSAGE'	=> 'Changes saved!<br><br><a href="%1$s">Back</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Allow users to like their own posts',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'If enabled, users can like their own posts. If disabled, the like button is hidden on the user\'s own posts.',

	'POSTLOVE_CLEAN_LOVES'			=> 'Clean post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'If you have installed Post Love before automatic post and user love cleaning - please press Clean to clean the unneeded Post Loves',
	'CLEAN'	=> 'Clean',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Summary Period',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'How many liked-today posts to show',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'How many liked-this-week posts to show',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'How many liked-this-month posts to show',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'How many liked-this-year posts to show',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'How many liked-ever posts to show',
	'POSTLOVE_FORUM'					=> 'How many to show on Forum pages',
	'POSTLOVE_INDEX'					=> 'How many to show on Index page',
	'POSTLOVE_SHOW_BUTTON'				=> 'Show like count in the post action bar?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'If enabled, the like count and action link appear as a button in the post action bar (next to Reply, Quote, etc.). If disabled, they appear below the post content instead.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Thanks records able to be imported',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Thanks records can be imported from the Thanks for Posts extension, this operation does not change the data of the other extension',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Thanks records can be imported from the Thanks for Posts extension but no suitable records found',
	'IMPORT'							=> 'Import',
));
