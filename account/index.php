<?php

$access_user = new access_user($pdo);
$access_artist = new access_artist($pdo);
$access_release = new access_release($pdo);

include('../account/head.php');

// Switch controller
switch(true) {
	case($_GET['template'] === 'users'):
		$template = 'users'; break;
		
	case($_GET['template'] === 'account' && $_SESSION['is_signed_in'] && $_SESSION['username'] == $_GET['username']):
		$template = 'account'; break;
		
	case($_GET['template'] === 'account' && $_SESSION['is_signed_in'] && !isset($_GET['username'])):
		$template = 'account'; break;
		
	case($_GET['template'] === 'account' && strlen($_GET['username']) && $_SESSION['username'] != $_GET['username'] && $_SESSION['can_edit_roles']):
		$template = 'account'; break;
		
	case($_GET['template'] === 'user' && strlen($_GET['username'])):
		$template = 'user'; break;
		
	case($_GET['template'] === 'activity' && strlen($_GET['username'])):
		$template = 'activity'; break;
		
	case($_GET['action'] === 'download' && strlen($_GET['username']) && $_GET['username'] === $_SESSION['username']):
		$template = 'download-collection'; break;
		
	case($_GET['template'] === 'edit-avatar' && $_SESSION['is_signed_in']):
		$template = 'edit-avatar'; break;
		
	case($_GET['template'] === 'account' && !$_SESSION['is_signed_in']):
		$template = 'sign-in'; break;
		
	case($_GET['action'] === 'sign-out'):
		$template = 'sign-out'; break;
		
	default:
		$template = 'users';
}

// Check if requested user exists, change template if necessary
if(in_array($template, [ 'account', 'activity', 'user' ])) {
	$user = $access_user->access_user([ 'username' => sanitize($_GET['username']) ?: $_SESSION['username'], 'get' => 'all' ]);
	
	if(is_array($user) && !empty($user)) {
		
		// Make sure moderators can't set roles for bosses
		if($template === 'account' && $user['is_boss'] && $_SESSION['username'] != $user['username']) {
			unset($user);
			$error = tr('Can\'t edit permissions for requested user. Showing all users instead.');
			$template = 'users';
		}
		
	}
	else {
		$error = tr('Couldn\'t find the requested user. Showing all users instead.');
		$template = 'users';
	}
}

// Get avatar if necessary
if(in_array($template, [ 'activity', 'account', 'edit-avatar', 'user' ])) {
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

// Get individual permissions if necessary
if($template === 'account') {
	$permissions = $access_user->check_permissions($user['id']);
	$user = array_merge($user, $permissions);
}
	
	// User list
	// =======================================================
	if($template === "users") {
		$users = $access_user->access_user([ 'get' => 'list', 'order' => 'date_added DESC' ]);
		
		$pageTitle = tr("Member list");
		
		breadcrumbs([ tr("Member list") => "/users/" ]);
		
		include("page-users.php");
	}
	
	// Sign out
	// =======================================================
	if($template === "sign-out") {
		$pageTitle = tr("Sign out");
		
		breadcrumbs([ tr("Sign out") => "/sign-out/" ]);
		
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
	$page_title = tr('{username} profile', [ 'replace' => [ 'username' => $user['username'] ] ]);
	
	breadcrumbs([ tr("Member list") => "/users/", $user["username"] => "/users/".$user["username"]."/" ]);
	
	// Get points
	$access_points = new access_points($pdo);
	$user_points = $access_points->access_points([ 'associative' => true, 'user_id' => $user['id'], 'get' => 'basics', 'order_by' => 'point_type' ]);
	
	// For certain point types, merge points
	$points_to_merge = [
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
	
	// Get total points of all users
	/*$sql_rank = '
		SELECT SUM(users_points.point_value) AS point_sum, users_points.user_id, users.username
		FROM users_points
		LEFT JOIN users ON users.id=users_points.user_id
		GROUP BY users_points.user_id
		ORDER BY point_sum DESC';
	$stmt_rank = $pdo->prepare($sql_rank);
	$stmt_rank->execute();
	$rslt_rank = $stmt_rank->fetchAll();
	$num_ranked = count($rslt_rank);
	
	// Get ranking among other users, based on points
	for($i=0; $i<$num_ranked; $i++) {
		if($rslt_rank[$i]['user_id'] === $user['id']) {
			
			$user_points['meta']['rank'] = $i + 1;
			
			if($i>0) {
				$rank['above'] = $rslt_rank[$i - 1];
				$rank['above']['rank'] = $i;
			}
			if($i+1<$num_ranked) {
				$rank['below'] = $rslt_rank[$i + 1];
				$rank['below']['rank'] = $i + 2; 
			}
			break;
			
		}
	}*/
	
	include('page-user.php');
}
	
	// User profile: activity
	// =======================================================
	if($template === 'activity') {
		$page_title = tr('{username} activity', [ 'replace' => ['username' => $user['user']] ]);
		
		breadcrumbs([ tr('Member list') => '/users/', $user['username'] => '/users/'.$user['username'].'/', tr('Activity') => '/users/'.$user['username'].'/activity/' ]);
		
		include('page-activity.php');
	}
	
	// Edit profile
	// =======================================================
	if($template === "account") {
		
		$pageTitle = tr("Edit account");
		
		breadcrumbs([ tr("Member list") => "/users/", $user["username"] => "/users/".$user["username"]."/", tr("Edit") => "/account/edit/" ]);
		
		include("page-edit.php");
	}
	
	// Edit avatar
	// =======================================================
	if($template === 'edit-avatar') {
		$page_title = tr("Edit avatar");
		
		breadcrumbs([ tr("Member list") => "/users/", $user["username"] => "/users/".$user["username"]."/", tr("Edit") => "/account/edit/" ]);
		
		include("page-edit-avatar.php");
	}
	
	// Sign in/register
	// =======================================================
	if($template === "sign-in") {
		$pageTitle = tr("Sign in or register");
		
		breadcrumbs([ tr("Sign in/register") => "/account/" ]);
		
		include("page-sign-in-register.php");
	}
?>