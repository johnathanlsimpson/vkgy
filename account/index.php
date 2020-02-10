<?php

$access_user = new access_user($pdo);
$access_artist = new access_artist($pdo);
$access_release = new access_release($pdo);

include('../account/head.php');

// Switch controller
switch(true) {
	case($_GET['page'] === 'users'):
		$template = 'users'; break;
		
	case($_GET['page'] === 'account' && $_SESSION['is_signed_in']):
		$template = 'account'; break;
		
	case($_GET['page'] === 'user' && strlen($_GET['username'])):
		$template = 'user'; break;
		
	case($_GET['page'] === 'activity' && strlen($_GET['username'])):
		$template = 'activity'; break;
		
	case($_GET['action'] === 'download' && strlen($_GET['username']) && $_GET['username'] === $_SESSION['username']):
		$template = 'download-collection'; break;
		
	case($_GET['page'] === 'edit-avatar' && $_SESSION['is_signed_in']):
		$template = 'edit-avatar'; break;
		
	case($_GET['page'] === 'account' && !$_SESSION['is_signed_in']):
		$template = 'sign-in'; break;
		
	case($_GET['action'] === 'sign-out'):
		$template = 'sign-out'; break;
		
	default:
		$template = 'users';
}

// Check if requested user exists, change template if necessary
if(in_array($template, [ 'account', 'activity', 'user' ])) {
	$user_check = $access_user->access_user([ 'username' => sanitize($_GET['username']), 'get' => 'name' ]);
	
	if(is_array($user_check) && !empty($user_check)) {
		$user = $access_user->access_user([ 'username' => sanitize($_GET['username']), 'get' => 'all' ]);
	}
	else {
		$error = 'Couldn\'t find the requested user. Showing all users instead.';
		$template = 'users';
	}
}

// Get avatar if necessary
if(in_array($template, [ 'activity', 'edit-avatar', 'user' ])) {
	include_once('../avatar/class-avatar.php');
	include_once('../avatar/avatar-definitions.php');
	
	$sql_avatar = "SELECT content FROM users_avatars WHERE user_id=? LIMIT 1";
	$stmt_avatar = $pdo->prepare($sql_avatar);
	$stmt_avatar->execute([ (is_numeric($user['id']) ? $user['id'] : $_SESSION['user_id']) ]);
	$rslt_avatar = $stmt_avatar->fetchColumn();
	
	$avatar_class = $rslt_avatar ? null : 'user--no-avatar';
	$rslt_avatar = $rslt_avatar ?: '{"head__base":"default","head__base-color":"i"}';
	
	$avatar = new avatar($avatar_layers, $rslt_avatar, ["is_vip" => true]);
	$user['avatar'] = $avatar->get_avatar_paths();
	
	unset($avatar);
}
	
	// User list
	// =======================================================
	if($template === "users") {
		$users = $access_user->access_user(["get" => "list"]);
		
		$pageTitle = "Member list";
		
		breadcrumbs([ "Member list" => "/users/" ]);
		
		include("page-users.php");
	}
	
	// Sign out
	// =======================================================
	if($template === "sign-out") {
		$pageTitle = "Sign out";
		
		breadcrumbs([ "Sign out" => "/sign-out/" ]);
		
		include("page-sign-out.php");
	}
	
	// Download collection
	// =======================================================
	if($template === "download-collection") {
		include("page-download-collection.php");
	}
	
// User profile: top
// =======================================================
if($template === "user") {
	$page_title = $user['username'].' profile (プロフィール)';
	
	breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/" ]);
	
	// Get points
	$access_points = new access_points($pdo);
	$user_points = $access_points->access_points([ 'associative' => true, 'user_id' => $user['id'], 'get' => 'basics', 'order_by' => 'point_type' ]);
	
	// For certain point types, merge points
	$points_to_merge = [
		//'artists' => ['added-artist', 'edited-artist'],
		//'blog' => ['added-blog', 'edited-blog'],
		//'labels' => ['added-label', 'edited-label'],
		//'musicians' => ['added-musician', 'edited-musician'],
		//'releases' => ['added-release', 'edited-release'],
		'added-other' => ['added-label', 'added-livehouse', 'added-image', 'added-video'],
		'edits' => ['edited-artist', 'edited-blog', 'edited-label', 'edited-live', 'edited-musician', 'edited-release'],
		'rated' => ['rated-artist', 'rated-release'],
		'tagged' => ['tagged-artist', 'tagged-release'],
	];
	foreach($points_to_merge as $merged_key => $merge_arrays) {
		
		// Set up merged array with default values
		$user_points[$merged_key] = [ 'num_points' => 0, 'points_value' => 0, 'point_type' => $merged_key, 'date_occurred' => '0000-00-00 00:00:00' ];
		
		// Cycle through arrays that will be merged, and apply their values to merged array
		foreach($merge_arrays as $individual_key) {
			if($user_points[$individual_key]['date_occurred'] > $user_points[$merged_key]['date_occurred']) {
				$user_points[$merged_key]['date_occurred'] = $user_points[$individual_key]['date_occurred'];
			}
			
			if(is_numeric($user_points[$individual_key]['num_points'])) {
				$user_points[$merged_key]['num_points'] += $user_points[$individual_key]['num_points'];
			}
			
			if(is_numeric($user_points[$individual_key]['points_value'])) {
				$user_points[$merged_key]['points_value'] += $user_points[$individual_key]['points_value'];
			}
		}
		
	}
	
	echo '<pre>'.print_r($user_points, true).'</pre>';
	
	include('page-user.php');
}
	
	// User profile: activity
	// =======================================================
	if($template === 'activity') {
		$page_title = $user['user'].' activity';
		
		breadcrumbs([ 'Member list' => '/users/', $user['username'] => '/users/'.$user['username'].'/', 'Activity' => '/users/'.$user['username'].'/activity/' ]);
		
		include('page-activity.php');
	}
	
	// Edit profile
	// =======================================================
	if($template === "account") {
		$pageTitle = "Edit account";
		
		breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/", "Edit" => "/account/edit/" ]);
		
		include("page-edit.php");
	}
	
	// Edit avatar
	// =======================================================
	if($template === 'edit-avatar') {
		$page_title = "Edit avatar";
		
		breadcrumbs([ "Member list" => "/users/", $user["username"] => "/users/".$user["username"]."/", "Edit" => "/account/edit/" ]);
		
		include("page-edit-avatar.php");
	}
	
	// Sign in/register
	// =======================================================
	if($template === "sign-in") {
		$pageTitle = "Sign in or register";
		
		breadcrumbs([ "Sign in/register" => "/account/" ]);
		
		include("page-sign-in-register.php");
	}
?>