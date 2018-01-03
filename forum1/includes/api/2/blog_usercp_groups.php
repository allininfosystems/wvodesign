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
		'content' => array(
			'blogbits' => array(
				'*' => array(
					'blog' => array(
						'bloguserid', 'title', 'username', 'jointime'
					),
					'show' => array('username', 'pending')
				)
			),
			'memberlist' => array(
				'*' => array(
					'member' => array(
						'userid', 'avatarurl', 'username'
					),
					'show' => array('pending')
				)
			), 'showavatarchecked'
		)
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'blog_cp_manage_group_blogbit':
			$r['blog']['jointime'] = $r['blog']['dateline'];
			break;
	}
}

vB_APICallback::instance()->add('result_prerender', 'api_result_prerender_2', 2);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/