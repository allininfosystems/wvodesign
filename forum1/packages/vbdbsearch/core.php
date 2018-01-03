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
 * @package vbdbsearch
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . '/packages/vbdbsearch/indexer.php');
require_once (DIR . '/packages/vbdbsearch/coresearchcontroller.php');
require_once (DIR . '/packages/vbdbsearch/postindexcontroller.php');

/**
*/
class vBDBSearch_Core extends vB_Search_Core
{
	/**
	 * Enter description here...
	 *
	 */
	static function init()
	{
		//register implementation objects with the search system.
		$search = vB_Search_Core::get_instance();
		$search->register_core_indexer(new vBDBSearch_Indexer());
		$search->register_index_controller('vBForum', 'Post', new vBDBSearch_PostIndexController());
		$__vBDBSearch_CoreSearchController = new vBDBSearch_CoreSearchController();
		$search->register_default_controller($__vBDBSearch_CoreSearchController);
//		$search->register_search_controller('vBForum', 'Post',$__vBDBSearch_CoreSearchController);
	}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/

