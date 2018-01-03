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
if (!VB_API) die;

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'anncount',
		'announcebits' => $VB_API_WHITELIST_COMMON['postbits'],
		'foruminfo' => $VB_API_WHITELIST_COMMON['foruminfo']
	),
	'show' => array(
		'viewall', 'post_new_announcement'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/