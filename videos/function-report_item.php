<?php

include_once('../php/include.php');

$allowed_report_types = [
	'no problem',
	'new user',
	'unofficial source',
	'dead link',
	'extreme content',
	'incorrect'
];

$allowed_item_types = [
	'image',
	'video',
];

$item_id = $_POST['item_id'];
$item_type = $_POST['item_type'] ?: 'video';
$report_type = $allowed_report_types[ $_POST['report_type'] ];

if( $_SESSION['is_signed_in'] ) {
	
	if( is_numeric($item_id) ) {
		
		if( in_array($item_type, $allowed_item_types) ) {
			
			if( in_array($report_type, $allowed_report_types) ) {
				
				$item_table = $item_type.'s';
				$report_type = array_search($report_type, $allowed_report_types);
				
				// All users can flag something, but only mods can unflag it (i.e. ignore 0 from non-mods)
				if( $report_type > 0 || $_SESSION['can_approve_data'] ) {
					
					$sql_update = 'UPDATE '.$item_table.' SET is_flagged=?, test=? WHERE id=? LIMIT 1';
					$stmt_update = $pdo->prepare($sql_update);
					
					if( $stmt_update->execute([ $report_type, 'set flag of video from videos/function-report_item'.print_r($_SESSION,true).print_r($_SERVER,true), $item_id ]) ) {
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t report video.';
					}
					
				}
				else {
					$output['result'] = 'Only moderators can unflag a release.';
				}
				
			}
			else {
				$output['result'] = 'That report type isn\'t allowed.';
			}
			
		}
		else {
			$output['result'] = 'That item can\'t be reported.';
		}
		
	}
	else {
		$output['result'] = 'Couldn\'t find item to report.';
	}
	
}
else {
	$output['result'] = 'Please sign in to report a video.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);