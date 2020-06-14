<?php

include_once('../php/include.php');

$access_points = new access_points($pdo);

// Only registered users can tag, for now
if($_SESSION['is_signed_in']) {
	
	// Set up vars
	$vote_type  = in_array($_POST['vote'], ['upvote', 'downvote', 'mod_upvote', 'mod_downvote']) ? sanitize($_POST['vote']) : null;
	$action     = $_POST['action'] === 'add' ? 'add' : 'remove';
	$item_type  = in_array($_POST['item_type'], ['artist', 'musician', 'release']) ? sanitize($_POST['item_type']) : null;
	$item_id    = is_numeric($_POST['id']) ? sanitize($_POST['id']) : null;
	$tag_id     = is_numeric($_POST['tag_id']) ? sanitize($_POST['tag_id']) : null;
	$user_id    = $_SESSION['user_id'];
	
	// If moderator is permanently upvoting/downvoting, set add'l flag then change to normal up/down
	if( strpos($vote_type, 'mod_') === 0 && $_SESSION['can_approve_data'] ) {
		$mod_agrees = $vote_type === 'mod_upvote' ? 1 : -1;
		$vote_type = str_replace('mod_', '', $vote_type);
	}
	
	// Set agrees flag
	$user_agrees = is_numeric($mod_agrees) ? $mod_agrees : ($vote_type === 'upvote' ? 1 : -1);
	$mod_agrees  = is_numeric($mod_agrees) ? $mod_agrees : 0;
	
	// Make sure we have all req's
	if( $vote_type && $item_type && is_numeric($item_id) && is_numeric($tag_id) ) {
		
		// Set up table names
		$tag_table = $item_type.'s_tags';
		$item_key = $item_type.'_id';
		$point_type = 'tagged-'.$item_type;
		
		// Check if anyone has used the tag
		$sql_check_num = 'SELECT COUNT(id) AS num_times_tagged, SUM(user_agrees) AS num_upvotes FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=?';
		$stmt_check_num = $pdo->prepare($sql_check_num);
		$stmt_check_num->execute([ $item_id, $tag_id ]);
		$rslt_check_num = $stmt_check_num->fetch();
		$num_times_tagged = $rslt_check_num['num_times_tagged'];
		$num_upvotes = $rslt_check_num['num_upvotes'];
		$output['prev_times_tagged'] = $num_times_tagged;
		$output['prev_upvotes'] = $num_upvotes;
		
		// Check whether user has already used tag (if $num_times_tagged is 0, then they def haven't)
		if($num_times_tagged) {
			$sql_check = 'SELECT 1 FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $item_id, $tag_id, $user_id ]);
			$user_already_tagged = $stmt_check->fetchColumn();
		}
		else {
			$user_already_tagged = 0;
		}
		
		// Check if element needs to disappear (i.e. tag removed entirely)
		if( $mod_agrees === -1 || ( $vote_type === 'downvote' && $action === 'add' && $num_times_tagged === 1 && $user_already_tagged ) ) {
			$output['hide_element'] = 1;
		}
		
		// So the way this works is that there's an artists_tags table (for example)
		// and each entry in it has tag_id, user_id, upvote or downvote
		// So regardless of whether the user is upvoting/downvoting, we need to add
		// and entry to that table (if user doesn't already have an entry there)
		
		// If user hasn't tagged this item before, and are upvoting (= agree with tag),
		// then we need to add the user's entry to the tag table
		if( !$user_already_tagged && $action === 'add' && $vote_type === 'upvote' ) {
			$need_to_add = true;
		}
		
		// If user is downvoting (= disagree with tag), then we need to add
		// user's entry to the tag table. If they're downvoting but no one else has
		// voted on that tag, then something's wrong and we'll just ignore (a.k.a.
		// can't downvote a tag which was never upvoted in the first place)
		if( !$user_already_tagged && $action === 'add' && $vote_type === 'downvote' && $num_times_tagged ) {
			$need_to_add = true;
		}
		
		// In the case that a user is downvoting a tag, but they were the only
		// one who had upvoted it in the first place, then we can just delete it
		if( 
			( $user_already_tagged && $action === 'add' && $vote_type === 'downvote' && $num_times_tagged === 1 )
			||
			( $user_already_tagged && $action === 'remove' )
		) {
			$need_to_delete = true;
		}
		
		// Get tag's attributes to check if it exists
		$sql_permissions = 'SELECT * FROM tags_'.$item_type.'s WHERE id=? LIMIT 1';
		$stmt_permissions = $pdo->prepare($sql_permissions);
		$stmt_permissions->execute([ $tag_id ]);
		$tag = $stmt_permissions->fetch();
		
		// Check if tag even exists
		if(is_array($tag) && !empty($tag)) {
			$tag_exists = true;
		}
		
		// Check if user is allowed to upvote/downvote tag
		if(
			!$tag['requires_permission']
			||
			($tag['requires_permission'] && $_SESSION[ $tag['requires_permission'] ])
		) {
			$user_has_permission = true;
		}
		
		// If tag exists and user has permission, we can add/delete and upvote/downvote as necessary
		if($tag_exists && $user_has_permission) {
			
			// Delete user's vote if necessary
			if( $need_to_delete ) {
				$sql_delete = 'DELETE FROM '.$tag_table.' WHERE '.$item_key.'=? AND tag_id=? AND user_id=? LIMIT 1';
				$stmt_delete = $pdo->prepare($sql_delete);
				if($stmt_delete->execute([ $item_id, $tag_id, $user_id ])) {
					
					$output['status'] = 'success';
					$output['num_times_tagged'] = $num_times_tagged - 1;
					$output['num_upvotes'] = $num_upvotes - ( $vote_type === 'upvote' || ($vote_type === 'downvote' && $user_already_tagged && $num_times_tagged === 1) ? 1 : -1 );
					
				}
				else {
					$output['result'] = 'Couldn\'t remove tag.';
				}
			}
			
			// Add user's vote if necessary
			else if( $need_to_add ) {
				$sql_add = 'INSERT INTO '.$tag_table.' ('.$item_key.', tag_id, user_id, user_agrees, mod_agrees) VALUES (?, ?, ?, ?, ?)';
				$stmt_add = $pdo->prepare($sql_add);
				if($stmt_add->execute([ $item_id, $tag_id, $user_id, $user_agrees, $mod_agrees ])) {
					
					$output['status'] = 'success';
					$output['num_times_tagged'] = $num_times_tagged + 1;
					$output['num_upvotes'] = $num_upvotes + ( $vote_type === 'upvote' ? 1 : -1 );
					$output['points'] += $access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
					
				}
				else {
					$output['result'] = 'Couldn\'t add tag.';
				}
			}
			
			// If neither adding/deleting, just update user's vote
			else {
				$sql_update = 'UPDATE '.$tag_table.' SET user_agrees=?, mod_agrees=? WHERE '.$item_key.'=? AND tag_id=? AND user_id=? LIMIT 1';
				$stmt_update = $pdo->prepare($sql_update);
				if($stmt_update->execute([ $user_agrees, $mod_agrees, $item_id, $tag_id, $user_id ])) {
					
					$output['status'] = 'success';
					$output['num_times_tagged'] = $num_times_tagged;
					$output['num_upvotes'] = $num_upvotes + ( ($action === 'add' && $vote_type === 'upvote') || ($action === 'remove' && $vote_type === 'downvote') ? 1 : -1 );
					
				}
				else {
					$output['result'] = 'Couldn\'t update tag.';
				}
			}
			
		}
		
		// Throw error if tag doesn't exist or don't have permission
		else {
			$output['status'] = 'error';
			$output['result'] = 'That tag doesn\'t exist, or you don\'t have permission to use it.';
		}
		
	}
	else {
		$output['result'] = 'Something is wrong with this tag.';
	}
}
else {
	$output['result'] = 'Must be signed in to tag items.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);