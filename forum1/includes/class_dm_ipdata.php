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

if (!class_exists('vB_DataManager', false))
{
	exit;
}

/**
* Class to do data save/delete operations for IP Data
*
* Example usage:
*
* $ipdata = datamanager_init('IPData', $vbulletin, ERRTYPE_STANDARD);
*
* @package	vBulletin
* @version	$Revision: 92645 $
* @date		$Date: 2017-01-26 22:51:44 +0000 (Thu, 26 Jan 2017) $
*/
class vB_DataManager_IPData extends vB_DataManager
{
	/**
	* Array of recognised and required fields for poll, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'ipid'			=> array(TYPE_UINT,	REQ_INCR, VF_METHOD, 'verify_nonzero'),
		'userid'		=> array(TYPE_UINT,	REQ_YES),
		'contentid'		=> array(TYPE_UINT,	REQ_YES, VF_METHOD, 'verify_nonzero'),
		'contenttypeid'	=> array(TYPE_INT,	REQ_YES, VF_METHOD, 'verify_nonzero_or_negone'),
		'rectype'		=> array(TYPE_STR,	REQ_YES, VF_METHOD),
		'ip'			=> array(TYPE_STR,	REQ_YES, VF_METHOD, 'verify_ipaddress'),
		'altip'			=> array(TYPE_STR,	REQ_NO, VF_METHOD, 'verify_ipaddress'),
		'dateline'      => array(TYPE_UINT,	REQ_AUTO, VF_METHOD, 'verify_nonzero'),
	);

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('ipid = %1$d', 'ipid');

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'ipdata';

	// Hooks, false = called locally //
	var $hook_start = false;
	var $hook_presave = false;
	var $hook_postsave = 'ipdata_postsave';
	var $hook_delete = 'ipdata_delete';

	// Skip save
	var $skip_update = false;
	
	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errtype);

		// Defaults
		if (defined('IPADDRESS')
		AND !defined('SKIP_IPDATA_DEFAULTS'))
		{
			$this->set_info('ip', IPADDRESS); 
			$this->set_info('altip', ALT_IP);
		}

		($hook = vBulletinHook::fetch_hook('ipdata_start')) ? eval($hook) : false;
	}

	/**
	* Format the data for saving
	*
	* @param	bool
	*
	* @return 	boolean	Function result
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}

		// If ip is missing, set to defaults
		if (!($ip = $this->fetch_field('ip')))
		{
			if (!($ip = $this->info['ip']))
			{
				$ip = '0.0.0.0';
			}
		}

		// If altip is missing, set to defaults
		if (!($altip = $this->fetch_field('altip')))
		{
			if (!($altip = $this->info['altip']))
			{
				$altip = $ip;
			}
		}

		// If dateline missing, set to now
		if (!$this->fetch_field('dateline'))
		{
			$this->set('dateline', TIMENOW);
		}

		// Set IP contenttypeid if required
		if ($this->fetch_field('contenttypeid') < 1)
		{
			$this->set('contenttypeid', vB_Types::instance()->getContentTypeID('vBForum_IPAddress'));
		}

		// Compress and set the ip data
		$this->set('ip', compress_ip($ip, false));
		$this->set('altip', compress_ip($altip, false));

		// Set contenttypeid if override exists
		if ($this->info['contenttype'])
		{
			$this->set('contenttypeid', vB_Types::instance()->getContentTypeID($this->info['contenttype']));
		}
		
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('ipdata_presave')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Saves the data from the object into the specified database tables
	*
	* We change the default for $replace to true, and then call the parent.
	*/
	function save($doquery = true, $delayed = false, $affected_rows = false, $replace = true, $ignore = false)
	{
		// We default $replace to true, and then call the parent.
		return parent::save($doquery, $delayed, $affected_rows, $replace, $ignore);
	}

	/**
	* Verifies the record type
	*
	* @param	string	The type
	*
	* @return 	boolean	Returns true if the address is valid
	*/
	function verify_rectype($type)
	{
		return in_array($type, array('content','read','view','visit','register','logon','logoff','other'));
	}

	/**
	* Updates a content record
	*
	* @param	string	The type
	* @param	string	The column name
	* @param	int		The ip address record id
	* @param	int		The content record id
	* @param	string	The ip address field name
	*
	* @return 	boolean	Returns true if the type is valid
	*/
	function update_content($type, $fieldid, $ipid, $contentid, $filedname = 'ipaddress')
	{
		$this->$type = array();
		$this->{$type}[$filedname] = $ipid;
		$sql = $this->fetch_update_sql(TABLE_PREFIX, $type, "$fieldid = $contentid");

		return $this->dbobject->query_write($sql);
	}

	/**
	* Sets the variables from an array.
	*
	* @param	array	Values to set
	*
	* @return	boolean
	*/
	function set_from_array($data)
	{
		foreach ($data AS $fieldname => $value)
		{
			$this->set($fieldname, $value);
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92645 $
|| # $Date: 2017-01-26 22:51:44 +0000 (Thu, 26 Jan 2017) $
|| ####################################################################
\*======================================================================*/
?>
