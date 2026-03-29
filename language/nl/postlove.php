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
	'POSTLOVE_USER_LIKES'	=> 'Gebruiker vindt leuk',
	'POSTLOVE_USER_LIKED'	=> 'Berichten leuk gevonden',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s vindt je bericht <b>leuk</b>:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Iemand vindt een bericht van je leuk.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s vindt %3$s\'s bericht "%4$s" <b>leuk</b> in onderwerp "%5$s"',
	'POSTLOVE_LIST'	=> 'Vind ik leuk',
	'POSTLOVE_LIST_VIEW'	=> 'Toon lijst met alle vind-ik-leuks',

	// Ver 2.0
	'CLICK_TO_LIKE'		=> 'klik om dit bericht leuk te vinden',
	'CLICK_TO_UNLIKE'	=> 'klik om je vind-ik-leuk te verwijderen',
	'LOGIN_TO_LIKE_POST'	=> 'log in om dit bericht leuk te vinden',
	'CANT_LIKE_OWN_POST'	=> 'je kunt je eigen bericht niet leuk vinden',
	'POST_OF_THE_DAY'	=> 'Populairste berichten',
	'POST_LIKES'		=> 'Leuk gevonden',
	'POSTED_AT'			=> 'Geplaatst',
	'LIKED_BY'			=> 'bericht leuk gevonden door: ',
	'POSTED_BY'			=> 'Auteur',
	'LIKES_TODAY'		=> array(
		1	=> 'Eenmaal vandaag',
		2	=> '%d keer vandaag',
	),
	'LIKES_THIS_WEEK'	=> array(
		1	=> 'Eenmaal deze week',
		2	=> '%d keer deze week',
	),
	'LIKES_THIS_MONTH'	=> array(
		1	=> 'Eenmaal deze maand',
		2	=> '%d keer deze maand',
	),
	'LIKES_THIS_YEAR'	=> array(
		1	=> 'Eenmaal dit jaar',
		2	=> '%d keer dit jaar',
	),
	'LIKES_EVER'		=> array(
		1	=> 'Eenmaal in totaal',
		2	=> '%d keer in totaal',
	),
	'POSTLOVE_HIDE'		=> 'Vind-ik-leuk-pictogrammen en samenvattingen verbergen',
	'ACL_U_POSTLOVE'			=> 'Post Love: Kan berichten leuk vinden',
	'ACL_U_POSTLOVE_SUMMARY'	=> 'Post Love: Kan de samenvatting van populairste berichten zien',
));
