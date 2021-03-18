<?php
include_once("../php/include.php");

foreach($_POST as $key => $value) {
	if(strpos($key, 'image_') === 0) {
		$new_key = substr($key, 6);
		
		$_POST[$new_key] = $value;
		
		unset($_POST[$key]);
	}
}

$image_id = sanitize($_POST['id']);
$item_type = sanitize($_POST['item_type']);
$item_id = sanitize($_POST['item_id']);
$action = $_POST['action'] === 'delete' ? 'delete' : 'unlink';

// Check whether user can delete images
if($_SESSION['can_delete_data']) {
	$can_delete_image = true;
}
else {
	$sql_check_uploader = 'SELECT 1 FROM images WHERE id=? AND user_id=? LIMIT 1';
	$stmt_check_uploader = $pdo->prepare($sql_check_uploader);
	$stmt_check_uploader->execute([ $image_id, $_SESSION['user_id'] ]);
	$can_delete_image = $stmt_check_uploader->fetchColumn();
}

if(is_numeric($image_id) && $can_delete_image) {
	
	// Check if > 1 existing link
	$check_sql = [
		'artist' => 'SELECT COUNT(*) FROM images_artists WHERE image_id=? GROUP BY image_id',
		'blog' => 'SELECT COUNT(*) FROM images_blog WHERE image_id=? GROUP BY image_id',
		'labels' => 'SELECT COUNT(*) FROM images_labels WHERE image_id=? GROUP BY image_id',
		'musician' => 'SELECT COUNT(*) FROM images_musicians WHERE image_id=? GROUP BY image_id',
		'release' => 'SELECT COUNT(*) FROM images_releases WHERE image_id=? GROUP BY image_id',
	];
	
	$check_values = [
		'artist' => $image_id,
		'blog' => $image_id,
		'label' => $image_id,
		'musician' => $image_id,
		'release' => $image_id,
	];
	
	foreach($check_sql as $key => $sql_check) {
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $check_values[$key] ]);
		$rslt_check += $stmt_check->fetchColumn();
	}
	
	// Unlink image (unless image was only linked to one thing, in which case we'll do an actual delete)
	if( $action === 'unlink' && $rslt_check > 1 ) {
		
		$values_delete = [ $image_id ];
		if(is_numeric($item_id)) {
			$values_delete[] = $item_id;
		}
		
		$sql_delete = 'DELETE FROM images_'.$item_type.($item_type != 'blog' ? 's' : null).' WHERE image_id=?'.(is_numeric($item_id) ? ' AND '.$item_type.'_id=?' : null);
		$stmt_delete = $pdo->prepare($sql_delete);
		if($stmt_delete->execute($values_delete)) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = 'Couldn\'t delete link.';
		}
		
	}
	
	// Real delete
	else {
		
		$sql_get = "SELECT extension FROM images WHERE id=? LIMIT 1";
		$stmt_get = $pdo->prepare($sql_get);
		$stmt_get->execute([ $image_id ]);
		
		$extension = $stmt_get->fetchColumn();
		if(!empty($extension)) {
			$sql_delete = "DELETE FROM images WHERE id=? LIMIT 1";
			$stmt_delete = $pdo->prepare($sql_delete);
			
			if($stmt_delete->execute([ $image_id ])) {
				if(file_exists("../images/image_files/".$image_id.".".$extension) && unlink("../images/image_files/".$image_id.".".$extension)) {
					$output["status"] = "success";
					
					// Delete resized versions
					foreach([ 'thumbnail', 'small', 'medium', 'large', 'watermarked' ] as $method) {
						if( $method && $extension && is_numeric($image_id) && file_exists('../images/image_files_'.$method.'/'.$image_id.'.'.$extension) && unlink('../images/image_files_'.$method.'/'.$image_id.'.'.$extension) ) {
						}
					}
					
				}
				else {
					$output["result"] = "Couldn't be deleted.";
				}
			}
			else {
				$output["result"] = "Couldn't be deleted from database.";
			}
		}
		else {
			$output["result"] = "Not found in database.";
		}
		
	}
}
else {
	$output["result"] = 'Only moderators can delete images. Please comment and ask <a href="/users/">a moderator</a> to help.';
}

$output["status"] = $output["status"] ?: "error";

echo json_encode($output);