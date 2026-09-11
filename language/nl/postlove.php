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
	'LOVELIST_LIKED'	=> 'vindt',
	'LOVELIST_POST_OF'	=> '\'s bericht leuk',
	'LOVELIST_IN_TOPIC'	=> 'in onderwerp',
	'POSTLOVE_LIST'	=> 'Vind ik leuk',
	'POSTLOVE_LIST_VIEW'	=> 'Toon lijst met alle vind-ik-leuks',

	// Ver 2.0
	'CLICK_TO_LIKE'		=> 'klik om dit bericht leuk te vinden',
	'CLICK_TO_UNLIKE'	=> 'klik om je vind-ik-leuk te verwijderen',
	'LOGIN_TO_LIKE_POST'	=> 'log in om dit bericht leuk te vinden',
	'NO_PERMISSION_TO_LIKE_POST'	=> 'je hebt geen toestemming om dit bericht leuk te vinden',
	'CANT_LIKE_OWN_POST'	=> 'je kunt je eigen bericht niet leuk vinden',
	'POST_OF_THE_DAY'	=> 'Populairste berichten',
	'POST_LIKES'		=> 'Leuk gevonden',
	'POSTED_AT'			=> 'Geplaatst',
	'LIKED_BY'			=> 'bericht leuk gevonden door: ',
	'TOTAL_LIKES_IN_TOPIC'	=> 'Totaal aantal likes in dit onderwerp',
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
	'POSTLOVE_HIDE'		=> 'Vind-ik-leuk-knop verbergen',
	'POSTLOVE_HIDE_EXPLAIN'	=> 'Verbergt de vind-ik-leuk-knop op elk bericht. De "vind-ik-leuks"-link op je profiel, het aantal vind-ik-leuks in de onderwerpenlijst en de panelen met meest gewaardeerde berichten worden hieronder apart ingesteld.',
	'POSTLOVE_HIDE_PROFILE'	=> '"Vind-ik-leuks"-link op profiel verbergen',
	'POSTLOVE_HIDE_PROFILE_EXPLAIN'	=> 'Verbergt de "vind-ik-leuks"-link op je eigen profiel, die alle berichten toont die je leuk hebt gevonden. Heeft geen invloed op de vind-ik-leuk-knop bij berichten.',
	'POSTLOVE_HIDE_TOPICS'	=> 'Aantal vind-ik-leuks in onderwerpenlijst verbergen',
	'POSTLOVE_HIDE_TOPICS_EXPLAIN'	=> 'Verbergt het aantal vind-ik-leuks dat naast elk onderwerp in de onderwerpenlijst wordt getoond. Heeft geen invloed op de vind-ik-leuk-knop bij berichten.',
	'POSTLOVE_HIDE_SUM'	=> 'Panelen met meest gewaardeerde berichten verbergen',
	'POSTLOVE_HIDE_SUM_EXPLAIN'	=> 'Verbergt de panelen met meest gewaardeerde berichten op de forumindex en de forums. Heeft geen invloed op de vind-ik-leuk-knop bij berichten.',
	'ACL_U_POSTLOVE'			=> 'Post Love: Kan berichten leuk vinden',
	'ACL_U_POSTLOVE_SUMMARY'	=> 'Post Love: Kan de samenvatting van populairste berichten zien',

	// Ver 2.1 — is_enableable() foutmeldingen
	'POSTLOVE_PHP_VERSION_FAIL'		=> 'Deze extensie vereist PHP %1$s of hoger. Je gebruikt PHP %2$s.',
	'POSTLOVE_PHPBB_VERSION_FAIL'	=> 'Deze extensie vereist phpBB %1$s of hoger. Je gebruikt phpBB %2$s.',

	'POSTLOVE_PAGE_TITLE'			=> 'Post Love',
	'NO_ACTIONS_FOUND'				=> 'Geen vind-ik-leuk-acties gevonden.',
));
