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
		'attachmentoption' => $VB_API_WHITELIST_COMMON['attachmentoption'],
		'disablesmiliesoption', 'emailchecked',
		'folderbits', 'checked', 'multiquote_empty', 'rate',
		'forumrules', 'human_verify', 'posthash', 'posticons', 'postpreview',
		'poststarttime', 'prefix_options', 'selectedicon', 'title',
		'htmloption', 'specifiedpost',
		'threadreviewbits' => array(
			'*' => array(
				'postdate', 'posttime', 'reviewmessage', 'reviewtitle',
				'post' => array(
					'postid', 'threadid', 'username', 'userid'
				)
			)
		),
		'unquoted_post_count', 'return_node',
		'threadinfo' => $VB_API_WHITELIST_COMMON['threadinfo']
	),
	'vboptions' => array(
		'postminchars', 'titlemaxchars', 'maxposts'
	),
	'show' => array(
		'posticons', 'smiliebox', 'attach', 'threadrating', 'openclose', 'stickunstick',
		'closethread', 'unstickthread', 'subscribefolders', 'reviewmore',
		'parseurl', 'misc_options', 'additional_options'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/