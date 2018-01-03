<?php

	// This sometimes happens
	if (!isset($GLOBALS['vbulletin']->db)) exit();

	// Let's define a set of flags
	define (VBGHSFS_NO_TEST, -1);
	define (VBGHSFS_PASS, 0);
	define (VBGHSFS_FAIL, 1);
	define (VBGHSFS_HIT_BUT_PASS, 2);
	define (VBGHSFS_REMOTE_ERROR, 3);
	define (VBGHSFS_BLOCKED, 1);
	define (VBGHSFS_ALLOWED, 0);

	// And few variables
	global $vbghsfs_userHash, $vbghsfs_CacheHit;

	/**
	* Function called on 'register_addmember_complete' hook
	*/
	function VBGHSFS_UpdateLog() {

		global $vbulletin, $vbghsfs_userHash;

		$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_log SET `user_id` = "'.$vbulletin->userinfo['userid'].'" WHERE `user_hash` = "'.$vbghsfs_userHash.'"; ';
		$logresult = $vbulletin->db->query($sql);
	}

	/**
	* Function called on 'register_addmember_process' hook
	*/
	function VBGHSFS_Process() {

		global $vbulletin, $userdata;

		$ip = $vbulletin->session->vars['host'];
		$username = $vbulletin->userinfo['username'];
		$email = $vbulletin->GPC['email'];
		if (($email == '') && (isset($userdata))) { // hook for new FB Connect procedures
			$username = $userdata->fetch_field('username');
			$email = $userdata->fetch_field('email');
		}

		$message = '  GPC_user: '.$vbulletin->GPC['username'].'         GPC_email: '.$vbulletin->GPC['email'];
		$message .= ' Data_user: '.$userdata->fetch_field('username').' Data_email:'.$userdata->fetch_field('email');
		$message .= ' Info_user: '.$vbulletin->userinfo['username'].'   Info_email: '.$vbulletin->userinfo['email'];
		$message .= ' REQ_username: '.$_REQUEST['username'].'           REQ_email: '.$_REQUEST['email'];

		if (($username != '') && ($username != 'Unregistered')) { //disable check if not set
			$result = VBGHSFS_checkForSpam($username, 'username');
			if ($result !== VBGHSFS_NO_TEST) {
				VBGHSFS_Action('username', $username, $username, $email, $ip, $result);
			}
		}

		if ($email != '') { //disable check if not set
			$result = VBGHSFS_checkForSpam($email, 'email');
			if ($result !== VBGHSFS_NO_TEST) {
				VBGHSFS_Action ('email', $email, $username, $email, $ip, $result);
			}
		}

		$result = VBGHSFS_checkForSpam($ip, 'ip');
		if ($result !== VBGHSFS_NO_TEST) {
			VBGHSFS_Action('ip', $ip, $username, $email, $ip, $result);
		}


		VBGHSFS_Log($field, $data.'Passed StopForumSpam checks. Sent to vBulletin Registration System.', $username, $email, $ip, VBGHSFS_ALLOWED);
	}

	/**
	* Function to check for SPAM
	* @param string $data Query param content
	* @param string $field Query param name
	* @return SPAM flag
	*/
	function VBGHSFS_checkForSpam($data, $field) {

		global $vbulletin;


		if ($vbulletin->options['glowhostspamomatic_testfield_'. $field]) {
			switch (VBGHSFS_CacheHit($data, $field)) {
				case 1: // Found in cache - SPAM
					$is_spam = VBGHSFS_FAIL;
					break;

				case 2: // Found in cache - OK
					$is_spam = VBGHSFS_PASS;
					break;

				case 0:  // Not found in cache - test
	  				switch ($field) {
						case 'username':
							$is_spam = VBGHSFS_getXML('http://www.stopforumspam.com/api?username='.urlencode($data).'&f=serial', $field);
							break;

						case 'email':
							$is_spam = VBGHSFS_getXML('http://www.stopforumspam.com/api?email='.urlencode($data).'&f=serial', $field);
							break;

						case 'ip':
							$is_spam = VBGHSFS_getXML('http://www.stopforumspam.com/api?ip='.urlencode($data).'&f=serial', $field);
							break;
					}
	  				break;
	  		}

	  		if ($is_spam !== VBGHSFS_REMOTE_ERROR) VBGHSFS_updateCache($data, $is_spam, $field);

	  } else return VBGHSFS_NO_TEST; // no test performed

 		return $is_spam;
	}

	/**
	* Function to check if spam flag cached and remove old records
	* @param string $data Query param content
	* @param string $field Query param name
	* @return true
	*/
	function VBGHSFS_CacheHit($data, $field) {

		global $vbulletin, $vbghsfs_Purged, $vbghsfs_CacheHit;

		$vbghsfs_CacheHit = false;

		if (!$vbghsfs_Purged) { //must remove old records
			$sql = 'DELETE FROM '.TABLE_PREFIX.'glowhostspamomatic_remotecache WHERE `date` < DATE_SUB(NOW(), INTERVAL '.(int)$vbulletin->options['glowhostspamomatic_remote_cache'].' DAY); ';
			$vbulletin->db->query($sql);
			$vbghsfsPurged = true;
		}

		$sql = 'SELECT is_spambot FROM '.TABLE_PREFIX.'glowhostspamomatic_remotecache WHERE `field`="'.$field.'" AND `data`="'.addslashes($data).'" LIMIT 1';
		$result = $vbulletin->db->query($sql);


		$line = $vbulletin->db->fetch_array($result);
  		if (!empty($line)) {
  			$vbghsfs_CacheHit = true;

  			if ($line['is_spambot'] > 0) return VBGHSFS_FAIL;
  			if ($line['is_spambot'] == 0) return VBGHSFS_HIT_BUT_PASS;
  		}


		return VBGHSFS_PASS; //no hit. Must check
	}

	/**
	* Function to connect to stopforumspam.com
	* @param string $url URL to connect to
	* @return query result
	*/
	function VBGHSFS_getXML($url, $field = '') {
		global $vbulletin;

		$curl_installed = (function_exists('curl_init'));
		$curl_failed = false;

	    if ($curl_installed) {

	        $cURL = curl_init();
	        curl_setopt($cURL, CURLOPT_URL, $url);
	        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($cURL, CURLOPT_TIMEOUT, '15');

	        $pageContent = curl_exec($cURL);

			if (curl_errno($cURL)) { // cURL failed
    			if (!($pageContent = @file_get_contents($url))) return VBGHSFS_REMOTE_ERROR;
	        }

	        curl_close($cURL);

	    } else if (!($pageContent = @file_get_contents($url))) return VBGHSFS_REMOTE_ERROR;

	    $pageContent = unserialize($pageContent);
	    if ($pageContent[$field]['appears']) {

			$sql = 'SELECT DATEDIFF(NOW(), "'.$pageContent[$field]['lastseen'].'") AS DAYS';
    		$result = $vbulletin->db->query($sql);
    		$line = $vbulletin->db->fetch_array($result);
    		$days = $line['DAYS'];
    		$result = ($days > $vbulletin->options['glowhostspamomatic_expire_day'])?VBGHSFS_PASS:VBGHSFS_FAIL; //user appears in SFS DB. but the entry is too old

	    } else $result = VBGHSFS_PASS;

	    return $result;
	}

	/**
	* Function to save result in cache
	* @param string $data Query param content
	* @param string $is_spambot Is SPAM flag
	* @param string $field Query param name
	*/
	function VBGHSFS_updateCache($data, $is_spambot, $field) {

		global $vbulletin, $vbghsfs_CacheHit;


		if (!$vbghsfs_CacheHit) {
			$sql = 'INSERT HIGH_PRIORITY IGNORE INTO '.TABLE_PREFIX.'glowhostspamomatic_remotecache(`date`, `data`, `is_spambot`, `field`) VALUES (now(), "'.addslashes($data).'", "'.$is_spambot.'", "'.$field.'"); ';
			$vbulletin->db->query($sql);

		}
	}

	/**
	* Function to add log into cache
	* @param string $field Query param name
	* @param string $data Query param content
	* @param string $username User login
	* @param string $email User e-mail
	* @param string $ip User IP
	* @param string $is_blocked Is blocked flag
	*/
	function VBGHSFS_Log($field, $data, $username, $email, $ip, $is_blocked, $predefined_message = '') {

		global $vbulletin, $vbghsfs_userHash;

		if (($username == 'Unregistered') || ($username == '')) $username = 'No Username Submitted';

		$prefix = 'Spam-O-Matic Tagged '.$field.' - ';
		if ($field == '') $prefix = '';
		$message = $prefix.$data;
		$vbghsfs_userHash = md5(date('l jS \of F Y h:i:s A').$username);
		if ($predefined_message != '') $message = $predefined_message;
		$sql = 'INSERT INTO '.TABLE_PREFIX.'glowhostspamomatic_log(`date`, `ip`, `email`, `username`, `message`, `is_blocked`, `user_hash`) VALUES (now(), "'.addslashes($ip).'", "'.addslashes($email).'", "'.addslashes($username).'", "'.addslashes($message).'", "'.$is_blocked.'", "'.$vbghsfs_userHash.'"); ';
		$vbulletin->db->query($sql);

	}

	/**
	* Function to perform required actions based on setting and SPAM check
	* @param string $field Query param name
	* @param string $data Query param content
	* @param string $username User login
	* @param string $email User e-mail
	* @param string $ip User IP
	* @param string $resultcode SPAM check result code
	*/
	function VBGHSFS_Action($field, $data, $username, $email, $ip, $resultcode) {

		global $vbulletin;

		settype($resultcode, 'int');

		if ($resultcode == VBGHSFS_REMOTE_ERROR) { //SPAM check failed
			if ($vbulletin->options['glowhostspamomatic_result'] == 0) { // Log result, but allow registration
				VBGHSFS_Log($field, $data." Unable to connect to StopForumSpam.com. This is probably due to some temporary problems on their website: Unable to check. User passed to vBulletin registration system per your settings at 'StopForumSpam: Query Connection Errors'", $username, $email, $ip, VBGHSFS_ALLOWED);
			} else {
				if ($vbulletin->options['glowhostspamomatic_result_timeout'] == 1) { // Connection result timeout
					VBGHSFS_Log($field, $data . " Unable to connect to StopForumSpam.com. This is probably due to some temporary problems on their website: Unable to check. User rejected per your settings at 'StopForumSpam: Query Connection Errors'", $username, $email, $ip, VBGHSFS_BLOCKED);
					standard_error(fetch_error('vbstopformspam_reject_connectionerror', $query));
				} else {
					VBGHSFS_Log($field, $data . " Unable to connect to StopForumSpam.com. This is probably due to some temporary problems on their website: Unable to check. User passed to vBulletin registration system per your settings at 'StopForumSpam: Query Connection Errors'", $username, $email, $ip, VBGHSFS_ALLOWED);
				}
			}
		} elseif ($resultcode == VBGHSFS_FAIL) { // SPAM detected

			$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET denied = denied + 1; ';
			$vbulletin->db->query($sql);

			if ($vbulletin->options['glowhostspamomatic_result'] == 0) { // Allow
				VBGHSFS_Log($field, $data.' - Spammer Found but allowed.', $username, $email, $ip, VBGHSFS_ALLOWED);
			} else { // Reject
				VBGHSFS_Log($field, $data.' - Spammer Found and rejected.', $username, $email, $ip, VBGHSFS_BLOCKED);
				standard_error(fetch_error('glowhostspamomatic_reject', $query));
			}
		}
	}

	/**
	* Function to make AKISMET service call
	* @param string $request Request string
	* @param string $host Hostname
	* @param string $path Path
	* @param string $port Post, default 80
	* @return Result array
	*/
	function VBGHSFS_AkismetRemoteCall($request, $host, $path, $port = 80) {
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: GHSFS/1.0 | Akismet/1.14\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;

		$response = '';
		$fs = @fsockopen($host, $port, $errno, $errstr, 3);
		if ($fs !== false) {
			fwrite($fs, $http_request);
			while (!feof($fs)) $response .= fgets($fs, 1160);
			fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
		return $response;
	}

	/**
	* Function to verify AKISMET key
	* @param string $key AKISMET key to verify
	* @return Bool result
	*/
	function VBGHSFS_AkismetVerifyKey($key) {
		$blog = urlencode('http://www.worldwidecreations.com/php_perl_help');
		$response = VBGHSFS_AkismetRemoteCall("key=$key&blog=$blog", 'rest.akismet.com', '/1.1/verify-key', 80);
		if ($response[1] == 'valid') return true;
		else return false;
	}

	/**
	* Function to check post/thread text aggainst AKISMET service
	* @param string $comment Post/Thread data
	* @param string $host Hostname
	* @return Bool result
	*/
	function VBGHSFS_AkismetCheckComment($comment, $host) {
		$query_string = '';
		foreach ($comment as $key => $data) $query_string .= $key.'='.urlencode(stripslashes($data)).'&';
		$response = VBGHSFS_AkismetRemoteCall($query_string, $host, '/1.1/comment-check', 80);
		if ($response[1] == 'true') { //the post is spam
			global $config;

			//VBGHSFS_Log('ip', $comment['user_ip'], $comment['comment_author'], '', $comment['user_ip'], 1, '<a href="../'.$config['Misc']['modcpdir'].'/moderate.php?do=posts">Found in Akismet Database. Held for moderation: '.substr(strip_tags($comment['comment_content']), 0, 50).'</a>');
			return true;
		} else { //the post is ok
			return false;
		}
	}

	/* EasyCleanup functions */

	/**
	* Takes comma separated list of options, cleans it up and returns it back
	* @param        string 	The list of options (numerical options expected)
	* @return       string 	The cleaned and validated options
	*/
	function clean_options_list($string) {
		$options = explode(',', $string);
		foreach ($options as $num) {
			if (is_numeric($num)) {
				$num = intval($num);
				$newoptions[] = $num;
			}
		}
		if (!empty($newoptions)) {
			return implode(',', $newoptions);
		}
	}

	/**
	* Bans a specified user
	* @param        integer UserID of user to be banned
	* @return 		boolean	Returns true if actual ban code was called
	*/
	function do_ban_user($userid = null) {
		global $vbulletin, $vbphrase;
		if (empty($userid)) { // If we've nobody to ban, then nothing more to see here... Move along...
			return;
		}
		$user = $vbulletin->db->query_first("SELECT userid, usergroupid, displaygroupid, usertitle, customtitle, username FROM " . TABLE_PREFIX . "user WHERE userid=$userid");
		$bantitle = $vbphrase['glowhostspamomatic_bsc_ban_text'];
		$reason = $vbulletin->GPC['banreason'];
		$banner = $vbulletin->userinfo['userid'];

		$alreadybanned = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "userban WHERE userid=$userid");
		if (isset($user) AND empty($alreadybanned) AND $user["usergroupid"] != $vbulletin->options['glowhostspamomatic_bsc_group']) {
   		   	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET usertitle = '". $vbulletin->db->escape_string($bantitle) ."', usergroupid = '" . $vbulletin->options['glowhostspamomatic_bsc_group'] . "', displaygroupid = '0' WHERE userid={$user['userid']}");
   		  	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "userban (userid, usergroupid, displaygroupid, usertitle, customtitle, adminid, bandate, liftdate, reason) VALUES ('{$user["userid"]}', '{$user["usergroupid"]}', '{$user["displaygroupid"]}', '" . $vbulletin->db->escape_string($user["usertitle"]) . "', '" . $vbulletin->db->escape_string($user["customtitle"]) . "', '$banner', '" . TIMENOW . "', '0', '" . $vbulletin->db->escape_string($reason) . "')");
			return true;
		}
	}


	/**
	* EasyCleanup actions
	* hook: inlinemod_action_switch
	*/
	function VBGHSFS_cleanupProcess() {
		global $vbulletin, $plist, $stylevar, $headinclude, $vboptions, $vbphrase, $header, $navbar, $footer;

		$handled_do = true;
		$securitytoken = $vbulletin->userinfo['securitytoken'];

		if ($_REQUEST['do'] == 'spamcleanconfirm') { //show confirmation dialog

			if (empty($plist)) {
				eval(standard_error(fetch_error('no_applicable_posts_selected')));
			}

			if ($vbulletin->options['glowhostspamomatic_bsc_enable'] == 0) {
				standard_error($vbphrase['glowhostspamomatic_bsc_disabled']);
			}

			if (!can_moderate()) {
				print_no_permission();
			}

			// cleanup and verify some option fields
			foreach (array('glowhostspamomatic_bsc_protect_group', 'glowhostspamomatic_bsc_protect_user') as $option) {
				$vbulletin->options[$option] = clean_options_list($vbulletin->options[$option]);
			}

			$posts = $vbulletin->db->query_read("
					SELECT postid, username, userid, ipaddress FROM ". TABLE_PREFIX . "post
					WHERE postid = ".implode($plist, ' OR postid = ')."
					AND threadid NOT IN
						(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
						WHERE type = 'post')
				");

			$users = array();
			$guests = array();
			while ($post = $vbulletin->db->fetch_array($posts)) {
				if ($post['userid'] != 0) {
					$checkuser = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "user WHERE userid=".$post['userid']);
					if (is_member_of($checkuser, explode(",", $vbulletin->options['glowhostspamomatic_bsc_protect_group'])) OR in_array($post['userid'], explode(",", $vbulletin->options['glowhostspamomatic_bsc_protect_user']))) {
						//standard_error($vbphrase['glowhostspamomatic_bsc_protected']);
					} else $users[$post['userid']] = array(
							'username' => $post['username'],
							'userid' => $post['userid'],
							'email' => $checkuser['email'],
							'post_ip' => $post['ipaddress'],
							'reg_ip' => $checkuser['ipaddress']
						);
				} else $guests[$post['postid']] = array(
							'username' => $post['username'],
							'post_ip' => $post['ipaddress']
						);
			}

			if ((count($users) == 0) && (count($guests) == 0)) standard_error($vbphrase['glowhostspamomatic_bsc_protected']);

			$postids = implode(',', $plist);

			$userids = implode(',', array_keys($users));

			$guestids = implode(',', array_keys($guests));

			$message = '<b>User Details:</b><br>';

			foreach ($users as $user_id => $user_info) {
				$message .= 'Username: '.$user_info['username'].'<br>';
				$message .= 'User ID Number: '.$user_info['userid'].'<br>';
				$message .= 'User E-mail: '.$user_info['email'].'<br>';
				$message .= 'User Registration IP Address: '.$user_info['reg_ip'].'<br>';
				$message .= 'User IP Address for Selected Post: '.$user_info['post_ip'].'<br>';
				$message .= '<br>';
			}

			foreach ($guests as $user_id => $user_info) {
				$message .= 'Username: '.$user_info['username'].' (Guest)<br>';
				$message .= 'User IP Address for Selected Post: '.$user_info['post_ip'].'<br>';
				$message .= '<br>';
			}

			if (count($guests) > 0) $message .= '<i>*Guest users can not be banned.</i><br>';

			$message .= '<br>';

			if ($vbulletin->options['glowhostspamomatic_bsc_overrride'] == '1') { //well, that's joke.
				//We will need to publish the form to override admin settings, but predefined defaults

				$message .= '<b>Select actions for the users above:</b><br>';

				$message .= '<table border="0">';

				if (($vbulletin->options['glowhostspamomatic_akismet_enabled'] == '1') && ($vbulletin->options['glowhostspamomatic_akismet_key'] != '')) {
					$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_akismet" value="1"';
					if ($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') $message .= ' checked';
					$message .= '>&nbsp;</td><td style="text-align:left">Submit posts details to Akismet</td></tr>';
				}

				if ($vbulletin->options['glowhostspamomatic_apikey'] != '') {
					$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_sfs" value="1"';
					if ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') $message .= ' checked';
					$message .= '>&nbsp;</td><td style="text-align:left">Submit users details to StopForumSpam</td></tr>';
				}

				$message .= '<tr><td style="text-align:right; width:160px">User Action:&nbsp;</td><td style="text-align:left"><select name="user_action"><option value="0"';
				if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == '0') $message .= ' selected';
				$message .= '>Remove Users</option><option value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == '1') $message .= ' selected';
				$message .= '>Ban Users</option></select></td></tr>'; // ban/remove

				$message .= '<tr><td colspan="2" style="text-align:left">The options below do not matter if Remove Users is selected. Everything posted by this user will be removed when choosing the Remove Users option. If you Ban a user, you can choose what items you wish to be removed below.</td></tr>';

				$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_threads" value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == '1') $message .= ' checked';
				$message .= '>&nbsp;</td><td style="text-align:left">Remove Threads</td></tr>'; // threads

				$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_posts" value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_delete_posts'] == '1') $message .= ' checked';
				$message .= '>&nbsp;</td><td style="text-align:left">Remove Posts</td></tr>'; // posts

				$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_pms" value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_delete_pms'] == '1') $message .= ' checked';
				$message .= '>&nbsp;</td><td style="text-align:left">Remove PMs</td></tr>'; // pms

				$message .= '<tr><td style="text-align:right; width:160px"><input type="checkbox" name="do_events" value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_delete_events'] == '1') $message .= ' checked';
				$message .= '>&nbsp;</td><td style="text-align:left">Remove Events</td></tr>'; // events

				$message .= '<tr><td style="text-align:right; width:160px">Posts removal method:&nbsp;</td><td style="text-align:left"><select name="removal_method"><option value="1"';
				if ($vbulletin->options['glowhostspamomatic_bsc_hard_delete'] == '1') $message .= ' selected';
				$message .= '>Delete permanently</option><option value="0"';
				if ($vbulletin->options['glowhostspamomatic_bsc_hard_delete'] == '0') $message .= ' selected';
				$message .= '>Soft Delete</option></select></td></tr>';

				$message .= '</table>';

			} else { //that' easy, just show the info and continue button

				if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == '1') {

					$message .= 'The users above will be banned:<br>';

					foreach ($users as $user_id => $user_name) $message .= $user_name.' (ID: '.$user_id.')<br>';
					foreach ($guests as $user_id => $user_name) $message .= $user_name.' (Guest)<br>';
					if (count($guests) > 0) $message .= '<i>*Guest users can not be banned.</i>';
					$message .= '<br>';

					$message .= 'Threads will ';
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == '0') $message .= 'not ';
					$message .= 'be removed.<br>';

					$message .= 'Posts will ';
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_posts'] == '0') $message .= 'not ';
					$message .= 'be removed.<br>';

					$message .= 'PMs will ';
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_pms'] == '0') $message .= 'not ';
					$message .= 'be removed.<br>';

					$message .= 'Calendar Events will ';
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_events'] == '0') $message .= 'not ';
					$message .= 'be removed.<br>';

				} else {

					$message .= 'The users above will be removed:<br>';

					foreach ($users as $user_id => $user_name) $message .= $user_name.' (ID: '.$user_id.')<br>';
					foreach ($guests as $user_id => $user_name) $message .= $user_name.' (Guest)<br>';
					$message .= '<br>';

					$message .= 'Threads, posts, PMs and calendar events of these users will also be removed.<br>';

				}

				if (($vbulletin->options['glowhostspamomatic_akismet_enabled'] == '1') && ($vbulletin->options['glowhostspamomatic_akismet_key'] != '') && ($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1')) {
					$message .= 'Post details will be submitted to Akismet.<br>';
				}

				if (($vbulletin->options['glowhostspamomatic_apikey'] != '') && ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1')) {
					$message .= 'User details will be submitted to StopForumSpam.<br>';
				}
			}

			/*
			inlinemod.php
			$navbits = construct_navbits($navbits);
			$navbar = render_navbar_template($navbits);
			*/

			$templater = vB_Template::create('glowhostspamomatic_bsc');
			$templater->register('message', $message);
			$templater->register('postids', $postids);
			$templater->register('userids', $userids);
			$templater->register('guestids', $guestids);
			$templater->register('securitytoken', $securitytoken);
			$page_html = $templater->render();

			$navbits = construct_navbits($navbits);
			$navbar = render_navbar_template($navbits);

			$templater = vB_Template::create('THREADADMIN');
			$templater->register_page_templates();
			$templater->register('HTML', $page_html);
			$templater->register('navbar', $navbar);
			$templater->register('onload', $onload);
			$templater->register('pagetitle', $vbulletin->options['bbtitle'].' - '.$vbphrase['glowhostspamomatic_bsc_title']);
			$templater->register('parentpostassoc', $parentpostassoc);
			$templater->register('threadinfo', $threadinfo);
			print_output($templater->render());

		}

		if ($_POST['do'] == 'dospamclean') { //perform required actions

			if ($vbulletin->options['glowhostspamomatic_bsc_enable'] == 0) {
				standard_error($vbphrase['glowhostspamomatic_bsc_disabled']);
			}

			if (!can_moderate()) {
				print_no_permission();
			}

			if ($vbulletin->options['glowhostspamomatic_bsc_overrride'] == '1') { //this means the settings are redefined.... overwright them
				$vbulletin->options['glowhostspamomatic_bsc_ban_user'] = $_REQUEST['user_action'];

				if (!isset($_REQUEST['do_threads'])) $_REQUEST['do_threads'] = 0;
				$vbulletin->options['glowhostspamomatic_bsc_delete_threads'] = $_REQUEST['do_threads'];

				if (!isset($_REQUEST['do_posts'])) $_REQUEST['do_posts'] = 0;
				$vbulletin->options['glowhostspamomatic_bsc_delete_posts'] = $_REQUEST['do_posts'];

				if (!isset($_REQUEST['do_pms'])) $_REQUEST['do_pms'] = 0;
				$vbulletin->options['glowhostspamomatic_bsc_delete_pms'] = $_REQUEST['do_pms'];

				if (!isset($_REQUEST['do_events'])) $_REQUEST['do_events'] = 0;
				$vbulletin->options['glowhostspamomatic_bsc_delete_events'] = $_REQUEST['do_events'];

				$vbulletin->options['glowhostspamomatic_bsc_hard_delete'] = $_REQUEST['removal_method'];

				if (($vbulletin->options['glowhostspamomatic_akismet_enabled'] == '1') && ($vbulletin->options['glowhostspamomatic_akismet_key'] != '')) {
					if (!isset($_REQUEST['do_akismet'])) $_REQUEST['do_akismet'] = 0;
					$vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] = $_REQUEST['do_akismet'];
				} else $vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] = 0;

				if ($vbulletin->options['glowhostspamomatic_apikey'] != '') {
					if (!isset($_REQUEST['do_sfs'])) $_REQUEST['do_sfs'] = 0;
					$vbulletin->options['glowhostspamomatic_do_submit_moderation'] = $_REQUEST['do_sfs'];
				} else $vbulletin->options['glowhostspamomatic_do_submit_moderation'] = 0;
			}

			$ips = array();

			if ($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') { //submit to Akismet DB

				$postids = explode(',', $_REQUEST['postids']);

				foreach ($postids as $post_id) {

					$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET s_akismet = s_akismet + 1; ';
					$vbulletin->db->query($sql);

					$sql = 'SELECT
									`'.TABLE_PREFIX.'post`.ipaddress,
									`'.TABLE_PREFIX.'post`.username,
									`'.TABLE_PREFIX.'post`.pagetext,
									`'.TABLE_PREFIX.'user`.email
								FROM `'.TABLE_PREFIX.'post`

								LEFT JOIN `'.TABLE_PREFIX.'user` ON
													`'.TABLE_PREFIX.'post`.userid=`'.TABLE_PREFIX.'user`.userid

								WHERE `'.TABLE_PREFIX.'post`.postid='.$post_id;
					$result = $vbulletin->db->query($sql);

					$line = $vbulletin->db->fetch_array($result);
  					if (!empty($line)) {

  						$key = $vbulletin->options['glowhostspamomatic_akismet_key'];
						$vbghsfs_host = $key.'.rest.akismet.com';

						$comment['user_ip'] = $line['ipaddress'];
						$comment['user_agent'] = '';
						$comment['referrer'] = '';
						$comment['blog'] = '';
						$comment['comment_author'] = $line['username'];
						$comment['comment_content'] = $line['pagetext'];

						$ips[] = $line['ipaddress'];

						$query_string = '';
						foreach ($comment as $key => $data) $query_string .= $key.'='.urlencode(stripslashes($data)).'&';
						$response = VBGHSFS_AkismetRemoteCall($query_string, $host, '/1.1/submit-spam', 80);

						VBGHSFS_Log('ip', $line['ipaddress'], $line['username'], $line['email'], $line['ipaddress'], 0, 'User data submitted to Akismet database');

  					}

				}
			}

			$deletedthreads = 0;
			$deletedposts = 0;
			$deletedpms = 0;
			$deletedevents = 0;

			//let's also submit gues details to SFS
			if ($_REQUEST['guestids'] != '') {

				$guestids = explode(',', $_REQUEST['guestids']);

				foreach ($guestids as $post_id) {

					if ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') { //submit to SFS DB

						$sql = 'SELECT username, ipaddress FROM `'.TABLE_PREFIX.'post` WHERE postid='.$post_id;
						$result = $vbulletin->db->query($sql);

						$line = $vbulletin->db->fetch_array($result);
  						if (!empty($line)) {

  							$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET s_sfs = s_sfs + 1; ';
							$vbulletin->db->query($sql);

  							$url = 'http://www.stopforumspam.com/add.php?username='.$line['username'].'&ip_addr='.$line['ipaddress'].'&api_key='.$vbulletin->options["glowhostspamomatic_apikey"];

							if (function_exists('curl_init')) {

								$cURL = curl_init();
								curl_setopt($cURL, CURLOPT_URL, $url);
								curl_setopt($cURL, CURLOPT_TIMEOUT, '15');
								 curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);

							   curl_exec($cURL);

								if (curl_errno($cURL)) { // cURL failed
    								@file_get_contents($url);
								}

								curl_close($cURL);

							} else @file_get_contents($url);

							//log write this user as spambot
							VBGHSFS_Log('ip', $line['ipaddress'], $line['username'], 'none', $line['ipaddress'], 1, 'User data submitted to StopForumSpam.com database');
  						}
					}

					// Delete threads started by user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == 1) {
						$deletedata = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => $vbulletin->GPC['deletereason'], 'keepattachments' => '0');
						// This query excludes threads that are already deleted
						$threads = $vbulletin->db->query_read("
							SELECT threadid, forumid FROM ". TABLE_PREFIX . "thread
							WHERE firstpostid = $post_id
							AND threadid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'thread')
						");

						while ($thread = $vbulletin->db->fetch_array($threads)) {
							delete_thread($thread['threadid'], true, $vbulletin->options['glowhostspamomatic_bsc_hard_delete'], $deletedata);
							build_forum_counters($thread['forumid']);
							$deletedthreads++;
						}
					}

					// Delete posts by user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_posts'] == 1) {
						$deletedata = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => $vbulletin->GPC['deletereason'], 'keepattachments' => '0');
						// This query excludes posts belonging to deleted threads, deleted posts, and the first post of any thread
						$posts = $vbulletin->db->query_read("
							SELECT postid, threadid FROM ". TABLE_PREFIX . "post
							WHERE postid = $post_id
							AND NOT parentid = 0
							AND postid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'post')
							AND threadid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'thread')
						");

						while ($post = $vbulletin->db->fetch_array($posts)) {
							$threadinfo = fetch_threadinfo($post['threadid']);
							delete_post($post['postid'], true, $post['threadid'], $vbulletin->options['glowhostspamomatic_bsc_hard_delete'], $deletedata);
							build_thread_counters($post['threadid']);
							build_forum_counters($threadinfo['forumid']);
							$deletedposts++;
						}
					}

				}

			}

			if ($_REQUEST['userids'] != '') {

				$userids = explode(',', $_REQUEST['userids']);

				foreach ($userids as $user_id) {
					if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban the user
						//we go for defaults on actions on posts, threads etc


					} else { //remove the user
						//remove user, threads, pms etc etc etc
						$vbulletin->options['glowhostspamomatic_bsc_delete_threads'] = 1;
						$vbulletin->options['glowhostspamomatic_bsc_delete_posts'] = 1;
						$vbulletin->options['glowhostspamomatic_bsc_delete_pms'] = 1;
						$vbulletin->options['glowhostspamomatic_bsc_delete_events'] = 1;
					}

					if ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') { //submit to SFS DB

						$sql = 'SELECT username, email, ipaddress FROM `'.TABLE_PREFIX.'user` WHERE userid='.$user_id;
						$result = $vbulletin->db->query($sql);

						$line = $vbulletin->db->fetch_array($result);
  						if (!empty($line)) {

  							$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET s_sfs = s_sfs + 1; ';
							$vbulletin->db->query($sql);

  							$url = 'http://www.stopforumspam.com/add.php?username='.$line['username'].'&email='.$line['email'].'&ip_addr='.$line['ipaddress'].'&api_key='.$vbulletin->options["glowhostspamomatic_apikey"];

							if (function_exists('curl_init')) {

								$cURL = curl_init();
								curl_setopt($cURL, CURLOPT_URL, $url);
								curl_setopt($cURL, CURLOPT_TIMEOUT, '15');
								 curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);

							   curl_exec($cURL);

								if (curl_errno($cURL)) { // cURL failed
    								@file_get_contents($url);
								}

								curl_close($cURL);

							} else @file_get_contents($url);

							//log write this user as spambot
							VBGHSFS_Log('ip', $line['ipaddress'], $line['username'], $line['email'], $line['ipaddress'], 1, 'User data submitted to StopForumSpam.com database');
  						}
					}


					// Delete threads started by user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == 1) {
						$deletedata = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => $vbulletin->GPC['deletereason'], 'keepattachments' => '0');
						// This query excludes threads that are already deleted
						$threads = $vbulletin->db->query_read("
							SELECT threadid, forumid FROM ". TABLE_PREFIX . "thread
							WHERE postuserid = $user_id
							AND threadid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'thread')
						");

						while ($thread = $vbulletin->db->fetch_array($threads)) {
							delete_thread($thread['threadid'], true, $vbulletin->options['glowhostspamomatic_bsc_hard_delete'], $deletedata);
							build_forum_counters($thread['forumid']);
							$deletedthreads++;
						}
					}

					// Delete posts by user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_posts'] == 1) {
						$deletedata = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => $vbulletin->GPC['deletereason'], 'keepattachments' => '0');
						// This query excludes posts belonging to deleted threads, deleted posts, and the first post of any thread
						$posts = $vbulletin->db->query_read("
							SELECT postid, threadid FROM ". TABLE_PREFIX . "post
							WHERE userid = $user_id
							AND NOT parentid = 0
							AND postid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'post')
							AND threadid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'thread')
						");

						while ($post = $vbulletin->db->fetch_array($posts)) {
							$threadinfo = fetch_threadinfo($post['threadid']);
							delete_post($post['postid'], true, $post['threadid'], $vbulletin->options['glowhostspamomatic_bsc_hard_delete'], $deletedata);
							build_thread_counters($post['threadid']);
							build_forum_counters($threadinfo['forumid']);
							$deletedposts++;
						}
					}

					// Delete PMs from user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_pms'] == 1) {

						// array to store userids for updating totals
						$banuserids = array();

						// now find all PMs sent by this user
						$messages = $vbulletin->db->query_read("
							SELECT userid FROM " . TABLE_PREFIX . "pm, " . TABLE_PREFIX . "pmtext
							WHERE " . TABLE_PREFIX . "pm.pmtextid = " . TABLE_PREFIX . "pmtext.pmtextid
							AND " . TABLE_PREFIX . "pmtext.fromuserid = $user_id
						");

						while ($message = $vbulletin->db->fetch_array($messages)) {
								// stick this userid onto our array
								$banuserids[] = $message['userid'];
						}
						$vbulletin->db->free_result($messages);

						// kill off relevant records
						$vbulletin->db->query_write("
								DELETE FROM " . TABLE_PREFIX . "pm WHERE pmtextid IN (SELECT pmtextid FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid = $user_id)
						");
						$vbulletin->db->query_write("
								DELETE FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid = $user_id
						");

						if (!empty($banuserids)) {
							foreach ($banuserids as $candidate) {

								$pmcount = $vbulletin->db->query_first("
								        SELECT
								                COUNT(pmid) AS pmtotal,
								                SUM(IF(messageread = 0 AND folderid >= 0, 1, 0)) AS pmunread
									    FROM " . TABLE_PREFIX . "pm AS pm
								        WHERE pm.userid = $candidate
								");

								$pmcount['pmtotal'] = intval($pmcount['pmtotal']);
								$pmcount['pmunread'] = intval($pmcount['pmunread']);

								$vbulletin->db->query_write("
		        					UPDATE " . TABLE_PREFIX . "user set pmtotal = " . $pmcount['pmtotal'] . ", pmunread = " . $pmcount['pmunread'] . " WHERE userid = $candidate
								");

								$vbulletin->db->query_write("
								    UPDATE " . TABLE_PREFIX . "user
								    SET pmpopup = IF(pmpopup=2 AND pmunread = 0, 1, pmpopup)
								    WHERE userid = $candidate
								");
								$deletedpms++;
							}
						}
					}

					// Delete calendar events posted by user
					if ($vbulletin->options['glowhostspamomatic_bsc_delete_events'] == 1) {
						$events = $vbulletin->db->query_read("SELECT * FROM ". TABLE_PREFIX . "event WHERE userid = $user_id");
						while ($event = $vbulletin->db->fetch_array($events)) {
							$eventdata =& datamanager_init('Event', $vbulletin, ERRTYPE_STANDARD);
							$eventdata->set_existing($event);
							$eventdata->delete();
							$deletedevents++;
						}
					}


					if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban

						if (do_ban_user($user_id)) {
							$userstatus = $vbphrase['glowhostspamomatic_bsc_ban'];

							$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET banned = banned + 1; ';
							$vbulletin->db->query_write($sql);

						}

					} else { //remove

						// check user is not set in the $undeletable users string
						$nodelete = explode(',', $vbulletin->config['SpecialUsers']['undeletableusers']);
						if (in_array($user_id, $nodelete)) {
        					print_stop_message('user_is_protected_from_alteration_by_undeletableusers_var');
						} else {
						    $info = fetch_userinfo($user_id);
						    if ($info['userid'] == $user_id) {
						        $userdm =& datamanager_init('User', $vbulletin, ERRTYPE_CP);
						        $userdm->set_existing($info);
						        $userdm->delete();
						        unset($userdm);
						        $userstatus = $vbphrase['glowhostspamomatic_bsc_delete'];

						        $sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET banned = banned + 1; ';
								$vbulletin->db->query_write($sql);
						    }
						}
					}
				}
			}

			//ok, let's try to create final message.....
			/*
			...

			Users who have the same IP address:
			List of users with same registration, thread and post IPs
			*/
			$message = '<br><b>Spam-O-Matic has performed the following actions:</b><br>';
			$message .= 'Selected users were ';
			if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //banned
				$message .= 'banned';
			} else { //removed
				$message .= 'removed';
			}
			$message .= '<br>';

			if ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') { //submitted to SFS
				$message .= 'User details were submitted to StopForumSpam<br>';
			}

			if ($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') { //submitted to Akismet
				$message .= 'User details were submitted to Akismet<br>';
			}

			if ($deletedthreads > 0) $message .= $deletedthreads.' thread'.(($deletedthreads > 1)?'s were':' was').' '.(($vbulletin->options['glowhostspamomatic_bsc_hard_delete'] == 1)?'removed permanently':'soft deleted').'<br>';
			if ($deletedposts > 0) $message .= $deletedposts.' post'.(($deletedposts > 1)?'s were':' was').' '.(($vbulletin->options['glowhostspamomatic_bsc_hard_delete'] == 1)?'removed permanently':'soft deleted').'<br>';
			if ($deletedpms > 0) $message .= $deletedpms.' PM'.(($deletedpms > 1)?'s were':' was').' removed<br>';
			if ($deletedevents > 0) $message .= $deletedevents.' event'.(($deletedevents > 1)?'s were':' was').' removed<br>';

			if (count($ips) > 0) {
				$ips = array_unique($ips);
				//now, let's get all users with the same ip.....

				//also let's make correct usergroup....
				$user_groups = array();
				$posts = $vbulletin->db->query_read("
							SELECT usergroupid, title
							FROM ". TABLE_PREFIX . "usergroup
						");

				while ($post = $vbulletin->db->fetch_array($posts)) {
					$user_groups[$post['usergroupid']] = $post['title'];
				}

				$ip_users = array();
				$ip_posts = array();

				$posts = $vbulletin->db->query_read("
							SELECT ". TABLE_PREFIX . "post.username, ". TABLE_PREFIX . "post.userid, ". TABLE_PREFIX . "user.usergroupid
							FROM ". TABLE_PREFIX . "post, ". TABLE_PREFIX . "user
							WHERE (". TABLE_PREFIX . "post.ipaddress = '".implode("' OR ". TABLE_PREFIX . "post.ipaddress='", $ips)."')

							AND ". TABLE_PREFIX . "post.userid = ". TABLE_PREFIX . "user.userid

							AND NOT ". TABLE_PREFIX . "post.parentid = 0
							AND ". TABLE_PREFIX . "post.postid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'post')
							AND ". TABLE_PREFIX . "post.threadid NOT IN
								(SELECT primaryid FROM " . TABLE_PREFIX . "deletionlog
								WHERE type = 'thread')
						");

				while ($post = $vbulletin->db->fetch_array($posts)) {
					if ($post['userid'] > 0) {
						$ip_posts[$post['userid']] = array(
							'username' => $post['username'],
							'usergroup' => $user_groups[$post['usergroupid']]
						);
					}
				}

				$posts = $vbulletin->db->query_read("
					SELECT username, userid, usergroupid FROM ". TABLE_PREFIX . "user
					WHERE (ipaddress = '".implode("' OR ipaddress='", $ips)."') AND
					 usergroupid != '" . $vbulletin->options['glowhostspamomatic_bsc_group'] . "'
				");

				while ($post = $vbulletin->db->fetch_array($posts)) {
					$ip_users[$post['userid']] = array(
							'username' => $post['username'],
							'usergroup' => $user_groups[$post['usergroupid']]
						);
				}

				//$ip_users = array_unique($ip_users);

				if ((count($ip_users) > 0) || (count($ip_posts) > 0)) {
					global $config;
					$message .= 'Users who have used the same IP address:<br>';
				}

				if (count($ip_users) > 0) {
					$message .= 'IPs used to register:<br>';
					foreach ($ip_users as $user_id => $user_name) {
						$message .= '<a href="user.php?do=edit&u='.$user_id.'" target="_blank">'.$user_name['username'].'</a> - '.$user_name['usergroup'].'<br>';
					}
					$message .= '<br>';
				}

				if (count($ip_posts) > 0) {
					$message .= 'IPs used to post:<br>';
					foreach ($ip_posts as $user_id => $user_name) {
						$message .= '<a href="user.php?do=edit&u='.$user_id.'" target="_blank">'.$user_name['username'].'</a> - '.$user_name['usergroup'].'<br>';
					}
				}

			}

			// empty cookie
			setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');

			$templater = vB_Template::create('glowhostspamomatic_bsc');
			$templater->register('message', $message);
			$templater->register('securitytoken', $securitytoken);
			$page_html = $templater->render();

			$navbits = construct_navbits($navbits);
			$navbar = render_navbar_template($navbits);

			$templater = vB_Template::create('THREADADMIN');
			$templater->register_page_templates();
			$templater->register('HTML', $page_html);
			$templater->register('navbar', $navbar);
			$templater->register('onload', $onload);
			$templater->register('pagetitle', $vbulletin->options['bbtitle'].' - '.$vbphrase['glowhostspamomatic_bsc_title']);
			$templater->register('parentpostassoc', $parentpostassoc);
			$templater->register('threadinfo', $threadinfo);
			print_output($templater->render());

		}
	}
?>
