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
require_once DIR . '/includes/class_xml.php';
require_once DIR . '/includes/functions_misc.php';
require_once (DIR."/includes/functions_search.php");
require_once (DIR."/vb/search/core.php");

/**
 * @package vBulletin
 * @subpackage Search
 * @author Ed Brown, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */


/**
 * Type class for a search item
 *
 * This is the generic search type. See /vb/search/type.php for the
 * code
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBForum_Search_Type_Common extends vB_Search_Type
{
	/**
	 * vBForum_Search_Type_Common::create_item()
	 *Returns an item of this type based on the id.
	 *
	 * @param integer $id. This isn't used, but it's a part of the type definition.
	 * @return the type
	 */
	public function create_item($id)
	{
		return new vBForum_Search_Type_Common;
	}

	protected $package = 'vBForum';

	protected $class = 'Common';

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/