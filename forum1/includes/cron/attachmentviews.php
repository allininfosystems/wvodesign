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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$mysqlversion = $vbulletin->db->query_first("SELECT version() AS version");
define('MYSQL_VERSION', $mysqlversion['version']);

$aggtable = "aaggregate_temp_$nextitem[nextrun]";

$vbulletin->db->query_write("
	CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "$aggtable (
		attachmentid INT UNSIGNED NOT NULL DEFAULT '0',
		views INT UNSIGNED NOT NULL DEFAULT '0',
		KEY attachmentid (attachmentid)
	) ENGINE = MEMORY");

if ($vbulletin->options['usemailqueue'] == 2)
{
	$vbulletin->db->lock_tables(array(
		$aggtable         => 'WRITE',
		'attachmentviews' => 'WRITE'
	));
}

$vbulletin->db->query_write("
	INSERT INTO ". TABLE_PREFIX ."$aggtable
		SELECT attachmentid, COUNT(*) AS views
		FROM " . TABLE_PREFIX . "attachmentviews
		GROUP BY attachmentid
");

if ($vbulletin->options['usemailqueue'] == 2)
{
	$vbulletin->db->unlock_tables();
}
/* Small race condition but better than lots of IO wait for a DELETE query */
$vbulletin->db->query_write("TRUNCATE TABLE " . TABLE_PREFIX . "attachmentviews");

$vbulletin->db->query_write(
	"UPDATE " . TABLE_PREFIX . "attachment AS attachment,". TABLE_PREFIX . "$aggtable AS aggregate
	SET attachment.counter = attachment.counter + aggregate.views
	WHERE attachment.attachmentid = aggregate.attachmentid
");

$vbulletin->db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . $aggtable);

log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>