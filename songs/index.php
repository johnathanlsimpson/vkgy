<?php

include_once('../php/class-song.php');
$access_song = new song($pdo);

// ========================================================
// Clean variables
// ========================================================
$allowed_actions = [ 'update', 'view' ];

$action = $_GET['action'];
$action = $action && in_array( $action, $allowed_actions ) ? $action : 'view';

$artist  = strlen($_GET['artist']) ? friendly($_GET['artist']) : null;
$song_id = is_numeric($_GET['song_id']) ? $_GET['song_id'] : null;

// ========================================================
// Check permissions
// ========================================================

// Update song
if( $action === 'update' && !$_SESSION['can_add_data'] ) {
	$error = 'Sorry, you don\'t have permission to update songs. Showing '.( is_numeric($id) ? 'song' : 'all songs' ).' instead.';
	$action = 'view';
}

// ========================================================
// Get data
// ========================================================

// Single song (song ID)
if( strlen($song_id) ) {
	
	// Single song
	$song = $access_song->access_song([ 'id' => $song_id, 'get' => 'all' ]);
	$song = is_array($song) && !empty($song) ? $song : null;
	
	// If song not found, reset page
	if( !$song ) {
		$error = 'The requested song couldn\'t be found. Showing '.( $action == 'update' ? '&ldquo;add new song&rdquo;' : 'index' ).' instead.';
		unset($song_id);
	}
	
}

// All songs
if( strlen($artist) ) {
	
	$songs = $access_song->access_song([ 'artist' => $artist, 'get' => 'all' ]);
	
	// If magazine not found, reset page
	if( !$songs ) {
		
		$error = 'Songs from the requested artist couldn\'t be found. Showing '.( $action == 'update' ? '&ldquo;add new song&rdquo;' : 'index' ).' instead.';
		unset($artist);
		
	}
	
}

// ========================================================
// Page setup
// ========================================================

subnav([
	lang('All songs', '曲の一覧', 'hidden') => '/songs/',
]);

subnav([
	lang('Add song', '曲を追加', 'hidden') => '/songs/add/'.( $artist ? '&artist='.$artist : null ),
], 'interact', true);

// ========================================================
// Display page
// ========================================================

// Add/update song
if( $action === 'update' ) {
	include('../songs/page-update.php');
}

// View song
elseif( $action === 'view' && $song ) {
	include('../songs/page-song.php');
}

// View artist
elseif( $action === 'view' && $songs ) {
	include('../songs/page-artist.php');
}

// View index
else {
	include('../songs/page-index.php');
}