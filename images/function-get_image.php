<?php
include_once("../php/include.php");
include_once("../php/external/class-imageresize.php");

function get_image($input, $pdo) {
	
	// If trying to get an artist's main image, find the ID of that image
	$artist = sanitize($input["artist"]);
	if(strlen($artist)) {
		$sql_artist = "SELECT images.id, images.extension FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.friendly=? LIMIT 1";
		$stmt_artist = $pdo->prepare($sql_artist);
		$stmt_artist->execute([ $artist ]);
		
		$artist = $stmt_artist->fetch();
		$input["id"] = $artist["id"];
		$input["ext"] = $artist["extension"];
	}
	
	// Clean up the inputs
	$id = sanitize($input["id"]);
	$ext = strtolower(sanitize($input["ext"]));
	$method = sanitize($input["method"]);
	
	// Set up allowed extensions, sizes, etc
	$allowed_extensions = ["jpg", "jpeg", "png", "gif"];
	$allowed_methods = ["thumbnail", "small", "medium", "large"];
	$max_sizes = [ 'thumbnail' => 100, 'small' => 150, 'medium' => 300, 'large' => 600, 'watermarked' => 800 ];
	
	// Set file paths
	$source_image_path = "../images/image_files/".$id.".".$ext;
	$resized_image_path = "../images/image_files_".$method."/".$id.".".$ext;
	$watermarked_image_path = "../images/image_files_watermarked/".$id.".".$ext;
	
	// If we have an ID and extension, and that file exists, move ahead
	if(is_numeric($id) && !empty($ext) && in_array($ext, $allowed_extensions)) {
		if(file_exists($source_image_path)) {
			
			// Get image info
			$sql_image = "SELECT is_exclusive, is_queued, user_id FROM images WHERE id=? LIMIT 1";
			$stmt_image = $pdo->prepare($sql_image);
			$stmt_image->execute([$id]);
			$rslt_image = $stmt_image->fetch();
			$is_exclusive = $rslt_image["is_exclusive"];
			
			// If resized
			if(!empty($method) && in_array($method, $allowed_methods)) {
				
				// Get height and width of image
				list($width, $height) = getimagesize($source_image_path);
				
				// Get height:width ratio so we decide to resize
				$size_ratio = $height && $width ? $height / $width : null;
				$orientation = $size_ratio ? ( $size_ratio > 1 ? 'vertical' : 'horizontal' ) : null;
				
				// If image is gif, or height/width can't be determined, or both height and width are less than max size, return original image
				if( $ext == 'gif' || !$height || !$width || ($height < $max_sizes[$method] && $width < $max_sizes[$method]) ) {
					$returned_image_path = $source_image_path;
				}
				
				// If resized image necessary, return it
				else {
					
					// If resized image already exists, return it
					if(file_exists($resized_image_path)) {
						$returned_image_path = $resized_image_path;
					}
					
					// Otherwise, try to resize image now
					else {
						
						// Temporarily up memory limit so we can resize
						function setMemoryLimit($height, $width) {
							
							//this might take time so we limit the maximum execution time to 50 seconds
							set_time_limit(50);
							
							//initializing variables
							$maxMemoryUsage = 256;
							$size = ini_get('memory_limit');
							
							//calculating the needed memory
							$size = $size + floor(($width * $height * 4 * 1.5 + 1048576) / 1048576);
							
							if ($size --> $maxMemoryUsage) {
								$size = $maxMemoryUsage;
							}
							
							//updating the default value
							ini_set('memory_limit',$size.'M');
							
						}
						setMemoryLimit($height, $width);
						
						// Set up image object
						$image = new \Eventviva\ImageResize($source_image_path);
						
						// Thumbnail gets cropped to 100x100
						if($method === "thumbnail") {
							$image->resizeToWidth(100);
						}
						
						// Other resize methods get largest dimension set to max
						else {
							$image->resizeToBestFit( $max_sizes[$method], $max_sizes[$method] );
						}
						
						// If able to save new image, return it; otherwise default
						if($image->save($resized_image_path)) {
							$returned_image_path = $resized_image_path;
						}
						else {
							$returned_image_path = $source_image_path;
						}
						
					}
					
				}
				
			}
			
			// If full size request
			else {
				
				// If exclusive, check whether or not we can serve it
				if($is_exclusive) {
					
					// If authorized in Mp3tag, allow access to VIP image
					if(stripos($_SERVER["HTTP_USER_AGENT"], "mp3tag") !== false && strlen($_GET["username"]) > 0 && strlen($_GET["hash"]) > 0) {
						$sql_verify = "SELECT 1 FROM users WHERE username=? AND tag_hash=? AND is_vip=? LIMIT 1";
						$stmt_verify = $pdo->prepare($sql_verify);
						$stmt_verify->execute([ sanitize($_GET["username"]), sanitize($_GET["hash"]), 1 ]);
						$rslt_verify = $stmt_verify->fetchColumn();
						
						$is_vip = $rslt_verify ? true : false;
					}
					
					// Allow signed-in VIP members to view full
					elseif($_SESSION['is_vip']) {
						$is_vip = true;
					}
					
					// If user is *not* allowed to view image, or if forcing to display non-VIP, display watermarked version
					if( (!$is_vip && $_SESSION["user_id"] !== $rslt_image["user_id"]) || $input["not_vip"] ) {
						
						// If watermarked version cached, return it
						if(file_exists($watermarked_image_path)) {
							$returned_image_path = $watermarked_image_path;
						}
						
						// Otherwise create new watermarked image
						else {
							$new_image = new \Eventviva\ImageResize($source_image_path);
							$new_image->resizeToBestFit( $max_sizes['watermarked'], $max_sizes['watermarked'] );
							$new_image->save($watermarked_image_path);
							
							$sql_user = "SELECT users.username FROM images LEFT JOIN users ON users.id=images.user_id WHERE images.id=? LIMIT 1";
							$stmt_user = $pdo->prepare($sql_user);
							if($stmt_user->execute([$id])) {
								$username = $stmt_user->fetchColumn();
							}
							
							if($ext === "png") {
								$watermarked_image = imagecreatefrompng($watermarked_image_path);
							}
							elseif($ext === "gif") {
								$watermarked_image = imagecreatefromgif($watermarked_image_path);
							}
							elseif($ext === "jpg" || $ext === "jpeg") {
								$watermarked_image = imagecreatefromjpeg($watermarked_image_path);
							}
							
							// If watermarked image creation successful, return
							if(is_resource($watermarked_image)) {
								$height = imagesy($watermarked_image);
								$width = imagesx($watermarked_image);
								$watermark_size = imagettfbbox(16, 0, "../style/font-lucida.ttf", "vk.gy");
								$user_watermark_size = imagettfbbox(12, 0, "../style/font-lucida.ttf", $username);
								
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] + 1, $height - 35 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", "vk.gy");
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] - 1, $height - 35 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", "vk.gy");
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] + 1, $height - 35 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", "vk.gy");
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] - 1, $height - 35 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", "vk.gy");
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2], $height - 35, imagecolorallocatealpha($watermarked_image, 255,255,255, 0), "../style/font-lucida.ttf", "vk.gy");
								
								if(!empty($username)) {
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] + 1, $height - 15 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] + 1, $height - 15 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] - 1, $height - 15 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] - 1, $height - 15 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), "../style/font-lucida.ttf", $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2], $height - 15, imagecolorallocatealpha($watermarked_image, 255,255,255, 0), "../style/font-lucida.ttf", $username);
								}
								
								imagejpeg($watermarked_image, $watermarked_image_path, 100);
								
								if($ext === "png") {
									imagepng($watermarked_image, $watermarked_image_path, 0);
								}
								elseif($ext === "gif") {
									imagegif($watermarked_image, $watermarked_image_path);
								}
								elseif($ext === "jpg" || $ext === "jpeg") {
									imagejpeg($watermarked_image, $watermarked_image_path, 100);
								}
								
								$returned_image_path = $watermarked_image_path;
							}
							
							// Unset if watermark failed
							else {
								unset($returned_image_path);
							}
							
						}
						
					}
					
					// If allowed to view full version, return it
					else {
						$returned_image_path = $source_image_path;
					}
					
				}
				
				// If not exclusive, return full path
				else {
					$returned_image_path = $source_image_path;
				}
				
			}
			
			// If returned path is set, and image isn't queued (or is queued but being viewed by uploader), show the image
			if(
				($returned_image_path && !$rslt_image['is_queued'])
				||
				($returned_image_path && $rslt_image['is_queued'] && !$input['is_hotlinked'])
				||
				($returned_image_path && $rslt_image['is_queued'] && $rslt_image['user_id'] === $_SESSION['user_id'])
			) {
				
				// Return image path only
				if($input["image_path_only"]) {
					return $returned_image_path;
				}
				
				// Print out image
				else {
					session_cache_limiter("public");
					header("Content-Type: image/".$ext);
					header("Content-Length: ".filesize($returned_image_path));
					header("Expires: ".gmdate( "D, d M Y H:i:s", time()+120 )." GMT");
					header('Cache-Control: max-age=120');
					readfile($returned_image_path);
				}
				
			}
			
			// 404 if image doesn't exist
			else {
				header("Location: /404/");
			}
			
		}
		
		// 404 if file doesn't exist
		else {
			header("Location: /404/");
		}
	}
	
}