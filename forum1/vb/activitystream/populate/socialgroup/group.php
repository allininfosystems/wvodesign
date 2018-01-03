<?php

/* ======================================================================*\
  || #################################################################### ||
  || # vBulletin 4.2.5 - Licence Number VBF6025393
  || # ---------------------------------------------------------------- # ||
  || # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
  || # This file may not be redistributed in whole or significant part. # ||
  || # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
  || #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
  || #################################################################### ||
  \*====================================================================== */

/**
 * Class to populate the activity stream from existing content
 *
 * @package	vBulletin
 * @version	$Revision: 92140 $
 * @date		$Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 */
class vB_ActivityStream_Populate_SocialGroup_Group extends vB_ActivityStream_Populate_Base
{
	/**
	 * Constructor - set Options
	 *
	 */
	public function __construct()
	{
		return parent::__construct();
	}

	/*
	 * Don't get: Deleted threads, redirect threads, CMS comment threads
	 *
	 */
	public function populate()
	{
		$typeid = vB::$vbulletin->activitystream['socialgroup_group']['typeid'];
		$this->delete($typeid);

		if (!vB::$vbulletin->activitystream['socialgroup_group']['enabled'])
		{
			return;
		}
		
		$timespan = TIMENOW - vB::$vbulletin->options['as_expire'] * 60 * 60 * 24;
		vB::$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "activitystream
				(userid, dateline, contentid, typeid, action)
				(SELECT
					creatoruserid, dateline, groupid, '{$typeid}', 'create'
				FROM " . TABLE_PREFIX . "socialgroup
				WHERE
					dateline >= {$timespan}
				)
		");
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/