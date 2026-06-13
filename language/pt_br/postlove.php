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
	'ACL_U_POSTLOVE'			=> 'Post Love: Pode curtir publicações',
	'ACL_U_POSTLOVE_SUMMARY'	=> 'Post Love: Pode ver o resumo das publicações mais curtidas',

	// Ver 2.1 — mensagens de erro de is_enableable()
	'POSTLOVE_PHP_VERSION_FAIL'		=> 'Esta extensão requer PHP %1$s ou superior. Você está executando PHP %2$s.',
	'POSTLOVE_PHPBB_VERSION_FAIL'	=> 'Esta extensão requer phpBB %1$s ou superior. Você está executando phpBB %2$s.',

	'POSTLOVE_PAGE_TITLE'			=> 'Post Love',
	'NO_ACTIONS_FOUND'				=> 'Nenhuma curtida encontrada.',
));
