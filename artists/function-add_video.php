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
		
		$returned_data = $access_video->add_video($youtube_id, $artist_id);
		
		if(is_array($returned_data) && !empty($returned_data)) {
			$output = $returned_data;
			$output['status'] = 'success';
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