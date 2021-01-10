<?php

include_once('../php/include.php');

$access_video = new access_video($pdo);

$allowed_actions = [
	'approve',
	'approve_all',
	'delete'
];

$id = $_POST['id'];
$action = sanitize($_POST['action']);

if( $_SESSION['can_approve_data'] ) {
	
	if( is_numeric($id) ) {
		
		if( in_array($action, $allowed_actions) ) {
			
			// Approve one video
			if( $action === 'approve' ) {
				
				$sql_approve = 'UPDATE videos SET is_flagged=? WHERE id=? LIMIT 1';
				$stmt_approve = $pdo->prepare($sql_approve);
				
				if( $stmt_approve->execute([ 0, $id ]) ) {
					
					$output['status'] = 'success';
					$access_video->check_user_video_permissions($id);
					
				}
				else {
					$output['result'] = 'Couldn\'t approve video.';
				}
				
			}
			
			// Approve all user's videos and give permission to upload w/out review
			elseif( $action === 'approve_all' ) {
				
				$sql_user = 'SELECT user_id FROM videos WHERE id=?';
				$stmt_user = $pdo->prepare($sql_user);
				$stmt_user->execute([ $id ]);
				$user_id = $stmt_user->fetchColumn();
				
				if( is_numeric($user_id) ) {
					
					$access_video->give_user_video_permission($user_id);
					$output['status'] = 'success';
					
				}
				else {
					$output['result'] = 'Couldn\'t find user.';
				}
				
			}
			
			elseif( $action === 'delete' ) {
				
				$sql_delete = 'DELETE FROM videos WHERE id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				
				if( $stmt_delete->execute([ $id ]) ) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Couldn\'t delete video.';
				}
				
			}
			
		}
		else {
			$output['result'] = 'That action isn\'t allowed.';
		}
		
	}
	else {
		$output['result'] = 'No video selected for moderation.';
	}
	
}
else {
	$output['result'] = 'Only moderators can approve or delete videos.';
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);