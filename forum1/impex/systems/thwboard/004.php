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
* thwboard_004 Import User module
*
* @package			ImpEx.thwboard
* @version			$Revision: 2321 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name$
* @date				$Date: 2011-01-03 11:45:32 -0800 (Mon, 03 Jan 2011) $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class thwboard_004 extends thwboard_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '003';
	var $_modulestring 	= 'Import User';

	function thwboard_004()
	{
		// Constructor
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_users'))
				{
					$displayobject->display_now('<h4>Imported users have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_users','Check database permissions');
				}
			}

			// Start up the table
			$displayobject->update_basic('title','Import User');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_user','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));

			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code('Users to import per cycle (must be greater than 1)','userperpage', 2000));

			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));

			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('userstartat','0');
			$sessionobject->add_session_var('userdone','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
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
		$user_array 	= $this->get_thwboard_user_details($Db_source, $source_database_type, $source_table_prefix, $user_start_at, $user_per_page);

		// Groups info
		$user_group_ids_array = $this->get_imported_group_ids($Db_target, $target_database_type, $target_table_prefix);

		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($user_array) . ' users</h4><p><b>From</b> : ' . $user_start_at . ' ::  <b>To</b> : ' . ($user_start_at + count($user_array)) . '</p>');

		$user_object = new ImpExData($Db_target, $sessionobject, 'user');

		foreach ($user_array as $user_id => $user_details)
		{
			$try = (phpversion() < '5' ? $user_object : clone($user_object));
			// Mandatory
			$bits = explode(',', $user_details['groupids']);

			if ($user_details['useractivate'] == '0')
			{
				$try->set_value('mandatory', 'usergroupid',				$user_group_ids_array[70]);
			}
			else
			{
				$try->set_value('mandatory', 'usergroupid',				$user_group_ids_array["$bits[1]"]);
			}
			
			$try->set_value('mandatory', 'username',				$user_details['username']);
			$try->set_value('mandatory', 'email',					$user_details['useremail']);
			$try->set_value('mandatory', 'importuserid',			$user_id);

			// Non Mandatory
			$old_groups = explode(',', $user_details['groupids']);
			$new_groups = array();
			
			foreach($old_groups AS $old_group_id)
			{
				$new_groups[] = $user_group_ids_array[$old_group_id];
			}
			$new_groups = implode(',', $new_groups);
			
			$try->set_value('nonmandatory', 'membergroupids',		$new_groups);
			$try->_password_md5_already = true;
			$try->set_value('nonmandatory', 'password',				$user_details['userpassword']);
			$try->set_value('nonmandatory', 'styleid',				$user_details['styleid']);
			$try->set_value('nonmandatory', 'homepage',				$user_details['userhomepage']);
			$try->set_value('nonmandatory', 'icq',					$user_details['usericq']);
			$try->set_value('nonmandatory', 'aim',					$user_details['useraim']);
			$try->set_value('nonmandatory', 'usertitle',			$user_details['usertitle']);
			$try->set_value('nonmandatory', 'joindate',				$user_details['userjoin']);
			$try->set_value('nonmandatory', 'options',				$this->_default_user_permissions);
			
			$other_one = explode('-', $user_details['userbday']); #dd-mm-yyyy
			
			$day 	= $other_one[0]; 
			$month	= $other_one[1];
			$year 	= $other_one[2];
			
			if($year AND $month AND $day)
			{
				$try->set_value('nonmandatory', 'birthday',			"{$month}-{$day}-{$year}");
				$try->set_value('nonmandatory', 'birthday_search',	"{$year}-{$month}-{$day}");
			}
			
			$try->set_value('nonmandatory', 'msn',					$user_details['usermsn']);
			$try->add_default_value('Location', 					$user_details['userlocation']);
			$try->add_default_value('Interests', 					$user_details['userinterests']);
			$try->add_default_value('Occupation', 					$user_details['useroccupation']);
			$try->add_default_value('signature', 					$user_details['usersignature']);

			// Check if user object is valid
			if($try->is_valid())
			{
				if($try->import_user($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: user -> ' . $user_details['username']);
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
					$displayobject->display_now("<br />Found avatar user and <b>DID NOT</b> imported to the  {$target_database_type} database");
				}
			}
			else
			{
				$displayobject->display_now("<br />Invalid user object, skipping." . $try->_failedon);
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
		}

		$sessionobject->set_session_var('userstartat',$user_start_at+$user_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php',$sessionobject->get_session_var('pagespeed')));
	}// End resume
}//End Class
# Autogenerated on : June 24, 2004, 2:27 pm
# By ImpEx-generator 1.0.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 2321 $
|| ####################################################################
\*======================================================================*/
?>
