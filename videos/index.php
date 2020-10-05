<?php

// Includes
include_once('../php/include.php');
include_once('../php/class-access_video.php');
include_once('../php/function-paginate.php');
$access_video = new access_video($pdo);

// Testing
$headless = $_GET['headless'] ? true : false;

// Get data: ID
if( is_numeric($_GET['id']) ) {
	
	// Get requested video
	$video = $access_video->access_video([ 'get' => 'all', 'id' => $_GET['id'] ]);
	
	if( is_array($video) && !empty($video) ) {
		
		// Returns array of videos
		$video = reset($video);
		
		// Get other videos from this artist
		$artist_videos = $access_video->access_video([ 'get' => 'basics', 'artist_id' => $video['artist']['id'] ]);
		
		// Set view
		$view = 'id';
		
	}
	
}

// Get data: index
else {
	
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
	$videos = $access_video->access_video([ 'get' => 'all', 'page' => $page, 'limit' => 5 ]);
	$pagination = paginate( is_array($videos) && !empty($videos[0]) && $videos[0]['meta'] ? $videos[0]['meta'] : [] );
	
	// Set view
	$view = 'index';
	
}

// Get view
if($view === 'id') {
	
	include('../videos/page-id.php');
	
}
else {
	
	if( is_array($videos) && !empty($videos) ) {
		
		if($headless) {
			include('../videos/partial-index.php');
		}
		else {
			include('../videos/page-index.php');
		}
		
	}
	
}