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
		'activeusers' => $VB_API_WHITELIST_COMMON['activeusers'],
		'announcebits' => array(
			'*' => array(
				'announcement' => array(
					'announcementid', 'title', 'views', 'username', 'userid', 'usertitle',
					'postdate'
				),
				'announcementidlink',
				'foruminfo' => array(
					'forumid'
				)
			)
		),
		'daysprune', 'daysprunesel',
		'forumbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['forumbit']
		),
		'foruminfo' => $VB_API_WHITELIST_COMMON['foruminfo'],
		'forumrules', 'limitlower',
		'limitupper',
		'moderatorslist' => array(
			'*' => array(
				'moderator' => $VB_API_WHITELIST_COMMON['moderator']
			)
		),
		'numberguest', 'numberregistered',
		'order', 'pagenumber',
		'perpage', 'prefix_options', 'prefix_selected', 'sort',
		'threadbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['threadbit']
		),
		'threadbits_sticky' => array(
			'*' => $VB_API_WHITELIST_COMMON['threadbit']
		),
		'totalmods', 'totalonline', 'totalthreads',
		'pagenav' => $VB_API_WHITELIST_COMMON['pagenav'],
	),
	'show' => array(
		'foruminfo', 'newthreadlink', 'threadicons', 'threadratings', 'subscribed_to_forum',
		'moderators', 'activeusers', 'post_queue', 'attachment_queue', 'mass_move',
		'mass_prune', 'post_new_announcement', 'addmoderator', 'adminoptions',
		'movethread', 'deletethread', 'approvethread', 'openthread', 'inlinemod',
		'spamctrls', 'noposts', 'dotthreads', 'threadslist', 'forumsearch',
		'forumslist', 'stickies'
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'threadbit_announcement':
			$r['announcement']['postdate'] = $r['announcement']['startdate'];
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