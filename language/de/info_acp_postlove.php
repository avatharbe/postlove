<?php

/**
*
* Post Love [German]
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
	'POSTLOVE_CONTROL'				=> 'Beitrag gefällt mir',
	'POSTLOVE_SHOW_LIKES'			=> 'Zeige die Anzahl an Beiträgen, die dem Benutzer gefallen',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zeige in <code>viewtopic</code> die Anzahl an Beiträgen, die dem Benutzer gefallen.',
	'POSTLOVE_SHOW_LIKED'			=> 'Zeige die Anzahl an Beiträgen des Benutzers, die Anderen gefallen',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zeige in <code>viewtopic</code> die Anzahl an Beiträgen des Benutzers, die Anderen gefallen.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Hier können die Post Love Einstellungen geändert werden',
	'CONFIRM_MESSAGE'	=> 'Änderungen gespeichert!<br><br><a href="%1$s">Zurück</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Autor darf eigene Beiträge liken',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Darf der Autor seine eigenen Beiträge liken oder nicht',

	'POSTLOVE_CLEAN_LOVES'			=> 'Gefällt-mir-Angaben bereinigen',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Falls Post Love installiert wurde, bevor die automatische Bereinigung aktiviert war, bitte "Bereinigen" drücken um verwaiste Gefällt-mir-Angaben zu entfernen',
	'CLEAN'	=> 'Bereinigen',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Zusammenfassungszeitraum',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Anzahl der beliebtesten Beiträge von heute',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Anzahl der beliebtesten Beiträge dieser Woche',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Anzahl der beliebtesten Beiträge dieses Monats',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Anzahl der beliebtesten Beiträge dieses Jahres',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Anzahl der beliebtesten Beiträge insgesamt',
	'POSTLOVE_FORUM'					=> 'Anzahl auf Forenseiten anzeigen',
	'POSTLOVE_INDEX'					=> 'Anzahl auf der Indexseite anzeigen',
	'POSTLOVE_SHOW_BUTTON'				=> 'Gefällt-mir-Anzahl als Beitrags-Button anzeigen?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'Die Gefällt-mir-Anzahl kann als Button oben im Beitrag oder im alten Format unten im Beitrag angezeigt werden',

	'POSTLOVE_IMPORT_THANKS'			=> 'Danke-Einträge zum Importieren verfügbar',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Danke-Einträge können aus der Thanks for Posts Erweiterung importiert werden. Die Daten der anderen Erweiterung werden nicht verändert',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Danke-Einträge können aus der Thanks for Posts Erweiterung importiert werden, aber es wurden keine passenden Einträge gefunden',
	'IMPORT'							=> 'Importieren',
));
