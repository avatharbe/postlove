<?php
/**
*
* newspage [Turkish]
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
	'POSTLOVE_USER_LIKES'	=> 'Kullanıcının beğenileri',
	'POSTLOVE_USER_LIKED'	=> 'Kullanıcının beğendikleri',
	'NOTIFICATION_POSTLOVE_ADD'	=> '%s paylaşımınızı <b>beğendi</b>:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Beğenilen paylaşımlar.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s , %3$s tarafından "%5$s" başlığında yapılan "%4$s" paylaşımını <b>beğendi</b>',
	'POSTLOVE_LIST'	=> 'Beğeniler',
	'POSTLOVE_LIST_VIEW'	=> 'Bütün beğeni eylemlerini listele',

	// Ver 2.0
	'CLICK_TO_LIKE' 	=> 'bu gönderiyi beğenmek için tıklayın',
	'CLICK_TO_UNLIKE'   => 'beğeniyi kaldırmak için tıklayın',
	'LOGIN_TO_LIKE_POST' => 'bu gönderiyi beğenmek için giriş yapın',
	'CANT_LIKE_OWN_POST' => 'kendi gönderinizi beğenemezsiniz',
	'POST_OF_THE_DAY'	=> 'En çok beğenilen gönderiler',
	'POST_LIKES'		=> 'Beğenildi',
	'POSTED_AT'			=> 'Gönderildi',
	'LIKED_BY'			=> 'gönderiyi beğenenler: ',
	'POSTED_BY'			=> 'Yazar',
	'LIKES_TODAY'   	=> array(
		1	=> 'Bugün bir kez',
		2	=> 'Bugün %d kez',
	),
	'LIKES_THIS_WEEK'   	=> array(
		1	=> 'Bu hafta bir kez',
		2	=> 'Bu hafta %d kez',
	),
	'LIKES_THIS_MONTH'  	 => array(
		1	=> 'Bu ay bir kez',
		2	=> 'Bu ay %d kez',
	),
	'LIKES_THIS_YEAR'   	=> array(
		1	=> 'Bu yıl bir kez',
		2	=> 'Bu yıl %d kez',
	),
	'LIKES_EVER'	   => array(
		1	=> 'Toplamda bir kez',
		2	=> 'Toplamda %d kez',
	),
	'POSTLOVE_HIDE' 			=> 'Beğeni simgelerini ve özetleri gizle',
));
