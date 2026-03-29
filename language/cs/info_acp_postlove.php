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
	'POSTLOVE_SHOW_LIKES'	=> 'Zobrazit, kolik příspěvků uživatel označil jako oblíbené',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zobrazí celkový počet příspěvků, které uživatel označil jako oblíbené, v jeho profilové oblasti u každého příspěvku.',
	'POSTLOVE_SHOW_LIKED'	=> 'Zobrazit, kolik oblíbení uživatel obdržel',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zobrazí celkový počet oblíbení, které příspěvky uživatele obdržely, v jeho profilové oblasti u každého příspěvku.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Zde je možné přizpůsobit nastavení Post Love',
	'CONFIRM_MESSAGE'	=> 'Změny uloženy!<br><br><a href="%1$s">Zpět</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Povolit uživatelům označovat vlastní příspěvky jako oblíbené',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Pokud je povoleno, uživatelé mohou označovat vlastní příspěvky jako oblíbené. Pokud je zakázáno, tlačítko Líbí se je u vlastních příspěvků skryto.',

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
	'POSTLOVE_SHOW_BUTTON'	=> 'Zobrazit počet oblíbení v liště akcí?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Pokud je povoleno, počet oblíbení a odkaz na akci se zobrazí jako tlačítko v liště akcí příspěvku (vedle Odpovědět, Citovat atd.). Pokud je zakázáno, zobrazí se pod obsahem příspěvku.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Záznamy poděkování k importu',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Záznamy poděkování mohou být importovány z rozšíření Thanks for Posts. Data jiného rozšíření nebudou změněna',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Záznamy poděkování mohou být importovány z rozšíření Thanks for Posts, ale nebyly nalezeny žádné vhodné záznamy',
	'IMPORT'							=> 'Importovat',
));
