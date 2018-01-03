<?php

	// Set error level
	error_reporting(E_ALL & ~E_NOTICE);

	// Pre-cache
	$phrasegroups = array('logging');
	$specialtemplates = array();

	// Required includes
	require_once('./global.php');
	require_once(DIR.'/includes/functions_log_error.php');

	// Log Admin Action
	log_admin_action();

	//Finally, let's go with the code
	print_cp_header($vbphrase['glowhostspamomatic_log']);

	// Define default action
	if (empty($_REQUEST['do'])) $_REQUEST['do'] = 'choose';

	// CHOOSE
	if ($_REQUEST['do'] == 'choose') {
		print_form_header('glowhostspamomatic', 'view');
		print_table_header($vbphrase['glowhostspamomatic_log_viewer']);
		print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
		print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username'], 'email'  => $vbphrase['email'], 'date' => $vbphrase['date']));
		print_submit_row($vbphrase['view'], 0);

		if (can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '')) {
			print_form_header('glowhostspamomatic', 'prunelog');
			print_table_header($vbphrase['prune_glowhostspamomatic_logs']);
			print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
			print_submit_row($vbphrase['prune_log_entries'], 0);
		}
	}

	// PRUNE
	if ($_REQUEST['do'] == 'prunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>')) {

		$vbulletin->input->clean_array_gpc('r', array(
			'daysprune' => TYPE_UINT,
			'modaction' => TYPE_STR
		));

		$datecut = $vbulletin->GPC['daysprune'];
		$query = 'SELECT COUNT(*) AS total FROM '.TABLE_PREFIX.'glowhostspamomatic_log WHERE `date` < DATE_SUB(NOW(), INTERVAL '.$datecut.' DAY)';

		$logs = $db->query_first($query);
		if ($logs['total']) {
			print_form_header('glowhostspamomatic', 'doprunelog');
			construct_hidden_code('datecut', $datecut);

			print_table_header($vbphrase['prune_glowhostspamomatic_logs']);
			print_description_row(construct_phrase('Are you sure you wish to delete '.vb_number_format($logs['total']).' records from the logs'));
			print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
		} else {
			print_stop_message('no_logs_matched_your_query');
		}
	}

	// EXECUTE PRUNE
	if ($_POST['do'] == 'doprunelog' AND can_access_logs($vbulletin->config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>')) {

		$vbulletin->input->clean_array_gpc('p', array(
			'datecut'   => TYPE_UINT
		));

		$sqlconds = ' ';

		$db->query_write('DELETE FROM '.TABLE_PREFIX.'glowhostspamomatic_log  WHERE `date` < DATE_SUB(NOW(), INTERVAL '.$vbulletin->GPC['datecut'].' DAY); ');

		define('CP_REDIRECT', 'glowhostspamomatic.php?do=choose');
		print_stop_message('pruned_glowhostspamomatic_log_successfully');
	}

	// VIEW
	if ($_REQUEST['do'] == 'view') {

		$vbulletin->input->clean_array_gpc('r', array(
			'perpage'    => TYPE_UINT,
			'pagenumber' => TYPE_UINT,
			'orderby'    => TYPE_STR,
		));

		// Define default number of entries per page
		if ($vbulletin->GPC['perpage'] < 1) $vbulletin->GPC['perpage'] = 15;

		$counter = $db->query_first('SELECT COUNT(*) AS total FROM '.TABLE_PREFIX.'glowhostspamomatic_log');

		$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

		if ($vbulletin->GPC['pagenumber'] < 1) $vbulletin->GPC['pagenumber'] = 1;

		$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

		switch($vbulletin->GPC['orderby']) {
			case 'ip': $order = '`ip` ASC, `date` DESC'; break;

			case 'email': $order = '`email` ASC, `date` DESC'; break;

			case 'username': $order = '`username` ASC, `date` DESC'; break;

			case 'date':
			default:
				$order = 'date DESC';
				break;
		}

		$logs = $db->query_read('SELECT * FROM '.TABLE_PREFIX.'glowhostspamomatic_log
									ORDER BY '.$order.'
									LIMIT '.$startat.', '.$vbulletin->GPC['perpage']);

		if ($db->num_rows($logs)) {
			if ($vbulletin->GPC['pagenumber'] != 1) {
				$prv = $vbulletin->GPC['pagenumber'] - 1;
				$firstpage = '<input type="button" class="button" value="&laquo; '.$vbphrase['first_page'].'" tabindex="1" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page=1\'">';
				$prevpage = '<input type="button" class="button" value="&lt; '.$vbphrase['prev_page'].'" tabindex="2" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$prv.'\'">';
			}

			if ($vbulletin->GPC['pagenumber'] != $totalpages) {
				$nxt = $vbulletin->GPC['pagenumber'] + 1;
				$nextpage = '<input type="button" class="button" value="'.$vbphrase['next_page'].' &gt;" tabindex="1" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$nxt.'\'">';
				$lastpage = '<input type="button" class="button" value="'.$vbphrase['last_page'].' &raquo;" tabindex="2" onclick="window.location=\'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&modaction='.$vbulletin->GPC['modaction'].'&pp='.$vbulletin->GPC['perpage'].'&orderby='.$vbulletin->GPC['orderby'].'&page='.$totalpages.'\'">';
			}

			print_form_header('glowhostspamomatic', 'remove');
			print_description_row(construct_link_code($vbphrase['restart'], 'glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl']), 0, 6, 'thead', vB_Template_Runtime::fetchStyleVar('right'));
			print_table_header(construct_phrase($vbphrase['glowhostspamomatic_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);

			$headings = array();

			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=date&page='.$vbulletin->GPC['pagenumber'].'">'.$vbphrase['date'].'</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=ip&page='.$vbulletin->GPC['pagenumber'].'">IP Address</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=username&page='.$vbulletin->GPC['pagenumber'].'">'.str_replace(' ', '&nbsp;', $vbphrase['username']).'</a>';
			$headings[] = '<a href="glowhostspamomatic.php?'.$vbulletin->session->vars['sessionurl'].'do=view&pp='.$vbulletin->GPC['perpage'].'&orderby=email&page='.$vbulletin->GPC['pagenumber'].'">'.$vbphrase['email'].'</a>';

			$headings[] = str_replace(' ', '&nbsp;', 'Message');

			print_cells_row($headings, 1);

			while ($log = $db->fetch_array($logs)) {

				$cell = array();

				$cell[] = '<span class="smallfont">'.$log['date'].'</span>';
				$cell[] = '<span class="smallfont">'.$log['ip'].'</span>';

				if ($log['user_id'] > 0) { //user exist
						$cell[] = '<span class="smallfont"><a href="user.php?do=edit&u='.$log['user_id'].'"><b>'.$log['username'].'</b></a></span>';
				} else {
						$cell[] = '<span class="smallfont"><b>'.$log['username'].'</b></span>';
				}

				$cell[] = '<span class="smallfont">'.$log['email'].'</span>';
				$cell[] = '<span class="smallfont">'.$log['message'].'</span>';

				print_cells_row($cell, 0, 0, -4);
			}

			print_table_footer(6, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
		} else {
			print_stop_message('no_results_matched_your_query');
		}
	}

	print_cp_footer();
?>