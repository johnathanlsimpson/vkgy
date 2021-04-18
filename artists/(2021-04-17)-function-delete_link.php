<?php

include_once('../php/include.php');

if( $_SESSION['can_delete_data'] ) {
	
	$link_id = $_POST['link_id'];
	
	if( is_numeric($link_id) ) {
		
		$sql_delete = 'DELETE FROM artists_urls WHERE id=? LIMIT 1';
		$stmt_delete = $pdo->prepare($sql_delete);
		
		if( $stmt_delete->execute([ $link_id ]) ) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = 'The link couldn\'t be deleted.';
		}
		
	}
	else {
		$output['result'] = 'No link specified.';
	}
	
}
else {
	$output['result'] = 'Sorry, you don\'t have permission to delete links.';
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);