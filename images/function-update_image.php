<?php
include_once('../php/include.php');
include_once('../php/function-post_deploy.php');

if(is_numeric($_POST['id'])) {
	$allowed_item_types = ['artist', 'blog', 'label', 'musician', 'release', 'other'];
	
	// Clean data
	$id           = sanitize($_POST['id']);
	$item_type    = in_array($_POST['item_type'], $allowed_item_types) ? $_POST['item_type'] : 'other';
	$item_id      = is_numeric($_POST['item_id']) ? $_POST['item_id'] : 0;
	
	$description  = sanitize($_POST['description']) ?: null;
	$friendly     = friendly($description) ?: null;
	$credit       = sanitize($_POST['credit']) ?: null;
	
	$is_exclusive = $_POST['is_exclusive'] ? 1 : 0;
	$is_default   = $_POST['is_default'] ? 1 : 0;
	$is_queued    = $_POST['is_queued'] ? 1 : 0;
	
	$links        = [ 'artists' => $_POST['artist_id'], 'labels' => $_POST['label_id'], 'musicians' => $_POST['musician_id'], 'releases' => $_POST['release_id'] ];
	
	// Run query
	$sql_update = 'UPDATE images SET description=?, friendly=?, credit=?, is_exclusive=?, is_queued=? WHERE id=? LIMIT 1';
	$stmt_update = $pdo->prepare($sql_update);
	
	if($stmt_update->execute([ $description, $friendly, $credit, $is_exclusive, $is_queued, $id ])) {
		$output['status'] = 'success';
		
		// Link to artists/releases/etc
		foreach($links as $link_table => $link_array) {
			$link_column = substr($link_table, 0, -1).'_id';
			
			// Get current links
			$sql_current_links = 'SELECT id, '.$link_column.' FROM images_'.$link_table.' WHERE image_id=?';
			$stmt_current_links = $pdo->prepare($sql_current_links);
			$stmt_current_links->execute([ $id ]);
			foreach($stmt_current_links->fetchAll() as $current_link) {
				$rslt_current_links[$current_link['id']] = $current_link[$link_column];
			}
			$rslt_current_links = is_array($rslt_current_links) ? $rslt_current_links : [];
			
			// Make IDs array if not already
			if(!is_array($link_array) && strlen($link_array)) {
				$link_array = explode(',', $link_array);
			}
			
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
						$stmt_add_link->execute([ $id, $link_array_value ]);
					}
				}
			}
			
			// Delete unneeded links
			if(is_array($rslt_current_links) && !empty($rslt_current_links)) {
				foreach($rslt_current_links as $current_link_id => $current_link_artist_id) {
					$sql_delete_link = 'DELETE FROM images_'.$link_table.' WHERE id=? LIMIT 1';
					$stmt_delete_link = $pdo->prepare($sql_delete_link);
					$stmt_delete_link->execute([ $current_link_id ]);
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
		
		if($is_queued) {
			update_development($pdo, ['type' => 'flyer']);
		}
	}
	else {
		$output['result'] = 'Couldn\'t update.';
	}
}
else {
	$output['result'] = 'Non-numeric ID.'.print_r($_POST, true);
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);