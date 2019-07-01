<?php

include_once('../php/include.php');

$allowed_methods = ['approve', 'delete', 'report'];

// Check that user is allowed
if($_SESSION['is_admin'] && is_numeric($_GET['id'])) {
	if(in_array($_GET['method'], $allowed_methods)) {
		
		// Check that video exists
		$sql_video = 'SELECT 1 FROM artists_videos WHERE id=? LIMIT 1';
		$stmt_video = $pdo->prepare($sql_video);
		$stmt_video->execute([ $_GET['id'] ]);
		$rslt_video = $stmt_video->fetchColumn();
		
		if($rslt_video) {
			// Approve
			if($_GET['method'] === 'approve') {
				$sql_approve = 'UPDATE artists_videos SET is_flagged=? WHERE id=? LIMIT 1';
				$stmt_approve = $pdo->prepare($sql_approve);
				if($stmt_approve->execute([ 0, $_GET['id'] ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = $sql_approve.'*';
				}
			}
			
			// Delete
			if($_GET['method'] === 'delete') {
				$sql_delete = 'DELETE FROM artists_videos WHERE id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				if($stmt_delete->execute([ $_GET['id'] ])) {
					$output['status']== 'success';
				}
				else {
					$output['result'] = $sql_delete.'*';
				}
			}
			
			// Report
			if($_GET['method'] === 'report') {
				$sql_report = 'UPDATE artists_videos SET is_flagged=? WHERE id=? LIMIT 1';
				$stmt_report = $pdo->prepare($sql_report);
				if($stmt_report->execute([ 1, $_GET['id'] ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = $sql_report.'*';
				}
			}
		}
		else {
			$output['result'] = 'No video';
		}
	}
	else {
		$output['result'] = 'Method not allowed';
	}
}
else {
	$output['result'] = 'Person not allowed';
}

$output['status'] = $output['status'] ?: 'success';

echo json_encode($output);