<?php

include_once('../php/include.php');

$id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$artist_id = is_numeric($_POST['artist_id']) ? '('.$_POST['artist_id'].')' : null;
$artist_name = sanitize($_POST['artist_name']) ?: null;

if( $_SESSION['is_signed_in'] && is_numeric($id) && strlen($artist_id) ) {
	
	$sql_update = 'UPDATE queued_flyers SET artist_id=?, description=? WHERE id=? LIMIT 1';
	$stmt_update = $pdo->prepare($sql_update);
	if($stmt_update->execute([ $artist_id, $artist_name.' flyer', $id ])) {
		$output['status'] = 'success';
	}
	
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);