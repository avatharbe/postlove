<?php
/**
*
* Post Love [Turkish]
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
	'POSTLOVE_CONTROL'	=> 'Paylaşım beğen',
	'POSTLOVE_SHOW_LIKES'	=> 'Kullanıcının beğendiği mesaj saysını göster',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> '<code>viewtopic</code> içinde kullanıcının beğendiği mesaj sayısını göster.',
	'POSTLOVE_SHOW_LIKED'	=> 'Kullanıcının beğenilen mesaj sayısını göster',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> '<code>viewtopic</code> içinde kullanıcının beğenilen mesaj sayısını göster.',
	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Buradan Post Love\'ın bazı ayarlarını değiştirebilirsiniz',
	'CONFIRM_MESSAGE'	=> 'Değişiklikler uygulandı!<br><br><a href="%1$s">Geri</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Kendi paylaşımlarını beğenme',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Yazar kendi paylaşımlarını beğenebilir mi',

	'POSTLOVE_CLEAN_LOVES'	=> 'Clean post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'If you have installed Post Love before automatic post and user love cleaning - please press Clean to clean the unneeded Post Loves',
	'CLEAN'	=> 'Clean',

	//Version 2.0
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Özet Dönemi',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Bugün en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Bu hafta en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Bu ay en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Bu yıl en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Tüm zamanların en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_FORUM'		=> 'Forum sayfalarında gösterilecek sayı',
	'POSTLOVE_INDEX'		=> 'Ana sayfada gösterilecek sayı',
	'POSTLOVE_SHOW_BUTTON'	=> 'Beğeni sayısını gönderi butonu olarak göster?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Beğeni sayısı gönderinin üstünde buton olarak veya gönderinin altında eski formatta gösterilebilir',

	'POSTLOVE_IMPORT_THANKS'			=> 'İçe aktarılabilir teşekkür kayıtları',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Teşekkür kayıtları Thanks for Posts eklentisinden içe aktarılabilir. Diğer eklentinin verileri değiştirilmez',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Teşekkür kayıtları Thanks for Posts eklentisinden içe aktarılabilir ancak uygun kayıt bulunamadı',
	'IMPORT'							=> 'İçe Aktar',
));
