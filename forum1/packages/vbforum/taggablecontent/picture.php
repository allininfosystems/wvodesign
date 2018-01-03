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

require_once(DIR . '/includes/class_taggablecontent.php');

/**
* Handle picture specific logic
*
*	Internal class, should not be directly referenced
* use vB_Taggable_Content_Item::create to get instances
*	see vB_Taggable_Content_Item for method documentation
*/
class vBForum_TaggableContent_Picture extends vB_Taggable_Content_Item
{
	protected function load_content_info()
	{
		return verify_id('picture', $this->contentid, 1, 1);
	}

	//Prevent the actual use of this object.
	//This was implemented as an example and not fully completed.
	//Its not ready for prime time and should not be used in production.
	//left here to avoid losing the work completed to date.
	private function __construct(){}

	public function fetch_content_type_diplay()
	{
		global $vbphrase;
		return $vbphrase['picture'];
	}

	public function fetch_return_url()
	{
		$url = parent::fetch_return_url();
		if(!$url)
		{
			$contentinfo = $this->fetch_content_info();
			$this->registry->input->clean_array_gpc('r', array(
				'albumid' => UINT
			));

			$url = "album.php?albumid=" . $this->registry->GPC['albumid'] . "&pictureid=$contentinfo[pictureid]#taglist";
		}
		return $url;
	}

	public function verify_ui_permissions()
	{
		/*
			For the moment allow anyone to tag pictures.  Should be revisited
			before we do this for real.
		*/
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
