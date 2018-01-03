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
* Class to do data save/delete operations on Content Read Data
*
* Example usage:
*
* $read = datamanager_init('ContentRead', $vbulletin, ERRTYPE_STANDARD);
*
* @package	vBulletin
* @version	$Revision: 92424 $
* @date		$Date: 2017-01-15 14:08:51 +0000 (Sun, 15 Jan 2017) $
*/
class vB_DataManager_ContentRead extends vB_DataManager
{
	/**
	* Array of recognised and required fields for content read data
	*
	* @var	array
	*/
	var $validfields = array(
		'readid'		=> array(TYPE_UINT,	REQ_INCR, VF_METHOD, 'verify_nonzero'),
		'userid'		=> array(TYPE_UINT,	REQ_YES),
		'contentid'		=> array(TYPE_UINT,	REQ_YES, VF_METHOD, 'verify_nonzero'),
		'contenttypeid'	=> array(TYPE_INT,	REQ_YES, VF_METHOD, 'verify_nonzero_or_negone'),
		'readtype'		=> array(TYPE_STR,	REQ_YES, VF_METHOD),
		'ipid'			=> array(TYPE_UINT,	REQ_YES),
		'dateline'      => array(TYPE_UINT,	REQ_AUTO, VF_METHOD, 'verify_nonzero'),
	);

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('readid = %1$d', 'readid');

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'contentread';

	// Hooks, false = called locally //
	var $hook_start = 'contentread_start';
	var $hook_presave = false;
	var $hook_postsave = 'contentread_postsave';
	var $hook_delete = 'contentread_delete';

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errtype);
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

		// If ipid missing, set to 0
		if (!$this->fetch_field('ipid'))
		{
			$this->set('ipid', 0);
		}
		
		// If dateline missing, set to now
		if (!$this->fetch_field('dateline'))
		{
			$this->set('dateline', TIMENOW);
		}
		
		// Set contenttypeid if override exists
		if (!$this->fetch_field('contenttypeid'))
		{
			if ($this->info['contenttype'])
			{
				$this->set('contenttypeid', vB_Types::instance()->getContentTypeID($this->info['contenttype']));
			}
		}
		
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('contentread_presave')) ? eval($hook) : false;

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
	function verify_readtype($type)
	{
		return in_array($type, array('read','view','other'));
	}

	/**
	* Sets the variables from another dataman.
	* This will normally be the IP Data Manager.
	* Note the change from rectype >> readtype
	*
	* @param	object	Dataman object
	*
	* @return	boolean
	*/
	function set_from_dataman($dataman)
	{
		$this->set('ipid', $dataman->fetch_field('ipid'));
		$this->set('userid', $dataman->fetch_field('userid'));
		$this->set('readtype', $dataman->fetch_field('rectype'));
		$this->set('dateline', $dataman->fetch_field('dateline'));
		$this->set('contentid', $dataman->fetch_field('contentid'));
		$this->set('contenttypeid', $dataman->fetch_field('contenttypeid'));
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92424 $
|| # $Date: 2017-01-15 14:08:51 +0000 (Sun, 15 Jan 2017) $
|| ####################################################################
\*======================================================================*/
?>
