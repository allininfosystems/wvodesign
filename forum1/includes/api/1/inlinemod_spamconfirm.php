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
			'deleteother', 'deletereason', 'deletetype', 'keepattachments', 'postid',
			'postids',
			'punitive_action' => array(
				'ban_usergroups'
			),
			'report', 'threadids',
			'type', 'useraction',
			'username_bits' => array(
				'*' => array(
					'user' => array(
						'userid', 'username', 'joindate_string', 'post_count'
					),
					'show' => array(
						'userid_checkbox', 'prevent_userselection'
					)
				)
			),
		)
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/