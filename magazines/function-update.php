<?php

// ========================================================
// Includes
// ========================================================

include_once('../php/include.php');
include_once('../php/class-magazine.php');
$access_magazine = new magazine($pdo);

// ========================================================
// Main logic
// ========================================================

// Eventually need to replace with a more generic permission
if($_SESSION['can_add_livehouses']) {
	
	if(is_array($_POST) && !empty($_POST)) {
		
		// Set up magazines--post is formatted such that name[1] and romaji[1] go together
		foreach( $_POST['name'] as $index => $name ) {
			
			// Loop through rest of $_POST at same level; e.g. $series[0][name] = $_POST[name][0];
			foreach( $_POST as $key => $values ) {
				$magazines[ $index ][ $key ] = $_POST[ $key ][ $index ];
			}
			
		}
		
		// Now loop through magazines and update database
		if( is_array($magazines) && !empty($magazines) ) {
			foreach( $magazines as $magazine ) {
				
				// Now update/add magazine
				$magazine_output = $access_magazine->update_magazine( $magazine );
				
				// Add to total output
				$output['status'] = $magazine_output['status'];
				if( $magazine_output['result'] ) {
					$output['result'][] = $magazine_output['result'];
				}
				
			}
		}
		
	}
	else {
		$output['result'][] = 'No data passed.';
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to add magazines.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
$output['points'] = $points;

echo json_encode($output);