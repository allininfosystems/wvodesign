<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF6025393
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is �2000-2011 vBulletin Solutions Inc. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* yabb2_002 Import User module
*
* @package			ImpEx.yabb2
* @version			$Revision: 2321 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @date				$Date: 2011-01-03 11:45:32 -0800 (Mon, 03 Jan 2011) $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class yabb2_002 extends yabb2_000
{
	var $_dependent 	= '001';


	function yabb2_002(&$displayobject)
	{
		$this->_modulestring = $displayobject->phrases['import_user'];
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_users'))
				{
					$displayobject->display_now("<h4>{$displayobject->phrases['users_cleared']}</h4>");
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error(substr(get_class($this) , -3), $displayobject->phrases['user_restart_failed'], $displayobject->phrases['check_db_permissions']);
				}
			}


			// Start up the table
			$displayobject->update_basic('title', $displayobject->phrases['import_user']);
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$which_username = array ( '1' => 'realname', '2' => 'username');

			$displayobject->update_html($displayobject->make_input_code($displayobject->phrases['users_per_page'],'userperpage',500));
			$displayobject->update_html($displayobject->make_description($displayobject->phrases['which_username'] . $displayobject->make_select($which_username, 'which_username')));
			$displayobject->update_html($displayobject->make_yesno_code($displayobject->phrases['email_match'], "email_match",0));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],$displayobject->phrases['reset']));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('userstartat','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description("<p>{$displayobject->phrases['dependant_on']}<i><b> " . $sessionobject->get_module_title($this->_dependent) . "</b> {$displayobject->phrases['cant_run']}</i> ."));
			$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['continue'],''));
			$sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}


	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		// Set up working variables.
		$displayobject->update_basic('displaymodules','FALSE');
		$target_database_type	= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix	= $sessionobject->get_session_var('targettableprefix');
		$source_database_type	= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix	= $sessionobject->get_session_var('sourcetableprefix');


		// Per page vars
		$user_start_at			= $sessionobject->get_session_var('userstartat');
		$user_per_page			= $sessionobject->get_session_var('userperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of user details
		$memberspath 	= $sessionobject->get_session_var('forumspath') . '/Members';
		$user_array		= $this->get_yabb2_user_details($memberspath, $user_start_at, $user_per_page);

		$user_group_ids_array 	= $this->get_imported_group_ids($Db_target, $target_database_type, $target_table_prefix);

		// Display count and pass time
		$displayobject->display_now("<h4>{$displayobject->phrases['importing']} " . count($user_array) . " {$displayobject->phrases['users']}</h4><p><b>{$displayobject->phrases['from']}</b> : " . $user_start_at . " ::  <b>{$displayobject->phrases['to']}</b> : " . ($user_start_at + count($user_array)) . "</p>");


		$user_object = new ImpExData($Db_target, $sessionobject, 'user');


		foreach ($user_array as $user_id => $user_details)
		{
			$try = (phpversion() < '5' ? $user_object : clone($user_object));


			// Auto associate
			if ($sessionobject->get_session_var('email_match'))
			{
				$try->_auto_email_associate = true;
			}


			// Mandatory
			$try->set_value('mandatory', 'email',				$user_details['email']);
			$try->set_value('mandatory', 'usergroupid',			$user_group_ids_array[69]);
			$try->set_value('mandatory', 'importuserid',		$user_id);

			// Mandatory
			if($sessionobject->get_session_var('which_username') == '1')
			{
				$try->set_value('mandatory', 'username',			$user_details['realname']);
			}
			else
			{
				$try->set_value('mandatory', 'username',			$user_details['username_file']);
			}




			// Non Mandatory
			$try->set_value('nonmandatory', 'birthday',			$user_details['bday']);			// Check format
			$try->set_value('nonmandatory', 'birthday_search',	$user_details['birthday_search']);

			$try->set_value('nonmandatory', 'options',			$this->_default_user_permissions);

			$try->set_value('nonmandatory', 'msn',				$user_details['msn']);
			$try->set_value('nonmandatory', 'icq',				$user_details['icq']);
			$try->set_value('nonmandatory', 'yahoo',			$user_details['yim']);
			$try->set_value('nonmandatory', 'aim',				$user_details['aim']);
			$try->set_value('nonmandatory', 'timezoneoffset',	$user_details['timeoffset']);
			$try->set_value('nonmandatory', 'homepage',			$user_details['weburl']);

			$try->set_value('nonmandatory', 'password',			$user_details['password']); // Can't be done, so random it.
			$try->set_value('nonmandatory', 'posts',			$user_details['postcount']);

			$try->set_value('nonmandatory', 'lastpost',			$user_details['lastpost']);
			$try->set_value('nonmandatory', 'lastactivity',		$user_details['lastonline']);
			$try->set_value('nonmandatory', 'lastvisit',		$user_details['lastonline']);
			$try->set_value('nonmandatory', 'joindate',			$user_details['regtime']);

			// If its not blank slash it and get it
			if($user['signature'] != '')
			{
				$try->add_default_value('signature', 	addslashes($this->yabb2_html($this->html_2_bb($user['signature']))));
			}

			// Check if user object is valid
			if($try->is_valid())
			{
				if($try->import_user($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span>' . $displayobject->phrases['user'] . ' -> ' . $try->get_value('mandatory','username'));
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error($user_id, $displayobject->phrases['user_not_imported'], $displayobject->phrases['user_not_imported_rem']);
					$displayobject->display_now("<br />{$impex_phrases['failed']} :: {$displayobject->phrases['user_not_imported']}");
				}
			}
			else
			{
				$displayobject->display_now("<br />{$impex_phrases['invalid_object']}" . $try->_failedon);
			}
			unset($try);
		}// End foreach


		// Check for page end
		if (count($user_array) == 0 OR count($user_array) < $user_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$this->build_user_statistics($Db_target, $target_database_type, $target_table_prefix);

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num, '_objects_failed')
			));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_user','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
		}


		$sessionobject->set_session_var('userstartat',$user_start_at+$user_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
	}// End resume
}//End Class
# Autogenerated on : March 20, 2006, 7:38 pm
# By ImpEx-generator 2.1.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 2321 $
|| ####################################################################
\*======================================================================*/
?>
