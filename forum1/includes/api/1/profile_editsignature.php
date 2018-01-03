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

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'inimaxattach', 'maxnote', 'preview', 'sigperms',
			'sigpicurl'
		)
	),
	'show' => array(
		'canbbcode', 'canbbcodebasic', 'canbbcodecolor', 'canbbcodesize',
		'canbbcodefont', 'canbbcodealign', 'canbbcodelist', 'canbbcodelink',
		'canbbcodecode', 'canbbcodephp', 'canbbcodehtml', 'canbbcodequote',
		'allowimg', 'allowvideo', 'allowsmilies', 'allowhtml', 'cansigpic', 'cananimatesigpic'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/