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
	'POSTLOVE_SHOW_LIKES'	=> 'Mostrar quantas publicações um usuário curtiu',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Exibe o número total de publicações que um usuário curtiu na sua área de perfil em cada publicação.',
	'POSTLOVE_SHOW_LIKED'	=> 'Mostrar quantas curtidas um usuário recebeu',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Exibe o número total de curtidas que as publicações de um usuário receberam na sua área de perfil em cada publicação.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'A partir daqui, você pode alterar algumas configurações do Post Love',
	'CONFIRM_MESSAGE'	=> 'Alterações salvas!<br><br><a href="%1$s">Voltar</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Permitir que usuários curtam suas próprias publicações',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Se ativado, os usuários podem curtir suas próprias publicações. Se desativado, o botão de curtida é ocultado nas publicações do próprio usuário.',

	'POSTLOVE_CLEAN_LOVES'	=> 'Limpar post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Se você instalou o Post Love antes da postagem automática e usou limpeza love - por favor, pressione Limpar para limpar os Post Loves desnecessários ',
	'CLEAN'	=> 'LIMPAR',

	//Version 2.0
	'POSTLOVE_FIELDSET_BEHAVIOUR'		=> 'Comportamento das curtidas',
	'POSTLOVE_FIELDSET_SUMMARY'			=> 'Resumo das publicações mais curtidas',
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Período de resumo',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Publicações mais curtidas hoje a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Publicações mais curtidas esta semana a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Publicações mais curtidas este mês a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Publicações mais curtidas este ano a exibir',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Publicações mais curtidas de todos os tempos a exibir',
	'POSTLOVE_FORUM'		=> 'Quantidade a exibir nas páginas dos fóruns',
	'POSTLOVE_INDEX'		=> 'Quantidade a exibir na página inicial',
	'POSTLOVE_SHOW_BUTTON'	=> 'Mostrar o contador de curtidas na barra de ações?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Se ativado, o contador de curtidas e o link de ação aparecem como botão na barra de ações da publicação (ao lado de Responder, Citar, etc.). Se desativado, aparecem abaixo do conteúdo da publicação.',

	'POSTLOVE_IMPORT_THANKS'			=> 'Registros de agradecimentos disponíveis para importação',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Os agradecimentos podem ser importados da extensão Thanks for Posts. Os dados da outra extensão não serão alterados',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Os agradecimentos podem ser importados da extensão Thanks for Posts, mas nenhum registro foi encontrado',
	'IMPORT'							=> 'Importar',
));
