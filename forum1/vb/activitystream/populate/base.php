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
 * Class to populate
 *
 * @package	vBulletin
 * @version	$Revision: 92140 $
 * @date		$Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 */
class vB_ActivityStream_Populate_Base
{
	/**
	 * Constructor - set Options
	 *
	 */
	public function __construct()
	{

	}

	/*
	 * Delete specific type from the stream
	 *
	 * @param	int	activitystreamtype typeid
	 */
	protected function delete($typeid)
	{
		vB::$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "activitystream
			WHERE
				typeid = " . intval($typeid)
		);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/