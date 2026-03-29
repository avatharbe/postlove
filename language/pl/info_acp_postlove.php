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
	'POSTLOVE_CONTROL'	=> 'Polubienia postów',
	'POSTLOVE_SHOW_LIKES'	=> 'Pokaż, ile postów użytkownik polubił',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Wyświetla łączną liczbę postów, które użytkownik polubił, w jego obszarze profilu przy każdym poście.',
	'POSTLOVE_SHOW_LIKED'	=> 'Pokaż, ile polubień otrzymał użytkownik',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Wyświetla łączną liczbę polubień, które posty użytkownika otrzymały, w jego obszarze profilu przy każdym poście.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Polubienia postów',
	'POSTLOVE_EXPLAIN'	=> 'Z tego miejsca możesz zmienić ustawienia rozszerzenia Post Love',
	'CONFIRM_MESSAGE'	=> 'Zmiany zostały zapisane pomyślnie!<br><br><a href="%1$s">Powrót</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Pozwól użytkownikom polubić własne posty',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Jeśli włączone, użytkownicy mogą polubić własne posty. Jeśli wyłączone, przycisk polubienia jest ukryty przy własnych postach.',

	'POSTLOVE_CLEAN_LOVES'	=> 'Wyczyść wszystkie polubienia postów',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Jeżeli zainstalowałeś rozszerzenie Post Love przed automatycznym postowaniem i czyszczeniem polubień użytkowników - użyj powyższej opcji, aby wyczyścić niepotrzebne polubienia postów.',
	'CLEAN'	=> 'WYCZYŚĆ',

	//Version 2.0
	'POSTLOVE_FIELDSET_BEHAVIOUR'		=> 'Zachowanie polubień',
	'POSTLOVE_FIELDSET_SUMMARY'			=> 'Podsumowanie najpopularniejszych postów',
	'POSTLOVE_SUMMARY_PERMISSION_NOTICE'	=> 'Widoczność tego podsumowania jest kontrolowana przez uprawnienie użytkownika <em>Może widzieć podsumowanie najpopularniejszych postów</em>. Skonfiguruj je w ACP &raquo; Uprawnienia.',
	'POSTLOVE_SUMMARY_POSITION'			=> 'Pozycja podsumowania na stronie głównej',
	'POSTLOVE_SUMMARY_POSITION_EXPLAIN'	=> 'Wybierz, czy podsumowanie najpopularniejszych postów pojawia się nad czy pod listą forów.',
	'POSTLOVE_SUMMARY_ABOVE'			=> 'Nad listą forów',
	'POSTLOVE_SUMMARY_BELOW'			=> 'Pod listą forów',
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Okres podsumowania',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Ile wyświetlać postów polubionych dzisiaj',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Ile wyświetlać postów polubionych w tym tygodniu',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Ile wyświetlać postów polubionych w tym miesiącu',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Ile wyświetlać postów polubionych w tym roku',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Ile wyświetlać postów polubionych ogółem',
	'POSTLOVE_FORUM'		=> 'Ile wyświetlać na forum',
	'POSTLOVE_INDEX'		=> 'Ile wyświetlać na stronie głównej',
	'POSTLOVE_SHOW_BUTTON'	=> 'Wyświetlać liczbę polubień na pasku akcji?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Jeśli włączone, liczba polubień i link akcji pojawią się jako przycisk na pasku akcji posta (obok Odpowiedz, Cytuj itp.). Jeśli wyłączone, pojawią się pod treścią posta.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Thanks records able to be imported',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Thanks records can be imported from the Thanks for Posts extension, this operation does not change the data of the other extension',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Thanks records can be imported from the Thanks for Posts extension but no suitable records found',
	'IMPORT'							=> 'Import',
));
