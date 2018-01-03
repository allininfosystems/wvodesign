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
			'blogheader',
			'featured_blogbits' => array(
				'*' => array(
					'blog' => $VB_API_WHITELIST_COMMON['blog'], 'status',
					'show' => array('postcomment', 'status')
				)
			), 'display',
			'recentblogbits' => array(
				'*' => array(
					'updated' => $VB_API_WHITELIST_COMMON['blog'],
					'show' => array('postcomment')
				)
			),
			'recentcommentbits' => array(
				'*' => array(
					'updated' => array(
						'userid', 'username', 'avatarurl', 'blogtextid', 'blogid',
						'posttime', 'excerpt', 'title', 'pagetext'
					),
					'show' => array('avatar', 'moderation', 'detailedtime')
				)
			),
			'blogbits' => array(
				'*' => array(
					'blog' => $VB_API_WHITELIST_COMMON['blog'], 'status',
					'show' => array('postcomment', 'status')
				)
			), 'blogcategoryid', 'blogtype',
			'categoryinfo', 'day', 'month', 'pagenav', 'selectedfilter',
			'userinfo' => array('userid', 'username', 'avatarurl'), 'year'
		),
		'sidebar' => $VB_API_WHITELIST_COMMON['blogsidebarcategory']
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'blog_home_list_comment':
			$r['updated']['posttime'] = $r['updated']['dateline'];
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