<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

error_reporting(E_ALL & ~E_NOTICE);

// #############################################################################
/**
* Checks whether or not an administrator can post announcements
*
* @param	integer	Forum ID
*
* @return	integer	The return value of this function is really rubbish... who wrote this?
*/
function fetch_announcement_permission_error($forumid)
{
	global $vbulletin, $phrase;

	if ($forumid == -1 AND !($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']))
	{
		return 1;
	}
	else if ($forumid != -1 AND !can_moderate($forumid, 'canannounce'))
	{
		return 2;
	}

	return 0;
}

// #############################################################################
/**
* Returns the phrase name for an error message
*
* @param	integer	Magic error number
*
* @return	string
*/
function fetch_announcement_permission_error_phrase($errno)
{
	global $vbphrase;
	switch($errno)
	{
		case 1:
			return 'you_do_not_have_permission_global';
			break;
		case 2:
			return 'you_do_not_have_permission_forum';
			break;
		default:
			return construct_phrase($vbphrase['unknown_error'], $errno);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>