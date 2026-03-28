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
	'POSTLOVE_CONTROL'	=> 'Oblíbené příspěvky',
	'POSTLOVE_SHOW_LIKES'	=> 'Zobrazovat počet příspěvků, které se líbí tomuto uživateli.',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zobrazovat ve <code>viewtopic</code> počet příspěvků, které se uživateli líbí.',
	'POSTLOVE_SHOW_LIKED'	=> 'Zobrazovat počet příspěvků, které se líbí ostatním uživatelům.',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zobrazovat ve <code>viewtopic</code> počet příspěvků, které se uživatelům líbily.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Zde je možné přizpůsobit nastavení Post Love',
	'CONFIRM_MESSAGE'	=> 'Změny uloženy!<br><br><a href="%1$s">Zpět</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Autor může označovat své vlastní příspěvky',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Je-li povoleno, autor může označit své vlastní příspěvky tlačítkem Líbí se.',

	'POSTLOVE_CLEAN_LOVES'	=> 'Pročistit hodnocení',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Pokud bylo rozšíření Post Love nainstalováno ještě před uvedením funkce automatického čištění příspěvků a uživatelského Post Love hodnocení, proveďte stiskem tlačítka „Vyčistit" pročištění nepotřebných Post Love hodnocení.',
	'CLEAN'	=> 'Vyčistit',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Období souhrnu',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Kolik nejoblíbenějších příspěvků dne zobrazit',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Kolik nejoblíbenějších příspěvků týdne zobrazit',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Kolik nejoblíbenějších příspěvků měsíce zobrazit',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Kolik nejoblíbenějších příspěvků roku zobrazit',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Kolik nejoblíbenějších příspěvků celkem zobrazit',
	'POSTLOVE_FORUM'	=> 'Kolik zobrazit na stránkách fóra',
	'POSTLOVE_INDEX'	=> 'Kolik zobrazit na hlavní stránce',
	'POSTLOVE_SHOW_BUTTON'	=> 'Zobrazit počet oblíbení jako tlačítko?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Počet oblíbení může být zobrazen jako tlačítko nahoře příspěvku nebo ve starém formátu dole',

	'POSTLOVE_IMPORT_THANKS'			=> 'Záznamy poděkování k importu',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Záznamy poděkování mohou být importovány z rozšíření Thanks for Posts. Data jiného rozšíření nebudou změněna',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Záznamy poděkování mohou být importovány z rozšíření Thanks for Posts, ale nebyly nalezeny žádné vhodné záznamy',
	'IMPORT'							=> 'Importovat',
));
