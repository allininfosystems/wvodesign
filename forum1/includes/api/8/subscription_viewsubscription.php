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

$threadbit = $VB_API_WHITELIST_COMMON['threadbit'];

$threadbit['thread'][] = 'forumtitle';

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'folder', 'folderjump', 'pagenav', 'totalallthreads',
			'threadbits' => array(
				'*' => $threadbit
			)
		)
	),
	'show' => array(
		'allfolders', 'threadicons', 'dotthreads', 'havethreads',
	)
);

function api_result_prerender($t, &$r)
{
	switch ($t)
	{
		case 'SUBSCRIBE':
			if (isset($r['threadbits']))
			{
				if (!isset($r['threadbits'][0]))
				{
					$r['threadbits'] = array($r['threadbits']);
				}
			}
		break;
	}
}

vB_APICallback::instance()->add('result_prerender', 'api_result_prerender', 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
