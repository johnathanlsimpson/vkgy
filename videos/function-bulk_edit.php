<?php

include_once('../php/include.php');
$access_video = new access_video($pdo);

if($_SESSION['can_approve_data']) {
	
	$ids = $_POST['ids'];
	
	// Make sure all IDs are numeric
	if( is_array($ids) && !empty($ids) ) {
		foreach($ids as $id_key => $id) {
			if( !is_numeric($id) ) {
				unset($ids[$id_key]);
			}
		}
	}
	
	// Make sure action is allowed
	$allowed_actions = [ 'approve', 'change_type' ];
	$action = sanitize($_POST['action']);
	$action = in_array($action, $allowed_actions) ? $action : null;
	
	// If action is change_type, make sure type is specified
	$type = sanitize($_POST['type']);
	$type = isset($access_video->video_types[$type]) ? $access_video->video_types[$type] : 0;
	
	// Loop through and perform action
	if( $action && is_array($ids) && !empty($ids) ) {
		
		// Values
		$values_edit[] = $action === 'approve' ? 0 : ( $action === 'change_type' ? $type : null );
		$values_edit = array_merge($values_edit, $ids);
		
		// Query
		$sql_edit =
			'UPDATE videos '.
			'SET '.($action === 'approve' ? 'is_flagged=?' : null).($action === 'change_type' ? 'type=?' : null).' '.
			'WHERE ('.substr(str_repeat('id=? OR ', count($ids)), 0, -4).')';
		$stmt_edit = $pdo->prepare($sql_edit);
		
		// Execute
		if($stmt_edit->execute($values_edit)) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = 'Couldn\'t update videos.';
		}
		
	}
	else {
		$output['result'] = 'No action or id.';
	}
	
}
else {
	$output['result'] = 'You don\'t have permission to bulk edit videos.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);