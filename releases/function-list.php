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
	
	// Set up points
	$access_points = new access_points($pdo);
	
	// Eventually, we'll award a generic list point (probably); for now let's set it up for releases
	$point_type = $list_id == 0 ? 'wanted-release' : ( $list_type == 1 ? 'collected-release' : ($list_type == 2 ? 'sold-release' : null) );
	
	// Make sure list and item are specified, and item is allowed
	if( is_numeric($item_id) && is_numeric($list_id) && in_array($item_type, ['release']) ) {
		
		// Eventually, all lists will be in one table (maybe?); for now, let's set up alternate tables
		$list_tables = [
			'releases_collections',
			'releases_wants',
			'releases_collections',
		];
		
		// Check current status of item in list
		$sql_check = 'SELECT id FROM '.$list_tables[$list_id].' WHERE user_id=? AND '.$item_type.'_id=? LIMIT 1';
		
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $user_id, $item_id ]);
		$extant_id = $stmt_check->fetchColumn();
		
		$output['result'] = $item_type.'*'.$item_id.'*'.$list_id.'*'.$user_id.'*'.$action.'*'.$is_sell.'*'.$extant_id.print_r($_POST, true);
		
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
				$access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
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
					$access_points->award_points([ 'point_type' => $point_type, 'allow_multiple' => false, 'item_id' => $item_id ]);
				}
			}
			else {
				$output['result'] = 'Couldn\'t update selling status.';
			}
			
		}
		
		
	//if(is_numeric($release_id) && in_array($action, ["own", "want", "sell"])) {
		/*if($action === "own") {
			$sql_check = "SELECT id FROM releases_collections WHERE user_id=? AND release_id=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([$user_id, $release_id]);

			$record_id = $stmt_check->fetchColumn();
			$is_owned = is_numeric($record_id);

			if($is_owned) {
				$sql_update = "DELETE FROM releases_collections WHERE id=? LIMIT 1";
				$sql_values = [$record_id];
			}
			else {
				$sql_update = "INSERT INTO releases_collections (user_id, release_id) VALUES (?, ?)";
				$sql_values = [$user_id, $release_id];
				$output["is_checked"] = "1";

				// Award point
				$access_points = new access_points($pdo);
				$access_points->award_points([ 'point_type' => 'collected-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
			}
		}
		elseif($action === "want") {
			$sql_check = "SELECT id FROM releases_wants WHERE user_id=? AND release_id=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([$user_id, $release_id]);

			$record_id = $stmt_check->fetchColumn();
			$is_wanted = is_numeric($record_id);

			if($is_wanted) {
				$sql_update = "DELETE FROM releases_wants WHERE id=? LIMIT 1";
				$sql_values = [$record_id];
			}
			else {
				$sql_update = "INSERT INTO releases_wants (user_id, release_id) VALUES (?, ?)";
				$sql_values = [$user_id, $release_id];
				$output["is_checked"] = "1";

				// Award point
				$access_points = new access_points($pdo);
				$access_points->award_points([ 'point_type' => 'wanted-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
			}
		}*/
		/*elseif($action === "sell") {
			$sql_check = "SELECT id FROM releases_collections WHERE user_id=? AND release_id=? AND is_for_sale=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([$user_id, $release_id, 1]);

			$record_id = $stmt_check->fetchColumn();
			$is_for_sale = is_numeric($record_id);

			if($is_for_sale) {
				$sql_update = "UPDATE releases_collections SET is_for_sale=? WHERE user_id=? AND release_id=? LIMIT 1";
				$sql_values = [ 0, $user_id, $release_id ];
			}
			else {
				$sql_update = "UPDATE releases_collections SET is_for_sale=? WHERE user_id=? AND release_id=? LIMIT 1";
				$sql_values = [ 1, $user_id, $release_id ];
				$output["is_checked"] = "1";

				// Award point
				$access_points = new access_points($pdo);
				$access_points->award_points([ 'point_type' => 'sold-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
			}
		}*/

		/*$stmt_update = $pdo->prepare($sql_update);
		if($stmt_update->execute($sql_values)) {
			$output["status"] = "success";
		}
		else {
			$output["result"] = "Sorry, your collection couldn't be updated.";
		}*/
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