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

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'header' => array(
			'pmbox', 'notifications_menubits', 'notifications_total', 'notices'
		),
		'activemembers', 'activeusers', 'birthdays',
		'forumbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['forumbit']
		),
		'newuserinfo', 'numberguest', 'numbermembers', 'numberregistered',
		'recorddate', 'recordtime', 'recordusers',
		'template_hook' => array(
			'forumhome_wgo_stats' => array(
				'blogstats',
				'latestentry' => array(
					'username', 'userid', 'title', 'blogid', 'postedby_username', 'postedby_userid', 'blogtitle'
				)
			)
		),
		'today', 'totalonline', 'totalposts', 'totalthreads', 'upcomingevents',
	),
	'show' => array(
		'birthdays', 'todaysevents', 'notices', 'dismiss_link', 'notifications',
		'loggedinusers', 'pmlink', 'homepage', 'addfriend', 'emaillink', 'activemembers'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/