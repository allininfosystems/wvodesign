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
/*
if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}
*/

class vB_Upgrade_424b1 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '424b1';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '4.2.4 Beta 1';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '4.2.3';

	/**
	* Beginning version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_STARTS = '';

	/**
	* Ending version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_ENDS   = '';

	/** Updated by VBIV-16186 **
	* Check attachment refcounts and fix any that are broken.
	* This checks to see if this has been previously run, as 
	* we have duplicated this update in 4.2.3 step 1 as well.
	*/
	public function step_1()
	{
		$check = $this->db->query_first_slave("
			SELECT text
			FROM " . TABLE_PREFIX . "adminutil
			WHERE title = 'attach_fix'
		");

		$hasrun = intval($check['text']);

		if ($hasrun)
		{
			$this->skip_message();
		}
		else
		{
			$sql = "
				INSERT INTO " . TABLE_PREFIX . "adminutil 
				(title, text) VALUES ('attach_fix', '1')
			";

			$this->db->query_write($sql);

			$sql = "
				UPDATE " . TABLE_PREFIX . "filedata
				LEFT JOIN (
					SELECT filedataid, COUNT(attachmentid) AS actual
					FROM " . TABLE_PREFIX . "attachment
					GROUP BY filedataid
				) list USING (filedataid) 
				SET refcount = IFNULL(actual, 0)
				WHERE refcount <> IFNULL(actual, 0)
			";

			$this->run_query(sprintf($this->phrase['vbphrase']['update_table_x'], 'filedata', 1, 1), $sql);
		}
	}

	/*
		Steps 2 to 6 Replicate 3.8.10 Beta 1 Step 1
	*/

	/**
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_2()
	{
		if ($this->field_exists('strikes', 'strikeip'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'strikes', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "strikes CHANGE strikeip strikeip VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_3()
	{
		if ($this->field_exists('adminlog', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'adminlog', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "adminlog CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_4()
	{
		if ($this->field_exists('moderatorlog', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'moderatorlog', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "moderatorlog CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_5()
	{
		if ($this->field_exists('user', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'user', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "user CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_6()
	{
		if ($this->field_exists('post', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'post', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "post CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92674 $
|| # $Date: 2017-01-29 18:09:40 -0800 (Sun, 29 Jan 2017) $
|| ####################################################################
\*======================================================================*/
