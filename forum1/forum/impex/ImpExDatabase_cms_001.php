<?php
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
* The database proxy object.
*
* This handles interaction with the different types of database.
*
* @package 		ImpEx
* @version		$Revision: 1771 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name$
* @date 		$Date: 2007-06-22 19:03:23 -0700 (Fri, 22 Jun 2007) $
* @copyright 	http://www.vbulletin.com/license.html
*
*/

if (!class_exists('ImpExFunction')) { die('Direct class access violation'); }

class ImpExDatabase extends ImpExDatabaseCore
{
	/**
	* Class version
	*
	* This will allow the checking for inter-operability of class version in different
	* versions of ImpEx
	*
	* @var    string
	*/

	var $_target_system = 'cms';


	/**
	* Constructor
	*
	* Empty
	*
	*/
	function ImpExDatabase()
	{
	}

	
	/**
	* Clears the currently imported blog and blog text
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_articles(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				
				#Delete the ones with the import id
				#Sort out the node tables ?

				# Dlete the content, the move all the node ids in the tables -1 after the node being removed  
				
				 return true;
			}
		}
	
		return false;
	}


	function create_cms_category(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{		
				# categoryid	int(10)
				# parentnode 	int(11)
				# category 		varchar(40)
				# description 	varchar(255)
				# catleft 		int(11)
				# catright 		int(11)
				# parentcat 	int(11)
				# enabled 		tinyint(3)
				# contentcount 	int(11)

			}// MySQL
		}// switch
		
		return false;		
	}#*/# function create_cms_category
	
	
	function import_article(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$new_article = array();

				######################
				## Create the artical, get the id, then sort out the nodes
			
				$Db_object->query("
					INSERT INTO {$tableprefix}cms_article (
						pagetext, postauthor, previewtext
					) VALUES (
						'" . addslashes($this->get_value('mandatory', 'pagetext')) . "',
						'" . addslashes($this->get_value('mandatory', 'postauthor')) . "',
						'" . addslashes($this->get_value('mandatory', 'previewtext')) . "'
					) 
				");

				if ($Db_object->affected_rows()) {
					$new_article['contentid'] = $Db_object->insert_id();
				} else {
					return false;
				}
				
				// CMS data inserted, now sort out the node(s) 
				######################
				## Create the Node in the hierarchical data

				# nodeid 		auto_increment
				# nodeleft		[generate]
				# noderight		[generate]
				# parentnode	<where are we going to nest the data under> - Create this then add nodes here and update the noderight from here on down				
				
				// TODO: We have to create a node some where to put all this stuff under ....... likely not here, but at 
				// the beginging and then save it to the session
				$parent_section_nodeid = 1; #get_parent_node();
				
				// Get the new leftnode position
				$parent = $Db_object->query_first("SELECT noderight FROM {$tableprefix}cms_node WHERE nodeid = {$parent_section_nodeid}");
				$left = $parent['noderight'] - 1;

				// Make a space for the new node
				$Db_object->query_first("UPDATE {$tableprefix}cms_node SET noderight = noderight + 2 WHERE noderight > {$left}");
				$Db_object->query_first("UPDATE {$tableprefix}cms_node SET nodeleft = nodeleft + 2 WHERE nodeleft > {$left}");

				// Fill the gap with our new leaf node
				$new_article['nodeleft'] = $left + 1;
				$new_article['noderight'] = $left + 2;
				$new_article['parentid'] = $parent_section_nodeid;

				// Type ID
				$cont_id = $Db_object->query_first("SELECT contenttypeid FROM {$tableprefix}contenttype INNER JOIN {$tableprefix}package on {$tableprefix}package.packageid = {$tableprefix}contenttype.packageid WHERE {$tableprefix}package.productid = 'vbcms' AND {$tableprefix}contenttype.class = 'Article'");
				$new_article['contenttypeid'] = $cont_id['contenttypeid'];

				// URL
				$new_article['url'] = $this->get_value('mandatory', 'title');
				#$new_article['url'] = preg_replace('#([\s;/\\\?:@&=+$,<>\#%"\'\.\r\n\t\x00-\x1f\x7f])#e','-', $this->get_value('mandatory', 'title'));
				#$new_article['url'] = trim(preg_replace('#-+#', '-', $new_article['url']), '-');
				
				$new_article['lastupdated'] = $this->get_value('mandatory', 'lastupdated');

				// Put it into the node table
				$Db_object->query("
					INSERT INTO {$tableprefix}cms_node (
							nodeleft, noderight, parentnode, contenttypeid, contentid, url, userid, publishdate, permissionsfrom
					) VALUES (
						{$new_article['nodeleft']},
						{$new_article['noderight']},
						{$new_article['parentid']},
						{$new_article['contenttypeid']},
						{$new_article['contentid']},
						'" . addslashes($new_article['url']) . "',
						'" . addslashes($this->get_value('mandatory', 'postauthor')) . "',
						'" . $this->get_value('nonmandatory', 'created') . "',
						'1'
					) 
				");

				if ($Db_object->affected_rows()) {
					$new_article['nodeid'] = $Db_object->insert_id();
				} else {
					return false;
				}
				
				######################
				## Update CMS nodeinfo
				// cms_nodeinfo
				// Put it into the node table
				$Db_object->query("
					INSERT INTO {$tableprefix}cms_nodeinfo (
							nodeid, description, title, html_title, creationdate, workflowstatus
					) VALUES (
						{$new_article['nodeid']},
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . $this->get_value('nonmandatory', 'publishdate') . "',
						'published'
					) 
				");
					
				return true;
			}// case MySQL
			
		return false;		
		}// switch
	}#*/# function imported_cms_articles

} // ImpExDatabase class end 

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 1771 $
|| ####################################################################
\*======================================================================*/
?>
