<?php
	include_once('../php/include.php');
	include_once('../php/class-access_social_media.php');
	include_once('../images/function-get_image.php');
	$access_artist = new access_artist($pdo);
	$access_social_media = new access_social_media($pdo);
	
	// Get IDs of artists that have biography entries
	$sql_artists = "SELECT artist_id FROM artists_bio GROUP BY artist_id";
	$stmt_artists = $pdo->prepare($sql_artists);
	$stmt_artists->execute();
	$rslt_artists = $stmt_artists->fetchAll();
	
	// Reformat IDs into list
	if(is_array($rslt_artists) && !empty($rslt_artists)) {
		for($i = 0; $i < count($rslt_artists); $i++) {
			$rslt_artists[$i] = $rslt_artists[$i]["artist_id"];
		}
	}
	
	// Get list of artists that have been AOD
	$sql_extant = "SELECT artist_id FROM queued_aod";
	$stmt_extant = $pdo->prepare($sql_extant);
	$stmt_extant->execute();
	$rslt_extant = $stmt_extant->fetchAll();
	
	// Reformat IDs into list
	if(is_array($rslt_extant) && !empty($rslt_extant)) {
		for($i = 0; $i < count($rslt_extant); $i++) {
			$rslt_extant[$i] = $rslt_extant[$i]["artist_id"];
		}
	}
	
	if(is_array($rslt_artists) && !empty($rslt_artists)) {
		
		// While AOD has not been found, and there are still possible selections
		while(!$aod_found && count($rslt_artists) > 0) {
			
			// Choose a random artist from the list of possible ones
			$rand_key = mt_rand(0, count($rslt_artists));
			$aod_id = $rslt_artists[$rand_key];
			
			// If that random artist has been chosen before, unset
			if(in_array($aod_id, $rslt_extant)) {
				unset($rslt_artists[$rand_key], $rand_key, $aod_id);
				$rslt_artists = array_values($rslt_artists);
			}
			else {
				
				// Otherwise, check if that random artist has an artist image
				$sql_check_image = "SELECT id, extension FROM images WHERE is_default=? AND artist_id=CONCAT('(', ?, ')') AND is_release IS NULL LIMIT 1";
				$stmt_check_image = $pdo->prepare($sql_check_image);
				$stmt_check_image->execute([ 1, $aod_id ]);
				$rslt_check_image = $stmt_check_image->fetch();
				
				// If they have an image, AOD found; otherwise, unset this choice and try again
				if(is_array($rslt_check_image) && is_numeric($rslt_check_image['id'])) {
					$aod_found = true;
				}
				else {
					unset($rslt_artists[$rand_key], $rand_key, $aod_id);
					$rslt_artists = array_values($rslt_artists);
				}
			}
		}
		
		// Queue chosen AOD
		if($aod_found && is_numeric($aod_id) && is_numeric($rslt_check_image['id'])) {
			$aod['filepath'] = get_image([ 'id' => $rslt_check_image['id'], 'ext' => $rslt_check_image["extension"], 'not_vip' => true, 'image_path_only' => true ], $pdo);
			
			$sql_aod = "INSERT INTO queued_aod (artist_id) VALUES (?)";
			$stmt_aod = $pdo->prepare($sql_aod);
			$stmt_aod->execute([ $aod_id ]);
			
			$social_post = $access_social_media->build_post($aod, 'artist_of_day');
			$access_social_media->post_to_social($social_post, 'both');
		}
	}
?>