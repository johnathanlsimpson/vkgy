<?php
include_once('../php/include.php');
$markdown_parser = new parse_markdown($pdo);
$access_comment = new access_comment($pdo);
$comments_table = 'comments';

if(strlen($_POST['content']) && !strlen($_POST['email']) && !strlen($_POST['website'])) {
	if(is_numeric($_POST['item_id']) && in_array($_POST['item_type'], $access_comment->comment_types)) {
		
		// Check that specified entry exists
		if($_POST['item_type'] != 'none') {
			$item_table = $_POST['item_type'].($_POST['item_type'] === 'artist' || $_POST['item_type'] === 'release' ? 's' : null);
			$sql_check = 'SELECT 1 FROM '.$item_table.' WHERE id=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $_POST['item_id'] ]);
			$item_exists = $stmt_check->fetchColumn();
		}
		
		if($item_exists || $_POST['item_type'] === 'none') {
			
			// Set variables
			$item_id      = sanitize($_POST['item_id']);
			$item_type    = array_search($_POST['item_type'], $access_comment->comment_types);
			$comment_id   = is_numeric($_POST['comment_id']) ? sanitize($_POST['comment_id']) : null;
			$thread_id    = is_numeric($_POST['thread_id']) ? sanitize($_POST['thread_id']) : null;
			$user_id      = $_SESSION['loggedIn'] ? $_SESSION['userID'] : null;
			$is_signed_in = is_numeric($user_id);
			$is_admin     = $_SESSION['is_admin'];
			$is_approved  = 1;
			$is_deleted   = 0;
			
			// If anonymous (not signed in), set "anonymous id," set to queue, and set handlename if present
			if(!$is_signed_in) {
				
				// Set anonymous ID
				$anonymous_id = $_COOKIE['anonymous_id'] ?: uniqid();
				
				// Try to sign in
				if(strlen($_POST["username"]) && strlen($_POST["password"])) {
					$sign_in = new login($pdo);
					$sign_in->sign_in($_POST);
					
					if($sign_in->check_login()) {
						$user_id = $_SESSION['userID'];
						$is_signed_in = true;
					}
					else {
						$output['result'][] = $sign_in->get_status_message();
					}
				}
				
				// If not signing in, or signin failed, post anonymously
				if(!$is_signed_in) {
					$is_approved = 0;
					$name = sanitize($_POST['name']) ?: null;
					
					if(!$_COOKIE['anonymous_id']) {
						setcookie('anonymous_id', $anonymous_id, time() + (60 * 60 * 24 * 7), '/', 'vk.gy');
					}
				}
			}
			
			// Format comment content
			$content = $_POST['content'];
			$content = str_replace(["\r\n", "\r"], "\n", $content);
			$content = trim($content);
			$content = $markdown_parser->validate_markdown($content);
			$content = sanitize($content);
			
			// If trying to edit comment
			if(is_numeric($comment_id)) {
				
				// If comment ID is provided, check that it exists, then check permission
				if($anonymous_id) {
					$sql_check_user = 'SELECT anonymous_id FROM '.$comments_table.' WHERE id=? LIMIT 1';
				}
				else {
					$sql_check_user = 'SELECT user_id FROM '.$comments_table.' WHERE id=? LIMIT 1';
				}
				
				$stmt_check_user = $pdo->prepare($sql_check_user);
				$stmt_check_user->execute([ $comment_id ]);
				$rslt_check_user = $stmt_check_user->fetchColumn();
				
				$edit_is_allowed = strlen($rslt_check_user) && $rslt_check_user === ($anonymous_id ?: $user_id);
				
				// Edit...
				if($edit_is_allowed) {
					$sql_edit = 'UPDATE '.$comments_table.' SET content=?, name=?, user_id=?, is_deleted=?, is_approved=? WHERE id=? LIMIT 1';
					$stmt_edit = $pdo->prepare($sql_edit);
					
					if($stmt_edit->execute([ $content, $name, $user_id, $is_deleted, $is_approved, $comment_id ])) {
						$output['status'] = 'success';
					}
					else {
						$output['result'][] = 'The comment couldn\'t be updated.';
					}
				}
				else {
					$output['result'][] = 'User doesn\'t have permission to edit this comment.';
				}
			}
			
			// ...or if trying to add comment
			else {
				$sql_add = 'INSERT INTO '.$comments_table.' (user_id, anonymous_id, thread_id, content, name, item_id, item_type, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
				$stmt_add = $pdo->prepare($sql_add);
				
				if($stmt_add->execute([ $user_id, $anonymous_id, $thread_id, $content, $name, $item_id, $item_type, $is_approved ])) {
					$output['status'] = 'success';
					$comment_id = $pdo->lastInsertId();
					
					// Award point
					$access_points = new access_points($pdo);
					$access_points->award_points([ 'point_type' => 'added-comment' ]);
				}
				else {
					$output['result'][] = 'The comment couldn\'t be added.';
				}
			}
			
			// If comment added/edited, return data
			if($output['status'] === 'success') {
				$output['thread_id'] = $thread_id;
				$output['item_type'] = sanitize($_POST['item_type']);
				$output['item_id'] = $item_id;
				$output['comment_id'] = $comment_id;
				$output['date_occurred'] = date('Y-m-d H:i:s');
				$output['content'] = $markdown_parser->parse_markdown($content);
				$output['markdown'] = $content;
				$output['is_admin'] = $_SESSION['is_admin'] ? '1' : '0';
				$output['is_user'] = '1';
				$output['is_approved'] = $is_approved ? '1' : '0';
				$output['username'] = $_SESSION['username'] ?: 'anonymous';
				$output['name'] = $name;
			}
		}
		else {
			$output['result'][] = 'The specified entry doesn\'t exist.';
		}
	}
	else {
		$output['result'][] = 'Entry and entry type must be specified.';
	}
}
else {
	$output['result'][] = 'Comment field is empty.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = $output['result'] && is_array($output['result']) ? implode('<br />', $output['result']) : ($output['result'] ?: null);
echo json_encode($output);