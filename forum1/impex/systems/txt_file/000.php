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
* txt_file API module
*
* @package			ImpEx.txt_file
* @version			$Revision: 2321 $
* @author			Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout		$Name$
* @date				$Date: 2011-01-03 11:45:32 -0800 (Mon, 03 Jan 2011) $
* @copyright		http://www.vbulletin.com/license.html
*
*/
class txt_file_000 extends ImpExModule
{
	
	var $_seperator = array(
		0 => ',',
		1 => '|',
		2 => '|||',
		3 => '|$|'
	);

	var $_layout = array(
		0 => 'NONE',
		1 => 'username',
		2 => 'id',
		3 => 'email',
		4 => 'password'
	);

	/**
	* Class version
	*
	* This is the version of the source system that is supported
	*
	* @var    string
	*/
	var $_version = '0.0';
	var $_tier = '1';

	/**
	* Module string
	*
	* @var    array
	*/
	var $_modulestring 	= 'Text file importer';
	var $_homepage 	= 'http://www.vbulletin.com';


	/**
	* Valid Database Tables
	*
	* @var    array
	*/
	var $_valid_tables = array ();


	function txt_file_000()
	{
	}


	/**
	* Parses and custom HTML for txt_file
	*
	* @param	string	mixed			The text to be parse
	*
	* @return	array
	*/
	function txt_file_html($text)
	{
		return $text;
	}

	/**
	* Returns the user_id => user array
	*
	* @param	object	databaseobject	The database object to run the query against
	* @param	string	mixed			Table database type
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			Start point
	* @param	int		mixed			End point
	*
	* @return	array
	*/
	function get_txt_file_user_details(&$path_file, &$start_at, &$per_page)
	{
		
		$return_array = array();

		// Check that there is not a empty value
		if (empty($path_file) OR empty($per_page)) { return $return_array; }

		if (!is_file($path_file))
		{
			return false;
		}

		$total = file($path_file);
		 
		foreach($total AS $line_no => $data)
		{
			$return_array[$line_no] = $data;
		}

		return array_slice($return_array, $start_at, $per_page);
	}


} // Class end
# Autogenerated on : December 17, 2004, 4:43 pm
# By ImpEx-generator 1.4.
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 2321 $
|| ####################################################################
\*======================================================================*/
?>
