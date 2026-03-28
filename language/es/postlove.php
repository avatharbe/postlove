<?php

/**
*
* Post Love [Spanish]
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
	'POSTLOVE_USER_LIKES'	=> 'Al usuario le han gustado',
	'POSTLOVE_USER_LIKED'	=> 'El usuario ha gustado',

	'NOTIFICATION_POSTLOVE_ADD'	=> 'A %s le ha <b>gustado</b> su mensaje:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Publicaciones gustadas.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>gustó</b> el mensaje de %3$s en "%4$s" en el tema "%5$s"',
	'POSTLOVE_LIST'	=> 'Gustó',
	'POSTLOVE_LIST_VIEW'	=> 'Mostrar lista con todas las acciones similares',

	// Ver 2.0
	'CLICK_TO_LIKE' 	=> 'haz clic para indicar que te gusta esta publicación',
	'CLICK_TO_UNLIKE'   => 'haz clic para quitar tu Me gusta',
	'LOGIN_TO_LIKE_POST' => 'inicia sesión para indicar que te gusta esta publicación',
	'CANT_LIKE_OWN_POST' => 'no puedes indicar que te gusta tu propia publicación',
	'POST_OF_THE_DAY'	=> 'Publicaciones más gustadas',
	'POST_LIKES'		=> 'Gustado',
	'POSTED_AT'			=> 'Publicado',
	'LIKED_BY'			=> 'publicación gustada por: ',
	'POSTED_BY'			=> 'Autor',
	'LIKES_TODAY'   	=> array(
		1	=> 'Una vez hoy',
		2	=> '%d veces hoy',
	),
	'LIKES_THIS_WEEK'   	=> array(
		1	=> 'Una vez esta semana',
		2	=> '%d veces esta semana',
	),
	'LIKES_THIS_MONTH'  	 => array(
		1	=> 'Una vez este mes',
		2	=> '%d veces este mes',
	),
	'LIKES_THIS_YEAR'   	=> array(
		1	=> 'Una vez este año',
		2	=> '%d veces este año',
	),
	'LIKES_EVER'	   => array(
		1	=> 'Una vez en total',
		2	=> '%d veces en total',
	),
	'POSTLOVE_HIDE' 			=> 'Ocultar iconos y resúmenes de Me gusta',
));
