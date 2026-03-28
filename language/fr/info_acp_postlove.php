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
	'POSTLOVE_CONTROL'				=> 'J\'aime un message',
	'POSTLOVE_SHOW_LIKES'			=> 'Afficher le nombre de J\'aime exprimés par l\'utilisateur',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Afficher le nombre de messages aimés par l\'utilisateur sur les pages des sujets.',
	'POSTLOVE_SHOW_LIKED'			=> 'Afficher le nombre de J\'aime reçus par les utilisateurs',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Afficher le nombre de messages aimés des autres utilisateurs sur les pages des sujets.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Modifier les paramètres de l\'extension Post Love.',
	'CONFIRM_MESSAGE'	=> 'Les modifications ont été sauvegardées !<br><br><a href="%1$s">Retour</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'L\'auteur peut aimer ses messages',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Permettre ou non à l\'auteur d\'aimer ses propres messages.',

	'POSTLOVE_CLEAN_LOVES'			=> 'Nettoyer les J\'aime des messages',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Nettoyer les J\'aime inutiles des messages.',
	'CLEAN'	=> 'Nettoyer',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Période de résumé',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Nombre de messages les plus aimés aujourd\'hui à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Nombre de messages les plus aimés cette semaine à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Nombre de messages les plus aimés ce mois-ci à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Nombre de messages les plus aimés cette année à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Nombre de messages les plus aimés de tous les temps à afficher',
	'POSTLOVE_FORUM'					=> 'Nombre à afficher sur les pages des forums',
	'POSTLOVE_INDEX'					=> 'Nombre à afficher sur la page d\'index',
	'POSTLOVE_SHOW_BUTTON'				=> 'Afficher le compteur de J\'aime dans un bouton ?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'Le compteur de J\'aime peut être affiché comme bouton en haut du message ou dans l\'ancien format en bas du message',

	'POSTLOVE_IMPORT_THANKS'			=> 'Enregistrements de remerciements disponibles pour l\'importation',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Les remerciements peuvent être importés depuis l\'extension Thanks for Posts. Les données de l\'autre extension ne seront pas modifiées',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Les remerciements peuvent être importés depuis l\'extension Thanks for Posts mais aucun enregistrement approprié n\'a été trouvé',
	'IMPORT'							=> 'Importer',
));
