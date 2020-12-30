<?php

include_once('../php/include.php');

$id = $_POST['id'];
$is_completed = $_POST['is_completed'] ? 1 : 0;

// Only mods can change issue status
if( $_SESSION['is_moderator'] && is_numeric($id) ) {
	
	// Check that issue exists
	$sql_check = 'SELECT 1 FROM development WHERE id=? AND is_issue=?';
	$stmt_check = $pdo->prepare($sql_check);
	$stmt_check->execute([ $id, 1 ]);
	
	// If issue exists, update status
	if($stmt_check->fetchColumn()) {
		
		$sql_update = 'UPDATE development SET is_completed=? WHERE id=?';
		$stmt_update = $pdo->prepare($sql_update);
		
		if( $stmt_update->execute([ $is_completed, $id ]) ) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = 'Couldn\'t update issue.';
		}
		
	}
	else {
		$output['result'] = 'Issue doesn\'t exist.';
	}
	
}
else {
	$output['result'] = 'Only moderators can change issue status.';
}

$output['result'] .= print_r($_POST, true);

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);