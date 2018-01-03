<?php
if (!defined('IDIR')) { die; }

#################################################################### |;
# vBulletin  - Licence Number VBF6025393
# ---------------------------------------------------------------- # |;
# Copyright �2000�2011 vBulletin Solutions Inc. All Rights Reserved. |;
# This file may not be redistributed in whole or significant part. # |;
# ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # |;
# http://www.vbulletin.com | http://www.vbulletin.com/license.html # |;
#################################################################### |;
# The following settings allow ImpEx to connect to the vBulletin 3
# database into which you will be importing data.

####
#
# TARGET - The target is the vBulletin database (where the  data is going to)
#
####

$impexconfig['target']['server']		= 'localhost';
$impexconfig['target']['user']			= 'wvodesig_forum';
$impexconfig['target']['password']		= 'X&heT!pULUIs';
$impexconfig['target']['database']		= 'wvodesig_forum';
$impexconfig['target']['tableprefix']	= 'vb_';

# If the system that is being imported from uses a database,
# enter the details for it here and set 'sourceexists' to true.
# If the source data is NOT stored in a database, set 'sourceexists' to false

$impexconfig['sourceexists']			= true;

####
#
# SOURCE - The source is the old forum database (where the  data is coming from)
#
####

# mysql / mssql
$impexconfig['source']['databasetype']	= 'mysql';  // mysql OR mssql
$impexconfig['source']['server']		= 'localhost';
$impexconfig['source']['user']			= 'wvodesig';
$impexconfig['source']['password']		= 'qwerty123';
$impexconfig['source']['database']		= 'wvodesig_forum';
$impexconfig['source']['tableprefix']   = 'smf_';
#ysql -u wvodesig -pqwerty123 wvodesig_forum

####
#
# DUPLICATE CHECKING
# Where unique import id's are available ImpEx can check for duplicates with some
# Tier2 systems this will need to be set to false.
#
# yahoo_groups, YaBB 2.1, Ikonboard 3.1.5 (for attachments)
#
####

define('dupe_checking', false);

###############################################################################
####
#
# ADVANCED - For a standard import or a novice user leave the settings below.
#
####

// Advanced Target
$impexconfig['target']['databasetype']	= 'mysql';	// currently mysql only
$impexconfig['target']['charset']		= '';
$impexconfig['target']['persistent']	= false; 	// (true/false) use mysql_pconnect

// Advanced Source
$impexconfig['source']['charset']		= '';
$impexconfig['source']['persistent']	= false;

# pagespeed is the second(s) wait before the page refreshes.

$impexconfig['system']['language']		= '/impex_language.php';
$impexconfig['system']['pagespeed']		= 1;

$impexconfig['system']['errorlogging']	= false;

define('impexdebug', false);
define('emailcasesensitive', false);
define('forcesqlmode', false);
define('skipparentids', false);
define('shortoutput', false);
define('do_mysql_fetch_assoc', false);
define('step_through', false);
define('lowercase_table_names', false);
define('use_utf8_encode', false);
define('use_utf8_decode', true);
?>
