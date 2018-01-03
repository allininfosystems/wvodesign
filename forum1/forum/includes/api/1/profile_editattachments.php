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

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'attachlimit', 'attachsize', 'attachsum', 'pagenav',
			'pagenumber', 'perpage', 'showthumbs', 'template',
			'totalattachments', 'totalsize', 'userid', 'username',
			'attachmentlistbits' => array(
				'*' => array(
					'show' => array(
						'moderated', 'inlinemod', 'thumbnail', 'inprogress',
						'candelete', 'canmoderate'
					),
					'info' => array(
						'attachmentid', 'dateline', 'thumbnail_dateline',
						'attachmentextension', 'dateline', 'filename',
						'size', 'counter', 'postdate', 'posttime',
						'userid'
					),
					'uniquebit' => array(
						'threadinfo' => $VB_API_WHITELIST_COMMON['threadinfo'],
						'post' => array(
							't_title', 'postid', 'p_title'
						),
						'template',
						'pageinfo',
						'article' => array(
							'title'
						),
						'url',
						'album' => array(
							'albumid', 'title'
						),
						'group' => array(
							'albumid', 'name'
						)
					)
				)
			)
		)
	),
	'show' => array(
		'attachment_list', 'attachquota',
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 13:53, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 35584 $
|| ####################################################################
\*======================================================================*/