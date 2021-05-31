<?php

// ========================================================
// Includes
// ========================================================

include_once('../php/include.php');
include_once('../php/class-issue.php');

$access_issue = new issue($pdo);

// ========================================================
// Main logic
// ========================================================

if($_SESSION['can_add_data']) {
	
	$issue = $_POST;
	
	if( is_array($issue) && !empty($issue) ) {
		
		// Update/add issue
		$issue_output = $access_issue->update_issue( $issue );
		
		// Add to total output
		$output = $issue_output;
		$output['is_new'] = strlen($_POST['id']) ? 0 : 1;
		
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to update magazines.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;

echo json_encode($output);