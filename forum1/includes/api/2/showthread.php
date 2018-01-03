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

foreach ($VB_API_WHITELIST['response'] as $k => $v)
{
	if ($v == 'similarthreads')
	{
		unset($VB_API_WHITELIST['response'][$k]);
		break;
	}
}
$VB_API_WHITELIST['response']['similarthreads'] = array(
	'similarthreadbits' => array(
		'*' => array(
			'simthread' => array(
				'threadid', 'forumid', 'title', 'prefixid', 'taglist', 'postusername',
				'postuserid', 'replycount', 'preview', 'lastreplytime', 'prefix_plain_html',
				'prefix_rich'
			)
		)
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'showthread_similarthreadbit':
			$r['simthread']['lastreplytime'] = $r['simthread']['lastpost'];
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