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

$VB_API_WHITELIST = array(
	'response' => array(
		'HTML' => array(
			'folderid', 'foldername',
			'messagelist_periodgroups' => array(
				'*' => array(
					'groupid', 'groupname', 'messagesingroup',
					'messagelistbits' => array(
						'*' => array(
							'pm' => array(
								'pmid', 'senddate', 'sendtime', 'statusicon',
								'iconpath', 'icontitle', 'title'
							),
							'userbit',
							'show' => array(
								'pmicon', 'unread'
							)
						)
					)
				)
			),
			'pagenav',
			'pagenumber', 'perpage', 'pmquota', 'pmtotal',
			'receipts', 'sortfilter', 'totalmessages', 'startmessage',
			'endmessage'
		)
	),
	'show' => array(
		'thisfoldertotal', 'allfolderstotal', 'pmicons', 'messagelist', 'openfilter',
		'pagenav', 'sentto', 'movetofolder'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/