<?php
/**
*
* @package Attachments files center
* @version $Id: listener.php 2015-09-28 16:35:17 $
* @copyright BB3.Mobi 2015 (c) Anvar(http://apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3mobi\attachments\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper)
	{
		$this->template = $template;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'attachments_link',
			'core.viewtopic_assign_template_vars_before'	=> 'attachments_data',
		);
	}

	public function attachments_link()
	{
		$this->template->assign_vars(array(
			'U_ATTACHMENTS'		=> $this->helper->route("bb3mobi_attach_cat"),
			'TOTAL_ATTACHMENTS'	=> (int) $this->config['num_files']
			)
		);
	}

	public function attachments_data($event)
	{
		$topic_id = $event['topic_id'];
		$sql = 'SELECT COUNT(attach_id) as num_attachments
			FROM ' . ATTACHMENTS_TABLE . " a
			WHERE topic_id = $topic_id
				AND a.is_orphan = 0";
		$result = $this->db->sql_query($sql);
		$num_attachments = $this->db->sql_fetchfield('num_attachments');
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'U_ATTACHMENTS_TOPIC'	=> $this->helper->route("bb3mobi_attach_cat", array('t' => $topic_id)),
			'TOTAL_ATTACH_TOPIC'	=> (int) $num_attachments,
			)
		);
	}
}
