<?php if (!defined('VB_ENTRY')) die('Access denied.');
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

/**
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */

class vB_Facebook_RegisterConnectlogin
{
	/**
	 * Url destination for post request
	 *
	 * @var	string
	 */
	CONST POSTURL = 'https://services.vbulletin.com/services/vbfacebook/v1/';

	/**
	 * Registry
	 *
	 * @var	vB_Registry
	 */
	private static $registry = null;

	/**
	 * Send Post request with user's fbuserid
	 *
	 * @param	vB_Registry Object
	 * @param	bool		Bypass the session->created check
	 *
	 * @return	string	Response to this request from remote server
	 */
	public static function registerLogin(&$registry, $bypassCreated = false)
	{
		self::$registry = $registry;

		if ((!$bypassCreated AND !self::$registry->session->created) OR !self::$registry->userinfo['userid'] OR !self::$registry->userinfo['fbuserid'] OR !is_facebookenabled())
		{
			return;
		}

		$params = array(
			'facebookProfileId'   => self::$registry->userinfo['fbuserid'],
			'facebookAccessToken' => self::$registry->userinfo['fbaccesstoken'],
			'licenseKey'          => '[#]facebookguid[#]',
			'hideFbConnect'       => self::$registry->userinfo['disablevbsocial'],
		);

		return self::sendRequest('registerConnectLogin', $params);
	}

	/**
	 * Send POST request to API server
	 *
	 * @param	string	API method to call
	 * @param	array	Variables to post
	 *
	 * @return	string	Response to this request from remote server
	 */
	private static function sendRequest($method, $params)
	{
		require_once(DIR . '/includes/class_vurl.php');
		$vurl = new vB_vURL(self::$registry);
		$vurl->set_option(VURL_URL, self::POSTURL . $method);
		$vurl->set_option(VURL_POST, 1);
		$vurl->set_option(VURL_RETURNTRANSFER, 1);
		$vurl->set_option(VURL_CLOSECONNECTION, true);
		$vurl->set_option(VURL_POSTFIELDS, http_build_query($params, '', '&'));
		return $vurl->exec();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/