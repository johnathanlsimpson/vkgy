<?php
include_once('../php/include.php');
$comments_table = 'comments';

if(is_numeric($_POST['comment_id'])) {
	$comment_id = sanitize($_POST['comment_id']);
	
	$sql_check = 'SELECT 1, user_id, is_approved FROM '.$comments_table.' WHERE id=? LIMIT 1';
	$stmt_check = $pdo->prepare($sql_check);
	$stmt_check->execute([ $comment_id ]);
	$rslt_check = $stmt_check->fetch();
	
	if(isset($rslt_check) && !empty($rslt_check)) {
		if($_SESSION['user_id'] === $rslt_check['user_id'] || $_SESSION['is_admin']) {
			
			// For approved comments, just mark them as deleted
			if($rslt_check['is_approved']) {
				$sql_delete = 'UPDATE '.$comments_table.' SET is_deleted=? WHERE id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				
				if($stmt_delete->execute([ 1, $comment_id ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'The comment couldn\'t be deleted.';
				}
			}
			
			// ...but for non-approved comments (spam?), actually delete them
			else {
				$sql_delete = 'DELETE FROM '.$comments_table.' WHERE id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				
				if($stmt_delete->execute([ $comment_id ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'The comment couldn\'t be deleted.';
				}
			}
		}
		else {
			$output['result'] = 'You must be the original poster, or an editor, to delete comments.';
		}
	}
	else {
		$output['result'] = 'The requested comment couldn\'t be found.';
	}
}
else {
	$output['result'] = 'Comment ID empty.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = $output['result'] ?: null;
echo json_encode($output);