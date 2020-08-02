<?php
	include_once('../php/include.php');
	include_once('../php/class-access_social_media.php');
	include_once('../php/class-access_image.php');
	
	$access_artist = new access_artist($pdo);
	$access_social_media = new access_social_media($pdo);
	$access_image = new access_image($pdo);
	
	// Select queued flyer
	$sql_flyer = "SELECT * FROM queued_flyers WHERE artist_id IS NOT NULL ORDER BY RAND() LIMIT 1";
	$stmt_flyer = $pdo->prepare($sql_flyer);
	$stmt_flyer->execute();
	$flyer = $stmt_flyer->fetch();
	
	$artist_ids = explode(")", str_replace("(", "", $flyer["artist_id"]));

	if(is_array($flyer) && !empty($flyer)) {
		
		// Move flyer from queued_flyers database to images database
		$sql_unqueue = "INSERT INTO images (extension, is_exclusive, user_id, description, friendly) VALUES (?, ?, ?, ?, ?)";
		$stmt_unqueue = $pdo->prepare($sql_unqueue);
		$input_unqueue = [
			$flyer["extension"],
			"1",
			$flyer["user_id"],
			($flyer["description"] ?: "flyer"),
			($flyer["friendly"] ?: "flyer")
		];
		
		if($stmt_unqueue->execute($input_unqueue)) {
			$new_image_id = $pdo->lastInsertId();
			
			if(is_numeric($flyer["id"]) && is_numeric($new_image_id) && !empty($flyer["extension"])) {
				
				// Move queued flyer file to images folder
				if(rename("../images/image_files_queued/".$flyer["id"].".".$flyer["extension"], "../images/image_files/".$new_image_id.".".$flyer["extension"])) {
					
					// Remove item from queued_flyers database
					$sql_delete = "DELETE FROM queued_flyers WHERE id=? LIMIT 1";
					$stmt_delete = $pdo->prepare($sql_delete);
					$stmt_delete->execute([ $flyer["id"] ]);
					
					// Change current FOD to selected flyer
					$sql_fod = "UPDATE queued_fod SET image_id=? WHERE id=?";
					$stmt_fod = $pdo->prepare($sql_fod);
					$stmt_fod->execute([ $new_image_id, 1 ]);
					
					// Create link
					foreach($artist_ids as $artist_id) {
						$sql_link = 'INSERT INTO images_artists (image_id, artist_id) VALUES (?, ?)';
						$stmt_link = $pdo->prepare($sql_link);
						$stmt_link->execute([ $new_image_id, $artist_id ]);
					}
					
					// Get artist info from flyer
					if(is_numeric($artist_id[0])) {
						$flyer['artist'] = $access_artist->access_artist(["id" => $artist_id[0], "get" => "name"]);
					}
					
					// Get filepath of watermarked version of flyer, set URL for normal access
					$flyer['filepath'] = $access_image->get_image([ 'id' => $new_image_id, 'not_vip' => true, 'image_path_only' => true ]);
					$flyer['url'] = 'https://vk.gy/images/'.$new_image_id.'-'.($flyer['friendly'] ?: 'flyer').'.'.$flyer['extension'];
					
					// Build and queue social media post
					$social_post = $access_social_media->build_post($flyer, 'flyer_of_day');
					$access_social_media->post_to_social($social_post, 'both');
				}
			}
		}
	}
?>