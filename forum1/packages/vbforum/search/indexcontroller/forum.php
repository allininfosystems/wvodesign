<?php if (!defined('VB_ENTRY')) die('Access denied.');

/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/


/**
 * @package vBulletin
 * @subpackage Search
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . "/vb/legacy/forum.php");
require_once (DIR."/vb/search/core.php");
/**
 * Index Controller for group Messages
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBForum_Search_IndexController_Forum extends vB_Search_IndexController
{
	//We need to set the content types. This is available in a static method as below
  public function __construct()
  {
     $this->contenttypeid = vB_Search_Core::get_instance()->get_contenttypeid("vBForum", "Forum");
  }

	public function get_max_id()
	{
		global $vbulletin;
		$row = $vbulletin->db->query_first_slave("
			SELECT MAX(forumid) AS max FROM " . TABLE_PREFIX . "forum"
		);
		return $row['max'];
	}

	public function index($id)
	{
		global $vbulletin;
		$row = $vbulletin->db->query_first_slave($this->make_query("forum.forumid = " . intval($id)));

		if ($row)
		{
			$indexer = vB_Search_Core::get_instance()->get_core_indexer();
			$fields = $this->record_to_indexfields($row);
			$indexer->index($fields);
		}
	}

	public function index_id_range($start, $finish)
	{
		global $vbulletin;
		$indexer = vB_Search_Core::get_instance()->get_core_indexer();
		$set = $vbulletin->db->query_read_slave($q = $this->make_query("forum.forumid BETWEEN " .
			intval($start) . " AND " . intval($finish)));
		while ($row = $vbulletin->db->fetch_array($set))
		{
			$fields = $this->record_to_indexfields($row);
			$indexer->index($fields);
		}
	}

	private function make_query($filter)
	{
		return "
			SELECT forum.forumid, forum.lastpost, forum.title, forum.description
			FROM " . TABLE_PREFIX . "forum as forum
			WHERE $filter
		";
	}



  /**
	 * Convert the basic table row to the index fieldset
	 *
	 * @param array $record
	 * @return return index fields
	 */
	private function record_to_indexfields($forum)
	{
		//make it easy to switch default fields
		$default = '';

		//common fields
		$fields['contenttypeid'] = $this->get_contenttypeid();
		$fields['id'] = $forum['forumid'];
		$fields['groupid'] = 0;
		$fields['dateline'] = $forum['lastpost'];
		$fields['userid'] = 0;
		$fields['username'] = '';
		$fields['ipaddress'] = '';
		$fields['title'] = $forum['title'];
		$fields['keywordtext'] = $forum['description'];
		return $fields;
	}

	protected $contenttypeid;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/