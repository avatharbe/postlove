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
	'POSTLOVE_CONTROL'				=> 'Beitrag gefällt mir',
	'POSTLOVE_SHOW_LIKES'			=> 'Zeige, wie viele Beiträge ein Benutzer geliked hat',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zeigt die Gesamtzahl der Beiträge, die ein Benutzer geliked hat, im Profilbereich jedes Beitrags an.',
	'POSTLOVE_SHOW_LIKED'			=> 'Zeige, wie viele Likes ein Benutzer erhalten hat',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zeigt die Gesamtzahl der Likes, die die Beiträge eines Benutzers erhalten haben, im Profilbereich jedes Beitrags an.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Hier können die Post Love Einstellungen geändert werden',
	'CONFIRM_MESSAGE'	=> 'Änderungen gespeichert!<br><br><a href="%1$s">Zurück</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Benutzer dürfen ihre eigenen Beiträge liken',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Wenn aktiviert, können Benutzer ihre eigenen Beiträge liken. Wenn deaktiviert, wird der Like-Button bei eigenen Beiträgen ausgeblendet.',

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
	'POSTLOVE_SHOW_BUTTON'				=> 'Gefällt-mir-Anzahl in der Aktionsleiste anzeigen?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'Wenn aktiviert, werden die Gefällt-mir-Anzahl und der Aktionslink als Button in der Aktionsleiste des Beitrags angezeigt (neben Antworten, Zitieren usw.). Wenn deaktiviert, erscheinen sie unter dem Beitragsinhalt.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Danke-Einträge zum Importieren verfügbar',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Danke-Einträge können aus der Thanks for Posts Erweiterung importiert werden. Die Daten der anderen Erweiterung werden nicht verändert',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Danke-Einträge können aus der Thanks for Posts Erweiterung importiert werden, aber es wurden keine passenden Einträge gefunden',
	'IMPORT'							=> 'Importieren',
));
