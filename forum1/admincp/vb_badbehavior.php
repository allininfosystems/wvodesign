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

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('logging');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_vb_badbehavior.php');

// ############################# LOG ACTION ###############################
if (!can_administer('canadminmodlog'))
{
	print_cp_no_permission();
}

log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
// Lookup what a key means
if ($_REQUEST['do'] == 'keycheck')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'key' => TYPE_STR
	));

	define('BB2_CORE', dirname(dirname(__FILE__)) . '/includes/bad-behavior/');
	require_once(DIR . '/includes/bad-behavior/responses.inc.php');

	$response = bb2_get_response($vbulletin->GPC['key']);

	if ($response[0] == '00000000')
	{
		echo 'Unknown';
	}
	else
	{
		echo <<<KEY
HTTP Response: $response[response]<br />\n
Explanation: $response[explanation]<br />\n
Log Message: $response[log]<br />\n
KEY;
	}
	exit;
}

// ########################################################################
print_cp_header($vbphrase['vb_badbehavior_logs']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'choose';
}

// ########################################################################
// View logs
if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'orderby'    => TYPE_STR
	));

	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	// Need to filter out any keys?
	$filterkeysql = '';

	if (!empty($vbulletin->options['vb_badbehavior_log_filter']))
	{
		$filterkeys = explode("\n", trim($vbulletin->options['vb_badbehavior_log_filter']));

		foreach ($filterkeys AS $filterkey)
		{
			$filterkeysql .= "'" . $db->escape_string($filterkey) . "',";
		}
		unset($filterkeys);

		$filterkeysql = trim($filterkeysql, ',');
	}

	//
	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "vb_badbehavior
		" . iif($filterkeysql, "WHERE `key` NOT IN($filterkeysql)") . "
	");

	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	switch ($vbulletin->GPC['orderby'])
	{
		case 'ip':
			$order = '`ip` ASC, `date` DESC';
			break;
		case 'user_agent':
			$order = '`user_agent` ASC, `date` DESC';
			break;
		case 'key':
			$order = '`key` ASC, `date` DESC';
			break;
		case 'date':
		default:
			$order = '`date` DESC';
			break;
	}

	$logs = $db->query_read("
		SELECT * 
		FROM " . TABLE_PREFIX . "vb_badbehavior
		" . iif($filterkeysql, "WHERE `key` NOT IN($filterkeysql)") . "
		ORDER BY $order
		LIMIT $startat, {$vbulletin->GPC['perpage']}
	");

	if ($db->num_rows($logs))
	{
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; $vbphrase[first_page]\" tabindex=\"1\" onclick=\"window.location='vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC[perpage]}&orderby={$vbulletin->GPC[orderby]}&page=1'\" />";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; $vbphrase[prev_page]\" tabindex=\"1\" onclick=\"window.location='vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby={$vbulletin->GPC['orderby']}&page=$prv'\" />";
		}

		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"$vbphrase[next_page] &gt;\" tabindex=\"1\" onclick=\"window.location='vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby={$vbulletin->GPC['orderby']}&page=$nxt'\" />";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"$vbphrase[last_page] &raquo;\" tabindex=\"1\" onclick=\"window.location='vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby={$vbulletin->GPC['orderby']}&page=$totalpages'\" />";
		}

		print_form_header('vb_badbehavior', 'remove');
		print_description_row(
			construct_link_code($vbphrase['restart'], "vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}"), 
			0, 9, 'thead', $stylevar['left']
		);
		print_table_header(construct_phrase(
			$vbphrase['vb_badbehavior_logs_viewer_page_x_y_there_are_z_total_log_entries'], 
			vb_number_format($vbulletin->GPC['pagenumber']), 
			vb_number_format($totalpages), 
			vb_number_format($counter['total'])
		), 9);
		print_cells_row(array(
			"<a href=\"vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby=ip&page={$vbulletin->GPC['pagenumber']}\">" . str_replace(' ', '&nbsp;', $vbphrase['ip_address']) . "</a>",
			"<a href=\"vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby=date&page={$vbulletin->GPC['pagenumber']}\">$vbphrase[date]</a>",
			"<a href=\"vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby=key&page={$vbulletin->GPC['pagenumber']}\">$vbphrase[vb_badbehavior_logs_key]</a>",
			"<a href=\"vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=view&pp={$vbulletin->GPC['perpage']}&orderby=user_agent&page={$vbulletin->GPC['pagenumber']}\">$vbphrase[vb_badbehavior_logs_useragent]</a>",
			$vbphrase['vb_badbehavior_logs_request_method'],
			$vbphrase['vb_badbehavior_logs_server_protocol'],
			$vbphrase['vb_badbehavior_logs_request_uri'],
			$vbphrase['vb_badbehavior_logs_request_entity'],
			$vbphrase['vb_badbehavior_logs_http_headers']
		), 1);

		$_title = ' title="Click inside the textbox/textarea to see full value."';

		while ($log = $db->fetch_array($logs))
		{
			$userid = bb2_log_userid($log['http_headers']);

			print_cells_row(array(
				/* IP, UserID (if applicable) */
				$log['ip'] . " <small>(<a href=\"http://who.is/whois-ip/ip-address/$log[ip]/\" target=\"_blank\">whois</a>)</small>" .  
				iif($userid !== false, "<br />UserID:<a href=\"{$vbulletin->options['bburl']}/member.php?{$vbulletin->session->vars['sessionurl']}u=$userid\" target=\"_blank\">$userid</a>"),
				/* Date */
				$log['date'],
				/* Key */
				"<a href=\"#\" onclick=\"window.open('vb_badbehavior.php?{$vbulletin->session->vars['sessionurl']}do=keycheck&key=$log[key]', 'keycheck', 'width=200,height=200');return false;\">$log[key]</a>",
				/* User Agent */
				"<input type=\"text\" value=\"$log[user_agent]\" onclick=\"alert(this.value);\"$_title />",
				/* Request Method */
				$log['request_method'],
				/* Protocol */
				$log['server_protocol'],
				/* Request URI */
				"<input type=\"text\" value=\"$log[request_uri]\" onclick=\"alert(this.value);\"$_title />",
				/* Request Entity */
				iif($log['request_entity'], "<textarea rows=\"4\" cols=\"30\" onclick=\"alert(this.value);\"$_title>$log[request_entity]</textarea>"),
				/* HTTP Headers */
				"<textarea rows=\"4\" cols=\"30\" onclick=\"alert(this.value);\"$_title>$log[http_headers]</textarea>"
			), 0, 0, -8, 'top', 0, 1);
		}
		print_table_footer(9, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
}

// ########################################################################
// Prune log
if ($_REQUEST['do'] == 'prunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('p', array(
		'daysprune' => TYPE_UINT
	));

	$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);

	$logs = $db->query_first("
		SELECT COUNT(*) AS total 
		FROM " . TABLE_PREFIX . "vb_badbehavior 
		WHERE UNIX_TIMESTAMP(date) < $datecut
	");

	if ($logs['total'])
	{
		print_form_header('vb_badbehavior', 'doprunelog');
		construct_hidden_code('daysprune', $datecut);
		print_table_header($vbphrase['prune_vb_badbehavior_logs']);
		print_description_row(construct_phrase(
			$vbphrase['are_you_sure_you_want_to_prune_x_log_entries_from_vb_badbehavior_logs'], 
			vb_number_format($logs['total'])
		));
		print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
	}
	else
	{
		print_stop_message('no_logs_matched_your_query');
	}
}

//
if ($_POST['do'] == 'doprunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('p', array(
		'daysprune' => TYPE_UINT
	));

	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "vb_badbehavior 
		WHERE UNIX_TIMESTAMP(date) < {$vbulletin->GPC['daysprune']}
	");

	define('CP_REDIRECT', 'vb_badbehavior.php?do=choose');
	print_stop_message('pruned_vb_badbehavior_logs_successfully');
}

// ########################################################################
// Modify
if ($_REQUEST['do'] == 'choose')
{
	print_form_header('vb_badbehavior', 'view');
	print_table_header($vbphrase['vb_badbehavior_logs_viewer']);
	print_description_row($vbphrase['vb_badbehavior_logs_prunenotice']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['order_by'], 'orderby', array(
		'date'       => $vbphrase['date'], 
		'user_agent' => $vbphrase['vb_badbehavior_logs_useragent'], 
		'key'        => $vbphrase['vb_badbehavior_logs_key'], 
		'ip'         => $vbphrase['ip_address']
	));
	print_submit_row($vbphrase['view'], 0);

	if (can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, ''))
	{
		print_form_header('vb_badbehavior', 'prunelog');
		print_table_header($vbphrase['prune_vb_badbehavior_logs']);
		print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
		print_submit_row($vbphrase['prune_log_entries'], 0);
	}
}

print_cp_footer();

?>