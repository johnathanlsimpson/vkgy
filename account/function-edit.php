<?php

include_once('../php/include.php');

if($_SESSION['is_signed_in']) {
	
	// Clean & set user preferences
	foreach(['name', 'motto', 'email', 'facebook', 'lastfm', 'tumblr', 'fan_since', 'site_theme', 'icon'] as $key) {
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
				$pronouns = $custom_pronouns;
			}
			
		}
	}
	else {
		$pronouns = 'prefer not to say';
	}
	$sql_values['pronouns'] = sanitize($pronouns);
	
	// If Twitter supplied, make sure we get username
	if(strlen($_POST['twitter'])) {
		$twitter = $_POST['twitter'];
		
		// If URL was supplied, explode and get last portion (assumed to be username)
		$twitter = explode('/', $twitter);
		$twitter = array_filter($twitter);
		$twitter = end($twitter);
		
		// Ignore @ or any other symbols, then sanitize
		preg_match('/'.'(\w{5,15})'.'/', $twitter, $twitter_match);
		$twitter = is_array($twitter_match) && strlen($twitter_match[1]) ? sanitize($twitter_match[1]) : null;
	}
	$sql_values['twitter'] = $twitter;
	
	// If OHP supplied, make sure protocol given
	if(strlen($_POST['website'])) {
		$website = $_POST['website'];
		
		if(strpos($_POST['website'], 'http') != 0) {
			$website = 'https://'.$website;
		}
	}
	$sql_values['website'] = $website;
	
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
		$output['result'] = $output['result'] ? $output['result'].' Other updates successful.' : 'Profile updated.';
		
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