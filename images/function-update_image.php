<?php
include_once('../php/include.php');
include_once('../php/function-post_deploy.php');

foreach($_POST as $key => $value) {
	if(strpos($key, 'image_') === 0) {
		$new_key = substr($key, 6);
		
		$_POST[$new_key] = $value;
		
		unset($_POST[$key]);
	}
}

if(is_numeric($_POST['id'])) {
	$allowed_item_types = ['artist', 'blog', 'label', 'musician', 'release', 'other'];
	
	// Clean data
	$id           = sanitize($_POST['id']);
	$item_type    = in_array($_POST['item_type'], $allowed_item_types) ? $_POST['item_type'] : 'other';
	$item_id      = is_numeric($_POST['item_id']) ? $_POST['item_id'] : 0;
	
	$description  = sanitize($_POST['description']) ?: (sanitize($_POST['default_description']) ?: null);
	$friendly     = friendly($description) ?: null;
	$credit       = sanitize($_POST['credit']) ?: null;
	
	$is_exclusive = $_POST['is_exclusive'] ? 1 : 0;
	$is_default   = $_POST['is_default'] ? 1 : 0;
	$is_queued    = $_POST['is_queued'] ? 1 : 0;
	
	// Set links array
	$links = [ 'artists' => $_POST['artist_id'], 'blog' => $_POST['blog_id'], 'labels' => $_POST['label_id'], 'musicians' => $_POST['musician_id'], 'releases' => $_POST['release_id'] ];
	
	// Standardize into array (might be string or 'array' of one string)
	foreach($links as $link_table => $link_array) {
		if(!is_array($link_array)) {
			$links[$link_table] = explode(',', $link_array);
		}
		else {
			$links[$link_table] = explode(',', implode(',', $link_array));
		}
	}
	
	// Set up default link
	if($item_type != 'other') {
		if($item_type === 'blog') {
			$links[$item_type][] = $item_id;
		}
		else {
			$tmp_key = $item_type.'s';
			$links[$tmp_key][] = $item_id;
		}
	}
	
	// Run query
	$sql_update = 'UPDATE images SET description=?, friendly=?, credit=?, is_exclusive=?, is_queued=? WHERE id=? LIMIT 1';
	$stmt_update = $pdo->prepare($sql_update);
	if($stmt_update->execute([ $description, $friendly, $credit, $is_exclusive, $is_queued, $id ])) {
		
		// Status
		$output['status'] = 'success';
		
		// Link to artists etc.
		foreach($links as $link_table => $link_array) {
			$link_column = ($link_table === 'blog' ? $link_table : substr($link_table, 0, -1)).'_id';
			
			// Get current links
			$sql_current_links = 'SELECT id, '.$link_column.' FROM images_'.$link_table.' WHERE image_id=?';
			$stmt_current_links = $pdo->prepare($sql_current_links);
			$stmt_current_links->execute([ $id ]);
			foreach($stmt_current_links->fetchAll() as $current_link) {
				$rslt_current_links[$current_link['id']] = $current_link[$link_column];
			}
			$rslt_current_links = is_array($rslt_current_links) ? $rslt_current_links : [];
			
			// Determine new links
			if(is_array($link_array) && !empty($link_array)) {
				foreach($link_array as $link_array_key => $link_array_value) {
					if(in_array($link_array_value, $rslt_current_links)) {
						unset($rslt_current_links[array_search($link_array_value, $rslt_current_links)]);
						unset($link_array[$link_array_key]);
					}
					elseif(!is_numeric($link_array_value)) {
						unset($link_array[$link_array_key]);
					}
					else {
						$sql_add_link = 'INSERT INTO images_'.$link_table.' (image_id, '.$link_column.') VALUES (?, ?)'; 
						$stmt_add_link = $pdo->prepare($sql_add_link);
						if($stmt_add_link->execute([ $id, $link_array_value ])) {
						}
						else {
							$output['result'][] = 'Couldn\'t add image-'.$link_table.' link.';
						}
					}
				}
			}
			
			// Delete unneeded links
			if(is_array($rslt_current_links) && !empty($rslt_current_links)) {
				foreach($rslt_current_links as $current_link_id => $current_link_artist_id) {
					$sql_delete_link = 'DELETE FROM images_'.$link_table.' WHERE id=? LIMIT 1';
					$stmt_delete_link = $pdo->prepare($sql_delete_link);
					if($stmt_delete_link->execute([ $current_link_id ])) {
					}
					else {
						$output['result'][] = 'Couldn\'t delete image-'.$link_table.' link.';
					}
				}
			}
			
			unset($rslt_current_links);
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