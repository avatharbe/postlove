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
	'POSTLOVE_CONTROL'	=> 'Paylaşım beğen',
	'POSTLOVE_SHOW_LIKES'	=> 'Kullanıcının kaç gönderiyi beğendiğini göster',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Kullanıcının beğendiği toplam gönderi sayısını her gönderideki profil alanında gösterir.',
	'POSTLOVE_SHOW_LIKED'	=> 'Kullanıcının kaç beğeni aldığını göster',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Kullanıcının gönderilerinin aldığı toplam beğeni sayısını her gönderideki profil alanında gösterir.',
	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Buradan Post Love\'ın bazı ayarlarını değiştirebilirsiniz',
	'CONFIRM_MESSAGE'	=> 'Değişiklikler uygulandı!<br><br><a href="%1$s">Geri</a>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Kullanıcıların kendi gönderilerini beğenmesine izin ver',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Etkinleştirildiğinde kullanıcılar kendi gönderilerini beğenebilir. Devre dışı bırakıldığında beğeni butonu kullanıcının kendi gönderilerinde gizlenir.',

	'POSTLOVE_CLEAN_LOVES'	=> 'Clean post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'If you have installed Post Love before automatic post and user love cleaning - please press Clean to clean the unneeded Post Loves',
	'CLEAN'	=> 'Clean',

	//Version 2.0
	'POSTLOVE_FIELDSET_BEHAVIOUR'		=> 'Beğeni davranışı',
	'POSTLOVE_FIELDSET_SUMMARY'			=> 'En çok beğenilen gönderi özeti',
	'POSTLOVE_SUMMARY_PERIOD'			=> 'Özet Dönemi',
	'POSTLOVE_HOWMANY_MOST_LIKED_DAY'	=> 'Bugün en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_WEEK'	=> 'Bu hafta en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_MONTH'	=> 'Bu ay en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_YEAR'	=> 'Bu yıl en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_HOWMANY_MOST_LIKED_EVER'	=> 'Tüm zamanların en çok beğenilen gösterilecek gönderi sayısı',
	'POSTLOVE_FORUM'		=> 'Forum sayfalarında gösterilecek sayı',
	'POSTLOVE_INDEX'		=> 'Ana sayfada gösterilecek sayı',
	'POSTLOVE_SHOW_BUTTON'	=> 'Beğeni sayısını eylem çubuğunda göster?',
	'POSTLOVE_SHOW_BUTTON_EXPLAIN'	=> 'Etkinleştirildiğinde, beğeni sayısı ve eylem bağlantısı gönderinin eylem çubuğunda bir buton olarak görünür (Yanıtla, Alıntıla vb. yanında). Devre dışı bırakıldığında, gönderi içeriğinin altında görünür.',

	'POSTLOVE_IMPORT_THANKS'			=> 'İçe aktarılabilir teşekkür kayıtları',
	'POSTLOVE_IMPORT_THANKS_EXPLAIN'	=> 'Teşekkür kayıtları Thanks for Posts eklentisinden içe aktarılabilir. Diğer eklentinin verileri değiştirilmez',
	'POSTLOVE_IMPORT_NO_THANKS_EXPLAIN'	=> 'Teşekkür kayıtları Thanks for Posts eklentisinden içe aktarılabilir ancak uygun kayıt bulunamadı',
	'IMPORT'							=> 'İçe Aktar',
));
