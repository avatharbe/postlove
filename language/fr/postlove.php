<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Galixte
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
	'POSTLOVE_USER_LIKES'	=> 'J\'aime partagés',
	'POSTLOVE_USER_LIKED'	=> 'J\'aime reçus',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>aime</b> votre message :',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Un utilisateur aime un de vos messages.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>a aimé</b> le message « %4$s » de %3$s dans le sujet « %5$s »',
	'POSTLOVE_LIST'	=> 'J\'aime',
	'POSTLOVE_LIST_VIEW'	=> 'Afficher la liste de tous les J\'aime partagés et reçus.',

	// Ver 2.0
	'CLICK_TO_LIKE'		=> 'cliquez pour aimer ce message',
	'CLICK_TO_UNLIKE'	=> 'cliquez pour retirer votre J\'aime',
	'LOGIN_TO_LIKE_POST'	=> 'connectez-vous pour aimer ce message',
	'CANT_LIKE_OWN_POST'	=> 'vous ne pouvez pas aimer votre propre message',
	'POST_OF_THE_DAY'	=> 'Messages les plus aimés',
	'POST_LIKES'		=> 'Aimé',
	'POSTED_AT'			=> 'Publié',
	'LIKED_BY'			=> 'message aimé par : ',
	'POSTED_BY'			=> 'Auteur',
	'LIKES_TODAY'		=> array(
		1	=> 'Une fois aujourd\'hui',
		2	=> '%d fois aujourd\'hui',
	),
	'LIKES_THIS_WEEK'	=> array(
		1	=> 'Une fois cette semaine',
		2	=> '%d fois cette semaine',
	),
	'LIKES_THIS_MONTH'	=> array(
		1	=> 'Une fois ce mois-ci',
		2	=> '%d fois ce mois-ci',
	),
	'LIKES_THIS_YEAR'	=> array(
		1	=> 'Une fois cette année',
		2	=> '%d fois cette année',
	),
	'LIKES_EVER'		=> array(
		1	=> 'Une fois au total',
		2	=> '%d fois au total',
	),
	'POSTLOVE_HIDE'		=> 'Masquer les icônes et résumés des J\'aime',
	'ACL_U_POSTLOVE'			=> 'Peut aimer des messages',
	'ACL_U_POSTLOVE_SUMMARY'	=> 'Peut voir le résumé des messages les plus aimés',
));
