<?php

include_once('../php/class-magazine.php');
include_once('../php/class-issue.php');
$access_issue = new issue($pdo);
$access_magazine = new magazine($pdo);

// ========================================================
// Clean variables
// ========================================================
$allowed_actions = [ 'update', 'update-issue', 'view' ];

$action = $_GET['action'];
$action = $action && in_array( $action, $allowed_actions ) ? $action : 'view';

$friendly = strlen($_GET['friendly']) ? friendly($_GET['friendly']) : null;
$issue_id = is_numeric($_GET['issue_id']) ? $_GET['issue_id'] : null;

// ========================================================
// Check permissions
// ========================================================

// Update magazine
if( $action === 'update-issue' && !$_SESSION['can_add_data'] ) {
	$error = 'Sorry, you don\'t have permission to update issues. Showing '.( is_numeric($id) ? 'issue' : 'all issues' ).' instead.';
	$action = 'view';
}

// Update magazine--need to update this to more generic permission later
if( $action === 'update' && !$_SESSION['can_add_livehouses'] ) {
	$error = 'Sorry, you don\'t have permission to update magazines. Showing '.( strlen($series_friendly) ? 'magazine' : 'all magazines' ).' instead.';
	$action = 'view';
}

// ========================================================
// Get data
// ========================================================

// Single issue (issue ID)
if( strlen($issue_id) ) {
	
	// Get issue
	$issue = $access_issue->access_issue([ 'id' => $issue_id, 'get' => 'all' ]);
	$issue = is_array($issue) && !empty($issue) ? $issue : null;
	
	// If issue not found, reset page
	if( !$issue ) {
		$error = 'The requested issue couldn\'t be found. Showing '.( $action == 'update-issue' ? '&ldquo;add new issue&rdquo;' : 'all issues' ).' instead.';
		unset($issue_id);
	}
	
}

// Single magazine (magazine friendly)
if( strlen($friendly) ) {
	
	$magazine = $access_magazine->access_magazine([ 'friendly' => $friendly, 'get' => 'all', 'limit' => 1 ]);
	$magazine = is_array($magazine) && !empty($magazine) ? $magazine : null;
	
	// If magazine not found, reset page
	if( !$magazine ) {
		
		$error = 'The requested magazine couldn\'t be found. Showing '.( $action == 'update' ? '&ldquo;add new magazine&rdquo;' : 'all magazines' ).' instead.';
		unset($friendly);
		
	}
	
}

// List of magazines (if no issue or magazine gotten in previous queries)
if( !$issue && !$magazine ) {
	
	$magazines = $access_magazine->access_magazine([ 'get' => 'basics' ]);
	
}

// List of attributes (if adding/editing magazine)
if( $action === 'update' ) {
	
	$magazine_attributes = $access_magazine->get_attributes();
	
}

// ========================================================
// Page setup
// ========================================================

subnav([
	lang('All magazines', '雑誌の一覧', 'hidden') => '/magazines/',
]);

// Need to change this to a more generic permission
if( $_SESSION['can_add_livehouses'] ) {
	subnav([
		lang('Add magazine', '叢書を追加', 'hidden') => '/magazines/add/',
	], 'interact', true);
}

subnav([
	lang('Add issue', '雑誌を追加', 'hidden') => '/magazines/add-issue/'.( $magazine['friendly'] || $issue['friendly'] ? '&magazine='.$magazine['friendly'].$issue['friendly'] : null ),
], 'interact', true);

// ========================================================
// Display page
// ========================================================

// Add/update magazine
if( $action === 'update' ) {
	include('../magazines/page-update.php');
}

// Add/update issue
elseif( $action === 'update-issue' ) {
	include('../magazines/page-update_issue.php');
}

// View issue
elseif( $action === 'view' && $issue ) {
	include('../magazines/page-issue.php');
}

// View magazine
elseif( $action === 'view' && $magazine ) {
	include('../magazines/page-magazine.php');
}

// View index
else {
	include('../magazines/page-index.php');
}