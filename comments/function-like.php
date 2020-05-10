<?php
include_once('../php/include.php');
$access_points = new access_points($pdo);

if(is_numeric($_POST['comment_id'])) {
	$comment_id = sanitize($_POST['comment_id']);
	$user_id = $_SESSION['user_id'] ?: null;
	$action = $_POST['action'] === 'add' ? 'add' : 'remove';
	
	if(!is_numeric($user_id)) {
		if(!$_COOKIE['anonymous_id']) {
			setcookie('anonymous_id', $anonymous_id, time() + (60 * 60 * 24 * 7), '/', 'vk.gy');
		}
		
		$anonymous_id = $_COOKIE['anonymous_id'] ?: uniqid();
	}
	
	$user_type = is_numeric($user_id) ? 'user' : (strlen($anonymous_id) ? 'anonymous' : null);
	
	if($user_type) {
		$sql_check = 'SELECT 1 FROM comments_likes WHERE comment_id=? AND '.$user_type.'_id=? LIMIT 1';
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $comment_id, ($user_type === 'user' ? $user_id : $anonymous_id) ]);
		$rslt_check = $stmt_check->fetchColumn();
		
		// Remove like
		if($rslt_check && $action === 'remove') {
			$sql_remove = 'DELETE FROM comments_likes WHERE comment_id=? AND '.$user_type.'_id=? LIMIT 1';
			$stmt_remove = $pdo->prepare($sql_remove);
			
			if($stmt_remove->execute([ $comment_id, ($user_type === 'user' ? $user_id : $anonymous_id) ])) {
				$output['status'] = 'success';
				$output['result'] = 'Like removed.';
			}
		}
		
		// Add like
		elseif(!$rslt_check && $action === 'add') {
			$sql_add = 'INSERT INTO comments_likes (comment_id, '.$user_type.'_id) VALUES (?, ?)';
			$stmt_add = $pdo->prepare($sql_add);
			
			if($stmt_add->execute([ $comment_id, ($user_type === 'user' ? $user_id : $anonymous_id) ])) {
				$output['status'] = 'success';
				$output['result'] = 'Comment liked.';
				
				// Get user who made liked comment
				$sql_op = 'SELECT user_id FROM comments WHERE id=? LIMIT 1';
				$stmt_op = $pdo->prepare($sql_op);
				$stmt_op->execute([ $comment_id ]);
				$op_id = $stmt_op->fetchColumn();
				
				// Award point
				if(is_numeric($_SESSION['user_id']) && $op_id != $_SESSION['user_id']) {
					
					// Award point to person who liked comment
					$output['points'] += $access_points->award_points([ 'point_type' => 'liked-comment', 'allow_multiple' => false, 'item_id' => $comment_id ]);
					
					// Award point to person who received like
					$access_points->award_points([ 'point_type' => 'comment-liked', 'allow_multiple' => false, 'item_id' => $comment_id, 'user_id' => $op_id ]);
					
				}
			}
		}
		
		// ...Or return error
		else {
			$output['result'] = 'Couldn\'t update like.';
		}
		
		// Set other return data
		if($output['status'] === 'success') {
			$sql_data = 'SELECT COUNT(*) FROM comments_likes WHERE comment_id=? GROUP BY comment_id';
			$stmt_data = $pdo->prepare($sql_data);
			$stmt_data->execute([ $comment_id ]);
			$rslt_data = $stmt_data->fetchColumn();
			
			$output['num_likes'] = $rslt_data;
		}
	}
	else {
		$output['result'] = 'Couldn\'t like comment.';
	}
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = $output['result'] ?: null;
echo json_encode($output);