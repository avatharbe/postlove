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
	'POSTLOVE_SHOW_LIKES'			=> 'Afficher combien de messages un utilisateur a aimés',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Affiche le nombre total de messages qu\'un utilisateur a aimés dans sa zone de profil sur chaque message.',
	'POSTLOVE_SHOW_LIKED'			=> 'Afficher combien de J\'aime un utilisateur a reçus',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Affiche le nombre total de J\'aime que les messages d\'un utilisateur ont reçus dans sa zone de profil sur chaque message.',

	//Version 1.1
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'		=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Modifier les paramètres de l\'extension Post Love.',
	'CONFIRM_MESSAGE'	=> 'Les modifications ont été sauvegardées !<br><br><a href="%1$s">Retour</a>',

	'POSTLOVE_AUTHOR_LIKE'			=> 'Autoriser les utilisateurs à aimer leurs propres messages',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Si activé, les utilisateurs peuvent aimer leurs propres messages. Si désactivé, le bouton J\'aime est masqué sur les messages de l\'utilisateur.',

	'POSTLOVE_CLEAN_LOVES'			=> 'Nettoyer les J\'aime des messages',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Nettoyer les J\'aime inutiles des messages.',
	'CLEAN'	=> 'Nettoyer',

	//Version 2.0
	'POSTLOVE_FIELDSET_BEHAVIOUR'		=> 'Comportement des J\'aime',
	'POSTLOVE_FIELDSET_SUMMARY'			=> 'Résumé des messages les plus aimés',
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Période de résumé',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Nombre de messages les plus aimés aujourd\'hui à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Nombre de messages les plus aimés cette semaine à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Nombre de messages les plus aimés ce mois-ci à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Nombre de messages les plus aimés cette année à afficher',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Nombre de messages les plus aimés de tous les temps à afficher',
	'POSTLOVE_FORUM'					=> 'Nombre à afficher sur les pages des forums',
	'POSTLOVE_INDEX'					=> 'Nombre à afficher sur la page d\'index',
	'POSTLOVE_SHOW_BUTTON'				=> 'Afficher le compteur de J\'aime dans la barre d\'actions ?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'		=> 'Si activé, le compteur de J\'aime et le lien d\'action apparaissent comme un bouton dans la barre d\'actions du message (à côté de Répondre, Citer, etc.). Si désactivé, ils apparaissent sous le contenu du message.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Enregistrements de remerciements disponibles pour l\'importation',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Les remerciements peuvent être importés depuis l\'extension Thanks for Posts. Les données de l\'autre extension ne seront pas modifiées',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Les remerciements peuvent être importés depuis l\'extension Thanks for Posts mais aucun enregistrement approprié n\'a été trouvé',
	'IMPORT'							=> 'Importer',
));
