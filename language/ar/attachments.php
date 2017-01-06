<?php
/**
*
* Attachments files center.
*
* @copyright (c) BB3.Mobi 2015 Anvar (phpbbguru.net)
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
* Translated By : Bassel Taha Alhitary - www.alhitary.net
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

// Common language entries
$lang = array_merge($lang, array(
	'ATTACHMENTS_EXPLAIN'	=> 'قائمة بالملفات المُرفقة في مشاركات هذا المنتدى.',
	'ATTACHMENTS_FORUMS'	=> 'المرفقات في المنتدى',
	'ATTACHMENTS_TOPIC'		=> 'جميع المرفقات في الموضوع',
	'ATTACHMENTS_BY'		=> '&copy; %s <a href="http://resspect.ru">Resspect 2015</a>',
	'ATTACHMENTS_COUNT_ALL'	=> 'جميع المرفقات في المنتدى ',
	'NO_ATTACHMENTS'		=> 'لا توجد ملفات لعرضها',
	'DOWNLOADS'				=> 'عدد التحميلات',
	'DOWNLOAD'				=> 'تحميل',
	'SORT'					=> 'ترتيب',
	'SORT_COMMENT'			=> 'تعليق الملف',
	'SORT_DOWNLOADS'		=> 'التحميلات',
	'SORT_EXTENSION'		=> 'الإمتداد',
	'SORT_FILENAME'			=> 'إسم الملف',
	'SORT_POST_TIME'		=> 'وقت المشاركة',
	'SORT_SIZE'				=> 'حجم الملف',
));
