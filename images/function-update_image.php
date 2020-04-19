<?php
include_once('../php/include.php');
include_once('../php/function-post_deploy.php');

// Remove 'image_' prefix from image fields
foreach($_POST as $key => $value) {
	if(strpos($key, 'image_') === 0) {
		$new_key = substr($key, 6);
		
		$_POST[$new_key] = $value;
		
		unset($_POST[$key]);
	}
}

if(is_numeric($_POST['id'])) {
	$allowed_item_types = ['artist', 'blog', 'label', 'musician', 'release', 'other'];
	
	// Clean data about image itself
	$id           = sanitize($_POST['id']);
	$item_type    = in_array($_POST['item_type'], $allowed_item_types) ? $_POST['item_type'] : 'other';
	$item_id      = is_numeric($_POST['item_id']) ? $_POST['item_id'] : null;
	
	$description  = sanitize($_POST['description']) ?: (sanitize($_POST['default_description']) ?: null);
	$friendly     = friendly($description) ?: null;
	$credit       = sanitize($_POST['credit']) ?: null;
	
	$is_exclusive = is_array($_POST['is_exclusive']) && reset($_POST['is_exclusive']) ? 1 : 0;
	$is_default   = $_POST['is_default'] ? 1 : 0;
	$is_queued    = $_POST['is_queued'] ? 1 : 0;
	
	// Set up array of possible new links (i.e. links passed from form)
	$new_links = [ 'artists' => $_POST['artist_id'], 'blog' => $_POST['blog_id'], 'labels' => $_POST['label_id'], 'musicians' => $_POST['musician_id'], 'releases' => $_POST['release_id'] ];
	
	// Standardize new links into array of item IDs, since there may be an array of IDs or one ID as a string
	foreach($new_links as $new_link_table => $new_item_ids) {
		if(!is_array($new_item_ids)) {
			$new_links[$new_link_table] = explode(',', $new_item_ids);
		}
		else {
			$new_links[$new_link_table] = explode(',', implode(',', $new_item_ids));
		}
		
		// Make sure there are no dupes in new links passed from POST
		$new_links[$new_link_table] = array_unique($new_links[$new_link_table]);
	}
	
	// Make sure there's at least one link to an item (unless this is an "other" type image, in which case it's basically just an unlinked upload)
	if($item_type != 'other') {
		
		// Get item id from $_POST and add to appropriate array of new links, then make sure there are no dupes
		$tmp_key = ($item_type === 'blog' ? 'blog' : $item_type.'s');
		$new_links[$tmp_key][] = $item_id;
		$new_links[$tmp_key] = array_unique($new_links[$tmp_key]);
		
	}
	
	// Update image info
	$sql_update = 'UPDATE images SET description=?, friendly=?, credit=?, is_exclusive=?, is_queued=? WHERE id=? LIMIT 1';
	$stmt_update = $pdo->prepare($sql_update);
	if($stmt_update->execute([ $description, $friendly, $credit, $is_exclusive, $is_queued, $id ])) {
		
		// Status
		$output['status'] = 'success';
		
		// Loop through possible new links passed from $_POST and see which links we need to add/keep/delete
		foreach($new_links as $new_link_table => $new_item_ids) {
			$new_link_column = ($new_link_table === 'blog' ? $new_link_table : substr($new_link_table, 0, -1)).'_id';
			
			// Get extant links from DB
			$sql_extant_links = 'SELECT id, '.$new_link_column.' FROM images_'.$new_link_table.' WHERE image_id=?';
			$stmt_extant_links = $pdo->prepare($sql_extant_links);
			$stmt_extant_links->execute([ $id ]);
			
			// So grab the actual ID of the extant link plus the item ID of the extant link
			foreach($stmt_extant_links->fetchAll() as $extant_link) {
				$rslt_extant_links[ $extant_link['id'] ] = $extant_link[$new_link_column];
			}
			$rslt_extant_links = is_array($rslt_extant_links) ? $rslt_extant_links : [];
			
			// If we have at least one link passed from the POST, let's check if it's extant in the DB
			if(is_array($new_item_ids) && !empty($new_item_ids)) {
				
				// For each items => item_ids
				foreach($new_item_ids as $new_item_ids_key => $new_item_ids_value) {
					
					// If the item_id from the POST array is in the array that we just got from the SQL
					if(in_array($new_item_ids_value, $rslt_extant_links)) {
						
						// Since ID is both in the POST and in the DB, we don't have to do anything, so unset it from both arrays and keep it moving Mary
						unset($rslt_extant_links[ array_search($new_item_ids_value, $rslt_extant_links) ]);
						unset($new_item_ids[$new_item_ids_key]);
						
					}
					
					// If the item_id from the POST array isn't a number somehow, ignore it
					elseif(!is_numeric($new_item_ids_value)) {
						unset($new_item_ids[$new_item_ids_key]);
					}
					
					// If the item_id from the POST isn't in DB, then we need to add it
					else {
						
						$sql_add_link = 'INSERT INTO images_'.$new_link_table.' (image_id, '.$new_link_column.') VALUES (?, ?)'; 
						$stmt_add_link = $pdo->prepare($sql_add_link);
						if($stmt_add_link->execute([ $id, $new_item_ids_value ])) {
						}
						else {
							$output['result'][] = 'Couldn\'t link image to '.$new_link_table.'.';
						}
						
					}
				}
			}
			
			// If links from the DB were also in the links passed from the POST, then they're unset; if any from DB remain, that means they're not in POST i.e. need deletion
			if(is_array($rslt_extant_links) && !empty($rslt_extant_links)) {
				foreach($rslt_extant_links as $extant_link_id => $extant_link_artist_id) {
					$sql_delete_link = 'DELETE FROM images_'.$new_link_table.' WHERE id=? LIMIT 1';
					$stmt_delete_link = $pdo->prepare($sql_delete_link);
					if($stmt_delete_link->execute([ $extant_link_id ])) {
					}
					else {
						$output['result'][] = 'Couldn\'t delete image-'.$new_link_table.' link.';
					}
				}
			}
			
			unset($rslt_extant_links);
		}
		
		// If default image for artist/label/release, update accordingly
		if(in_array($item_type, ['artist', 'blog', 'label', 'release'])) {
			if($is_default) {
				$sql_make_default = 'UPDATE '.$item_type.($item_type != 'blog' ? 's' : null).' SET image_id=? WHERE id=? LIMIT 1';
				$stmt_make_default = $pdo->prepare($sql_make_default);
				$stmt_make_default->execute([ $id, $item_id ]);
			}
			else {
				$sql_unset_default = 'UPDATE '.$item_type.($item_type != 'blog' ? 's' : null).' SET image_id=? WHERE id=? AND image_id=? LIMIT 1';
				$stmt_unset_default = $pdo->prepare($sql_unset_default);
				$stmt_unset_default->execute([ null, $item_id, $id ]);
			}
		}
		
		if($is_queued && $item_type === 'flyer') {
			update_development($pdo, ['type' => 'flyer']);
		}
	}
	else {
		$output['result'][] = 'Couldn\'t update.';
	}
}
else {
	$output['result'][] = 'Non-numeric ID.';
}

if(!$suppress_output) {
	$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
	$output['status'] = $output['status'] ?: 'error';
	
	echo json_encode($output);
}