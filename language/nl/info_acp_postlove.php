<?php

/**
*
* Post Love [Dutch]
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
	'POSTLOVE_CONTROL'				=> 'Bericht leuk vinden',
	'POSTLOVE_SHOW_LIKES'			=> 'Toon het aantal berichten dat deze gebruiker leuk vindt',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Toon in <code>viewtopic</code> het aantal berichten dat de gebruiker leuk vindt.',
	'POSTLOVE_SHOW_LIKED'			=> 'Toon het aantal leuk gevonden berichten van de gebruiker',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Toon in <code>viewtopic</code> hoeveel berichten van de gebruiker leuk gevonden zijn door anderen.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Hier kun je de Post Love instellingen wijzigen',
	'CONFIRM_MESSAGE'	=> 'Wijzigingen opgeslagen!<br><br><a href="%1$s">Terug</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Auteur mag eigen berichten leuk vinden',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Mag de auteur zijn/haar eigen berichten leuk vinden of niet',

	'POSTLOVE_CLEAN_LOVES'			=> 'Vind-ik-leuks opschonen',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Als je Post Love hebt geinstalleerd voordat automatisch opschonen beschikbaar was, druk dan op "Opschonen" om verweesde vind-ik-leuks te verwijderen',
	'CLEAN'	=> 'Opschonen',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Samenvattingsperiode',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Aantal populairste berichten van vandaag',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Aantal populairste berichten van deze week',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Aantal populairste berichten van deze maand',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Aantal populairste berichten van dit jaar',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Aantal populairste berichten ooit',
	'POSTLOVE_FORUM'					=> 'Aantal te tonen op forumpagina\'s',
	'POSTLOVE_INDEX'					=> 'Aantal te tonen op de indexpagina',
	'POSTLOVE_SHOW_BUTTON'				=> 'Vind-ik-leuk-aantal tonen als berichtknop?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'De vind-ik-leuk-telling kan als knop bovenaan het bericht of in het oude formaat onderaan het bericht worden getoond',

	'POSTLOVE_IMPORT_THANKS'			=> 'Bedankjes beschikbaar om te importeren',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Bedankjes kunnen worden geimporteerd uit de Thanks for Posts extensie. De gegevens van de andere extensie worden niet gewijzigd',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Bedankjes kunnen worden geimporteerd uit de Thanks for Posts extensie, maar er zijn geen geschikte records gevonden',
	'IMPORT'							=> 'Importeren',
));
