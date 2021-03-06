<?php
include_once('../php/include.php');
$markdown_parser = new parse_markdown($pdo);
$access_comment = new access_comment($pdo);
$comments_table = 'comments';

if(strlen($_POST['content']) && !strlen($_POST['email']) && !strlen($_POST['website'])) {
	if(is_numeric($_POST['item_id']) && in_array($_POST['item_type'], $access_comment->comment_types)) {
		
		// Check that specified entry exists
		if($_POST['item_type'] != 'none') {
			$item_table = $_POST['item_type'].($_POST['item_type'] === 'artist' || $_POST['item_type'] === 'release' || $_POST['item_type'] === 'video' ? 's' : null);
			$sql_check = 'SELECT 1 FROM '.$item_table.' WHERE id=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $_POST['item_id'] ]);
			$item_exists = $stmt_check->fetchColumn();
		}
		
		if($item_exists || $_POST['item_type'] === 'none' || $_POST['item_type'] === 'vip') {
			
			// Set variables
			$item_id      = sanitize($_POST['item_id']);
			$item_type    = array_search($_POST['item_type'], $access_comment->comment_types);
			$comment_id   = is_numeric($_POST['comment_id']) ? sanitize($_POST['comment_id']) : null;
			$thread_id    = is_numeric($_POST['thread_id']) ? sanitize($_POST['thread_id']) : null;
			$user_id      = $_SESSION['is_signed_in'] ? $_SESSION['user_id'] : null;
			$is_signed_in = is_numeric($user_id);
			$is_admin     = $_SESSION['can_approve_data'];
			$is_approved  = 1;
			$is_deleted   = 0;
			
			// If anonymous (not signed in), set "anonymous id," set to queue, and set handlename if present
			if(!$is_signed_in) {
				
				// Set anonymous ID
				$anonymous_id = $_SESSION['anonymous_id'] ?: ($_COOKIE['anonymous_id'] ?: uniqid());
				
				// Try to sign in
				if(strlen($_POST['username']) && strlen($_POST['password'])) {
					
					if($_POST['sign_in_type'] === 'sign-in') {
					
						$sign_in = new login($pdo);
						$sign_in->sign_in($_POST);
						
						if($sign_in->check_login()) {
							$user_id = $_SESSION['userID'];
							$is_signed_in = true;
							$new_sign_in = true;
						}
						else {
							$output['result'][] = $sign_in->get_status_message();
						}
						
					}
					
					// Register new account while commenting
					elseif($_POST['sign_in_type'] === 'register') {
						
						ob_start();
						$_POST['register_username'] = $_POST['username'];
						$_POST['register_password'] = $_POST['password'];
						include('../account/function-register.php');
						ob_end_clean();
						
						// Sign in new account
						if(is_numeric($user_id)) {
							
							$sign_in = new login($pdo);
							$sign_in->sign_in($_POST);
							
							if($sign_in->check_login()) {
								$is_signed_in = true;
								$new_sign_in = true;
								$output['redirect_url'] = '/account/';
							}
							else {
								$output['result'] = $sign_in->get_status_message();
							}
							
							// Technically this is running as a comment edit, but since it's the user's first comment, let's make sure they get a point
							$access_points = new access_points($pdo);
							$output['points'] += $access_points->award_points([ 'point_type' => 'added-comment' ]);
							
						}
						else {
							$output['result'] = is_array($output['result']) ? $output['result'] : [ $output['result'] ];
						}
						
					}
				}
				
				// If not signing in, or signin failed, post anonymously
				if(!$is_signed_in) {
					$is_approved = 0;
					$name = sanitize($_POST['name']) ?: null;
					
					if(!$_SESSION['anonymous_id']) {
						$_SESSION['anonymous_id'] = $anonymous_id;
					}
					
					if(!$_COOKIE['anonymous_id']) {
						setcookie('anonymous_id', $anonymous_id, time() + (60 * 60 * 24 * 7), '/', 'vk.gy');
					}
				}
			}
			
			// This is kinda messy, but when newly registering after making a comment, it sets $name, so undo
			$name = $_SESSION['is_signed_in'] ? null : $name;
			
			// Clean up comment content
			$content = $_POST['content'];
			$content = str_replace(["\r\n", "\r"], "\n", $content);
			
			// Allow single line breaks for JP users. To do this, add a space+space
			// at the end of each line--Markdown will decide which cases to put <br />
			// and which to leave alone. Note that we should strip these when editing comment
			// Also make sure advanced users can still do manual space+backslash
			$content = str_replace("\n", "  \n", $content);
			$content = str_replace("\\  \n", "\\\n", $content);
			
			// Clean comment more and validate
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
					$output['points'] += $access_points->award_points([ 'point_type' => 'added-comment' ]);
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
				$output['is_admin'] = $_SESSION['can_approve_data'] ? '1' : '0';
				$output['is_user'] = '1';
				$output['is_approved'] = $is_approved ? '1' : '0';
				$output['username'] = $_SESSION['username'] ?: 'anonymous';
				$output['name'] = $name;
				
				// Trim trailing spaces from Markdown, which we put there to allow single linebreaks
				$output['markdown'] = explode("\n", $content);
				foreach($output['markdown'] as $line_key => $line) {
					$output['markdown'][$line_key] = trim($line);
				}
				$output['markdown'] = implode("\n", $output['markdown']);
				
				// If newly signed in (i.e. commented anonymously, then signed in), attribute comment to user
				if($new_sign_in) {
					$sql_attribute = 'UPDATE comments SET user_id=? WHERE id=? LIMIT 1';
					$stmt_attribute = $pdo->prepare($sql_attribute);
					$stmt_attribute->execute([ $_SESSION['user_id'], $comment_id ]);
				}
				
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