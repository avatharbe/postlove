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
	'POSTLOVE_CONTROL'				=> 'Bericht leuk vinden',
	'POSTLOVE_SHOW_LIKES'			=> 'Toon hoeveel berichten een gebruiker leuk heeft gevonden',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Toont het totale aantal berichten dat een gebruiker leuk heeft gevonden in het profielgebied bij elk bericht.',
	'POSTLOVE_SHOW_LIKED'			=> 'Toon hoeveel vind-ik-leuks een gebruiker heeft ontvangen',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Toont het totale aantal vind-ik-leuks dat de berichten van een gebruiker hebben ontvangen in het profielgebied bij elk bericht.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Hier kun je de Post Love instellingen wijzigen',
	'CONFIRM_MESSAGE'	=> 'Wijzigingen opgeslagen!<br><br><a href="%1$s">Terug</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Gebruikers mogen hun eigen berichten leuk vinden',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Indien ingeschakeld kunnen gebruikers hun eigen berichten leuk vinden. Indien uitgeschakeld wordt de vind-ik-leuk-knop verborgen bij eigen berichten.',

	'POSTLOVE_CLEAN_LOVES'			=> 'Vind-ik-leuks opschonen',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Als je Post Love hebt geinstalleerd voordat automatisch opschonen beschikbaar was, druk dan op "Opschonen" om verweesde vind-ik-leuks te verwijderen',
	'CLEAN'	=> 'Opschonen',

	//Version 2.0
	'POSTLOVE_FIELDSET_BEHAVIOUR'		=> 'Vind-ik-leuk-gedrag',
	'POSTLOVE_FIELDSET_SUMMARY'			=> 'Samenvatting populairste berichten',
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Samenvattingsperiode',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Aantal populairste berichten van vandaag',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Aantal populairste berichten van deze week',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Aantal populairste berichten van deze maand',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Aantal populairste berichten van dit jaar',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Aantal populairste berichten ooit',
	'POSTLOVE_FORUM'					=> 'Aantal te tonen op forumpagina\'s',
	'POSTLOVE_INDEX'					=> 'Aantal te tonen op de indexpagina',
	'POSTLOVE_SHOW_BUTTON'				=> 'Vind-ik-leuk-aantal tonen in de actiebalk?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'Indien ingeschakeld verschijnen het vind-ik-leuk-aantal en de actielink als knop in de actiebalk van het bericht (naast Reageren, Citeren, enz.). Indien uitgeschakeld verschijnen ze onder de berichtinhoud.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Bedankjes beschikbaar om te importeren',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Bedankjes kunnen worden geimporteerd uit de Thanks for Posts extensie. De gegevens van de andere extensie worden niet gewijzigd',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Bedankjes kunnen worden geimporteerd uit de Thanks for Posts extensie, maar er zijn geen geschikte records gevonden',
	'IMPORT'							=> 'Importeren',
));
