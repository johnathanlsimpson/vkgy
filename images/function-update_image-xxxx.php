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
	
	$face_boundaries = null;
	
	// Make sure face boundaries is proper json
	if( strlen($_POST['face_boundaries']) ) {
		
		// Decode sanitized text, then do basic check that it's correct format
		$face_boundaries = html_entity_decode( $_POST['face_boundaries'], ENT_QUOTES, 'UTF-8' );
		$json_pattern = '^\[[\{\}0-9a-z\_\:\,\"]+\]$';
		
		if( !preg_match('/'.$json_pattern.'/', $face_boundaries) ) {
			$face_boundaries = null;
		}
		
	}
	
	// For potential images_items joins, put into one array
	$image_item_join_types = [
		'artists'   => $_POST['artist_id'],
		'blog'      => $_POST['blog_id'],
		'labels'    => $_POST['label_id'],
		'musicians' => $_POST['musician_id'],
		'releases'  => $_POST['release_id']
	];
	
	// Make empty face boundaries array for later
	$musician_face_boundaries = [];
	
	// Standardize new links into array of item IDs, since there may be an array of IDs or one ID as a string
	foreach($image_item_join_types as $items_table => $item_ids) {
		
		// IDs may be passed like 1,2 so make sure we turn into proper array
		if( !is_array($item_ids) ) {
			$item_ids = explode(',', $item_ids);
		}
		
		/*// Forcing it into an array, but don't remember why the second part is there
		if(!is_array($item_ids)) {
			$image_item_join_types[$items_table] = explode(',', $item_ids);
		}
		else {
			$image_item_join_types[$items_table] = explode(',', implode(',', $item_ids));
		}*/
		
		// Remove any duplicates or non-numeric ids
		$item_ids = array_unique( $item_ids );
		$item_ids = array_filter( $item_ids, 'is_numeric' );
		
		// Musicians_ids [ 'abc' => 1, 'xyz' => 2 ] Musicians_face_boundaries [ 'abc' => 'xxx', 'xyz' => 'xxx' ]
		// So after cleaning up musicians_ids, we're going to make a face_boundaries array with only the entries that have associated ids
		if( $items_table === 'musicians' ) {
			
			// Construct boundaries array
			foreach( $item_ids as $musician_array_key => $musician_id ) {
				$musician_face_boundaries[ $musician_array_key ] = $_POST['musician_face_boundaries'][ $musician_array_key ] ?: null;
			}
			
			// Clean up keys (will clean up musician keys in next step)
			$musician_face_boundaries = array_values($musician_face_boundaries);
			
		}
		
		// Now clean up keys and update original array
		$item_ids = array_values($item_ids);
		$image_item_join_types[$items_table] = $item_ids;
		
		// This was used to reset array keys but let's see if we can get on without it
		//$image_item_join_types[$items_table] = array_values($image_item_join_types[$items_table]);
		
	}
	
	print_r($image_item_join_types);
	print_r($musician_face_boundaries);
	
	// Make sure there's at least one join to an item (unless this is an "other" type image, in which case it's basically just an unlinked upload)
	if($item_type != 'other') {
		
		// Get item id from $_POST and add to appropriate array of new links, then make sure there are no dupes
		$tmp_key = ($item_type === 'blog' ? 'blog' : $item_type.'s');
		$image_item_join_types[$tmp_key][] = $item_id;
		$image_item_join_types[$tmp_key] = array_unique( $image_item_join_types[$tmp_key] );
		
	}
	
	// Update is_queued flag on its own pass, as we only want to change it if the image was originally uploaded with this item type
	// i.e. If image was uploaded with a blog post, and that blog post changes to queued, the image should be queued--
	// but image was uploaded on artist's profile, we don't care about the blog post being queued or not
	$sql_queued = 'UPDATE images SET is_queued=? WHERE id=? AND item_type=? LIMIT 1';
	$stmt_queued = $pdo->prepare($sql_queued);
	$stmt_queued->execute([ $is_queued, $id, $item_type ]);
	
	// Update image info
	$sql_update = 'UPDATE images SET description=?, friendly=?, credit=?, is_exclusive=?, face_boundaries=? WHERE id=? LIMIT 1';
	$stmt_update = $pdo->prepare($sql_update);
	if($stmt_update->execute([ $description, $friendly, $credit, $is_exclusive, $face_boundaries, $id ])) {
		
		// Status
		$output['status'] = 'success';
		
		// Loop through images_items links to see which ones we need to change
		foreach($image_item_join_types as $items_table => $item_ids) {
			
			$item_id_column = ($items_table === 'blog' ? $items_table : substr($items_table, 0, -1)).'_id';
			
			// Get extant image_item joins from DB (if item is musician, also get potential face boundaries)
			$sql_extant_joins = 'SELECT id, '.$item_id_column.($items_table === 'musicians' ? ', face_boundaries' : null).' FROM images_'.$items_table.' WHERE image_id=?';
			$stmt_extant_joins = $pdo->prepare($sql_extant_joins);
			$stmt_extant_joins->execute([ $id ]);
			$rslt_extant_joins = $stmt_extant_joins->fetchAll();
			
			// Grab ids of extant images_items rows, plug item_id
			foreach($rslt_extant_joins as $extant_join) {
				
				//$extant_joins[ $extant_join['id'] ] = $extant_join[ $item_id_column ];
				// Save like [ item_id => images_items_id ]
				
				$extant_joins[ $extant_join[ $item_id_column ] ] = [
					'join_id' => $extant_join['id'],
					'face_boundaries' => ( $extant_join['face_boundaries'] ?: null ),
				];
				
				//$rslt_extant_face_boundaries[ $extant_join['id'] ]
				
				/*if($items_table === 'musicians') {
					$extant_face_boundaries[ $extant_join['id'] ] = $extant_join['face_boundaries'];
				}*/
				
			}
			
			// Make sure we have some kind of array for extant_joins
			$extant_joins = is_array($extant_joins) ? $extant_joins : [];
			
			echo 'extant joins';
			print_r($extant_joins);
			
			// If we have at least one item id passed from the post for this item type, continue checks
			if( is_array($item_ids) && !empty($item_ids) ) {
				
				// For each item_ids => item_id
				foreach($item_ids as $items_ids_array_key => $temp_item_id) {
					
					/*// If we're looking at potential images_musicians links, then we need to check if face boundary needs to be updated
					if($items_table === 'musicians') {
						
						// Which face boundary are we looking at?
						$face_boundary = $musician_faces[ $items_ids_array_key ];
						
						// If the face boundary coming from POST is not already in the extant links, OR if it is but has a different musician id, update it
						$extant_face_boundary_key = is_array($extant_face_boundaries) ? array_search($face_boundary, $extant_face_boundaries) : null;
						
						// So if we found the face boundary json in the array of faces already in images_musicians
						// Then we need to check if the musician changed
						if( is_numeric($extant_face_boundary_key) ) {
							
							// So get the musician id from the other extant array of images_musicians from the db
							if( $extant_joins[$extant_face_boundary_key] == $item_ids[$extant_face_boundary_key] ) {
								
								
								
							}
							
							else {
								
							}
							
						}
						
						
						
						
						
						print_r($item_ids);
						print_r($musician_faces);
						echo 'extant links:';
						print_r($extant_joins);
						echo 'extant faces:';
						print_r($extant_face_boundaries);
						echo "\n";
						
						
						
						
					}*/
					
					// If item key is one of the arrays of the extant joins, we probably don't have to do anything
					if( in_array( $temp_item_id, array_keys($extant_joins) ) ) {
						
						// If item type is musicians specifically, 
						
							
							// Since ID is both in the POST and in the DB, we don't have to do anything, so unset it from both arrays and keep it moving Mary
							unset($extant_joins[ array_search($temp_item_id, $extant_joins) ]);
							unset($item_ids[$items_ids_array_key]);
							
						
					}
					
					// If the item_id from the POST array isn't a number somehow, ignore it
					elseif(!is_numeric($temp_item_id)) {
						unset($item_ids[$items_ids_array_key]);
					}
					
					// If the item_id from the POST isn't in DB, then we need to add it
					else {
						
						// If linking images_musicians, also need to update face_boundaries
						$values_add_link = [ $id, $temp_item_id ];
						$face_boundary = $_POST['image_musician_face'][$items_ids_array_key];
						
						if($items_table === 'musicians') {
							$sql_add_link = 'INSERT INTO images_'.$items_table.' (image_id, '.$item_id_column.', face_boundaries) VALUES (?, ?, ?)';
							$values_add_link[] = $face_boundary;
						}
						else {
							$sql_add_link = 'INSERT INTO images_'.$items_table.' (image_id, '.$item_id_column.') VALUES (?, ?)';
						}
						
						$stmt_add_link = $pdo->prepare($sql_add_link);
						
						if($stmt_add_link->execute()) {
						}
						else {
							$output['result'][] = 'Couldn\'t link image to '.$items_table.'.';
						}
						
					}
				}
			}
			
			// If links from the DB were also in the links passed from the POST, then they're unset; if any from DB remain, that means they're not in POST i.e. need deletion
			if(is_array($extant_joins) && !empty($extant_joins)) {
				foreach($extant_joins as $extant_join_id => $extant_join_artist_id) {
					$sql_delete_link = 'DELETE FROM images_'.$items_table.' WHERE id=? LIMIT 1';
					$stmt_delete_link = $pdo->prepare($sql_delete_link);
					if($stmt_delete_link->execute([ $extant_join_id ])) {
					}
					else {
						$output['result'][] = 'Couldn\'t delete image-'.$items_table.' link.';
					}
				}
			}
			
			unset($extant_joins);
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
	
	// If image was just uploaded, show that the user gained one point, even though technically the point was awarded on upload and not on *update*
	// Will have to change this point value manually, I guess
	if($_POST['is_new']) {
		$output['points'] = 1;
	}
	
	echo json_encode($output);
}