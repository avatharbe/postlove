<?php

/**
*
* Post Love [Bulgarian]
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
	'POSTLOVE_CONTROL'	=> 'Харесване на постове',
	'POSTLOVE_SHOW_LIKES'	=> 'Покажи броя на харесаните от потребителя постове',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Покажи в <code>viewtopic</code> общия брой на харесаните от този потребител постове.',
	'POSTLOVE_SHOW_LIKED'	=> 'Покажи броя на харесаните постове на потребителя',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Покажи в <code>viewtopic</code> общия брой на харесаните постове на този потребител.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'От тук можете да контролирате различни настройки на харесването на постове',
	'CONFIRM_MESSAGE'	=> 'Промените запазени!<br><br><a href="%1$s">Върни се обратно</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Авторът може да харесва публикации',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Може ли авторът да харесва собствените си публикации',

	'POSTLOVE_CLEAN_LOVES'	=> 'Почисти излишните харесвания',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Ако случайно сте използвали Post Love преди да сложат почистването след триене на постове и потребители - натиснете Изчисти, за да почистите излишните записи в базата',
	'CLEAN'	=> 'Почисти',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Период на обобщение',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Колко най-харесвани публикации за деня да се показват',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Колко най-харесвани публикации за седмицата да се показват',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Колко най-харесвани публикации за месеца да се показват',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Колко най-харесвани публикации за годината да се показват',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Колко най-харесвани публикации за всички времена да се показват',
	'POSTLOVE_FORUM'	=> 'Колко да се показват на страниците на форумите',
	'POSTLOVE_INDEX'	=> 'Колко да се показват на началната страница',
	'POSTLOVE_SHOW_BUTTON'	=> 'Показване на брояча на харесвания като бутон?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Броячът на харесвания може да се показва като бутон в горната част на публикацията или в стария формат в долната част',

	'POSTLOVE_IMPORT_THANKS'			=> 'Налични записи за благодарности за импортиране',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Записите за благодарности могат да бъдат импортирани от разширението Thanks for Posts. Данните на другото разширение няма да бъдат променени',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Записите за благодарности могат да бъдат импортирани от разширението Thanks for Posts, но не бяха намерени подходящи записи',
	'IMPORT'							=> 'Импортиране',
));
