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
	'POSTLOVE_CONTROL'	=> 'Mensaje que gusta',
	'POSTLOVE_SHOW_LIKES'	=> 'Mostrar el número de mensajes que le han gustado.',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Mostrar en <code>viewtopic</code> el número de mensajes que han gustado a este usuario.',
	'POSTLOVE_SHOW_LIKED'	=> 'Mostrar el número de mensajes que han gustado.',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Mostrar en <code>viewtopic</code> cuántos mensajes del usuario han gustado a los demás.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Desde aquí puede cambiar algunas opciones de Post Love',
	'CONFIRM_MESSAGE'	=> '¡Cambios guardados!<br><br><a href="%1$s">Volver</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'El autor puede enviar me gusta',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Puede el autor hacer me gusta sus propios mensajes o no',

	'POSTLOVE_CLEAN_LOVES'	=> 'Limpiar post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Si ha instalado Post Love antes de la publicación automática, y el usuario ama la limpieza - por favor, presione Limpiar, para limpiar los innecesarios Post Loves',
	'CLEAN'	=> 'Limpiar',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Período de resumen',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Publicaciones más gustadas hoy a mostrar',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Publicaciones más gustadas esta semana a mostrar',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Publicaciones más gustadas este mes a mostrar',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Publicaciones más gustadas este año a mostrar',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Publicaciones más gustadas de todos los tiempos a mostrar',
	'POSTLOVE_FORUM'		=> 'Cantidad a mostrar en páginas del foro',
	'POSTLOVE_INDEX'		=> 'Cantidad a mostrar en la página de inicio',
	'POSTLOVE_SHOW_BUTTON'	=> '¿Mostrar el contador de Me gusta como botón?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'El contador de Me gusta puede mostrarse como botón en la parte superior o en el formato antiguo en la parte inferior',

	'POSTLOVE_IMPORT_THANKS'			=> 'Registros de agradecimientos disponibles para importar',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Los agradecimientos se pueden importar desde la extensión Thanks for Posts. Los datos de la otra extensión no se modificarán',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Los agradecimientos se pueden importar desde la extensión Thanks for Posts pero no se encontraron registros',
	'IMPORT'							=> 'Importar',
));
