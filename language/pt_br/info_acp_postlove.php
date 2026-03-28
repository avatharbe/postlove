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
	'POSTLOVE_CONTROL'	=> 'Curtir Post',
	'POSTLOVE_SHOW_LIKES'	=> 'Mostra o número de postagens que este usuário curtiu',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Mostre em <code>viewtopic</code> o número de postagens que o usuário curtiu.',
	'POSTLOVE_SHOW_LIKED'	=> 'Mostra o número de curtidas nas postagens do usuário',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Mostrar em <code>viewtopic</code> quantos posts do usuário foram curtidos por outros.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'A partir daqui, você pode alterar algumas configurações do Post Love',
	'CONFIRM_MESSAGE'	=> 'Alterações salvas!<br><br><a href="%1$s">Voltar</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'O autor pode curtir posts',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'O autor pode curtir suas próprios posts ou não',

	'POSTLOVE_CLEAN_LOVES'	=> 'Limpar post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Se você instalou o Post Love antes da postagem automática e usou limpeza love - por favor, pressione Limpar para limpar os Post Loves desnecessários ',
	'CLEAN'	=> 'LIMPAR',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Período de resumo',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Publicações mais curtidas hoje a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Publicações mais curtidas esta semana a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Publicações mais curtidas este mês a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Publicações mais curtidas este ano a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Publicações mais curtidas de todos os tempos a exibir',
	'POSTLOVE_FORUM'		=> 'Quantidade a exibir nas páginas dos fóruns',
	'POSTLOVE_INDEX'		=> 'Quantidade a exibir na página inicial',
	'POSTLOVE_SHOW_BUTTON'	=> 'Mostrar o contador de curtidas como botão?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'O contador de curtidas pode ser exibido como botão no topo da publicação ou no formato antigo na parte inferior',

	'POSTLOVE_IMPORT_THANKS'			=> 'Registros de agradecimentos disponíveis para importação',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Os agradecimentos podem ser importados da extensão Thanks for Posts. Os dados da outra extensão não serão alterados',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Os agradecimentos podem ser importados da extensão Thanks for Posts, mas nenhum registro foi encontrado',
	'IMPORT'							=> 'Importar',
));
