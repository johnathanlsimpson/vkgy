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
		
		// Only one artist ID is passed, but will have multiple songs,
		// so loop through them and build proper array
		foreach( $_POST['name'] as $index => $name ) {
			
			$song['name']      = $name;
			$song['artist_id'] = $_POST['artist_id'];
			$song['romaji']    = $_POST['romaji'][$index];
			$song['hint']      = $_POST['hint'][$index];
			$song['force_new'] = 1;
			
			// If we have artist and song name, add song
			if( is_numeric($song['artist_id']) && strlen($song['name']) ) {
				
				// Set up values--may or may not check for hin
				$values_check = [
					$song['artist_id'],
					sanitize($song['name']),
				];
				if( $song['hint'] ) {
					$values_check[] = sanitize($song['hint']);
				}
				
				// If song already exists, let's force them to add a hint
				$sql_check = 'SELECT id FROM songs WHERE artist_id=? AND name=? AND hint'.($song['hint'] ? '=?' : ' IS NULL').' LIMIT 1';
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute($values_check);
				$extant_id = $stmt_check->fetchColumn();
				
				// If song exists, tell them to add hint
				if( is_numeric($extant_id) ) {
					
					$output['status'] = 'error';
					$output['result'][] = 'The song <a class="symbol__song" href="/songs/artist/'.$extant_id.'/song/">'.sanitize($song['romaji'] ?: $song['name']).'</a> already exists'.($song['hint'] ? ' (with the same hint)' : null).'. Please '.($song['hint'] ? 'change' : 'fill in').' the <span class="any__note">hint</span> field to add a variant.';
					
				}
				
				// Otherwise go ahead and add it
				else {
					
					// Now edit song
					$song_output = $access_song->update_song( $song );
					
					// Add to total output
					if( $song_output['result'] ) {
						$output['result'][] = $song_output['result'];
					}
					$output['status'] = $song_output['status'];
					
				}
				
			}
			
		}
		
	}
	else {
		$output['result'][] = 'No data passed.';
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to add songs.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
$output['points'] = $points;

echo json_encode($output);