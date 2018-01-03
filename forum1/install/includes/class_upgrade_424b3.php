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

class vB_Upgrade_424b3 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '424b3';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '4.2.4 Beta 3';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '4.2.4 Beta 2';

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

	/**
	 * Replicates 3.8.10 Beta 2 Step 1
	 * Change moderator id field from small int to int
	 */
	public function step_1()
	{
		if ($this->field_exists('moderator', 'moderatorid'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'moderator', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "moderator CHANGE moderatorid moderatorid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.10 Beta 2 Step 2
	 * Change [passwordhistory] passworddate field default for MySQL 5.7
	 */
	public function step_2()
	{
		if ($this->field_exists('passwordhistory', 'passworddate'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'passwordhistory', 1, 2),
				"ALTER TABLE " . TABLE_PREFIX . "passwordhistory CHANGE COLUMN passworddate passworddate DATE NOT NULL DEFAULT '1000-01-01'"
			);

			// There shouldn't be any to change, but lets play safe.
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'passwordhistory', 2, 2),
				"UPDATE " . TABLE_PREFIX . "passwordhistory SET passworddate = '1000-01-01' WHERE passworddate = '0000-00-00'"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.10 Beta 2 Step 3
	 * Change [user] passworddate field default for MySQL 5.7
	 */
	public function step_3()
	{
		if ($this->field_exists('user', 'passworddate'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'user', 1, 4),
				"ALTER TABLE " . TABLE_PREFIX . "user CHANGE COLUMN passworddate passworddate DATE NOT NULL DEFAULT '1000-01-01'"
			);

			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'user', 2, 4),
				"UPDATE " . TABLE_PREFIX . "user SET passworddate = '1000-01-01' WHERE passworddate = '0000-00-00'"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.10 Beta 2 Step 4
	 * Change [user] birthday_search field default for MySQL 5.7
	 */
	public function step_4()
	{
		if ($this->field_exists('user', 'birthday_search'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'user', 3, 4),
				"ALTER TABLE " . TABLE_PREFIX . "user CHANGE COLUMN birthday_search birthday_search DATE NOT NULL DEFAULT '1000-01-01'"
			);

			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'user', 4, 4),
				"UPDATE " . TABLE_PREFIX . "user SET birthday_search = '1000-01-01' WHERE birthday_search = '0000-00-00'"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Delete old Panjo & Postrelease products.
	 */
	function step_5()
	{
		require_once(DIR . '/includes/adminfunctions_plugin.php');
		$this->show_message($this->phrase['version']['420a1']['disable_products']);

		$products = array(
			'panjo', 'postrelease'
		);

		remove_products($products, false, false);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92674 $
|| # $Date: 2017-01-29 18:09:40 -0800 (Sun, 29 Jan 2017) $
|| ####################################################################
\*======================================================================*/
