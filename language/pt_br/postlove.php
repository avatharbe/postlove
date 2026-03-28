<?php

/**
*
* newspage [Brazilian Portuguese [pt_br]]
* Brazilian Portuguese translation by eunaumtenhoid (c) 2017 [ver 1.2.1] (https://github.com/phpBBTraducoes)
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
	'POSTLOVE_USER_LIKES'	=> 'O usuário curtiu',
	'POSTLOVE_USER_LIKED'	=> 'O usuário foi curtido',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>Curtiu</b> seu post:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Posts Curtidos.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>Curtiu</b> o post do %3$s "%4$s" no tópico "%5$s"',
	'POSTLOVE_LIST'	=> 'Curtidas',
	'POSTLOVE_LIST_VIEW'	=> 'Mostrar lista com todas as ações de curtir',

	// Ver 2.0
	'CLICK_TO_LIKE' 	=> 'clique para curtir esta publicação',
	'CLICK_TO_UNLIKE'   => 'clique para remover sua curtida',
	'LOGIN_TO_LIKE_POST' => 'faça login para curtir esta publicação',
	'CANT_LIKE_OWN_POST' => 'você não pode curtir sua própria publicação',
	'POST_OF_THE_DAY'	=> 'Publicações mais curtidas',
	'POST_LIKES'		=> 'Curtido',
	'POSTED_AT'			=> 'Publicado',
	'LIKED_BY'			=> 'publicação curtida por: ',
	'POSTED_BY'			=> 'Autor',
	'LIKES_TODAY'   	=> array(
		1	=> 'Uma vez hoje',
		2	=> '%d vezes hoje',
	),
	'LIKES_THIS_WEEK'   	=> array(
		1	=> 'Uma vez esta semana',
		2	=> '%d vezes esta semana',
	),
	'LIKES_THIS_MONTH'  	 => array(
		1	=> 'Uma vez este mês',
		2	=> '%d vezes este mês',
	),
	'LIKES_THIS_YEAR'   	=> array(
		1	=> 'Uma vez este ano',
		2	=> '%d vezes este ano',
	),
	'LIKES_EVER'	   => array(
		1	=> 'Uma vez no total',
		2	=> '%d vezes no total',
	),
	'POSTLOVE_HIDE' 			=> 'Ocultar ícones e resumos de curtidas',
));
