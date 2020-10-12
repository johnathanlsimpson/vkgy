<?php

include_once('../php/include.php');

$access_artist = new access_artist($pdo);
$access_video = new access_video($pdo);

$allowed_methods = ['approve', 'delete', 'report'];

$id = $_GET['id'] ?: ($_POST['id'] ?: null);
$method = $_GET['method'] ?: ($_POST['method'] ?: null);
$artist_id = $_GET['artist_id'] ?: ($_POST['artist_id'] ?: null);
$channel_id = $_GET['channel_id'] ?: ($_POST['channel_id'] ?: null);

// Check that user is allowed
if(is_numeric($id)) {
	if(in_array($method, $allowed_methods)) {
		
		// Check that video exists
		$sql_video = 'SELECT 1 FROM videos WHERE id=? LIMIT 1';
		$stmt_video = $pdo->prepare($sql_video);
		$stmt_video->execute([ $id ]);
		$rslt_video = $stmt_video->fetchColumn();
		
		if($rslt_video) {
			// Approve
			if($method === 'approve' && $_SESSION['can_approve_data']) {
				$sql_approve = 'UPDATE videos SET is_flagged=? WHERE id=? LIMIT 1';
				$stmt_approve = $pdo->prepare($sql_approve);
				if($stmt_approve->execute([ 0, $id ])) {
					$output['status'] = 'success';
					
					// If we've approved 5 videos added by a certain user, spread across 5 artists, then remove the need for them to get approval
					$access_video->check_user_video_permissions($id);
					
					// If artist ID and channel ID provided
					if(is_numeric($artist_id) && strlen($channel_id)) {
							
						// If channel not whitelisted already, add to official artist links
						$sql_check_artist = 'SELECT 1 FROM artists_urls WHERE artist_id=? AND content LIKE CONCAT("%", ?, "%") LIMIT 1';
						$stmt_check_artist = $pdo->prepare($sql_check_artist);
						$stmt_check_artist->execute([ $artist_id, 'youtube.com/channel/'.sanitize($channel_id) ]);
						
						if($stmt_check_artist->fetchColumn()) {
							$output['result'] = 'Found artist with channel in official links.';
						}
						else {
							$channel_url = 'https://youtube.com/channel/'.sanitize($channel_id).'/';
							
							// Add channel link to artist
							//$sql_update_artist = 'UPDATE artists SET official_links=IF(official_links IS NULL, ?, CONCAT_WS("\n", official_links, ?)) WHERE id=? LIMIT 1';
							//$stmt_update_artist = $pdo->prepare($sql_update_artist);
							//if($stmt_update_artist->execute([ $channel_url, $channel_url, $artist_id ])) {
							if($access_artist->update_url($artist_id, $channel_url)) {
								$output['result'] = 'Added channel to whitelist.';
							}
							else {
								$output['result'] = 'Couldn\'t add channel to artist\'s links.';
							}
							
						}
					}
					else {
						$output['result'] = 'No artist/channel provided.';
					}
				}
				else {
					$output['result'] = 'Video couldn\'t be approved.';
				}
			}
			
			// Delete
			if($method === 'delete' && $_SESSION['can_delete_data']) {
				$sql_delete = 'DELETE FROM videos WHERE id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				if($stmt_delete->execute([ $id ])) {
					$output['status']== 'success';
				}
				else {
					$output['result'] = 'Video couldn\'t be deleted.';
				}
			}
			
			// Report
			if($method === 'report') {
				$sql_report = 'UPDATE videos SET is_flagged=? WHERE id=? LIMIT 1';
				$stmt_report = $pdo->prepare($sql_report);
				if($stmt_report->execute([ 1, $id ])) {
					$output['status'] = 'success';
					$output['result'] = lang('Reported. Thank you.', '報告されました。 ありがとうございました。', 'hidden');
				}
				else {
					$output['result'] = 'Video couldn\'t be reported.';
				}
			}
		}
		else {
			$output['result'] = 'YT video not found.';
		}
	}
	else {
		$output['result'] = 'Method not allowed.';
	}
}
else {
	$output['result'] = 'No ID supplied.';
}

$output['status'] = $output['status'] ?: 'success';

echo json_encode($output);