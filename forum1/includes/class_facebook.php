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

require_once(DIR . '/includes/facebook/facebook.php');

/**
 * vBulletin wrapper for the facebook client api, singleton
 *
 * @package vBulletin
 * @author Michael Henretty, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Facebook
{
	const VERSION = '/v2.2';

	/**
	 * A reference to the singleton instance
	 *
	 * @var vB_Facebook
	 */
	protected static $instance = null;

    /**
	 * A reference to the login_facebook singleton instance
	 *
	 * @var vB_Facebook
	 */
	protected static $login_facebook_instance = null;

	/**
	 * The facebook client api object
	 *
	 * @var Facebook
	 */
	protected $facebook = null;

	/**
	 * The facebook user array
	 *
	 * @var array
	 */
	protected $fb_user = null;

	/**
	 * The facebook userid if logged in
	 *
	 * @var int
	 */
	protected $registry = null;

	/**
	 * The facebook userid if logged in
	 *
	 * @var int
	 */
	protected $fb_userid = null;

	/**
	 * The associated vBulletin userid if available
	 *
	 * @var int
	 */
	protected $vb_userid = null;
	protected $fb_userinfo = array();

	/**
	 * The users connection info we want to grab
	 *
	 * @var array
	 */
	protected $fb_userconnectioninfo = array();
	protected $connection_fields = array(
		'activities',
		'interests',
		'music',
		'movies',
		'books',
		'notes',
		'website'
	);

	/**
	 * Returns an instance of the facebook client api object
	 *
	 * @return vB_Facebook
	 */
	public static function instance()
	{
		global $vbulletin;
		if (!isset(self::$instance))
		{
			// boot up the facebook api
			self::$instance = new vB_Facebook(
				$vbulletin->options['facebookappid'],
				$vbulletin->options['facebooksecret']
			);
		}

		return self::$instance;
	}

	/**
	 * Returns the login_facebook instance of the facebook client api object
	 *
	 * @return vB_Facebook
	 */
	public static function login_facebook_instance()
	{
		global $vbulletin;
		if (!isset(self::$login_facebook_instance))
		{
			// boot up the facebook api
			self::$login_facebook_instance = new vB_Facebook(
				$vbulletin->options['facebookappid'],
				$vbulletin->options['facebooksecret']
			);
		}

		return self::$login_facebook_instance;
	}


	/**
	 * Constructor
	 *
	 * @param int $apikey	the api key for the facebook user
	 * @param int $secret	the facebook secret for the application
	 */
	protected function __construct($facebookappid, $facebooksecret)
	{
		// cache a reference to the registry object
		global $vbulletin;
		$this->registry = $vbulletin;

		// initialize fb api and grab fb userid to cache locally
		try
		{
			if (!$this->initFacebook($facebookappid, $facebooksecret))
			{
				//force use of stored access token if cookie value is bad or expired.
				unset($_COOKIE['fbsr_' . $facebookappid]);
				$this->initFacebook($facebookappid, $facebooksecret);
			}
		
			if (!$this->fb_userid AND $_REQUEST['dofbredirect'])
			{
				exec_header_redirect($this->facebook->getLoginUrl());
			}
		}
		catch (Exception $e)
		{
			$this->logFacebookException($e);
			$this->fb_userid = null;
		}
	}


	protected function initFacebook($facebookappid, $facebooksecret)
	{
  	// init the facebook graph api
    $this->facebook = new Facebook(array(
    	'appId'  => $facebookappid,
			'secret' => $facebooksecret
		));

		// check for valid session without pinging facebook
		if ($this->fb_userid = $this->facebook->getUser())
		{
			// make sure local copy of fb session is up to date
			$this->validateFBSession();
			return true;
		}
		return false;
	}

	/**
	 * Checks the fb userid returned from api to make sure its valid
	 *
	 * @return bool, fb userid if logged in, false otherwise
	 */
	protected function isValidUser()
	{
		// check for null restuls, or error code (<1000)
		return (!empty($this->fb_userid) AND $this->fb_userid >= 1000);
	}

	/**
	 * Makes sure local copy of FB session is in synch with actual FB session
	 *
	 * This function doesn't actually validate anything -- it simply saves the FB session 
	 * information if it differs from what is currently stored.
	 * @return none 
	 */
	protected function validateFBSession()
	{
		// grab the current access token stored locally (in cookie or db depending on login status)
		if ($this->registry->userinfo['userid'] == 0)
		{
			$curaccesstoken = $this->registry->input->clean_gpc('c', COOKIE_PREFIX . 'fbaccesstoken', TYPE_STR);
		}
		else
		{
			$curaccesstoken = !empty($this->registry->userinfo['fbaccesstoken']) ? $this->registry->userinfo['fbaccesstoken'] : '';
		}

		// if we have a new access token that is valid, re-query FB for updated info, and cache it locally
		if ($curaccesstoken != $this->facebook->getAccessToken() AND $this->isValidAuthToken())
		{
			// update the userinfo array with fresh facebook data
			$this->registry->userinfo['fbaccesstoken'] = $this->facebook->getAccessToken();

			//$this->registry->userinfo['fbprofilepicurl'] = $this->fb_userinfo['pic_square'];

			// if user is guest, store fb session info in cookie
			if ($this->registry->userinfo['userid'] == 0)
			{
				vbsetcookie('fbaccesstoken', $this->facebook->getAccessToken());
				vbsetcookie('fbprofilepicurl', $this->fb_userinfo['pic_square']);
			}

			// if authenticated user, store fb session in user table
			else
			{
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET
						fbaccesstoken = '" . $this->facebook->getAccessToken() . "'
					WHERE userid = " . $this->registry->userinfo['userid'] . "
				");
			}
		}
	}

	/**
	 * Checks if the current user is logged into facebook
	 *
	 * @return bool
	 */
	public function userIsLoggedIn()
	{
		// make sure facebook is connect also enabled
		return self::instance()->isValidUser();
	}


	/**
	 * Verifies that the current session auth token is still valid with facebook
	 * 	- performs a Facebook roundtrip
	 *
	 * @return bool, true if auth token is still valid
	 */
	public function isValidAuthToken()
	{
		if (!$this->getFbUserInfo())
		{
			$this->facebook->setAccessToken(null);
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Checks for a currrently logged in user through facebook api
	 *
	 * @return mixed, fb userid if logged in, false otherwise
	 */
	public function getLoggedInFbUserId()
	{
		if (!$this->isValidUser())
		{
			return false;
		}

		return $this->fb_userid;
	}


	/**
	 * Grabs logged in user info from faceboook if user is logged in
	 *
	 * @param bool, forces a roundtrip to the facebook server, ie. dont use cached info
	 *
	 * @return array, fb userinfo array if logged in, false otherwise
	 */
	public function getFbUserInfo($force_reload = false)
	{
		// check for cached versions of this, and return it if so
		if (!empty($this->fb_userinfo) AND !$force_reload)
		{
			return $this->fb_userinfo;
		}

		// make sure we have a fb user and fb session, otherwise we cant return any data
		if (!$this->isValidUser() OR !$this->facebook->getAccessToken())
		{
			return false;
		}

		try
		{
			$response = $this->facebook->api(self::VERSION . '/me');

			if (is_array($response) AND !empty($response))
			{
				$this->fb_userinfo = $response;

				//this is no longer returned, but can be trivially constructured
				$base_url = 'http://graph.facebook.com/' . self::VERSION . '/' . $this->fb_userinfo['id'] . '/picture';
				$this->fb_userinfo['pic_big'] = $base_url . '?type=large';
				$this->fb_userinfo['pic'] = $base_url . '?type=normal';
				$this->fb_userinfo['profile_url'] =  'http://www.facebook.com/' . $this->fb_userinfo['id'];
			}
		}
		catch (Exception $e)
		{
			$this->logFacebookException($e);
			return false;
		}

		// now return the user info if we got any
		return $this->fb_userinfo;
	}

	/**
	 * Grabs logged in user connections (ie likes, activities, interests, etc)
	 *
	 * @param bool, forces a roundtrip to the facebook server, ie. dont use cached info
	 *
	 * @return array, fb userconnectioninfo array if logged in, false otherwise
	 */
	public function getFbUserConnectionInfo($force_reload = false)
	{
		// check for cached versions of this, and return it if so
		if (!empty($this->fb_userconnectioninfo) AND !$force_reload)
		{
			return $this->fb_userconnectioninfo;
		}

		// make sure we have a fb user and fb session, otherwise we cant return any data
		if (!$this->isValidUser() OR !$this->facebook->getAccessToken())
		{
			return false;
		}

		// attempt to grab userinfo from fb graph api, using FQL
		try
		{
			$response = $this->facebook->api(
				self::VERSION . '/me?fields='.implode(',', $this->connection_fields)
			);

			if (is_array($response) AND !empty($response))
			{
				$this->fb_userconnectioninfo = $response[0];
			}
		}
		catch (Exception $e)
		{
			$this->logFacebookException($e);
			return false;
		}

		// now return the user info if we got any
		return $this->fb_userconnectioninfo;
	}


	/**
	 * Checks if current facebook user is associated with a vb user, and returns vb userid if so
	 *
	 * @param int, facebook userid to check in vb database, if not there well user current
	 * 		logged in user
	 * @return mixed, vb userid if one is associated, false if not
	 */
	public function getVbUseridFromFbUserid($fb_userid = false)
	{
		// if no fb userid was passed in, attempt to use current logged in fb user
		// but if no current fb user, there cannot be an associated vb account, so return false
		if (empty($fb_userid) AND !$fb_userid = $this->getLoggedInFbUserId())
		{
			return false;
		}

		// check if vB userid is already cached in this object
		if ($fb_userid == $this->getLoggedInFbUserId() AND !empty($this->vb_userid))
		{
			return $this->vb_userid;
		}

		// otherwise we have to grab the vb userid from the database
		$user = $this->registry->db->query_first_slave("
			SELECT userid
			FROM `" . TABLE_PREFIX . "user`
			WHERE fbuserid = '$fb_userid' LIMIT 1
		");
		$this->vb_userid = (!empty($user['userid']) ? $user['userid'] : false);

		return $this->vb_userid;
	}

	/**
	 * Checks if current facebook user is associated with a vb user, and returns vb userid if so
	 *
	 * @param int, facebook userid to check in vb database, if not there well user current
	 * 		logged in user
	 * @return mixed, vb userid if one is associated, false if not
	 */
	public function publishFeed($message, $name, $link, $description, $picture = null)
	{
		global $vbulletin;

		$params = array(
			'message'     => $message,
			'name'        => $name,
			'link'        => $link,
			'description' => $description,
		);

		// add picture link if we get one
		if (!empty($picture))
		{
			$params['picture'] = $vbulletin->options['facebookfeedimageurl'];
		}

		// if no link was passed in, try using the admin option
		else if (!empty($vbulletin->options['facebookfeedimageurl']))
		{
			$params['picture'] = $vbulletin->options['facebookfeedimageurl'];
		}

		// attempt to publish to user's wall
		try
		{
			$response = $this->facebook->api(
				self::VERSION . '/me/feed',
				'POST',
				$params
			);
			return !empty($response);
		}
		catch (Exception $e)
		{
			$this->logFacebookException($e);
			return false;
		}
	}

	/**
	 * Kills the current Facebook session
	 */
	public function doLogoutFbUser()
	{
		// set the current session to null
		$this->facebook->setAccessToken(null);
		$this->fb_userid = 0;
	}

	/**
	 * Issue graph request to check login
	 */
	public function verifyLoginFromServer()
	{
		try
		{
			return $this->facebook->api(self::VERSION . '/me');
		}
		catch (FacebookApiException $e)
		{
			$this->logFacebookException($e);
			return false;
		}
	}

	private function logFacebookException($e)
	{
		//leaving here as a noop to make it easier to add back for debugging purposes
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
