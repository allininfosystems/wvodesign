<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.2.5 - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

class vB_APIMethod_api_usersearch extends vBI_APIMethod
{
	public function output()
	{
		global $vbulletin, $db;

		$vbulletin->input->clean_array_gpc('p', array('fragment' => TYPE_STR));

		$vbulletin->GPC['fragment'] = convert_urlencoded_unicode($vbulletin->GPC['fragment']);

		if ($vbulletin->GPC['fragment'] != '' AND strlen($vbulletin->GPC['fragment']) >= 3)
		{
			$fragment = htmlspecialchars_uni($vbulletin->GPC['fragment']);
		}
		else
		{
			$fragment = '';
		}

		if ($fragment != '')
		{
			$users = $db->query_read_slave("
				SELECT user.userid, user.username FROM " . TABLE_PREFIX . "user
				AS user WHERE username LIKE('" . $db->escape_string_like($fragment) . "%')
				ORDER BY username
				LIMIT 15
			");
			while ($user = $db->fetch_array($users))
			{
				$data[$user['userid']] = $user['username'];
			}
		}

		return $data;

	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/