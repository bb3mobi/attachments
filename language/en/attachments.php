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
	'ATTACHMENTS_EXPLAIN'	=> 'List attachments in messages posted on this board.',
	'ATTACHMENTS_FORUMS'	=> 'Attachments forums',
	'ATTACHMENTS_TOPIC'		=> 'All files in topic',
	'ATTACHMENTS_BY'		=> '&copy; %s <a href="http://resspect.ru">Resspect 2015</a>',
	'ATTACHMENTS_COUNT_ALL'	=> 'All files on forums',
	'NO_ATTACHMENTS'		=> 'No files to display',
	'DOWNLOADS'				=> 'Downloaded',
	'DOWNLOAD'				=> 'Download',
	'SORT'					=> 'Sort',
	'SORT_COMMENT'			=> 'File comment',
	'SORT_DOWNLOADS'		=> 'Downloads',
	'SORT_EXTENSION'		=> 'Extension',
	'SORT_FILENAME'			=> 'Filename',
	'SORT_POST_TIME'		=> 'Post time',
	'SORT_SIZE'				=> 'File size',
));
