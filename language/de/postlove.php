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
	'POSTLOVE_USER_LIKES'	=> 'Gefällt dem Benutzer',
	'POSTLOVE_USER_LIKED'	=> 'Beiträge gefallen Anderen',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s hat deinen Beitrag mit <b>Gefällt mir</b> markiert:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Beitrag gefällt.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>gefällt</b> %3$s\'s Beitrag "%4$s" im Thema "%5$s"',
	'POSTLOVE_LIST'	=> 'Gefällt',
	'POSTLOVE_LIST_VIEW'	=> 'Zeige Liste mit allen Gefällt-Angaben',

	// Ver 2.0
	'CLICK_TO_LIKE'		=> 'Klicke um diesen Beitrag zu liken',
	'CLICK_TO_UNLIKE'	=> 'Klicke um das Gefällt mir zu entfernen',
	'LOGIN_TO_LIKE_POST'	=> 'Anmelden um diesen Beitrag zu liken',
	'CANT_LIKE_OWN_POST'	=> 'Du kannst deinen eigenen Beitrag nicht liken',
	'POST_OF_THE_DAY'	=> 'Beliebteste Beiträge',
	'POST_LIKES'		=> 'Gefällt',
	'POSTED_AT'			=> 'Geschrieben',
	'LIKED_BY'			=> 'Beitrag gefällt: ',
	'POSTED_BY'			=> 'Autor',
	'LIKES_TODAY'		=> array(
		1	=> 'Einmal heute',
		2	=> '%d mal heute',
	),
	'LIKES_THIS_WEEK'	=> array(
		1	=> 'Einmal diese Woche',
		2	=> '%d mal diese Woche',
	),
	'LIKES_THIS_MONTH'	=> array(
		1	=> 'Einmal diesen Monat',
		2	=> '%d mal diesen Monat',
	),
	'LIKES_THIS_YEAR'	=> array(
		1	=> 'Einmal dieses Jahr',
		2	=> '%d mal dieses Jahr',
	),
	'LIKES_EVER'		=> array(
		1	=> 'Einmal insgesamt',
		2	=> '%d mal insgesamt',
	),
	'POSTLOVE_HIDE'		=> 'Gefällt-mir-Symbole und Zusammenfassungen ausblenden',
	'ACL_U_POSTLOVE'			=> 'Kann Beiträge liken',
));
