<?php

// Includes
include_once('../php/include.php');
include_once('../php/class-access_video.php');
include_once('../php/function-paginate.php');
$access_video = new access_video($pdo);

// Testing
$headless = $_GET['headless'] ? true : false;

// Subnav
subnav([
	'All videos' => '/videos/'
]);

// Get data: ID
if( is_numeric($_GET['id']) ) {
	
	// Get requested video
	$video = $access_video->access_video([ 'get' => 'all', 'id' => $_GET['id'] ]);
	
	if( is_array($video) && !empty($video) ) {
		
		// Returns array of videos
		$video = reset($video);
		
		// Format YouTube description
		$video['youtube_content'] = str_replace( [ "\r", "\n" ], [ '', '<br />' ], $video['youtube_content'] );
		
		// Get other videos from this artist
		$artist_videos = $access_video->access_video([ 'get' => 'basics', 'artist_id' => $video['artist']['id'] ]);
		
		// Set view
		$view = 'id';
		
	}
	
}

// Get data: index
else {
	
	// Default filters
	$type = null;
	$order = null;
	
	// Filters
	foreach( $_GET as $key => $value ) {
		
		// Video type (really should be an array but our URL is fucky)
		if( strpos($key, 'type_') === 0 && in_array($value, $access_video->video_types) ) {
			
			$type[] = $access_video->video_types[ $value ];
			
		}
		
		// Order
		if( $key === 'sort' ) {
			
			if( $value === 'date_added' ) {
				$order = 'videos.date_added DESC';
			}
			elseif( $value === 'num_views' ) {
				$order = 'views_daily_videos.num_views DESC';
			}
			
		}
		
		// Date published
		if( $key === 'date_occurred' ) {
			$date_occurred = $value;
		}
		
	}
	
	// Query
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
	$videos = $access_video->access_video([ 'get' => 'all', 'page' => $page, 'date_occurred' => $date_occurred, 'type' => $type, 'order' => $order, 'limit' => 21 ]);
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