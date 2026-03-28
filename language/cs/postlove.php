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
	'POSTLOVE_USER_LIKES'	=> 'Uživateli se líbí',
	'POSTLOVE_USER_LIKED'	=> 'Uživatel se líbí',
	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>se líbí</b> váš příspěvek:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Oblíbené příspěvky',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s – %2$s <b>se líbí</b> příspěvek „%4$s" uživatele %3$s v tématu „%5$s"',
	'POSTLOVE_LIST'	=> 'Hodnocení',
	'POSTLOVE_LIST_VIEW'	=> 'Zobrazit seznam se všemi událostmi',

	// Ver 2.0
	'CLICK_TO_LIKE' 	=> 'klikněte pro označení příspěvku jako oblíbený',
	'CLICK_TO_UNLIKE'   => 'klikněte pro odebrání označení oblíbený',
	'LOGIN_TO_LIKE_POST' => 'přihlaste se pro označení příspěvku jako oblíbený',
	'CANT_LIKE_OWN_POST' => 'nemůžete označit svůj vlastní příspěvek jako oblíbený',
	'POST_OF_THE_DAY'	=> 'Nejoblíbenější příspěvky',
	'POST_LIKES'		=> 'Oblíbeno',
	'POSTED_AT'			=> 'Publikováno',
	'LIKED_BY'			=> 'příspěvek se líbí: ',
	'POSTED_BY'			=> 'Autor',
	'LIKES_TODAY'   	=> array(
		1	=> 'Jednou dnes',
		2	=> '%d krát dnes',
	),
	'LIKES_THIS_WEEK'   	=> array(
		1	=> 'Jednou tento týden',
		2	=> '%d krát tento týden',
	),
	'LIKES_THIS_MONTH'  	 => array(
		1	=> 'Jednou tento měsíc',
		2	=> '%d krát tento měsíc',
	),
	'LIKES_THIS_YEAR'   	=> array(
		1	=> 'Jednou tento rok',
		2	=> '%d krát tento rok',
	),
	'LIKES_EVER'	   => array(
		1	=> 'Jednou celkem',
		2	=> '%d krát celkem',
	),
	'POSTLOVE_HIDE' 			=> 'Skrýt ikony a souhrny oblíbených',
	'ACL_U_POSTLOVE'			=> 'Může označit příspěvky jako oblíbené',
));
