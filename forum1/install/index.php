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
chdir('./../');

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('VB_AREA', 'Install');
define('TIMENOW', time());

header('Expires: ' . gmdate("D, d M Y H:i:s", TIMENOW) . ' GMT');
header("Last-Modified: " . gmdate("D, d M Y H:i:s", TIMENOW) . ' GMT');

// ########################## REQUIRE BACK-END ############################
require_once('./install/includes/class_upgrade.php');
require_once('./install/init.php');
require_once(DIR . '/includes/functions.php');
require_once(DIR . '/includes/functions_misc.php');

$db->hide_errors();
$db->query_first("SELECT * FROM " . TABLE_PREFIX . "datastore");
if ($db->errno())
{
	exec_header_redirect('install.php');
}
else
{
	exec_header_redirect('upgrade.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 10:21, Mon Dec 11th 2017 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/