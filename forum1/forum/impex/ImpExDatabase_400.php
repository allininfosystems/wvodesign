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
* @version		$Revision: 1830 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name$
* @date 		$Date: 2007-08-23 17:22:48 -0700 (Thu, 23 Aug 2007) $
* @copyright 	http://www.vbulletin.com/license.html
*
*/

if (!class_exists('ImpExDatabaseCore')) { die('Direct class access violation'); }

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
	var $_version = '0.0.1';

	var $_target_system = 'forum';

	/**
	* Constructor
	*
	* Empty
	*
	*/
	function ImpExDatabase()
	{
	}

	function import_attachment(&$Db_object, &$databasetype, &$tableprefix, $import_post_id = TRUE)
	{

		/*
		 Flow :
			1) Get the post if if we don't have it
			2) Update the attach count on post table
			3) Find the target location of the data (file system or database), default to database
			4) Write the data to the store and get the auto_inc id
			5) Update attachment
			6) Return attachmentid
		 */
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				if($import_post_id)
				{
					if($this->get_value('nonmandatory', 'postid'))
					{
						// Get the real post id
						$post_id = $Db_object->query_first("
							SELECT postid, userid
							FROM " . $tableprefix . "post
							WHERE
							importpostid = " . $this->get_value('nonmandatory', 'postid'));

						if(empty($post_id['postid']))
						{
							// Its not there to be attached through.
							return false;
						}
					}
					else
					{
						// No post id !!!
						return false;
					}
				}
				else
				{
					$sql ="
					SELECT userid, postid
					FROM " . $tableprefix . "post
					WHERE postid = " . $this->get_value('nonmandatory', 'postid');

					$post_id = $Db_object->query_first($sql);
				}

				// Update the post attach
				$Db_object->query("UPDATE " . $tableprefix . "post SET attach = attach+1 WHERE postid = " . $post_id['postid']);

				// Ok, so now where is it going ......
				$attachpath =  $this->get_options_setting($Db_object, $databasetype, $tableprefix, 'attachpath');
				$attachfile = $this->get_options_setting($Db_object, $databasetype, $tableprefix, 'attachfile');

				$attachment_id = $Db_object->insert_id();

				// Put the data some where, then set this and then update attach
				$filedataid = 0;

				$extension = $this->get_value('mandatory', 'filename');
				$extension = substr($extension, strpos($extension, '.')+1);

				#echo "attach file " . intval($attachfile);

				switch (intval($attachfile))
				{
					case '0':	// Straight into the dB
					{
						// Put something into the filedata table and get the auto_inc #

						$sql = "
						INSERT INTO {$tableprefix}filedata
						(
							userid,
							dateline,
							thumbnail_dateline,
							filedata,
							filesize,
							filehash,
							extension
						)
						VALUES
						(
							'" . $post_id['userid'] . "',
							'" . @time() . "',
							'" . @time() . "',
							'" . addslashes($this->get_value('mandatory', 'filedata')) . "',
							'" . intval($this->get_value('nonmandatory', 'filesize')) . "',
							'" . md5($this->get_value('mandatory', 'filedata')) . "',
							'" . addslashes($extension) . "'
						)";

						$Db_object->query($sql);

						$filedataid = $Db_object->insert_id();
						break;
					}
/*
					case '1':	// file system OLD naming schema
					{
						$full_path = $this->fetch_attachment_path($post_id['userid'], $attachpath, false, $attachment_id);

						if($this->vbmkdir(substr($full_path, 0, strrpos($full_path, '/'))))
						{
							if ($fp = fopen($full_path, 'wb'))
							{
								fwrite($fp, $this->get_value('mandatory', 'filedata'));
								fclose($fp);
								$filesize = filesize($full_path);

								if($filesize)
								{
									$Db_object->query("
										UPDATE " . $tableprefix . "attachment
										SET
										filesize = " . intval($this->get_value('nonmandatory', 'filesize'))  . "
										WHERE attachmentid = {$attachment_id}
									");

									return $attachment_id;
								}
							}
						}
						return false;
					}

					case '2':	// file system NEW naming schema
					{
						$full_path = $this->fetch_attachment_path($post_id['userid'], $attachpath, true, $attachment_id);

						if($this->vbmkdir(substr($full_path, 0, strrpos($full_path, '/'))))
						{
							if ($fp = fopen($full_path, 'wb'))
							{
								fwrite($fp, $this->get_value('mandatory', 'filedata'));
								fclose($fp);
								$filesize = filesize($full_path);

								if($filesize)
								{
									$Db_object->query("
										UPDATE " . $tableprefix . "attachment
										SET
										filesize = " . $this->get_value('nonmandatory', 'filesize')  . "
										WHERE attachmentid = {$attachment_id}
									");

									return $attachment_id;
								}
							}
						}
						return false;
					}
*/
					default :
					{
						// Shouldn't ever get here
						return false;
					}
				}

/*
posthash - TODO
contentid - TODO

import id for  filedata and clean out

=contenttypeid=
1-Post			2-Thread		3-Forum		4-Announcement		5-SocialGroupMessage	6-SocialGroupDiscussion
7-SocialGroup	8-Album			9-Picture	10-PictureComment	11-VisitorMessage		12-User
13-Event		14-Calendar
*/


				$Db_object->query("
					INSERT INTO " . $tableprefix . "attachment
					(
						importattachmentid,
						filename,
						userid,
						dateline,
						posthash,
						counter,
						reportthreadid,
						caption,
						state,
						contentid,
						filedataid,
						contenttypeid
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importattachmentid') . "',
						'" . addslashes($this->get_value('mandatory', 'filename')) . "',
						'" . $post_id['userid'] . "',
						'" . $this->get_value('nonmandatory', 'dateline')  . "',
						'imported-posthash',
						'" . $this->get_value('nonmandatory', 'counter')  . "',
						0,
						'" . /*caption*/ addslashes($this->get_value('mandatory', 'filename')) . "',
						'visible',
						'" . intval($post_id['postid'])  . "',
						'" . $filedataid . "',
						1
					)
				");

				return $Db_object->insert_id();
			}
			// Postgres Database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 14:23, Fri Mar 18th 2011
|| # CVS: $RCSfile$ - $Revision: 1830 $
|| ####################################################################
\*======================================================================*/
?>
