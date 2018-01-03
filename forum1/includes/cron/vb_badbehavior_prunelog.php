<?php

/**
* @package	vB Bad Behavior (vB4)
* @author	Eric Sizemore <admin@secondversion.com>
* @version	1.0.13
* @license	GNU LGPL http://www.gnu.org/licenses/lgpl.txt
* 
*	vB Bad Behavior - Integrates vBulletin and Bad Behavior
*	Copyright (C) 2011 - 2013 Eric Sizemore
*
*	vB Bad Behavior is free software; you can redistribute it and/or modify it under
*	the terms of the GNU Lesser General Public License as published by the Free
*	Software Foundation; either version 3 of the License, or (at your option) any
*	later version.
*
*	This program is distributed in the hope that it will be useful, but WITHOUT ANY
*	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
*	PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
*
*	You should have received a copy of the GNU Lesser General Public License along
*	with this program. If not, see <http://www.gnu.org/licenses/>.
*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################## REQUIRE BACK-END ############################

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "vb_badbehavior WHERE UNIX_TIMESTAMP(`date`) < " . (TIMENOW - (7 * 60 * 60 * 24)));

log_cron_action('', $nextitem, 1);

?>