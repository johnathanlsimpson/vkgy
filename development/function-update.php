<?php

include_once('../php/include.php');

$markdown_parser = new parse_markdown($pdo);

// Make sure signed in--other checks come later
if( $_SESSION['is_signed_in'] ) {
	
	$id = sanitize($_POST['id']);
	$title = sanitize($_POST['title']);
	$friendly = friendly($_POST['friendly'] ?: $title);
	$content = sanitize( $markdown_parser->validate_markdown($_POST['content']) );
	$content = strlen($content) ? $content : null;
	$user_id = is_numeric($_POST['user_id']) ? $_POST['user_id'] : $_SESSION['user_id'];
	$is_issue = $_POST['is_issue'] ? 1 : 0;
	$is_completed = $_POST['is_completed'] ? 1 : null;
	$issue_type = is_numeric($_POST['issue_type']) ? $_POST['issue_type'] : null;
	
	if( strlen($title) ) {
		
		// Make sure post content is provided (unless adding issue)
		if( strlen($content) || $is_issue ) {
			
			// Edit post/issue
			if( is_numeric($id) ) {
				
				// Get author of original post
				$sql_check = 'SELECT user_id, is_issue FROM development WHERE id=? LIMIT 1';
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([ $id ]);
				$extant_post = $stmt_check->fetch();
				
				// Moderators can edit issues, but only bosses can edit dev posts
				if( ( $extant_post['is_issue'] && $_SESSION['is_moderator'] ) || ( !$extant_post['is_issue'] && $_SESSION['user_id'] == $extant_post['user_id'] ) ) {
					
					$sql_update = 'UPDATE development SET title=?, content=?, friendly=?, is_completed=?, issue_type=?, user_id=? WHERE id=?';
					$stmt_update = $pdo->prepare($sql_update);
					if($stmt_update->execute([ $title, $content, $friendly, $is_completed, $issue_type, $user_id, $id ])) {
						
						$output['status'] = 'success';
						$output['friendly'] = $friendly;
						$output['title'] = $title;
						$output['url'] = '/development/'.$id.'/';
						$output['edit_url'] = '/development/'.$id.'/edit/';
						
					}
					else {
						$output['result'] = 'Couldn\'t update entry.';
					}
					
				}
				else {
					$output['result'] = 'You don\'t have permission to edit this.';
				}
				
			}
			
			// Add post/issue
			else {
				
				$sql_add = 'INSERT INTO development (title, content, friendly, user_id, is_issue, issue_type) VALUES (?, ?, ?, ?, ?, ?)';
				$stmt_add = $pdo->prepare($sql_add);
				if($stmt_add->execute([ $title, $content, $friendly, $user_id, $is_issue, $issue_type ])) {
					
					$id = $pdo->lastInsertId();
					
					$output['status'] = 'success';
					$output['friendly'] = $friendly;
					$output['title'] = $title;
					$output['url'] = '/development/'.$id.'/';
					$output['edit_url'] = '/development/'.$id.'/edit/';
					
				}
				else {
					$output['result'] = 'Couldn\'t add '.($is_issue ? 'issue' : 'entry').'.';
				}
				
			}
			
		}
		else {
			$output['result'] = 'Post content is required.';
		}
		
	}
	else {
		$output['result'] = 'Title is required.';
	}
	
}
else {
	$output['result'] = 'You don\'t have permission to do that.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);