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

class vB_Upgrade_425b2 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '425b2';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '4.2.5 Beta 2';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '4.2.5 Beta 1';

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
	 * Replicates 3.8.11 Beta 2 Step 1
	 * Change host (ip address) field to varchar 45 for IPv6
	 */
	public function step_1()
	{
		if ($this->field_exists('session', 'host'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'session', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "session CHANGE host host VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.11 Beta 2 Step 1
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_2()
	{
		if ($this->field_exists('threadrate', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'threadrate', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "threadrate CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.11 Beta 2 Step 1
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_3()
	{
		if ($this->field_exists('apiclient', 'initialipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'apiclient', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "apiclient CHANGE initialipaddress initialipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Replicates 3.8.11 Beta 2 Step 1
	 * Change ip address field to varchar 45 for IPv6
	 */
	public function step_4()
	{
		if ($this->field_exists('apilog', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'apilog', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "apilog CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
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
		if ($this->field_exists('searchlog', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'searchlog', 1, 1),
				"ALTER TABLE " . TABLE_PREFIX . "searchlog CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Add IP Address Content Type, 
	 * Used as default in IP Data, where no other type is relevant.
	 */
	public function step_6()
	{
		$package = $this->db->query_first_slave("
			SELECT *
			FROM " . TABLE_PREFIX . "package
			WHERE productid = 'vbulletin'
		");

		$packageid = $package['packageid'];

		if (!$packageid)
		{
			$this->abort('Unable to find Package : vbulletin'); 
			return;
		}
		else
		{
			$sql = "
				INSERT IGNORE INTO " . TABLE_PREFIX . "contenttype 
				(class, packageid) VALUES ('IPAddress', $packageid)
			";

			vB_Cache::instance()->purge('vb_types.types');
			$this->run_query(sprintf($this->phrase['vbphrase']['update_table_x'], 'contenttype', 1, 1), $sql);
		}
	}

	/**
	 * Update User Change Log IP Data 
	 */
	public function step_7()
	{
		if ($typeid = vB_Types::instance()->getContentTypeID('vBForum_IPAddress'))
		{
			$records = $this->db->query_read_slave("
				SELECT cl.*
				FROM " . TABLE_PREFIX . "userchangelog cl
				LEFT JOIN " . TABLE_PREFIX . "ipdata ip ON (ip.ipid = cl.ipaddress)
				WHERE ipid IS NULL
			");

			while ($record = $this->db->fetch_array($records))
			{
				$ipman = datamanager_init('IPData', $this->registry, ERRTYPE_STANDARD);

				$ipman->set('rectype', 'other');
				$ipman->set('contenttypeid', $typeid);
				$ipaddr = long2ip($record['ipaddress']);

				$ipman->set('userid', $record['adminid']);
				$ipman->set('contentid', $record['changeid']);
				$ipman->set('dateline', $record['change_time']);
			
				$ipman->set('ip', $ipaddr);
				$ipman->set('altip', $ipaddr);

				$ipid = $ipman->save();
				$ipman->update_content('userchangelog', 'changeid', $ipid, $record['changeid']);
				unset($ipman);
			}
		}
		else
		{
			$this->abort('Unable to find Type : vBForum_IPAddress'); 
			return;
		}

		$this->show_message(sprintf($this->phrase['vbphrase']['update_table'], TABLE_PREFIX . 'userchangelog'));
	}

	/**
	 * Update IPData fileds to be consistant
	 */
	public function step_8()
	{
		if ($this->field_exists('ipdata', 'ip'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'ipdata', 1, 2),
				"ALTER TABLE " . TABLE_PREFIX . "ipdata CHANGE ip ip VARCHAR(45) NOT NULL DEFAULT ''"
			);

			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'ipdata', 2, 2),
				"ALTER TABLE " . TABLE_PREFIX . "ipdata CHANGE altip altip VARCHAR(45) NOT NULL DEFAULT ''"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Update Postlog for IPv6
	 * Related to 3.8.11 Beta 2 Step 1
	 */
	public function step_9()
	{
		$this->run_query(
			sprintf($this->phrase['vbphrase']['update_table'], 'postlog'),
			"TRUNCATE TABLE " . TABLE_PREFIX . "postlog"
		);

		if (!$this->field_exists('postlog', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['vbphrase']['alter_table'], 'postlog'),
				"ALTER TABLE " . TABLE_PREFIX . "postlog ADD COLUMN ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);
		};

		if ($this->field_exists('postlog', 'ip'))
		{
			$this->run_query(
				sprintf($this->phrase['vbphrase']['alter_table'], 'postlog'),
				"ALTER TABLE " . TABLE_PREFIX . "postlog DROP COLUMN ip"
			);
		};
	}

	/**
	 * Update searchcore for IPv6 
	 * (I dont think this IP Address is actually used for anything !)
	 */
	public function step_10()
	{
		if ($this->field_exists('searchcore', 'ipaddress'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'searchcore', 1, 2),
				"ALTER TABLE " . TABLE_PREFIX . "searchcore CHANGE ipaddress ipaddress VARCHAR(45) NOT NULL DEFAULT ''"
			);

			$this->run_query(
				sprintf($this->phrase['core']['altering_x_table'], 'searchcore', 2, 2),
				"UPDATE " . TABLE_PREFIX . "searchcore SET ipaddress = '0.0.0.0'"
			);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Suggest rebuilding the index, to update the IP's
	 */
	public function step_11()
	{
		$this->add_adminmessage(
			'after_upgrade_425_rebuild_search_index',
			array(
				'dismissable' => 1,
				'script'      => 'misc.php',
				'action'      => 'doindextypes',
				'execurl'     => 'misc.php?do=doindextypes&pp=250&autoredirect=1',
				'method'      => 'get',
				'status'      => 'undone',
			)
		);
	}

	/**
	 * Import any vB3 IP Address data
	 */
	public function step_12()
	{
		if ($this->field_exists('ipaddress', 'ipid'))
		{
			$tables = array(
				'groupmessage' => array (
						'field' => 'gmid',
						'type' => 'vBForum_SocialGroupMessage',
				),
				'picturecomment' => array (
						'field' => 'commentid',
						'type' => 'vBForum_PictureComment',
				),
				'visitormessage' => array (
						'field' => 'vmid',
						'type' => 'vBForum_VisitorMessage',
				),
			);

			$records = array();
			foreach ($tables AS $table => $values)
			{
				$ipdata = $this->db->query_read_slave("
					SELECT contenttype, contentid, ip.dateline, ip, 
						altip, postuserid AS userid, 'content' AS rectype
					FROM " . TABLE_PREFIX . "$table tb
					JOIN " . TABLE_PREFIX . "ipaddress ip ON (tb.ipaddress = ip.ipid)
				");

				while ($record = $this->db->fetch_array($ipdata))
				{
					$records[] = $record;
				}
			}

			foreach ($records AS $record)
			{
				$table = $record['contenttype'];
				$type = $tables[$table]['type'];
				$field = $tables[$table]['field'];

				if (isset($tables[$table]))
				{
					unset ($record['contenttype']);

					if ($typeid = vB_Types::instance()->getContentTypeID($type))
					{
						$record['contenttypeid'] = $typeid;
						$ipman = datamanager_init('IPData', $this->registry, ERRTYPE_STANDARD);
						$ipman->set_from_array($record);
						$ipid = $ipman->save();
						$ipman->update_content($table, $field, $ipid, $record['contentid']);
						unset($ipman);
					}
					else
					{
						$this->abort("Unable to find Type : $type"); 
						return;
					}
				}
				else
				{
					$this->abort("Unknown Content Type : $table"); 
					return;
				}
			}

			$this->show_message($this->phrase['version']['425b2']['import_ipaddress']);
		}
		else
		{
			$this->skip_message();
		}
	}

	/**
	 * Update Group Messages IP Data 
	 * Replicates 3.8.11 Beta 2 Step 2 (Sort of)
	 */
	public function step_13()
	{
		$field = 'gmid';
		$table = 'groupmessage';
		$type = 'vBForum_SocialGroupMessage';

		if ($typeid = vB_Types::instance()->getContentTypeID($type))
		{
			$records = $this->db->query_read_slave("
				SELECT tb.*
				FROM " . TABLE_PREFIX . "$table tb
				LEFT JOIN " . TABLE_PREFIX . "ipdata ip ON (ip.ipid = tb.ipaddress)
				WHERE ipid IS NULL
			");

			while ($record = $this->db->fetch_array($records))
			{
				$ipman = datamanager_init('IPData', $this->registry, ERRTYPE_STANDARD);

				$ipman->set('rectype', 'content');
				$ipman->set('contenttypeid', $typeid);
				$ipaddress = long2ip($record['ipaddress']);

				$ipman->set('contentid', $record[$field]);
				$ipman->set('userid', $record['postuserid']);
				$ipman->set('dateline', $record['dateline']);
			
				$ipman->set('ip', $ipaddress);
				$ipman->set('altip', $ipaddress);

				$ipid = $ipman->save();
				$ipman->update_content($table, $field, $ipid, $record[$field]);
				unset($ipman);
			}
		}
		else
		{	/* This should never happen as we would
			 already have failed on the previous step */
			$this->abort("Unable to find Type : $type"); 
			return;
		}

		$this->show_message(sprintf($this->phrase['vbphrase']['update_table'], TABLE_PREFIX . $table));
	}

	/**
	 * Update Picture Comments IP Data 
	 * Replicates 3.8.11 Beta 2 Step 3 (Sort of)
	 */
	public function step_14()
	{
		$field = 'commentid';
		$table = 'picturecomment';
		$type = 'vBForum_PictureComment';

		if ($typeid = vB_Types::instance()->getContentTypeID($type))
		{
			$records = $this->db->query_read_slave("
				SELECT tb.*
				FROM " . TABLE_PREFIX . "$table tb
				LEFT JOIN " . TABLE_PREFIX . "ipdata ip ON (ip.ipid = tb.ipaddress)
				WHERE ipid IS NULL
			");

			while ($record = $this->db->fetch_array($records))
			{
				$ipman = datamanager_init('IPData', $this->registry, ERRTYPE_STANDARD);

				$ipman->set('rectype', 'content');
				$ipman->set('contenttypeid', $typeid);
				$ipaddress = long2ip($record['ipaddress']);

				$ipman->set('contentid', $record[$field]);
				$ipman->set('userid', $record['postuserid']);
				$ipman->set('dateline', $record['dateline']);
			
				$ipman->set('ip', $ipaddress);
				$ipman->set('altip', $ipaddress);

				$ipid = $ipman->save();
				$ipman->update_content($table, $field, $ipid, $record[$field]);
				unset($ipman);
			}
		}
		else
		{	/* This should never happen as we would
			 already have failed on the previous step */
			$this->abort("Unable to find Type : $type"); 
			return;
		}

		$this->show_message(sprintf($this->phrase['vbphrase']['update_table'], TABLE_PREFIX . $table));
	}

	/**
	 * Update Visitor Messages IP Data 
	 * Replicates 3.8.11 Beta 2 Step 4 (Sort of)
	 */
	public function step_15()
	{
		$field = 'vmid';
		$table = 'visitormessage';
		$type = 'vBForum_VisitorMessage';

		if ($typeid = vB_Types::instance()->getContentTypeID($type))
		{
			$records = $this->db->query_read_slave("
				SELECT tb.*
				FROM " . TABLE_PREFIX . "$table tb
				LEFT JOIN " . TABLE_PREFIX . "ipdata ip ON (ip.ipid = tb.ipaddress)
				WHERE ipid IS NULL
			");

			while ($record = $this->db->fetch_array($records))
			{
				$ipman = datamanager_init('IPData', $this->registry, ERRTYPE_STANDARD);

				$ipman->set('rectype', 'content');
				$ipman->set('contenttypeid', $typeid);
				$ipaddress = long2ip($record['ipaddress']);

				$ipman->set('contentid', $record[$field]);
				$ipman->set('userid', $record['postuserid']);
				$ipman->set('dateline', $record['dateline']);
			
				$ipman->set('ip', $ipaddress);
				$ipman->set('altip', $ipaddress);

				$ipid = $ipman->save();
				$ipman->update_content($table, $field, $ipid, $record[$field]);
				unset($ipman);
			}
		}
		else
		{	/* This should never happen as we would
			 already have failed on the previous step */
			$this->abort("Unable to find Type : $type"); 
			return;
		}

		$this->show_message(sprintf($this->phrase['vbphrase']['update_table'], TABLE_PREFIX . $table));
	}

	/**
	 * Update Postlog for IPv6
	 */
	public function step_16()
	{
		if ($this->field_exists('ipaddress', 'ipid'))
		{
			$this->run_query(
				sprintf($this->phrase['core']['dropping_old_table_x'], 'ipaddress'),
				"DROP TABLE " . TABLE_PREFIX . "ipaddress"
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
|| # Downloaded: 10:21, Mon Dec 11th 2017
|| # CVS: $RCSfile$ - $Revision: 35750 $
|| ####################################################################
\*======================================================================*/
