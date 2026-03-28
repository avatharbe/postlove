<?php

/**
*
* newspage [Bulgarian]
*
* @package language
* @version $Id$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
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
	'POSTLOVE_USER_LIKES'	=> 'Потребителя е харесал',
	'POSTLOVE_USER_LIKED'	=> 'Потребителя е харесан',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>хареса</b> вашето мнение:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Харесани постове',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>хареса</b> мнението на %3$s "%4$s" в тема "%5$s"',
	'POSTLOVE_LIST'	=> 'Харесвания',
	'POSTLOVE_LIST_VIEW'	=> 'Покажи списък с харесванията',

	// Ver 2.0
	'CLICK_TO_LIKE' 	=> 'натиснете за да харесате тази публикация',
	'CLICK_TO_UNLIKE'   => 'натиснете за да премахнете харесването',
	'LOGIN_TO_LIKE_POST' => 'влезте за да харесате тази публикация',
	'CANT_LIKE_OWN_POST' => 'не можете да харесате собствената си публикация',
	'POST_OF_THE_DAY'	=> 'Най-харесвани публикации',
	'POST_LIKES'		=> 'Харесано',
	'POSTED_AT'			=> 'Публикувано',
	'LIKED_BY'			=> 'публикацията е харесана от: ',
	'POSTED_BY'			=> 'Автор',
	'LIKES_TODAY'   	=> array(
		1	=> 'Веднъж днес',
		2	=> '%d пъти днес',
	),
	'LIKES_THIS_WEEK'   	=> array(
		1	=> 'Веднъж тази седмица',
		2	=> '%d пъти тази седмица',
	),
	'LIKES_THIS_MONTH'  	 => array(
		1	=> 'Веднъж този месец',
		2	=> '%d пъти този месец',
	),
	'LIKES_THIS_YEAR'   	=> array(
		1	=> 'Веднъж тази година',
		2	=> '%d пъти тази година',
	),
	'LIKES_EVER'	   => array(
		1	=> 'Веднъж общо',
		2	=> '%d пъти общо',
	),
	'POSTLOVE_HIDE' 			=> 'Скриване на иконите и обобщенията за харесвания',
));
