<?php

// ========================================================
// Includes
// ========================================================

include_once('../php/include.php');
include_once('../php/class-song.php');
$access_song = new song($pdo);

// ========================================================
// Main logic
// ========================================================

if($_SESSION['can_add_data']) {
	
	if(is_array($_POST) && !empty($_POST)) {
		
		// Now edit song
		$song_output = $access_song->update_song( $_POST );
		
		// Add to total output
		$output['status'] = $song_output['status'];
		if( $song_output['result'] ) {
			$output['result'][] = $song_output['result'];
		}
		
	}
	else {
		$output['result'][] = 'No data passed.';
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to edit songs.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
$output['points'] = $points;

echo json_encode($output);