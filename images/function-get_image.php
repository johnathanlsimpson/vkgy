<?php
	include_once("../php/include.php");
	
	if($_SESSION['username'] === 'inartistic') {
		include('function-get_image-inartistic.php');
		//echo '*';
	}
else {
	include_once("../php/external/class-imageresize.php");
	function get_image($input, $pdo) {
		$artist = sanitize($input["artist"]);
		
		if(!empty($artist)) {
			$sql_artist = "SELECT images.id, images.extension FROM artists LEFT JOIN images ON images.artist_id=CONCAT('(', artists.id, ')') AND images.is_default=? AND images.is_release IS NULL WHERE artists.friendly=? LIMIT 1";
			$stmt_artist = $pdo->prepare($sql_artist);
			$stmt_artist->execute([1, $artist]);
			
			$artist = $stmt_artist->fetch();
			$input["id"] = $artist["id"];
			$input["ext"] = $artist["extension"];
		}
		
		$id = sanitize($input["id"]);
		$ext = strtolower(sanitize($input["ext"]));
		$method = sanitize($input["method"]);
		
		$allowed_extensions = ["jpg", "jpeg", "png", "gif"];
		$allowed_methods = ["thumbnail", "small", "medium", "large"];
		
		$source_image_path = "../images/image_files/".$id.".".$ext;
		$resized_image_path = "../images/image_files_".$method."/".$id.".".$ext;
		$watermarked_image_path = "../images/image_files_watermarked/".$id.".".$ext;
		
		if(is_numeric($id) && !empty($ext) && in_array($ext, $allowed_extensions)) {
			if(file_exists($source_image_path)) {
				// If resized
				if(!empty($method) && in_array($method, $allowed_methods)) {
					if($ext === "gif") {
						$returned_image_path = $source_image_path;
					}
					else {
						if(!file_exists($resized_image_path)) {
							
							function setMemoryLimit($filename){
								//this might take time so we limit the maximum execution time to 50 seconds
								set_time_limit(50);

								//initializing variables
								$maxMemoryUsage = 256;
								$width = 0;
								$height = 0;
								$size = ini_get('memory_limit');

								//getting the image width and height
								list($width, $height) = getimagesize($filename);

								//calculating the needed memory
								$size = $size + floor(($width * $height * 4 * 1.5 + 1048576) / 1048576);

								if ($size --> $maxMemoryUsage){
										$size = $maxMemoryUsage;
							 }

							 //updating the default value
							 ini_set('memory_limit',$size.'M');
							}
							setMemoryLimit($source_image_path);
							
							
							$image = new \Eventviva\ImageResize($source_image_path);
							
							if($method === "thumbnail") {
								$image->resizeToWidth(100);
							}
							elseif($method === "small") {
								$image->resizeToBestFit(150, 150);
							}
							elseif($method === "medium") {
								$image->resizeToWidth(300);
							}
							elseif($method === "large") {
								$image->resizeToBestFit(600, 400);
							}
							
							$image->save($resized_image_path);
						}
						
						$ext = "jpg";
						$returned_image_path = $resized_image_path;
					}
				}
				else {
					$sql_image = "SELECT is_exclusive, user_id FROM images WHERE id=? LIMIT 1";
					$stmt_image = $pdo->prepare($sql_image);
					$stmt_image->execute([$id]);
					$rslt_image = $stmt_image->fetch();
					$is_exclusive = $rslt_image["is_exclusive"];
					
					// If exclusive
					if($is_exclusive) {
						// Allow full-size images in Mp3tag
						if(stripos($_SERVER["HTTP_USER_AGENT"], "mp3tag") !== false && strlen($_GET["username"]) > 0 && strlen($_GET["hash"]) > 0) {
							$sql_verify = "SELECT 1 FROM users WHERE username=? AND tag_hash=? LIMIT 1";
							$stmt_verify = $pdo->prepare($sql_verify);
							$stmt_verify->execute([ sanitize($_GET["username"]), sanitize($_GET["hash"]) ]);
							$rslt_verify = $stmt_verify->fetchColumn();
							
							$is_vip = $rslt_verify ? true : false;
						}
						elseif($_SESSION["loggedIn"] && !$is_vip) {
							$sql_check_vip = "SELECT 1 FROM users WHERE id=? AND is_vip=? LIMIT 1";
							$stmt_check_vip = $pdo->prepare($sql_check_vip);
							$stmt_check_vip->execute([sanitize($_SESSION["userID"]), 1]);
							
							if($stmt_check_vip->fetchColumn() === "1") {
								$is_vip = true;
							}
						}
						else {
							$is_vip = false;
						}
						
						if(!$is_vip && $_SESSION["userID"] !== $rslt_image["user_id"] || $input["not_vip"]) {
							$new_image = new \Eventviva\ImageResize($source_image_path);
							$new_image->resizeToBestFit(800, 800);
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
							else {
								unset($returned_image_path);
							}
						}
						else {
							$returned_image_path = $source_image_path;
						}
					}
					else {
						$returned_image_path = $source_image_path;
					}
				}
				
				if($returned_image_path) {
					if($input["image_path_only"]) {
						return $returned_image_path;
					}
					else {
						session_cache_limiter("public");
						header("Content-Type: image/".$ext);
						header("Content-Length: ".filesize($returned_image_path));
						header("Expires: ".gmdate( "D, d M Y H:i:s", time()+120 )." GMT");
						header('Cache-Control: max-age=120');
						readfile($returned_image_path);
					}
				}
				else {
					header("Location: /404/");
				}
			}
			else {
				header("Location: /404/");
			}
		}
	}
}
?>