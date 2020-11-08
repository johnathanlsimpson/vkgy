<?php

include_once('../php/include.php');
include_once('../php/external/class-tinify.php');
include_once('../php/external/class-gumletImageResize.php');
include_once('../php/function-post_deploy.php');

function upload_image($image, $pdo) {
	
	$allowed_extensions = [
		'gif',
		'jpeg',
		'jpg',
		'png',
		'webp',
	];
	
	session_write_close();
	
	foreach($_POST as $key => $value) {
		if(strpos($key, 'image_') === 0) {
			$new_key = substr($key, 6);
			
			$_POST[$new_key] = $value;
			
			unset($_POST[$key]);
		}
	}

	if(is_array($image) && !empty($image)) {
		$name      = preg_replace('/'.'[^A-z0-9\-\.]'.'/', '', $image['name']);
		$type      = $image['type'];
		$tmp_name  = $image['tmp_name'];
		$error     = $image['error'];
		$user_id   = $_SESSION['user_id'] ?: 0;
		$item_type = sanitize($_POST['item_type']);
		$item_id   = sanitize($_POST['item_id']);
		$is_queued = $_POST['is_queued'] ? 1 : 0;
		$hash      = sha1_file($tmp_name);
		
		// Check DB for images with same file hash, to prevent dupes
		$sql_dupes = 'SELECT id, extension FROM images WHERE hash=? LIMIT 1';
		$stmt_dupes = $pdo->prepare($sql_dupes);
		$stmt_dupes->execute([ $hash ]);
		$dupe = $stmt_dupes->fetch();
		
		// If dupe, just return image info
		/*if( is_array($dupe) && !empty($dupe) ) {
			$output['status']           = 'success';
			$output['image_id']         = $dupe['id'];
			$output['image_url']        = '/images/'.$dupe['id'].'.'.$dupe['extension'];
			$output['image_style']      = 'background-image: url(/images/'.$dupe['id'].'.thumbnail.'.$dupe['extension'].');';
			$output['image_markdown']   = '![](/images/'.$file_name.')';
			$output['is_exclusive_for'] = 'is-exclusive-'.$dupe['id'];
			$output['is_default_for']   = 'is-default-'.$dupe['id'];
			$output['item_type']        = $item_type;
			$output['item_id']          = $item_id;
			$output[$item_type.'_id']   = $item_id;
			$output['image_status']     = 'new';
			$output['image_extension']  = $dupe['extension'];
			$output['is_dupe']          = 1;
			$is_dupe = true;
		}*/
		
		// Move forward if not dupe and no initial error and appears to be image
		if( !$is_dupe && $error === 0 && preg_match('/'.'image.+'.'/', $type) ) {
			$sql_init = 'INSERT INTO images (extension, user_id) VALUES (?, ?)';
			$stmt_init = $pdo->prepare($sql_init);
			$stmt_init->execute([ 'jpg', $user_id ]);
			
			$id = $pdo->lastInsertId();
			$new_tmp_name = '../images/tmp/'.$id.'-'.$name;
			
			if(is_numeric($id)) {
				if(($queued && rename($tmp_name, $new_tmp_name)) || move_uploaded_file($tmp_name, $new_tmp_name)) {
					
					// Get actual file type
					$extension = strtolower( pathinfo($new_tmp_name, PATHINFO_EXTENSION) );
					$image_info = getimagesize($new_tmp_name);
					$file_type = $image_info[2];
					
					// Make sure webp type is set
					if( !defined('IMAGETYPE_WEBP') ) {
						define('IMAGETYPE_WEBP', 18);
					}
					
					// Set extension based on actual file type
					switch($file_type) {
						case IMAGETYPE_GIF:
							$extension = 'gif';
							break;
						case IMAGETYPE_JPEG:
							$extension = 'jpg';
							break;
						case IMAGETYPE_PNG:
							$extension = 'png';
							break;
						case IMAGETYPE_WEBP:
							$extension = 'webp';
							break;
						default:
							$extension = null;
					}
					
					// Convert webp to jpg, since some users still can't see them
					if($extension === 'webp') {
						
						$webp_image = new \Gumlet\ImageResize($new_tmp_name);
						$new_tmp_name = '../images/tmp/'.$id.'.jpg';
						
						// Attempt to save
						if($webp_image->save($new_tmp_name, IMAGETYPE_JPEG, 100)) {
							$extension = 'jpg';
						}
						
						// If failed, return null so rest of function stops
						else {
							$extension = null;
						}
						
					}
					
					// Set the final file name
					$file_name = $id.'.'.$extension;
					
					if( in_array($extension, $allowed_extensions) ) {
						
						$sql_update = 'UPDATE images SET extension=?, is_queued=?, item_type=?, hash=? WHERE id=? LIMIT 1';
						$stmt_update = $pdo->prepare($sql_update);
						
						if($stmt_update->execute([ $extension, $is_queued, $item_type, $hash, $id ])) {
							
							if(rename($new_tmp_name, '../images/tmp/'.$file_name)) {
								
								rename('../images/tmp/'.$file_name, ($queued ? '../images/image_files_queued/' : '../images/image_files/').$file_name);
								
								if($queued) {
									$image_thumb = new \Gumlet\ImageResize('../images/image_files_queued/'.$file_name);
									$image_thumb->resizeToWidth(100);
									$image_thumb->save('../images/image_files_queued_thumbnail/'.$file_name);
									
									update_development($pdo, [ 'type' => 'flyer', 'user_id' => $_SESSION['user_id'] ]);
								}
								
								// Set data for update_image function
								$_POST[$item_type.'_id'] = $item_id;
								$_POST['id'] = $id;
								$suppress_output = true;
								include_once('../images/function-update_image.php');
								
								$output['status'] = 'success';
								$output['image_id'] = $id;
								$output['image_url'] = '/images/'.$id.'.'.$extension;
								$output['image_style'] = 'background-image: url(/images/'.$id.'.thumbnail.'.$extension.');';
								$output['image_markdown'] = '![](/images/'.$file_name.')';
								$output['is_exclusive_for'] = 'is-exclusive-'.$id;
								$output['is_default_for'] = 'is-default-'.$id;
								$output['item_type'] = $item_type;
								$output['item_id'] = $item_id;
								$output[$item_type.'_id'] = $item_id;
								$output['image_status'] = 'new';
								$output['image_extension'] = $extension;
								
								// Award point here, but don't show it until update_image, since status elem might not exist until that point
								$access_points = new access_points($pdo);
								$access_points->award_points([ 'point_type' => 'added-image' ]);
							}
							else {
								$output['result'][] = 'Couldn\'t rename file.';
							}
						}
						else {
							$output['result'][] = 'Couldn\'t update database.';
						}
					}
					else {
						$output['result'][] = 'Only jpg, jpeg, gif, or png accepted.';
					}
				}
				else {
					$output['result'][] = 'Couldn\'t upload file.';
				}
			}
			else {
				$output['result'][] = 'Couldn\'t get ID.';
			}
		}
		elseif( !$is_dupe ) {
			$output['result'][] = 'The image couldn\'t be uploaded. '.$error.'*'.$type;
		}
		
	}
	else {
		$output['result'][] = 'Image data empty.';
	}

	$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
	$output['status'] = $output['status'] ?: 'error';
	
	echo $queued ? null : json_encode($output);
}

if(!$suppress_auto_upload_image) {
	upload_image($_FILES['image'], $pdo);
}