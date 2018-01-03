<?php
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
/*
if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}
*/

class vB_Upgrade_424rc3 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '424rc3';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '4.2.4 Release Candidate 3';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '4.2.4 Release Candidate 2';

	/**
	* Beginning version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_STARTS = '';

	/**
	* Ending version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_ENDS   = '';

	/**
	 * Update Read Marking Option
	 * Replicates 3.8.10 Release Candidate 3 Step 1
	 * Strictly speaking we dont need this as we remove the option in 4.2.5 anyway.
	 * Step left in place to retain consistency of upgrader file with previous version(s).
	 */
	public function step_1()
	{
		$this->run_query(
			$this->phrase['version']['424rc3']['update_marking'],
			"UPDATE ".TABLE_PREFIX."setting SET value = '2' WHERE varname = 'threadmarking'"
		);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92674 $
|| # $Date: 2017-01-29 18:09:40 -0800 (Sun, 29 Jan 2017) $
|| ####################################################################
\*======================================================================*/
