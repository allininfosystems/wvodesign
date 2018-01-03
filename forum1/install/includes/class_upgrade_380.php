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

#### NOTE FOR UPGRADES 3.8.1 onwards ####

Upgrade steps for 3.8.1 to 3.8.4 are included in class_upgrade_400a1
Upgrade steps for 3.8.5 are included in class_upgrade_403
Upgrade steps for 3.8.6 are included in class_upgrade_400a1
Upgrade steps for 3.8.9 Beta 5 are included in class_upgrade_423b4
Upgrade steps for 3.8.10 Beta 1 are included in class_upgrade_424b1
Upgrade steps for 3.8.10 Beta 2 are included in class_upgrade_424b3

*/

class vB_Upgrade_380 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '380';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '3.8.0';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '3.8.0 Release Candidate 2';

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
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
