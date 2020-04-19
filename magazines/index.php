<?php

// Validate request type
if($_GET['action'] === 'add') {
	
	if($_SESSION['can_add_livehouses']) {
		$action = 'add';
	}
	else {
		$error = 'Sorry, you don\'t have permission to add magazines.';
	}
	
}

// Display page
if($action === 'add') {
	include('page-add.php');
}