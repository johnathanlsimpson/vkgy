<?php

include_once('../php/include.php');

$artist_id = is_numeric($_POST['artist_id']) ? $_POST['artist_id'] : null;
$musician_id = is_numeric($_POST['musician_id']) ? $_POST['musician_id'] : null;
$image_id = is_numeric($_POST['image_id']) ? $_POST['image_id'] : null;

if( $_SESSION['can_add_data'] ) {
	
	if( is_numeric($artist_id) && is_numeric($musician_id) ) {
		
		$sql_update = 'UPDATE artists_musicians SET image_id=? WHERE artist_id=? AND musician_id=? LIMIT 1';
		$stmt_update = $pdo->prepare($sql_update);
		
		if( $stmt_update->execute([ $image_id, $artist_id, $musician_id ]) ) {
			
			$output['status'] = 'success';
			
		}
		
	}
	
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);