<?php 
if (!defined('IDIR')) { die; }
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
* dzoic _001
*
* @package 		ImpEx.dzoic 
* @version		$Revision: $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name$
* @date 		$Date: $
* @copyright 	http://www.vbulletin.com/license.html
*
*/

class dzoic_001 extends dzoic_000
{
	function dzoic_001($displayobject)
	{
		$this->_modulestring = $displayobject->phrases['check_update_db'];
	}

	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		$displayobject->update_basic('title', $displayobject->phrases['get_db_info']);
		$displayobject->update_html($displayobject->do_form_header('index','001'));
		$displayobject->update_html($displayobject->make_table_header($displayobject->phrases['get_db_info']));
		$displayobject->update_html($displayobject->make_hidden_code('database','working'));

		$displayobject->update_html($displayobject->make_description($displayobject->phrases['check_tables']));

		$displayobject->update_html($displayobject->do_form_footer($displayobject->phrases['check_update_db'],''));
		$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
		$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
	}

	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if (!$sessionobject->get_session_var('sourceexists'))
		{
			$displayobject->display_error($displayobject->phrases['sourceexists_is_false']);
			exit;
		}

		// Setup some working variables
		$displayobject->update_basic('displaymodules','FALSE');
		$target_db_type 		= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix 	= $sessionobject->get_session_var('targettableprefix');
		$source_db_type			= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix 	= $sessionobject->get_session_var('sourcetableprefix');

		$class_num        = substr(get_class($this) , -3);

		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num, 'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		$displayobject->update_basic('title',$displayobject->phrases['altering_tables']);
		$displayobject->display_now("<h4>{$displayobject->phrases['altering_tables']}</h4>");
		$displayobject->display_now($displayobject->phrases['alter_desc_1']);
		$displayobject->display_now($displayobject->phrases['alter_desc_2']);
		$displayobject->display_now($displayobject->phrases['alter_desc_3']);
		$displayobject->display_now($displayobject->phrases['alter_desc_4']);


		// Add an importids now
		foreach ($this->_import_ids as $id => $table_array)
		{
			foreach ($table_array as $tablename => $column)
			{
				if ($this->add_import_id($Db_target, $target_db_type, $target_table_prefix, $tablename, $column))
				{
					$displayobject->display_now("\n<br /><b>$tablename</b> - $column <i>{$displayobject->phrases['completed']}</i>");
				}
				else
				{
					$sessionobject->add_error($Db_target, 'fatal', $class_num, 0, $displayobject->phrases['table_alter_fail'], $displayobject->phrases['table_alter_fail_rem']);
					exit;
				}
			}
		}

		// Add the importpostid indexs
		$this->add_index($Db_target, $target_db_type, $target_table_prefix, 'user');
		$this->add_index($Db_target, $target_db_type, $target_table_prefix, 'post');
		#$this->add_index($Db_target, $target_db_type, $target_table_prefix, 'thread');

		if($sessionobject->get_session_var('added_default_unknown_group') != 'yup')
		{
			$try = new ImpExData($Db_target, $sessionobject, 'usergroup');
			$try->set_value('mandatory', 'importusergroupid',		'69');
			$try->set_value('nonmandatory', 'title',				"Admin");
			$try->import_usergroup($Db_target, $target_db_type, $target_table_prefix);
			unset($try);

			$try = new ImpExData($Db_target, $sessionobject, 'usergroup');
			$try->set_value('mandatory', 'importusergroupid',		'70');
			$try->set_value('nonmandatory', 'title',				"Moderator");
			$try->import_usergroup($Db_target, $target_db_type, $target_table_prefix);
			unset($try);
			
			$try = new ImpExData($Db_target, $sessionobject, 'usergroup');
			$try->set_value('mandatory', 'importusergroupid',		'71');
			$try->set_value('nonmandatory', 'title',				"User");
			$try->import_usergroup($Db_target, $target_db_type, $target_table_prefix);
			unset($try);
			
			$sessionobject->add_session_var('added_default_unknown_group', 'yup');
		}

		// Check the database connection
		$result = $this->check_database($Db_source, $source_db_type, $source_table_prefix, $sessionobject->get_session_var('sourceexists'));

		$displayobject->display_now($result['text']);

		if ($result['code'])
		{
			$sessionobject->timing($class_num, 'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');

			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
				$sessionobject->return_stats($class_num, '_time_taken'),
				$sessionobject->return_stats($class_num, '_objects_done'),
				$sessionobject->return_stats($class_num,'_objects_failed')
			));

			$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
			$sessionobject->set_session_var(substr(get_class($this), -3), 'FINISHED');
			$sessionobject->set_session_var('module','000');
			$displayobject->update_basic('displaymodules','FALSE');
			$displayobject->update_html($displayobject->print_redirect_001('index.php',$sessionobject->get_session_var('pagespeed')));
		}
		else
		{
			$sessionobject->add_session_var($class_num . '_objects_failed',intval($sessionobject->get_session_var($class_num . '_objects_failed')) + 1 );
			$sessionobject->add_error($Db_target, 'fatal', $class_num, 0, $displayobject->phrases['table_check_error'], $displayobject->phrases['check_db_permissions']);

			$displayobject->update_html($displayobject->make_description("{$displayobject->phrases['failed']} {$displayobject->phrases['check_db_permissions']}"));
			$sessionobject->set_session_var('001','FAILED');
			$sessionobject->set_session_var('module','000');
			$displayobject->update_html($displayobject->print_redirect_001('index.php',$sessionobject->get_session_var('pagespeed')));
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: $
|| ####################################################################
\*======================================================================*/
?>
