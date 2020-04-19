<?php

include_once('../php/include.php');

// Edit user's role
if($_SESSION['can_edit_roles'] && is_numeric($_POST['id']) && $_SESSION['user_id'] != $_POST['id']) {
	
	$user_id = sanitize($_POST['id']);
	$sql_check_boss = 'SELECT is_boss FROM users WHERE id=? LIMIT 1';
	$stmt_check_boss = $pdo->prepare($sql_check_boss);
	$stmt_check_boss->execute([ sanitize($_POST['id']) ]);
	$rslt_check_boss = $stmt_check_boss->fetchColumn();
	
	// Make sure no one can edit permissions of user with boss role
	if(!$rslt_check_boss) {
		
		// Get current roles and permissions, so we can compare
		$sql_current = 'SELECT * FROM users WHERE id=?';
		$stmt_current = $pdo->prepare($sql_current);
		$stmt_current->execute([ $user_id ]);
		$rslt_current = $stmt_current->fetch();
		
		// Set roles
		$roles = [ 'vip' => ['access_drafts'], 'editor' => ['add_data'], 'moderator' => ['approve_data', 'delete_data', 'edit_roles'] ];
		foreach($roles as $role_key => $permissions) {
			$values_user[ 'is_'.$role_key ] = $_POST[ 'is_'.$role_key ] == 1 ? 1 : 0;
			
			// Auto-set permissions based on roles, if user is unable to set permissions individually
			if(!$_SESSION['can_edit_permissions']) {
				foreach($permissions as $permission_key) {
					$values_user[ 'can_'.$permission_key ] = $_POST[ 'is_'.$role_key ] == 1 ? 1 : 0;
				}
			}
		}
		
		// If user can set permissions individually, get those
		if($_SESSION['can_edit_permissions']) {
			$permissions = [ 'add_data', 'add_livehouses', 'delete_data', 'approve_data', 'comment', 'access_drafts', 'edit_roles', 'edit_permissions' ];
			foreach($permissions as $permission_key) {
				$values_user[ 'can_'.$permission_key ] = $_POST[ 'can_'.$permission_key ] == 1 ? 1 : 0;
			}
		}
		
		// Separate keys and values, add value for id=?
		$keys_user = array_keys($values_user);
		$values_user = array_values($values_user);
		$values_user[] = $user_id;
		
		$sql_user = 'UPDATE users SET '.implode('=?, ', $keys_user).'=? WHERE id=? LIMIT 1';
		$stmt_user = $pdo->prepare($sql_user);
		if($stmt_user->execute( $values_user )) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = 'Couldn\'t update user permissions.';
		}
		
	}
	else {
		$output['result'] = 'Can\'t edit permissions for the requested user.';
	}
	
}

// Edit own profile
elseif($_SESSION['is_signed_in']) {
	
	// Clean socials
	foreach(['facebook', 'lastfm', 'twitter', 'mh'] as $key) {
		$value = $_POST[$key];
		if(strlen($value)) {
			$value = explode('/', $value);
			$value = array_filter($value);
			$value = end($value);
			$value = preg_replace('/'.'[^\w\.\-]'.'/', '', $value);
		}
		$value = strlen($value) ? sanitize($value) : null;
		$sql_values[$key] = $value;
	}
	
	// Clean website
	if(strlen($_POST['website'])) {
		$website = $_POST['website'];
		if(!preg_match('/'.'^https?:\/\/'.'/', $website)) {
			$website = strpos($website, '//') === 0 ? 'https:'.$website : 'https://'.$website;
		}
	}
	$sql_values['website'] = strlen($website) ? sanitize($website) : null;
	
	// Clean fan since
	$sql_values['fan_since'] = is_numeric($_POST['fan_since']) && $_POST['fan_since'] > 1980 && $_POST['fan_since'] <= date('Y') ? sanitize($_POST['fan_since']) : null;
	
	// Clean & set user preferences
	foreach(['name', 'motto', 'email', 'site_theme', 'icon'] as $key) {
		$sql_values[$key] = sanitize($_POST[$key]);
		$sql_values[$key] = strlen($sql_values[$key]) ? $sql_values[$key] : null;
	}
	
	// Clean pronouns
	if(in_array($_POST['pronouns'], ['prefer not to say', 'she/her', 'he/him', 'they/them', 'custom'])) {
		$pronouns = $_POST['pronouns'];
		
		if($pronouns === 'custom' && strlen($_POST['custom_pronouns'])) {
			$custom_pronouns = $_POST['custom_pronouns'];
			
			if(in_array( preg_replace('/'.'[^A-z]'.'/', '', $custom_pronouns), ['attack', 'helicopter'] )) {
				$pronouns = 'prefer not to say';
			}
			else {
				$pronouns = strlen($custom_pronouns) ? $custom_pronouns : 'prefer not to say';
			}
			
		}
	}
	else {
		$pronouns = 'prefer not to say';
	}
	$sql_values['pronouns'] = sanitize($pronouns);
	
	// Further clean some values
	$email_pattern = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
	$sql_values['site_theme'] = is_numeric($sql_values['site_theme']) ? $sql_values['site_theme'] : 0;
	$sql_values['icon'] = is_numeric($sql_values['icon']) ? $sql_values['icon'] : 0;
	$sql_values['icon'] = $sql_values['icon'] > 0 && !$_SESSION['is_vip'] ? 0 : $sql_values['icon'];
	$sql_values['email'] = preg_match($email_pattern, $sql_values['email']) ? $sql_values['email'] : null;
	
	// If email doesn't match pattern, make note
	if($_POST['email'] && $sql_values['email'] != $_POST['email']) {
		$output['result'] = 'Sorry, that email isn\'t allowed. Please try another one.';
	}
	
	// Set some session variables based on potential changes
	$_SESSION['site_theme'] = $sql_values['site_theme'];
	
	// Handle username change
	$new_username = sanitize($_POST['new_username']) ?: null;
	if(strlen($new_username)) {
		
		// Check that username is appropriate and isn't already used
		if(preg_match('/'.'^[A-z0-9-]{3,}$'.'/', $new_username)) {
			$sql_check_username = 'SELECT 1 FROM users WHERE username=? LIMIT 1';
			$stmt_check_username = $pdo->prepare($sql_check_username);
			$stmt_check_username->execute([ $new_username ]);
			
			if($stmt_check_username->fetchColumn() == 1) {
				$output['result'] = 'Sorry, that username is taken.';
			}
			else {
				
				// If username available, set with main array of user settings, to be changed later
				$sql_values['username'] = $new_username;
			}
		}
		else {
			$output['result'] = 'Usernames must only contain letters, numbers, and/or hyphens, and must be at least 3 characters.';
		}
	}
	
	// Handle password change
	if(strlen($_POST['new_password_1'])) {
		if(strlen($_POST['current_password'])) {
			if($_POST['new_password_1'] === $_POST['new_password_2']) {
				
				// Check current password
				$sql_check_password = 'SELECT password FROM users WHERE id=? LIMIT 1';
				$stmt_check_password = $pdo->prepare($sql_check_password);
				$stmt_check_password->execute([ $_SESSION['user_id'] ]);
				
				if(password_verify($_POST['current_password'], $stmt_check_password->fetchColumn())) {
					
					// If current password matches, set with main array of user settings, to be changed later
					$sql_values['password'] = password_hash($_POST['new_password_1'], PASSWORD_DEFAULT);
				}
				else {
					$output['result'] = 'Current password is incorrect.';
				}
			}
			else {
				$output['result'] = 'New password and password confirmation don\'t match.';
			}
		}
		else {
			$output['result'] = 'Please enter your current password.';
		}
	}
	
	// Core update function
	$sql_edit = 'UPDATE users SET '.implode('=?, ', array_keys($sql_values)).'=? WHERE id=? LIMIT 1';
	$stmt_edit = $pdo->prepare($sql_edit);
	
	// Add user ID as last item in values
	$sql_values['id'] = $_SESSION['user_id'];
	
	// Execute query
	if($stmt_edit->execute( array_values($sql_values) )) {
		
		$output['status'] = 'success';
		
		// If username was changed, rename avatar image file and update SESSION
		if(strlen($sql_values['username']) && $sql_values['username'] != $_SESSION['username']) {
			
			// Rename avatar
			$old_avatar = '../usericons/avatar-'.$_SESSION['username'].'.png';
			$new_avatar = '../usericons/avatar-'.$sql_values['username'].'.png';
			if(file_exists($old_avatar)) {
				rename($old_avatar, $new_avatar);
			}
			
			// Update session
			$_SESSION['username'] = $sql_values['username'];
			
			// Note that username was changed, redirect to new profile
			$output['result'] = 'Username changed; redirecting to <a href="/users/'.$sql_values['useranme'].'/">new profile</a>. <meta http-equiv="refresh" content="3;url=/users/'.$sql_values['username'].'/" />';
		}
		
		// Award point
		$access_points = new access_points($pdo);
		$access_points->award_points([ 'point_type' => 'edited-profile', 'allow_multiple' => false ]);
	}
	else {
		$output['result'] = 'Couldn\'t update profile.';
	}
}
else {
	$output['result'] = 'Please sign in before editing your account.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);