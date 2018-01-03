<?php

	// Set error level
	error_reporting(E_ALL & ~E_NOTICE);

	// Pre-cache
	$phrasegroups = array('logging');
	$specialtemplates = array();

	// Required includes
	require_once('./global.php');
	require_once(DIR.'/includes/functions_log_error.php');
	require_once(DIR.'/includes/functions_ghsom.php');
	require_once(DIR.'/includes/functions_databuild.php');

	log_admin_action();

	if (isset($_REQUEST['u'])) settype($_REQUEST['u'], 'int');

	//Finally, let's go with the code
	if ($_REQUEST['do'] == 'som_ban_confirm') {
		print_cp_header("Spam-O-Matic - Confirm User Removal");
	} else if ($_REQUEST['do'] == 'som_ban') {
		print_cp_header("Spam-O-Matic - User Removal");
	} else print_cp_header($vbphrase['glowhostspamomatic_log']);

	// Define default action
	if (empty($_REQUEST['do'])) $_REQUEST['do'] = 'choose';

	if ($_REQUEST['do'] == 'som_ban_confirm') {
		print_form_header('', '');
		print_table_header('&nbsp;');

		print_description_row('<b>Spam-O-Matic is going to:</b>');
		if ($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') {
			print_description_row('- submit user details to Akismet database');
		}
		if ($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') {
			print_description_row('- submit user details to StopForumSpam.com database');
		}
		if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban the user
			print_description_row('- ban the user');
		} else { //remove the user
			print_description_row('- remove the user');

			$vbulletin->options['glowhostspamomatic_bsc_delete_threads'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_posts'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_pms'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_events'] = 1;
		}

		if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == 1) {
			print_description_row('- remove the user threads');
		}

		if ($vbulletin->options['glowhostspamomatic_bsc_delete_posts'] == 1) {
			print_description_row('- remove the user posts');
		}

		if ($vbulletin->options['glowhostspamomatic_bsc_delete_pms'] == 1) {
			print_description_row('- remove the user PMs');
		}

		if ($vbulletin->options['glowhostspamomatic_bsc_delete_events'] == 1) {
			print_description_row('- remove the user calendar events');
		}

		print_description_row('&nbsp;');

		//check user registration data. If more than 15 days - alert message about submit.
		$sql = 'SELECT
						`'.TABLE_PREFIX.'user`.joindate
					FROM `'.TABLE_PREFIX.'user`
					WHERE `'.TABLE_PREFIX.'user`.userid='.$_REQUEST['u'];
		$result = $vbulletin->db->query($sql);

		$line = $vbulletin->db->fetch_array($result);
  		if (!empty($line)) {
  			if (time() - $line['joindate'] > 60*60*24*15) {
				print_description_row('<b>This user is too old to submit to the StopForumSpam and Akismet databases. The details will not be submitted, but the user\'s details will be removed from vBulletin\'s database.</b>');
				print_description_row('&nbsp;');
  			}
		}

		print_description_row('Click the <a href="glowhostspamomatic.php?do=som_ban&amp;u='.$_REQUEST['u'].'">link</a> to confirm the actions above.');
		print_table_footer();
	}

	if ($_REQUEST['do'] == 'som_ban') {
		//do what we normally do
		//show message we have done what we wanted :)
		$ips = array();

		$user_too_old = false;
		$sql = 'SELECT
						`'.TABLE_PREFIX.'user`.joindate
					FROM `'.TABLE_PREFIX.'user`
					WHERE `'.TABLE_PREFIX.'user`.userid='.$_REQUEST['u'];
		$result = $vbulletin->db->query($sql);

		$line = $vbulletin->db->fetch_array($result);
  		if (!empty($line)) {
  			if (time() - $line['joindate'] > 60*60*24*15) {
				$user_too_old = true;
  			}
		}

		if (($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') && ($user_too_old == false)) { //submit to Akismet DB

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

						WHERE `'.TABLE_PREFIX.'post`.userid='.$_REQUEST['u'];
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
				$response = SOM_AkismetRemoteCall($query_string, $host, '/1.1/submit-spam', 80);

				SOM_Log('ip', $line['ipaddress'], $line['username'], $line['email'], $line['ipaddress'], 0, 'User data submitted to Akismet database');

  			}

		}

		$deletedthreads = 0;
		$deletedposts = 0;
		$deletedpms = 0;
		$deletedevents = 0;

		if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban the user
			//we go for defaults on actions on posts, threads etc


		} else { //remove the user
			//remove user, threads, pms etc etc etc
			$vbulletin->options['glowhostspamomatic_bsc_delete_threads'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_posts'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_pms'] = 1;
			$vbulletin->options['glowhostspamomatic_bsc_delete_events'] = 1;
		}

		if (($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') && ($user_too_old == false)) { //submit to SFS DB

			$sql = 'SELECT username, email, ipaddress FROM `'.TABLE_PREFIX.'user` WHERE userid='.$_REQUEST['u'];
			$result = $vbulletin->db->query($sql);

			$line = $vbulletin->db->fetch_array($result);
  			if (!empty($line)) {

  				$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET s_sfs = s_sfs + 1; ';
				$vbulletin->db->query($sql);

  				$url = 'http://www.stopforumspam.com/add.php?username='.urlencode($line['username']).'&email='.urlencode($line['email']).'&ip_addr='.urlencode($line['ipaddress']).'&api_key='.urlencode($vbulletin->options["glowhostspamomatic_apikey"]);

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
				SOM_Log('ip', $line['ipaddress'], $line['username'], $line['email'], $line['ipaddress'], 1, 'User data submitted to StopForumSpam.com database');
  			}
		}

		// Delete threads started by user
		if ($vbulletin->options['glowhostspamomatic_bsc_delete_threads'] == 1) {
			$deletedata = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => $vbulletin->GPC['deletereason'], 'keepattachments' => '0');
			// This query excludes threads that are already deleted
			$threads = $vbulletin->db->query_read("
				SELECT threadid, forumid FROM ". TABLE_PREFIX . "thread
				WHERE postuserid = ".$_REQUEST['u']."
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
				WHERE userid = ".$_REQUEST['u']."
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
				AND " . TABLE_PREFIX . "pmtext.fromuserid = ".$_REQUEST['u']."
			");

			while ($message = $vbulletin->db->fetch_array($messages)) {
					// stick this userid onto our array
					$banuserids[] = $message['userid'];
			}
			$vbulletin->db->free_result($messages);

			// kill off relevant records
			$vbulletin->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "pm WHERE pmtextid IN (SELECT pmtextid FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid = ".$_REQUEST['u'].")
			");
			$vbulletin->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "pmtext WHERE fromuserid = ".$_REQUEST['u']."
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
			$events = $vbulletin->db->query_read("SELECT * FROM ". TABLE_PREFIX . "event WHERE userid = ".$_REQUEST['u']."");
			while ($event = $vbulletin->db->fetch_array($events)) {
				$eventdata =& datamanager_init('Event', $vbulletin, ERRTYPE_STANDARD);
				$eventdata->set_existing($event);
				$eventdata->delete();
				$deletedevents++;
			}
		}

		if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban

			if (do_ban_user($_REQUEST['u'])) {
				$userstatus = $vbphrase['glowhostspamomatic_bsc_ban'];

				$sql = 'UPDATE '.TABLE_PREFIX.'glowhostspamomatic_stats SET banned = banned + 1; ';
				$vbulletin->db->query_write($sql);

			}

		} else { //remove

			// check user is not set in the $undeletable users string
			$nodelete = explode(',', $vbulletin->config['SpecialUsers']['undeletableusers']);
			if (in_array($_REQUEST['u'], $nodelete)) {
        		print_stop_message('user_is_protected_from_alteration_by_undeletableusers_var');
			} else {
				$info = fetch_userinfo($_REQUEST['u']);
				if ($info['userid'] == $_REQUEST['u']) {
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


		//ok, let's try to create final message.....
		/*
		...

		Users who have the same IP address:
		List of users with same registration, thread and post IPs
		*/
		$message = '<br><b>Spam-O-Matic has performed the following actions:</b><br>';
		$message .= 'Selected user was ';
		if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //banned
			$message .= 'banned';
		} else { //removed
			$message .= 'removed';
		}
		$message .= '<br>';

		if (($vbulletin->options['glowhostspamomatic_do_submit_moderation'] == '1') && ($user_too_old == false)) { //submitted to SFS
			$message .= 'User details were submitted to StopForumSpam<br>';
		}

		if (($vbulletin->options['glowhostspamomatic_akismet_do_submit_moderation'] == '1') && ($user_too_old == false)) { //submitted to Akismet
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

		//ok, we need to render something I guess..... with a link to user search or user profile - depends on if we removed the user or banned him
		if ($vbulletin->options['glowhostspamomatic_bsc_ban_user'] == 1) { //ban the user
			$message .= '<a href="user.php?do=edit&u='.$_REQUEST['u'].'">Back to User Profile</a>';
		} else { //remove the user
			$message .= '<a href="user.php?do=modify">Back to User Search</a>';
		}

		$message = '<table border="0" width="100%">
			<tr>
				<td>'.$message.'</td>
				<td style="text-align:right">
					<a href="http://glowhost.com/order/order.php?cid=113" target="_blank" title="GlowHost Web Hosting With vBulletin Support"><img border="0" src="http://glowhost.com/images/avatars/vb-gh.jpg"></a><br>
					<span style="color:#00CC66"><b>Save $100</b></span> on any web hosting package at GlowHost<br>
					Use Coupon Code: <b>SpamOMatic</b> when placing your order<br>
					<a href="http://glowhost.com/order/order.php?cid=113" target="_blank" title="GlowHost Web Hosting With vBulletin Support">Visit GlowHost Web Hosting</a>
				</td>
			</tr>
		</table>';

		print_form_header('', '');
		print_table_header('&nbsp;');
		print_description_row($message);
		print_table_footer();
	}

	// CHOOSE
	if ($_REQUEST['do'] == 'choose') {
		print_form_header('glowhostspamomatic', 'view');
		print_table_header($vbphrase['glowhostspamomatic_log_viewer']);
		print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
		print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username'], 'email'  => $vbphrase['email'], 'date' => $vbphrase['date']));
		print_submit_row($vbphrase['view'], 0);

		//if (can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '')) {
			print_form_header('glowhostspamomatic', 'prunelog');
			print_table_header($vbphrase['prune_glowhostspamomatic_logs']);
			print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
			print_submit_row($vbphrase['prune_log_entries'], 0);
		//}
	}

	// PRUNE
	//if ($_REQUEST['do'] == 'prunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>')) {
	if ($_REQUEST['do'] == 'prunelog') {

		$vbulletin->input->clean_array_gpc('r', array(
			'daysprune' => TYPE_UINT,
			'modaction' => TYPE_STR
		));

		$datecut = $vbulletin->GPC['daysprune'];
		$query = 'SELECT COUNT(*) AS total FROM '.TABLE_PREFIX.'glowhostspamomatic_log WHERE `date` < DATE_SUB(NOW(), INTERVAL '.$datecut.' DAY)';

		$logs = $db->query_first($query);
		if ($logs['total']) {
			print_form_header('glowhostspamomatic', 'doprunelog');
			construct_hidden_code('datecut', $datecut);

			print_table_header($vbphrase['prune_glowhostspamomatic_logs']);
			print_description_row(construct_phrase('Are you sure you wish to delete '.vb_number_format($logs['total']).' records from the logs'));
			print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
		} else {
			print_stop_message('no_logs_matched_your_query');
		}
	}

	// EXECUTE PRUNE
	//if ($_POST['do'] == 'doprunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>')) {
	if ($_POST['do'] == 'doprunelog') {

		$vbulletin->input->clean_array_gpc('p', array(
			'datecut'   => TYPE_UINT
		));

		$sqlconds = ' ';

		$db->query_write('DELETE FROM '.TABLE_PREFIX.'glowhostspamomatic_log  WHERE `date` < DATE_SUB(NOW(), INTERVAL '.$vbulletin->GPC['datecut'].' DAY); ');

		define('CP_REDIRECT', 'glowhostspamomatic.php?do=choose');
		print_stop_message('pruned_glowhostspamomatic_log_successfully');
	}

	// VIEW
	if ($_REQUEST['do'] == 'view') {

		$vbulletin->input->clean_array_gpc('r', array(
			'perpage'    => TYPE_UINT,
			'pagenumber' => TYPE_UINT,
			'orderby'    => TYPE_STR,
		));

		// Define default number of entries per page
		if ($vbulletin->GPC['perpage'] < 1) $vbulletin->GPC['perpage'] = 15;

		$counter = $db->query_first('SELECT COUNT(*) AS total FROM '.TABLE_PREFIX.'glowhostspamomatic_log');

		$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

		if ($vbulletin->GPC['pagenumber'] < 1) $vbulletin->GPC['pagenumber'] = 1;

		$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

		switch($vbulletin->GPC['orderby']) {
			case 'ip': $order = '`ip` ASC, `date` DESC'; break;

			case 'email': $order = '`email` ASC, `date` DESC'; break;

			case 'username': $order = '`username` ASC, `date` DESC'; break;

			case 'date':
			default:
				$order = 'date DESC';
				break;
		}

		$logs = $db->query_read('SELECT * FROM '.TABLE_PREFIX.'glowhostspamomatic_log
									ORDER BY '.$order.'
									LIMIT '.$startat.', '.$vbulletin->GPC['perpage']);

		if ($db->num_rows($logs)) {
			if ($vbulletin->GPC['pagenumber'] != 1) {
				$prv = $vbulletin->GPC['pagenumber'] - 1;
				$firstpage = '<input type="button" class="button" value="&laquo; '.$vbphrase['first_page'].'" tabindex="1" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page=1\'">';
				$prevpage = '<input type="button" class="button" value="&lt; '.$vbphrase['prev_page'].'" tabindex="2" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$prv.'\'">';
			}

			if ($vbulletin->GPC['pagenumber'] != $totalpages) {
				$nxt = $vbulletin->GPC['pagenumber'] + 1;
				$nextpage = '<input type="button" class="button" value="'.$vbphrase['next_page'].' &gt;" tabindex="1" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$nxt.'\'">';
				$lastpage = '<input type="button" class="button" value="'.$vbphrase['last_page'].' &raquo;" tabindex="2" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$totalpages.'\'">';
			}

			print_form_header('glowhostspamomatic', 'remove');
			print_description_row(construct_link_code($vbphrase['restart'], 'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl']), 0, 6, 'thead', vB_Template_Runtime::fetchStyleVar('right'));
			print_table_header(construct_phrase($vbphrase['glowhostspamomatic_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);

			$headings = array();

			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=date&page='.$vbulletin->GPC['pagenumber'].'">'.$vbphrase['date'].'</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=ip&page='.$vbulletin->GPC['pagenumber'].'">IP Address</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=username&page='.$vbulletin->GPC['pagenumber'].'">'.str_replace(' ', '&nbsp;', $vbphrase['username']).'</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=email&page='.$vbulletin->GPC['pagenumber'].'">'.$vbphrase['email'].'</a>';

			$headings[] = str_replace(' ', '&nbsp;', 'Message');

			print_cells_row($headings, 1);

			while ($log = $db->fetch_array($logs)) {

				$cell = array();

				$cell[] = '<span class="smallfont">'.$log['date'].'</span>';
				$cell[] = '<span class="smallfont">'.$log['ip'].'</span>';

				if ($log['user_id'] > 0) { //user exist
						$cell[] = '<span class="smallfont"><a href="user.php?do=edit&u='.$log['user_id'].'"><b>'.$log['username'].'</b></a></span>';
				} else {
						$cell[] = '<span class="smallfont"><b>'.$log['username'].'</b></span>';
				}

				$cell[] = '<span class="smallfont">'.$log['email'].'</span>';
				$cell[] = '<span class="smallfont">'.$log['message'].'</span>';

				print_cells_row($cell, 0, 0, -4);
			}

			print_description_row("$firstpage $prevpage &nbsp; $nextpage $lastpage", 0, 6, 'thead', 'center');

			print_table_footer(6, "&dagger; When your forum permissions are set to allowed unregistered users to post, Spam-O-Matic will still check the username they entered and / or IP against the SFS database if your Spam-O-Matic settings are configured for these checks. When you see the dagger symbol (&dagger;) in the logs, it means Spam-O-Matic caught a known spammer even though they were not required to register, but they attempted to make a new post or thread.");
		} else {
			print_stop_message('no_results_matched_your_query');
		}
	}

	print_cp_footer();
?>