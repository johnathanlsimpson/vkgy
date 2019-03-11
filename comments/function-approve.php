<?php
include_once('../php/include.php');
$comments_table = 'comments';

if(is_numeric($_POST['comment_id'])) {
	if($_SESSION['loggedIn'] && $_SESSION['admin']) {
		$sql_approve = 'UPDATE '.$comments_table.' SET is_approved=? WHERE id=? LIMIT 1';
		$stmt_approve = $pdo->prepare($sql_approve);
		
		if($stmt_approve->execute([ 1, sanitize($_POST['comment_id']) ])) {
			$output['status'] = 'success';
			$output['result'] = 'Comment approved.';
		}
		else {
			$output['result'] = 'Comment couldn\'t be approved.';
		}
	}
	else {
		$output['result'] = 'You must be an editor to approve comments.';
	}
}
else {
	$output['result'] = 'Comment ID empty.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = $output['result'] ?: null;
echo json_encode($output);