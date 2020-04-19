<?php

include_once('../php/include.php');

// Only registered users can tag, for now
if($_SESSION['is_signed_in']) {
	if(in_array($type, ['artist', 'musician', 'release'])) {
		
		// Set up vars
		$item_id = is_numeric($_POST['id']) ? sanitize($_POST['id']) : null;
		$tag_id = is_numeric($_POST['tag_id']) ? sanitize($_POST['tag_id']) : null;
		$user_id = $_SESSION['user_id'];
		
		// $type is passed from another function
		$tag_table = $type.'s_tags';
		$item_key = $type.'_id';
		$point_type = 'tagged-'.$type;
		
		// Check whether tag is admin
		$sql_admin_check = 'SELECT 1 FROM tags_'.$type.'s WHERE id=? AND is_admin_tag=? LIMIT 1';
		$stmt_admin_check = $pdo->prepare($sql_admin_check);
		$stmt_admin_check->execute([ $tag_id, 1 ]);
		$is_admin_tag = $stmt_admin_check->fetchColumn();
		
		// Check whether user has already used tag
		$sql_check = 'SELECT 1 FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=? LIMIT 1';
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $item_id, $tag_id, $user_id ]);
		$is_already_tagged = $stmt_check->fetchColumn();
		
		// Remove all instances of an admin tag
		if($is_admin_tag && $_POST['action'] === 'delete') {
			if($_SESSION['is_moderator']) {
				
				$sql_untag_admin = 'DELETE FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=?';
				$stmt_untag_admin = $pdo->prepare($sql_untag_admin);
				if($stmt_untag_admin->execute([ $item_id, $tag_id ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Couldn\'t remove admin tag.';
				}
				
			}
			else {
				$output['result'] = 'You don\'t have permission to remove that tag.';
			}
			
		}
		
		// Add/remove tags
		else {
			
			// Delete normal tag
			if(!$is_admin_tag && $is_already_tagged) {
				
				$sql_untag = 'DELETE FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=?';
				$stmt_untag = $pdo->prepare($sql_untag);
				if($stmt_untag->execute([ $item_id, $tag_id, $user_id ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Couldn\'t remove tag.';
				}
				
			}
			
			// Add normal or admin tag
			elseif(!$is_already_tagged) {
				
				// Make sure user has permission to add
				if(!$is_admin_tag || ($is_admin_tag && $_SESSION['is_moderator'])) {
					
					$sql_add = 'INSERT INTO '.$tag_table.' ('.$item_key.', tag_id, user_id) VALUES (?, ?, ?)';
					$stmt_add = $pdo->prepare($sql_add);
					if($stmt_add->execute([ $item_id, $tag_id, $user_id ])) {
						
						// Make sure tag element has a checkmark by it afterward
						$output['is_checked'] = '1';
						$output['status'] = 'success';
						
						// Award point
						$access_points = new access_points($pdo);
						$access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
						
					}
					else {
						$output['result'] = 'Couldn\'t add tag.';
					}
					
				}
				else {
					$output['result'] = 'You don\'t have permission to add that tag.';
				}
				
			}
			
			// Error
			else {
				$output['result'] = 'Couldn\'t update tag.';
			}
			
		}
		
	}
	else {
		$output['result'] = 'Can\'t tag this type of item.';
	}
}
else {
	$output['result'] = 'Must be signed in to tag items.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);