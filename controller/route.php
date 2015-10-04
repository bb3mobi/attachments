<?php
/**
*
* @package Attachments files center
* @copyright BB3.Mobi 2015 (c) Anvar(http://apwa.ru)
* @version $Id: route.php 2015-09-28 16:38:10 $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3mobi\attachments\controller;

class route
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->config = $config;
		$this->auth = $auth;
		$this->user = $user;
		$this->request = $request;
		$this->db = $db;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function cat($cat_id = 0, $attach_id = 0)
	{
		if (!$this->auth->acl_get('u_download'))
		{
			trigger_error($this->user->lang['RULES_DOWNLOAD_CANNOT']);
		}

		$this->user->setup('acp/attachments');
		$this->user->add_lang_ext('bb3mobi/attachments', 'attachments');

		$topic_id = $this->request->variable('t', 0);

		if ($attach_id)
		{
			$page_title = $this->file($attach_id);
		}
		else
		{
			$page_title_number = $this->view($cat_id, $topic_id);
		}

		$sql_topic = ($topic_id) ? " AND a.topic_id = " . (int) $topic_id : '';

		$sql = "SELECT eg.*, COUNT(a.attach_id) AS count_file
			FROM " . EXTENSION_GROUPS_TABLE . " eg,
				" . EXTENSIONS_TABLE . " e,
				" . ATTACHMENTS_TABLE . " a
			WHERE eg.group_id = e.group_id
			AND e.extension = a.extension
			AND eg.allow_group = 1
			AND a.is_orphan = 0
			AND a.in_message = 0
			$sql_topic
		GROUP BY group_id";
		$result = $this->db->sql_query($sql);

		$catrow = array();
		$count_download = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$view_ary = ($topic_id) ? array('t' => $topic_id) : array();
			$cat_icon = '';
			if ($row['upload_icon'])
			{
				$cat_icon = '<img src="' . generate_board_url() . '/images/upload_icons/' . $row['upload_icon'] . '" alt="" style="vertical-align: middle" />';
			}

			$title = (isset($this->user->lang['EXT_GROUP_' . $row['group_name']])) ? $this->user->lang['EXT_GROUP_' . $row['group_name']] : $row['group_name'];
			$selected = false;
			if ($row['group_id'] == $cat_id)
			{
				$page_title = $title . $page_title_number;
				$selected = true;
			}

			$this->template->assign_block_vars('catrow', array(
				'CAT_ID'		=> $row['group_id'],
				'CAT_TITLE'		=> $title,
				'CAT_COUNT'		=> $row['count_file'],
				'CAT_ICON'		=> $cat_icon,
				'CAT_SELECT'	=> $selected,
				'U_CAT_VIEW'	=> $this->helper->route("bb3mobi_attach_view", array_merge(array('cat_id' => $row['group_id']), $view_ary)),
				)
			);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_var('S_ATTACH_CAT', (!$cat_id && !$attach_id) ? true : false);

		$page_title = (!isset($page_title)) ? $this->user->lang['ATTACHMENTS_FORUMS'] : $page_title;
		page_header($page_title);

		$this->template->set_filenames(array(
			'body' => '@bb3mobi_attachments/download.html')
		);

		page_footer();
	}

	public function file($attach_id)
	{
		$this->user->setup('viewtopic');

		$sql_attach = 'SELECT a.*, u.username, u.user_colour, p.post_id, p.topic_id, p.forum_id, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid
			FROM ' . ATTACHMENTS_TABLE . ' a, ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p
			WHERE a.attach_id = " . (int) $attach_id . "
				AND p.post_id = a.post_msg_id
				AND a.poster_id = u.user_id
				AND a.is_orphan = 0";
		$result = $this->db->sql_query($sql_attach);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Start auth check
		if (!$this->auth->acl_get('u_download') || !$this->auth->acl_get('f_read', $row['forum_id']))
		{
			trigger_error('LINKAGE_FORBIDDEN');
		}

		$attachments = $update_count = array();

		$sql_attach = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
			WHERE post_msg_id = " . (int) $row['post_id'] . "
				AND is_orphan = 0";
		$result_attach = $this->db->sql_query($sql_attach);
		while($attach_row = $this->db->sql_fetchrow($result_attach))
		{
			$attachments[] = $attach_row;
		}
		$this->db->sql_freeresult($result_attach);

		// Parse the message and subject
		$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$message = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);

		// Parse attachments
		parse_attachments($row['forum_id'], $message, $attachments, $update_count);

		// Replace naughty words such as farty pants
		$row['post_subject'] = censor_text($row['post_subject']);

		$view_post = append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", "t={$row['topic_id']}&amp;p={$row['post_id']}") . "#p{$row['post_id']}";

		$this->template->assign_vars(array(
			'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
			'POST_DATE'				=> $this->user->format_date($row['filetime'], false, false),
			'POST_TITLE'			=> $row['post_subject'],
			'DESCRIPTION'			=> $row['post_subject'],
			'MESSAGE'				=> $message,
			'U_VIEW_POST'			=> $view_post,
			'U_ATTACHMENTS_TOPIC'	=> $this->helper->route("bb3mobi_attach_cat", array('t' => $row['topic_id'])),
			'ATTACHMENTS_BY'		=> $this->user->lang('ATTACHMENTS_BY', '<a href="http://bb3.mobi/forum/viewtopic.php?t=226">Download by</a>'),
			'S_HAS_ATTACHMENTS'		=> (!empty($attachments)) ? true : false,
			)
		);

		foreach ($attachments as $attachment)
		{
			$this->template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=>  $attachment)
			);
		}

		return $row['real_filename'];
	}

	private function view($cat_id, $topic_id = 0)
	{
		$start		= $this->request->variable('start', 0);
		$sort_key	= $this->request->variable('sk', 'a');
		$sort_dir	= $this->request->variable('sd', 'a');

		// Select box eventually
		$sort_key_text = array('a' => $this->user->lang['SORT_FILENAME'], 'b' => $this->user->lang['SORT_COMMENT'], 'c' => $this->user->lang['SORT_EXTENSION'], 'd' => $this->user->lang['SORT_SIZE'], 'e' => $this->user->lang['SORT_DOWNLOADS'], 'f' => $this->user->lang['SORT_POST_TIME'], 'g' => $this->user->lang['SORT_TOPIC_TITLE']);
		$sort_key_sql = array('a' => 'a.real_filename', 'b' => 'a.attach_comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title');

		$sort_dir_text = array('a' => $this->user->lang['ASCENDING'], 'd' => $this->user->lang['DESCENDING']);

		$s_sort_key = '';
		foreach ($sort_key_text as $key => $value)
		{
			$selected = ($sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$s_sort_dir = '';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = 'a';
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		if ($cat_id)
		{
			$sql_topic = ($topic_id) ? " AND a.topic_id = " . (int) $topic_id : '';

			$sql = "SELECT e.extension, COUNT(a.attach_id) AS num_attachments
				FROM " . EXTENSIONS_TABLE . " e, " . ATTACHMENTS_TABLE . " a
				WHERE e.group_id = " . (int) $cat_id . "
					$sql_topic
					AND a.extension = e.extension
					AND a.is_orphan = 0
					AND a.in_message = 0
				GROUP BY a.extension";
			$result = $this->db->sql_query($sql);

			$extension = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$extension[] = $row['extension'];
				$num_attachments = ((isset($num_attachments)) ? $num_attachments + $row['num_attachments'] : $row['num_attachments']);
			}
			$this->db->sql_freeresult($result);

			if (!sizeof($extension))
			{
				return;
			}

			$sql_where = 'WHERE ' . $this->db->sql_in_set('a.extension', $extension);
			$sql_where .= $sql_topic . ' AND a.in_message = 0';
		}
		else
		{
			$sql_where = ($topic_id) ? "WHERE a.topic_id = " . (int) $topic_id . " AND " : "WHERE ";
			$sql_where = "$sql_where a.in_message = 0";

			$sql = 'SELECT COUNT(attach_id) as num_attachments
				FROM ' . ATTACHMENTS_TABLE . " a
				$sql_where
					AND a.is_orphan = 0";
			$result = $this->db->sql_query($sql);
			$num_attachments = $this->db->sql_fetchfield('num_attachments');
			$this->db->sql_freeresult($result);
		}

		// Ensure start is a valid value
		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $num_attachments);

		$sql = 'SELECT a.*, t.forum_id, t.topic_title
			FROM ' . ATTACHMENTS_TABLE . ' a
				LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id AND a.in_message = 0)
			' . $sql_where . "
				AND a.is_orphan = 0
			ORDER BY $order_by";
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

		$row_count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$view_topic = append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}";
			$this->template->assign_block_vars('attachrow', array(
				'ROW_NUMBER'		=> $row_count + ($start + 1),
				'FILENAME'			=> $row['real_filename'],
				'COMMENT'			=> bbcode_nl2br($row['attach_comment']),
				'EXTENSION'			=> $row['extension'],
				'SIZE'				=> get_formatted_filesize($row['filesize']),
				'DOWNLOAD_COUNT'	=> $row['download_count'],
				'POST_TIME'			=> $this->user->format_date($row['filetime']),
				'TOPIC_TITLE'		=> $row['topic_title'],
				'ATTACH_ID'			=> $row['attach_id'],
				'POST_ID'			=> $row['post_msg_id'],
				'TOPIC_ID'			=> $row['topic_id'],
				'AUTH_DOWNLOAD'		=> $this->auth->acl_get('f_download', $row['forum_id']),
				'U_VIEW_ATTACHMENT'	=> $this->helper->route("bb3mobi_attach_file", array('attach_id' => $row['attach_id'])),
				'U_DOWN_ATTACHMENT'	=> append_sid("{$this->phpbb_root_path}download/file.$this->php_ext", 'id=' . $row['attach_id']),
				'U_VIEW_TOPIC'		=> $view_topic)
			);
			$row_count++;
		}
		$this->db->sql_freeresult($result);

		if ($cat_id)
		{
			$route = "bb3mobi_attach_view";
			$view_ary = array('cat_id' => $cat_id);
			$view_ary += ($topic_id) ? array('t' => $topic_id) : array();
		}
		else
		{
			$route = "bb3mobi_attach_cat";
			$view_ary = ($topic_id) ? array('t' => $topic_id) : array();
		}

		$base_url = $this->helper->route($route, array_merge($view_ary, array('sk' => $sort_key, 'sd' => $sort_dir)));

		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_attachments, $this->config['topics_per_page'], $start);

		if ($start)
		{
			$view_ary = array_merge($view_ary, array('start' => $start));
		}

		$this->template->assign_vars(array(
			'DESCRIPTION'			=> $this->user->lang('ATTACHMENTS_EXPLAIN'),
			'NUM_ATTACHMENTS'		=> $this->user->lang('NUM_ATTACHMENTS', $num_attachments),
			'ATTACHMENTS_BY'		=> ($row_count) ? $this->user->lang('ATTACHMENTS_BY', '<a href="http://bb3.mobi">Download by</a>') : '',
			'CAT_ID'				=> $cat_id,

			'U_CANONICAL'			=> $this->helper->route($route, $view_ary, false, '', true),
			'U_SORT_FILENAME'		=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'a','sd' => (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_FILE_COMMENT'	=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'b','sd' => (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_EXTENSION'		=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'c','sd' => (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_FILESIZE'		=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'd','sd' => (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_DOWNLOADS'		=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'e','sd' => (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_POST_TIME'		=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'f','sd' => (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a')))),
			'U_SORT_TOPIC_TITLE'	=> $this->helper->route($route, array_merge($view_ary, array('sk' => 'g','sd' => (($sort_key == 'g' && $sort_dir == 'a') ? 'd' : 'a')))),

			'S_DISPLAY_MARK_ALL'	=> ($num_attachments) ? true : false,
			'S_DISPLAY_PAGINATION'	=> ($num_attachments) ? true : false,
			'S_ATTACH_ACTION'		=> $this->helper->route($route, $view_ary),
			'S_SORT_OPTIONS' 		=> $s_sort_key,
			'S_ORDER_SELECT'		=> $s_sort_dir)
		);
		return ($start ? ' - ' . $this->user->lang('PAGE_TITLE_NUMBER', $this->pagination->get_on_page($this->config['topics_per_page'], $start)) : '');
	}
}
