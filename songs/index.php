<?php

include_once('../php/class-song.php');
$access_song = new song($pdo);
$access_artist = new access_artist($pdo);

// ========================================================
// Clean variables
// ========================================================
$allowed_actions = [ 'add', 'edit', 'view' ];

$action = $_GET['action'];
$action = $action && in_array( $action, $allowed_actions ) ? $action : 'view';

$artist  = strlen($_GET['artist']) ? friendly($_GET['artist']) : null;
$song_id = is_numeric($_GET['song_id']) ? $_GET['song_id'] : null;

// ========================================================
// Check permissions
// ========================================================

// Add song
if( $action === 'add' && !$_SESSION['can_add_data'] ) {
	$error = 'Sorry, you don\'t have permission to add songs. Showing '.( $artist ? 'all songs' : 'index' ).' instead.';
	$action = 'view';
}

// edit song
if( $action === 'edit' && !$_SESSION['can_add_data'] ) {
	$error = 'Sorry, you don\'t have permission to edit songs. Showing '.( is_numeric($id) ? 'song' : 'all songs' ).' instead.';
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
		
		$error = 'The requested song couldn\'t be found. Showing '.( $action == 'edit' ? '&ldquo;add new song&rdquo;' : 'all songs' ).' instead.';
		unset($song_id);
		
	}
	
}

// All artist songs (if getting single song, artist info will be got based on its id)
elseif( strlen($artist) ) {
	
	$artist = $access_artist->access_artist([ 'friendly' => $artist, 'get' => 'name' ]);
	
	if( is_array($artist) && !empty($artist) ) {
		
		$songs = $access_song->access_song([ 'artist_id' => $artist['id'], 'get' => 'basics', 'associative' => true ]);
		
	}
	else {
		
		$error = 'That artist couldn\'t be found in the database. Showing '.( $action == 'edit' ? '&ldquo;add new song&rdquo;' : 'index' ).' instead.';
		unset($artist);
		
	}
	
}

// ========================================================
// Display page
// ========================================================

// Add song
if( $action === 'add' ) {
	include('../songs/page-add.php');
}

// Edit song
elseif( $action === 'edit' && $song ) {
	include('../songs/page-edit.php');
}

// View song
elseif( $action === 'view' && $song ) {
	include('../songs/page-song.php');
}

// View artist
elseif( $action === 'view' && is_array($artist) && !empty($artist) ) {
	include('../songs/page-artist.php');
}

// View index
else {
	include('../songs/page-index.php');
}