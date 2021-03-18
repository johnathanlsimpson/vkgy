<?php

include_once('../php/include.php');
include_once('../php/class-access_image.php');
include_once('../php/external/class-tinify.php');
include_once('../php/external/class-gumletImageResize.php');
include_once('../php/function-post_deploy.php');

function upload_image($image, $pdo) {
	
	// Stops the site from freezing when uploading large images
	session_write_close();
	
	// Fields are named image_xxx, so just get rid of that first chunk
	foreach($_POST as $key => $value) {
		if(strpos($key, 'image_') === 0) {
			$new_key = substr($key, 6);
			
			$_POST[$new_key] = $value;
			
			unset($_POST[$key]);
		}
	}
	
	if( is_array($image) && !empty($image) ) {
		
		$name      = preg_replace('/'.'[^A-z0-9\-\.]'.'/', '', $image['name']);
		$type      = $image['type'];
		$tmp_name  = $image['tmp_name'];
		$error     = $image['error'];
		$user_id   = $_SESSION['user_id'] ?: 0;
		$item_type = sanitize($_POST['item_type']);
		$item_id   = sanitize($_POST['item_id']);
		$is_queued = $_POST['is_queued'] ? 1 : 0;
		$image_content = 0;
		
		// Check DB for images with same file hash, to prevent dupes
		$hash = sha1_file($tmp_name);
		$sql_dupes = 'SELECT id, extension FROM images WHERE hash=? LIMIT 1';
		$stmt_dupes = $pdo->prepare($sql_dupes);
		$stmt_dupes->execute([ $hash ]);
		$dupe = $stmt_dupes->fetch();
		
		// If dupe, just return image info
		if( is_array($dupe) && !empty($dupe) ) {
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
			$output['is_facsimile']     = 1;
			$is_facsimile = true;
		}
		
		// Move forward if not dupe and no initial error and appears to be image
		if( !$is_facsimile && $error === 0 && preg_match('/'.'image.+'.'/', $type) ) {
			
			// Get actual file type
			list($width, $height, $file_type) = getimagesize($tmp_name);
			
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
			
			if( in_array($extension, access_image::$allowed_extensions) ) {
				
				$sql_init = 'INSERT INTO images (extension, user_id) VALUES (?, ?)';
				$stmt_init = $pdo->prepare($sql_init);
				$stmt_init->execute([ 'jpg', $user_id ]);
				
				$id = $pdo->lastInsertId();
				$new_tmp_name = '../images/tmp/'.$id.'-'.$name;
				
				if(is_numeric($id)) {
					if(($queued && rename($tmp_name, $new_tmp_name)) || move_uploaded_file($tmp_name, $new_tmp_name)) {
						
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
						
						if($extension) {
							
							// If uploading on release, assume image contents are release
							if( $item_type === 'release' ) {
								
								$image_content_name = 'release';
								
							}
							
							// If "queued" (not is_queued, but rather uploading flyer from queue), mark as flyer
							elseif( $queued ) {
								
								$image_content_name = 'flyer';
								
							}
							
							// Otherwise make a guess at the image contents
							else {
								
								// Get size ratio by shortest side
								$size_ratio = $width / $height;
								$size_ratio = sprintf("%01.2f", $size_ratio);
								
								// Check size ratio against standard sizes
								if( access_image::$image_ratios[$size_ratio] ) {
									
									$image_content_name = access_image::$image_ratios[ $size_ratio ];
									
								}
								
							}
							
							$image_content = array_search( $image_content_name, access_image::$allowed_image_contents ) ?: 0;
							
							// Set the final file name
							$file_name = $id.'.'.$extension;
							
							$sql_update = 'UPDATE images SET extension=?, is_queued=?, item_type=?, image_content=?, hash=?, width=?, height=? WHERE id=? LIMIT 1';
							$stmt_update = $pdo->prepare($sql_update);
							
							if($stmt_update->execute([ $extension, $is_queued, $item_type, $image_content, $hash, $width, $height, $id ])) {
								
								if(rename($new_tmp_name, '../images/tmp/'.$file_name)) {
									
									rename('../images/tmp/'.$file_name, ($queued ? '../images/image_files_queued/' : '../images/image_files/').$file_name);
									
									// If uploading queued flyer, save thumbnail so we can show it
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
									$output['image_style'] = 'background-image: url(https://vk.gy/images/'.$id.'.thumbnail.'.$extension.');';
									$output['image_markdown'] = '![](/images/'.$file_name.')';
									$output['image_content'] = $image_content;
									$output['is_exclusive_for'] = 'is-exclusive-'.$id;
									$output['is_default_for'] = 'is-default-'.$id;
									$output['item_type'] = $item_type;
									$output['item_id'] = $item_id;
									$output[$item_type.'_id'] = $item_id;
									$output['image_status'] = 'new';
									$output['image_extension'] = $extension;
									$output['is_new'] = 1;
									
									// Award point here, but don't show it until update_image, since status elem might not exist until that point
									$access_points = new access_points($pdo);
									$access_points->award_points([ 'point_type' => 'added-image' ]);
									
								}
								else {
									$output['result'][] = 'Couldn\'t rename file.';
								}
								
							}
							else {
								$output['result'][] = 'Couldn\'t update database.'.print_r([ $extension, $is_queued, $item_type, $image_content, $hash, $id ], true);
							}
							
						}
						else {
							$output['result'][] = 'Couldn\'t convert file.';
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
			else {
				$output['result'][] = 'File isn\'t a valid jpg, gif, png, or webp.';
			}
		}
		
		// Throw error if couldn't upload (and isn't facsimile)
		elseif( !$is_facsimile ) {
			$output['result'][] = 'The image couldn\'t be uploaded: '.$error;
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