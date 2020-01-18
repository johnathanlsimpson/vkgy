<?php

include_once('../php/include.php');

if($_SESSION['is_signed_in'] && $_SESSION['is_vip'] && is_numeric($_POST['id'])) {
	
	$sql_delete = 'DELETE FROM vip WHERE id=? LIMIT 1';
	$stmt_delete = $pdo->prepare($sql_delete);
	if($stmt_delete->execute([ sanitize($_POST['id']) ])) {
		$output['status'] = 'success';
	}
	else {
		$output['result'] = 'Couldn\'t delete entry.';
	}
	
}
else {
	$output['result'] = 'You don\'t have permission to delete this entry.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);