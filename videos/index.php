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
		
		// Format YouTube description
		$video['youtube_content'] = str_replace( [ "\r", "\n" ], [ '', '<br />' ], $video['youtube_content'] );
		
		// Get other videos from this artist
		$artist_videos = $access_video->access_video([ 'get' => 'basics', 'artist_id' => $video['artist']['id'] ]);
		$num_artist_videos = count($artist_videos);
		
		// Remove current videos from artist_videos
		foreach($artist_videos as $artist_video_key => $artist_video) {
			if($artist_video['id'] === $video['id']) {
				
				// Save the key so we can do stuff later, then unset
				$current_artist_video_index = $artist_video_key;
				unset($artist_videos[$artist_video_key]);
				
			}
		}
		
		// Get next video in loop of all videos from artist
		if( $num_artist_videos > 1 ) {
			
			// If at end of loop, get video at 0th index, otherwise get video at next index
			if( $current_artist_video_index + 1 === $num_artist_videos ) {
				$next_artist_video_index = 0;
			}
			else {
				$next_artist_video_index = $current_artist_video_index + 1;
			}
			
			$next_artist_video = $artist_videos[ $next_artist_video_index ];
			
		}
		
		
		// Set view
		$view = 'id';
		
	}
	else {
		
		header('Location: /videos/&error=not_found');
		
	}
	
}

// Get data: index
else {
	
	// Errors
	$errors = [
		'not_found' => 'The requested video doesn\'t exist. Showing all videos instead.'
	];
	
	if( $_GET['error'] ) {
		$error = $errors[ $_GET['error'] ];
	}
	
	// Get users for filter list, if viewing as someone who can approve data
	if( $_SESSION['can_approve_data'] ) {
		$access_user = new access_user($pdo);
		$users = $access_user->access_user([ 'get' => 'name' ]);
	}
	
	// Default filters
	$type = null;
	$order = 'date_added DESC';
	$is_flagged = $_SESSION['can_approve_data'] ? -1 : 0;
	$user_id = null;
	
	// Filters
	foreach( $_GET as $key => $value ) {
		
		// Type (really should be an array but our URL is fucky)
		if( strpos($key, 'type_') === 0 && in_array($value, $access_video->video_types) ) {
			
			$type[] = $access_video->video_types[ $value ];
			
		}
		
		// Order
		if( $key === 'sort' ) {
			
			if( $value === 'date_added' ) {
				$order = 'videos.date_added DESC';
			}
			if( $value === 'date_occurred' ) {
				$order = 'videos.date_occurred DESC';
			}
			elseif( $value === 'num_views' ) {
				$order = 'views_videos_daily.num_views DESC';
			}
			
		}
		
		// Date published
		if( $key === 'date_occurred' ) {
			$date_occurred = $value;
		}
		
		// Flagged status
		if( $key === 'is_flagged' ) {
			
			if( is_numeric($value) && $value < 3 && $_SESSION['can_approve_data'] ) {
				$is_flagged = $value;
			}
			
		}
		
		// User ID
		if( $key === 'user_id' ) {
			
			if( is_numeric($value) ) {
				$user_id = $value;
			}
			
		}
		
	}
	
	// Query
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
	$videos = $access_video->access_video([
		'get' => 'all',
		'date_occurred' => $date_occurred,
		'type' => $type,
		'user_id' => $user_id,
		'is_flagged' => $is_flagged,
		'order' => $order,
		'limit' => 24,
		'page' => $page,
	]);
	$pagination = paginate( is_array($videos) && !empty($videos[0]) && $videos[0]['meta'] ? $videos[0]['meta'] : [] );
	
	// Set view
	$view = 'index';
	
	// Set error
	if( !is_array($videos) || empty($videos) ) {
		$error = 'No results.';
	}
	
}

// View: ID
if($view === 'id') {
	
	include('../videos/page-id.php');
	
}

// View: Index
elseif($view === 'index') {
	
	if($headless) {
		include('../videos/partial-index.php');
	}
	else {
		include('../videos/page-index.php');
	}
	
}