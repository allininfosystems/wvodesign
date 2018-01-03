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
 * ContentType Collection
 * Fetches a collection of contenttypes.
 *
 * ItemId
 * The itemid is an integer referring to contenttype.contenttypeid
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vBForum_Collection_SocialGroupDiscussion extends vB_Collection
{
	/*Config Info======================================================================*/
	protected $item_package = 'vBForum';
	protected $item_class = 'SocialGroupDiscussion';

	/*LoadInfo======================================================================*/

	/**
	 * Fetches the SQL for loading.
	 * $required_query is used to identify which query to build for classes that
	 * have multiple queries for fetching info.
	 *
	 * This can safely be based on $this->required_info as long as a consitent
	 * flag is used for identifying the query.
	 *
	 * @param int $required_query				- The required query
	 * @param bool $force_rebuild				- Whether to rebuild the string
	 *
	 * @return string
	 */
	protected function getLoadQuery($required_query = self::QUERY_BASIC, $force_rebuild = false)
	{
		// Hooks should check the required query before populating the hook vars
		$hook_query_fields = $hook_query_join = $hook_query_where = '';
		($hook = vBulletinHook::fetch_hook($this->query_hook)) ? eval($hook) : false;

		if (self::QUERY_BASIC == $required_query)
		{
			$ids = array_map('intval', $this->itemid);
			return $query = "
				SELECT
					discussion.discussionid as itemid,
					discussion.*,
					firstpost.postuserid,
					firstpost.postusername,
					firstpost.dateline,
					firstpost.state,
					firstpost.title,
					firstpost.pagetext,
					firstpost.ipaddress,
					firstpost.allowsmilie,
					firstpost.reportthreadid " .
					$hook_query_fields . "
				FROM " . TABLE_PREFIX . "discussion AS discussion JOIN " .
					TABLE_PREFIX . "groupmessage AS firstpost ON discussion.firstpostid = firstpost.gmid
					INNER JOIN " .	TABLE_PREFIX . "socialgroup AS socialgroup ON socialgroup.groupid = discussion.groupid " .
				$hook_query_join . "
				WHERE discussion.discussionid IN (" . implode(',', $ids) . ")
				$hook_query_where";
		}

		throw (new vB_Exception_Model('Invalid query id \'' . htmlspecialchars_uni($required_query) .
			'\'specified for social group message collection: ' . htmlspecialchars_uni($query)));
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/