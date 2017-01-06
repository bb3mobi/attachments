<?php
/**
*
* Attachments files center.
*
* @copyright (c) BB3.Mobi 2015 Anvar (resspect.ru)
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
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
	'ATTACHMENTS_EXPLAIN'	=> 'Список вложений в сообщениях, оставленных на этой конференции.',
	'ATTACHMENTS_FORUMS'	=> 'Каталог файлов форума',
	'ATTACHMENTS_TOPIC'		=> 'Все файлы этой темы',
	'ATTACHMENTS_BY'		=> '&copy; %s <a href="http://rybalovka.com">Rybalov 2017</a>',
	'ATTACHMENTS_COUNT_ALL'	=> 'Все файлы форума',
	'NO_ATTACHMENTS'		=> 'Нет файлов для отображения',
	'DOWNLOADS'				=> 'Скачивания',
	'DOWNLOAD'				=> 'Скачать',
	'SORT'					=> 'Сортировать',
	'SORT_COMMENT'			=> 'Комментарии',
	'SORT_DOWNLOADS'		=> 'Скачивания',
	'SORT_EXTENSION'		=> 'Расширение',
	'SORT_FILENAME'			=> 'Имя файла',
	'SORT_POST_TIME'		=> 'Время',
	'SORT_SIZE'				=> 'Размер',
));
