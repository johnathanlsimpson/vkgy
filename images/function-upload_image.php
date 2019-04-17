<?php
	include_once('../php/include.php');
	include_once('../php/external/class-tinify.php');
	include_once('../php/external/class-gumletImageResize.php');
	include_once('../php/function-post_deploy.php');
	
	function upload_image($image, $pdo) {
		if(is_array($image) && !empty($image)) {
			$name              = strtolower($image["name"]);
			$type              = $image["type"];
			$tmp_name          = $image["tmp_name"];
			$error             = $image["error"];
			$user_id           = $_SESSION["userID"] ?: 0;
			$queued            = $image["queued"] ?: false;
			$needs_compression = isset($image["needs_compression"]) ? $image["needs_compression"] : true;
			
			if($error === 0 && preg_match("/"."image.+"."/", $type)) {
				$sql_init = $queued ? "INSERT INTO images_queued (extension, user_id, is_exclusive) VALUES (?, ?, '1')" : "INSERT INTO images (extension, user_id) VALUES (?, ?)";
				$stmt_init = $pdo->prepare($sql_init);
				$stmt_init->execute(["jpg", $user_id]);
				
				$id = $pdo->lastInsertId();
				$new_name = "../images/tmp/".$id."-".$name;
				
				if(is_numeric($id)) {
					if(($queued && rename($tmp_name, $new_name)) || move_uploaded_file($tmp_name, $new_name)) {
						$extension = strtolower(pathinfo($new_name, PATHINFO_EXTENSION));
						$file_name = $id.".".$extension;
						
						if(in_array($extension, ["jpg", "jpeg", "png", "gif"])) {
							$sql_update = $queued ? "UPDATE images_queued SET extension=? WHERE id=? LIMIT 1" : "UPDATE images SET extension=? WHERE id=? LIMIT 1";
							$stmt_update = $pdo->prepare($sql_update);
							
							if($stmt_update->execute([$extension, $id])) {
								
								if(rename($new_name, "../images/tmp/".$file_name)) {
									
									// There's an error with Tinify with certain images, but will have to fix later
									unset($needs_compression);
									if($needs_compression && $extension !== "gif") {
										
										// Let's try setting a timelimit here, then do some other stuff I guess
										set_time_limit(10);
										
										try {
											if($source = \Tinify\fromFile("../images/tmp/".$file_name)) {
												if($source->toFile(($queued ? "../images/image_files_queued/" : "../images/image_files/").$file_name)) {
													unlink("../images/tmp/".$file_name);
												}
											}
										}
										catch(\Tinify\Exception $e) {
											rename("../images/tmp/".$file_name, ($queued ? "../images/image_files_queued/" : "../images/image_files/").$file_name);
										}
									}
									else {
										rename("../images/tmp/".$file_name, ($queued ? "../images/image_files_queued/" : "../images/image_files/").$file_name);
									}
									
									if($queued) {
										$image_thumb = new \Gumlet\ImageResize("../images/image_files_queued/".$file_name);
										$image_thumb->resizeToWidth(100);
										$image_thumb->save("../images/image_files_queued_thumbnail/".$file_name);
										
										update_development($pdo, [ "type" => "flyer", "user_id" => $_SESSION["userID"] ]);
									}
									
									$output["status"] = "success";
									$output["image_id"] = $id;
									$output["image_style"] = 'background-image: url(/images/'.$id.'.thumbnail.jpg);';
									$output["image_markdown"] = "![](/images/".$file_name.")";
									$output['scanned_by_text'] = 'you';
								}
								else {
									$output["result"] = "Couldn't rename file.";
								}
							}
							else {
								$output["result"] = "Couldn't update database.";
							}
						}
						else {
							$output["result"] = "Extension not allowed.";
						}
					}
					else {
						$output["result"] = "Couldn't upload file.";
					}
				}
				else {
					$output["result"] = "Couldn't get ID.";
				}
			}
			else {
				$output["result"] = "The image couldn't be uploaded. ".$error."*".$type;
			}
		}
		else {
			$output["result"] = "Image data empty.";
		}
		
		$output["status"] = $output["status"] ?: "error";
		
		echo $queued ? null : json_encode($output);
	}
	
	if(!$suppress_auto_upload_image) {
		upload_image($_FILES["image"], $pdo);
	}
?>