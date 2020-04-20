<?php

include_once('../php/include.php');

// Only registered users can tag, for now
if($_SESSION['is_signed_in']) {
	
	// Set item type
	$item_type = sanitize($_POST['item_type']);
	
	if(in_array($item_type, ['artist', 'musician', 'release'])) {
		
		// Set up vars
		$item_id = is_numeric($_POST['id']) ? sanitize($_POST['id']) : null;
		$tag_id = is_numeric($_POST['tag_id']) ? sanitize($_POST['tag_id']) : null;
		$user_id = $_SESSION['user_id'];
		
		// $item_type is passed from another function
		$tag_table = $item_type.'s_tags';
		$item_key = $item_type.'_id';
		$point_type = 'tagged-'.$item_type;
		
		// Check whether user has already used tag
		$sql_check = 'SELECT 1 FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=? LIMIT 1';
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $item_id, $tag_id, $user_id ]);
		$is_already_tagged = $stmt_check->fetchColumn();
		
		// Remove all instances of an admin tag
		if($_POST['action'] === 'permanent_delete') {
			
			if($_SESSION['can_approve_data']) {
				
				$sql_untag_admin = 'DELETE FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=?';
				$stmt_untag_admin = $pdo->prepare($sql_untag_admin);
				if($stmt_untag_admin->execute([ $item_id, $tag_id ])) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Couldn\'t remove tag.';
				}
				
			}
			else {
				$output['result'] = 'You don\'t have permission to remove that tag.';
			}
			
		}
		
		// Add/remove tags
		else {
			
			// Get attributes of tag so we can check permission
			$sql_permissions = 'SELECT * FROM tags_'.$item_type.'s WHERE id=? LIMIT 1';
			$stmt_permissions = $pdo->prepare($sql_permissions);
			$stmt_permissions->execute([ $tag_id ]);
			$tag = $stmt_permissions->fetch();
			
			// If tag exists
			if(is_array($tag) && !empty($tag)) {
				
				// And if user has permission to add/delete that tag
				if(
					!$tag['requires_permission']
					||
					($tag['requires_permission'] && $_SESSION[ $tag['requires_permission'] ])
				) {
					
					// If user has already used that tag for that item, delete it
					if($is_already_tagged) {
						
						$sql_untag = 'DELETE FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=?';
						$stmt_untag = $pdo->prepare($sql_untag);
						if($stmt_untag->execute([ $item_id, $tag_id, $user_id ])) {
							$output['status'] = 'success';
						}
						else {
							$output['result'] = 'Couldn\'t remove tag.';
						}
						
					}
					
					// If user hasn't used that tag for that item, add it
					else {
						
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
					
				}
				else {
					$output['result'] = 'You don\'t have permission to add that tag.';
				}
				
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