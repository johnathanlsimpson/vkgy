<?php

include_once('../php/include.php');
include_once('../php/class-access_video.php');

$access_video = new access_video($pdo);
$input_url = $_POST['url'];
$artist_id = $_POST['artist_id'];

if(strlen($input_url) && $_SESSION['is_signed_in']) {
	
	// Parse URL and get YouTube ID
	$youtube_id = $access_video->get_youtube_id($input_url);
	if($youtube_id) {
		
		// Get video data, if exists
		$video_data = $access_video->get_youtube_data($youtube_id)[0];
		if(is_array($video_data) && !empty($video_data)) {
			
			// If channel ID provided, we'll use that
			if(strlen($video_data['channel_id'])) {
			
				// If artist provided, check if video is from official channel
				// If artist not provided, try to find artist with that channel listed
				$sql_artist = 'SELECT id FROM artists WHERE '.(is_numeric($artist_id) ? 'id=? AND ' : null).' official_links LIKE CONCAT("%", ?, "%") LIMIT 1';
				$values_artist[] = 'youtube.com/channel/'.$video_data['channel_id'];
				if(is_numeric($artist_id)) {
					array_unshift($values_artist, $artist_id);
				}
				
				$stmt_artist = $pdo->prepare($sql_artist);
				$stmt_artist->execute($values_artist);
				$rslt_artist = $stmt_artist->fetchColumn();
				
				if(is_numeric($rslt_artist)) {
					$artist_id = $rslt_artist;
					$is_whitelisted = true;
				}
				
				// If artist was provided, or was found by searching links, go ahead
				if(is_numeric($artist_id)) {
					$values_video = [
						$artist_id,
						is_numeric($release_id) ? $release_id : null,
						$_SESSION['user_id'],
						$youtube_id,
						$video_data['date_occurred'],
						$is_whitelisted ? 0 : 1,
					];
					
					$sql_video = 'INSERT INTO videos (artist_id, release_id, user_id, youtube_id, date_occurred, is_flagged) VALUES (?, ?, ?, ?, ?, ?)';
					$stmt_video = $pdo->prepare($sql_video);
					if($stmt_video->execute($values_video)) {
						$output = $youtube_data;
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t add video.';
					}
					
				}
			}
		}
		else {
			$output['result'] = 'No data found.';
		}
	}
	else {
		$output['result'] = 'Video ID not found.';
	}
}
else {
	$output['result'] = 'URL missing or not signed in.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);