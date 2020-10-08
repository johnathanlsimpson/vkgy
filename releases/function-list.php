<?php

include_once('../php/include.php');

// Only signed-in users can add items to lists
if($_SESSION['is_signed_in']) {
	
	// Clean variables
	$item_type = sanitize($_POST['item_type']);
	$item_id   = sanitize($_POST['item_id']);
	$list_id   = sanitize($_POST['list_id']);
	$user_id   = $_SESSION['user_id'];
	$action    = $_POST['item_is_listed'] ? 'add' : 'delete';
	$is_sell   = $list_id == 2;
	
	// Only allow certain kinds of items in lists
	$allowed_item_types = [
		'release',
		'video'
	];
	
	// Set up points
	$access_points = new access_points($pdo);
	
	// Eventually, we'll award a generic list point (probably); for now let's set it up for releases
	$point_type = $list_id == 0 ? 'wanted-release' : ( $list_type == 1 ? 'collected-release' : ($list_type == 2 ? 'sold-release' : null) );
	
	// Make sure list and item are specified, and item is allowed
	if( is_numeric($item_id) && is_numeric($list_id) && in_array($item_type, $allowed_item_types) ) {
		
		// Eventually, all lists will be in one table (maybe?); for now, let's set up alternate tables
		$list_tables = [
			'releases_collections',
			'releases_wants',
			'releases_collections',
		];
		
		// !! Start release lists
		if( $list_id < 3 ) {
		
		// Check current status of item in list
		$sql_check = 'SELECT id FROM '.$list_tables[$list_id].' WHERE user_id=? AND '.$item_type.'_id=? LIMIT 1';
		
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $user_id, $item_id ]);
		$extant_id = $stmt_check->fetchColumn();
		
		// Delete if necessary ($is_sell is temporary while sales are handled in releases_collections table instead of generic list table)
		if($action === 'delete' && !$is_sell && is_numeric($extant_id)) {
			$sql_delete = 'DELETE FROM '.$list_tables[$list_id].' WHERE id=? LIMIT 1';
			$stmt_delete = $pdo->prepare($sql_delete);
			
			if($stmt_delete->execute([ $extant_id ])) {
				$output['status'] = 'success';
				$output['result'] = 'Item removed from list.';
			}
			else {
				$output['result'] = 'Couldn\'t remove item from list.';
			}
		}
		
		// Add if necessary ($is_sell is temporary while sales are handled in releases_collections table instead of generic list table)
		if($action === 'add' && !$is_sell && !is_numeric($extant_id)) {
			$sql_add = 'INSERT INTO '.$list_tables[$list_id].' (user_id, '.$item_type.'_id) VALUES (?, ?)';
			$stmt_add = $pdo->prepare($sql_add);
			
			if($stmt_add->execute([ $user_id, $item_id ])) {
				$output['status'] = 'success';
				$output['result'] = 'Item added to list.';
				
				// Award point
				$output['points'] += $access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
			}
			else {
				$output['result'] = 'Couldn\'t add item to list.';
			}
		}
		
		// Handle "is for sale" updates
		if($is_sell && is_numeric($extant_id)) {
			
			$sql_update = 'UPDATE '.$list_tables[$list_id].' SET is_for_sale=? WHERE id=?';
			$stmt_update = $pdo->prepare($sql_update);
			
			if($stmt_update->execute([ ($action === 'add' ? 1 : 0), $extant_id ])) {
				$output['status'] = 'success';
				$output['result'] = 'Updated selling status.';
				
				// Award point
				if($action === 'add') {
					$output['points'] += $access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
				}
			}
			else {
				$output['result'] = 'Couldn\'t update selling status.';
			}
			
		}
		
		// !! End release lists
		}
		
		// !! Normal lists
		else {
			
			// Check that user owns list
			$sql_check = 'SELECT 1 FROM lists WHERE id=? AND user_id=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $list_id, $user_id ]);
			
			// Exit if not owner
			if( $stmt_check->fetchColumn() ) {
				
				// Add
				if( $action === 'add' ) {
					
					$values_add = [ $list_id, $item_id, array_search($item_type, $allowed_item_types) ];
					$sql_add = 'INSERT INTO lists_items (list_id, item_id, item_type) VALUES (?, ?, ?)';
					$stmt_add = $pdo->prepare($sql_add);
					
					if($stmt_add->execute($values_add)) {
						$output['status'] = 'success';
						$output['result'] = 'Item added to list.';
					}
					else {
						$output['result'] = 'Couldn\'t add item to list.';
					}
					
				}
				
				// Delete
				elseif( $action === 'delete' ) {
					
					$values_delete = [ $list_id, $item_id, array_search($item_type, $allowed_item_types) ];
					$sql_delete = 'DELETE FROM lists_items WHERE list_id=? AND item_id=? AND item_type=? LIMIT 1';
					$stmt_delete = $pdo->prepare($sql_delete);
					
					if($stmt_delete->execute($values_delete)) {
						$output['status'] = 'success';
						$output['result'] = 'Item deleted from list.';
					}
					else {
						$output['result'] = 'Couldn\'t delete item from list.';
					}
					
				}
				
			}
			
		}
		
	}
	else {
		$output['result'] = 'Sorry, something went wrong.';
	}
}
else {
	$output['result'] = 'Please sign in to use lists.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);