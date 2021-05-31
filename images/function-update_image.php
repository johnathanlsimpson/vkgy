<?php
include_once('../php/include.php');
include_once('../php/function-post_deploy.php');

// Remove 'image_' prefix from image fields
foreach($_POST as $key => $value) {
	if(strpos($key, 'image_') === 0) {
		$new_key = substr($key, 6);
		
		// image_content has to have unique id to make it work as a radio, so just strip that
		if( strpos($new_key, 'type') === 0 ) {
			$value = is_array($_POST[$key]) ? reset($_POST[$key]) : 0;
		}
		
		$_POST[$new_key] = $value;
		
		unset($_POST[$key]);
	}
}

if(is_numeric($_POST['id'])) {
	
	// Clean data about image itself
	$id           = sanitize($_POST['id']);
	$item_type    = in_array($_POST['item_type'], access_image::$allowed_item_types) ? $_POST['item_type'] : 'other';
	$item_id      = is_numeric($_POST['item_id']) ? $_POST['item_id'] : null;
	$image_type   = is_numeric($_POST['type']) ? $_POST['type'] : 0;
	
	$description  = sanitize($_POST['description']) ?: (sanitize($_POST['default_description']) ?: null);
	$friendly     = friendly($description) ?: null;
	$credit       = sanitize($_POST['credit']) ?: null;
	
	$is_exclusive = is_array($_POST['is_exclusive']) && reset($_POST['is_exclusive']) ? 1 : 0;
	$is_default   = $_POST['is_default'] ? 1 : 0;
	$is_queued    = $_POST['is_queued'] ? 1 : 0;
	
	// If default image for artist/label/release, update accordingly
	if( $item_type != 'other' && in_array($item_type, access_image::$allowed_item_types) ) {
		
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
	
	// If image is facsimile (i.e. artist photo automatically linked with blog post but not actually uploaded there)
	// then we'll do a simple pass to update the is_default state without updating any other info, that way we
	// can protect the image's original data. If it's not facsimile then we can go ahead with updating all data
	if( $_POST['is_facsimile'] ) {
		
		// Make sure the facsimile is still linked to the item--this is redundant but it'll just have to be improved later
		if( $item_type != 'other' ) {
			
			// Check if we need to add link
			$sql_check_fac_link = 'SELECT 1 FROM images_'.$item_type.($item_type === 'blog' ? null : 's').' WHERE image_id=? AND '.$item_type.'_id=? LIMIT 1';
			$stmt_check_fac_link = $pdo->prepare($sql_check_fac_link);
			$stmt_check_fac_link->execute([ $id, $item_id ]);
			if( !$stmt_check_fac_link->fetchColumn() ) {
				
				// Insert link
				$sql_add_fac_link = 'INSERT INTO images_'.$item_type.($item_type === 'blog' ? null : 's').' (image_id, '.$item_type.'_id) VALUES (?, ?)';
				$stmt_add_fac_link = $pdo->prepare($sql_add_fac_link);
				$stmt_add_fac_link->execute([ $id, $item_id ]);
				
			}
			
		}
		
		// We assume the is_default pass was successful so just return success data
		$output['status'] = 'success';
		
	}
	
	// If not facsimile, update all data for the image itself
	else {
		
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
		
		// For potential images_artists (etc) joins, put into one array
		$image_item_join_types = [
			'artists'   => $_POST['artist_id'],
			'blog'      => $_POST['blog_id'],
			'labels'    => $_POST['label_id'],
			'musicians' => $_POST['musician_id'],
			'releases'  => $_POST['release_id'],
			'issues'    => $_POST['issue_id'],
		];
		
		// Standardize artist_ids (etc) into array of item IDs, since there may be an array of IDs or one ID as a string
		foreach($image_item_join_types as $items_table => $item_ids) {
			
			// For $_POST[musician_id] specifically, we may have {xyz} => 1, {xyz} => 2, 0 => 3,4,5
			// So we have to potentially explode entries with numeric keys
			if( $items_table === 'musicians' ) {
				
				if( is_array($item_ids) ) {
					
					foreach($item_ids as $item_ids_key => $ids) {
						
						if( is_numeric($item_ids_key) ) {
							
							unset($item_ids[$item_ids_key]);
							$item_ids = array_merge( $item_ids, explode(',', $ids) );
							
						}
						
					}
					
				}
				
				else {
					$item_ids = explode(',', $item_ids);
				}
				
			}
			
			// IDs may be passed like 1,2 so make sure we turn into proper array
			elseif( !is_array($item_ids) ) {
				$item_ids = explode(',', $item_ids);
			}
			
			// Remove any duplicates or non-numeric ids
			$item_ids = array_unique( $item_ids );
			$item_ids = array_filter( $item_ids, 'is_numeric' );
			
			// Loop through $_POST['musician_id'] and combine duplicates and hold onto face boundary
			if( $items_table === 'musicians' ) {
				
				$tmp_musician_ids = [];
				
				foreach( $item_ids as $musician_array_key => $musician_id ) {
					
					// We'll make a temporary array that prefers to store IDs with face boundaries attached
					// So basically if there's an array entry with the same ID but non-numeric (i.e. JSON) as key, we'll just continue loop
					// Otherwise store this ID and key, and then we'll update the original array later
					if( !isset($tmp_musician_ids[ $musician_id ]) || is_numeric( $tmp_musician_ids[ $musician_id ]['key'] ) ) {
						$tmp_musician_ids[ $musician_id ] = [
							'id' => $musician_id,
							'key' => $musician_array_key,
							'face_boundaries' => is_numeric($musician_array_key) ? null : html_entity_decode( urldecode($musician_array_key), ENT_QUOTES, 'UTF-8' )
						];
					}
					
				}
				
				$item_ids = $tmp_musician_ids;
				
			}
			
			// Now clean up keys and update original array
			$image_item_join_types[$items_table] = $item_ids;
			
		}
		
		// All images need to join to at least one artist (etc) *unless* it's an "other" image, in which case it can just float in space
		if($item_type != 'other') {
			
			// Get artist_id (id) from $_POST and add to appropriate array of new links, then make sure there are no dupes
			$tmp_key = ($item_type === 'blog' ? 'blog' : $item_type.'s');
			$image_item_join_types[$tmp_key][] = $item_id;
			$image_item_join_types[$tmp_key] = array_unique( $image_item_join_types[$tmp_key] );
			
		}
		
		// Conditionally set is_queued--if image was uploaded to an artist page (etc), is_queued can only be toggled from that artist page
		$sql_queued = 'UPDATE images SET is_queued=? WHERE id=? AND item_type=? LIMIT 1';
		$stmt_queued = $pdo->prepare($sql_queued);
		$stmt_queued->execute([ $is_queued, $id, $item_type ]);
		
		// Update rest of image info
		$sql_update = 'UPDATE images SET description=?, friendly=?, credit=?, is_exclusive=?, image_content=?, face_boundaries=? WHERE id=? LIMIT 1';
		$stmt_update = $pdo->prepare($sql_update);
		if($stmt_update->execute([ $description, $friendly, $credit, $is_exclusive, $image_type, $face_boundaries, $id ])) {
			
			// Status
			$output['status'] = 'success';
			
			// Loop through images_artists (etc) links to see which ones we need to change
			foreach($image_item_join_types as $items_table => $item_ids) {
				
				// Set name of artist_id (etc) column
				$item_id_column = ($items_table === 'blog' ? $items_table : substr($items_table, 0, -1)).'_id';
				
				// Get extant images_artists (etc) joins from DB (if item is musician, also get potential face boundaries)
				$sql_extant_joins = 'SELECT id, '.$item_id_column.($items_table === 'musicians' ? ', face_boundaries' : null).' FROM images_'.$items_table.' WHERE image_id=?';
				$stmt_extant_joins = $pdo->prepare($sql_extant_joins);
				$stmt_extant_joins->execute([ $id ]);
				$rslt_extant_joins = $stmt_extant_joins->fetchAll();
				
				// Grab ids of extant images_artists (etc) rows, plug item_id
				foreach($rslt_extant_joins as $extant_join) {
					
					$extant_joins[ $extant_join[ $item_id_column ] ] = [
						'join_id' => $extant_join['id'],
						'face_boundaries' => ( $extant_join['face_boundaries'] ?: null ),
					];
					
				}
				
				// Make sure we have some kind of array for extant_joins
				$extant_joins = is_array($extant_joins) ? $extant_joins : [];
				
				// If we have at least one item id passed from the post for this item type, continue checks
				if( is_array($item_ids) && !empty($item_ids) ) {
					
					// For each item_ids => item_id
					foreach($item_ids as $key_in_item_ids_array => $item_id_to_be_joined) {
						
						// For item_ids[musicians] only, each item is an array instead of just id, so get id
						if( $items_table === 'musicians' ) {
							$item_id_to_be_joined = $item_id_to_be_joined['id'];
						}
						
						// If this particular item has already been joined to the image, we probably don't have to do anything, but let's make sure
						if( isset( $extant_joins[ $item_id_to_be_joined ] ) ) {
							
							// If looking at musician table, first we need to see if the join has the correct boundary, and if not we need to update it
							if( $items_table === 'musicians' ) {
								
								// If face boundary has changed, we need to update that join row; then either way we remove this entry from both arrays since item is already joined to image
								if( $extant_joins[ $item_id_to_be_joined ]['face_boundaries'] != $item_ids[ $item_id_to_be_joined ]['face_boundaries'] ) {
									
									$sql_update_join = 'UPDATE images_'.$items_table.' SET face_boundaries=? WHERE id=?';
									$stmt_update_join = $pdo->prepare($sql_update_join);
									$stmt_update_join->execute([ $item_ids[ $item_id_to_be_joined ]['face_boundaries'], $extant_joins[ $item_id_to_be_joined ]['join_id'] ]);
									
								}
								
							}
							
							// Now, since item is already joined to image (and if it was musician we already updated face boundary if necessary), we remove from both arrays
							// so that it's neither added nor deleted from joins
							unset($extant_joins[ $item_id_to_be_joined ]);
							unset($item_ids[ $key_in_item_ids_array ]);
							
						}
						
						// If the item_id from the POST array isn't a number somehow, ignore it
						elseif(!is_numeric($item_id_to_be_joined)) {
							unset($item_ids[$key_in_item_ids_array]);
						}
						
						// If the item_id from the POST isn't in DB, then we need to add it
						else {
							
							// If linking images_musicians, also need to update face_boundaries
							$values_add_link = [ $id, $item_id_to_be_joined ];
							$musician_face_boundaries = $item_ids[ $item_id_to_be_joined ]['face_boundaries'];
							
							if($items_table === 'musicians') {
								$sql_add_link = 'INSERT INTO images_'.$items_table.' (image_id, '.$item_id_column.', face_boundaries) VALUES (?, ?, ?)';
								$values_add_link[] = $musician_face_boundaries;
							}
							else {
								$sql_add_link = 'INSERT INTO images_'.$items_table.' (image_id, '.$item_id_column.') VALUES (?, ?)';
							}
							
							$stmt_add_link = $pdo->prepare($sql_add_link);
							
							if( $stmt_add_link->execute($values_add_link) ) {
							}
							else {
								$output['result'][] = 'Couldn\'t link image to '.$items_table.'.';
							}
							
							// So now if we are linking a musician to the photo, and the photo is of the type musician body shot, or the photo is of type group or flyer and there is a face boundary set, then we can assume it's the default headshot for for that musician and band combo if there isn't one set already
							
							// If we're adding a musician to the image
							if( $items_table === 'musicians' ) {
								
								// If this image is linked to one artist specifically
								if( is_array($image_item_join_types['artists']) && !empty($image_item_join_types['artists']) && count($image_item_join_types['artists']) == 1 ) {
									
									// And the image is either a solo photo, or a group/flyer with face boundaries specified
									if(
										( $image_type == 2 )
										||
										( $image_type == 1 && strlen($musician_face_boundaries) )
										||
										( $image_type == 3 && strlen($musician_face_boundaries) )
									) {
										
										$sql_set_headshot = 'UPDATE artists_musicians SET image_id=? WHERE artist_id=? AND musician_id=? AND image_id IS NULL';
										$stmt_set_headshot = $pdo->prepare($sql_set_headshot);
										$stmt_set_headshot->execute([ $id, reset($image_item_join_types['artists']), $item_id_to_be_joined ]);
										
									}
									
								}
								
							}
							
						}
					}
				}
				
				// If links from the DB were also in the links passed from the POST, then they're unset; if any from DB remain, that means they're not in POST i.e. need deletion
				if(is_array($extant_joins) && !empty($extant_joins)) {
					foreach($extant_joins as $extant_join_item_id => $extant_join) {
						
						$sql_delete_link = 'DELETE FROM images_'.$items_table.' WHERE id=? LIMIT 1';
						$stmt_delete_link = $pdo->prepare($sql_delete_link);
						
						if($stmt_delete_link->execute([ $extant_join['join_id'] ])) {
						}
						else {
							$output['result'][] = 'Couldn\'t delete image-'.$items_table.' link.';
						}
						
						
						// If we're looping through artists, let's see if we have to delete any artists_musicians headshots
						if( $items_table === 'artists' ) {
							
							// And we have a musician set for this image
							$sql_unset_headshot = 'UPDATE artists_musicians SET image_id=? WHERE artist_id=? AND image_id=?';
							$stmt_unset_headshot = $pdo->prepare($sql_unset_headshot);
							$stmt_unset_headshot->execute([ null, $extant_join_item_id, $id ]);
							
						}
						
						// If we're looping through musicians, let's see if we have to unset a headshot for that musician with that band
						if( $items_table === 'musicians' ) {
							
							$sql_unset_headshot = 'UPDATE artists_musicians SET image_id=? WHERE musician_id=?';
							$stmt_unset_headshot = $pdo->prepare($sql_unset_headshot);
							$stmt_unset_headshot->execute([ null, $extant_join_item_id ]);
							
						}
						
					}
				}
				
				unset($extant_joins);
				
			}
			
			if($is_queued && $item_type === 'flyer') {
				update_development($pdo, ['type' => 'flyer']);
			}
			
		}
		else {
			$output['result'][] = 'Couldn\'t update.';
		}
		
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