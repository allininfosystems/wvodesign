<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.2 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'avatarchecked', 'avatarlist', 'categorybits', 'categoryname',
			'currentavatar', 'inimaxattach', 'maxnote', 'nouseavatarchecked',
			'pagenav'
		)
	),
	'show' => array(
		'categories', 'forumavatars', 'customavatar', 'customavatar_url',
		'maxnote'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 13:53, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/