<?php
	include_once("../php/include.php");
	
	if($_SESSION["loggedIn"]) {
		foreach(["name", "artist_id", "motto", "email", "website", "twitter", "facebook", "lastfm", "tumblr", 'fan_since', 'site_theme', 'gender'] as $key) {
			$sql_values[$key] = sanitize($_POST[$key]) ?: null;
		}
		
		$_SESSION['site-theme'] = is_numeric($_POST['site_theme']) ? $_POST['site_theme'] : 0;
		
		$new_username = sanitize($_POST["new_username"]);
		$current_password = $_POST["current_password"];
		$new_password = $_POST["new_password_1"];
		$new_password_confirmation = $_POST["new_password_2"];
		
		if(!empty($new_username)) {
			if(preg_match("/"."^[\w\-\.]{3,}$"."/", $new_username)) {
				$sql_check = "SELECT 1 FROM users WHERE username=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$new_username]);
				
				if(!$stmt_check->fetchColumn()) {
					$sql_values["username"] = $new_username;
				}
				else {
					$output["result"] = "Sorry, that username is already taken.";
				}
			}
			else {
				$output["result"] = "Usernames may only contain letters, numbers, underscores, hyphens, and/or periods, and must be at least 3 characters.";
			}
		}
		
		if(!empty($new_password)) {
			if(!empty($current_password)) {
				if($new_password === $new_password_confirmation) {
					$sql_check = "SELECT password FROM users WHERE id=? LIMIT 1";
					$stmt_check = $pdo->prepare($sql_check);
					$stmt_check->execute([$_SESSION["userID"]]);
					
					$password = $stmt_check->fetchColumn();
					if(password_verify($current_password, $password)) {
						$sql_values["password"] = password_hash($new_password, PASSWORD_DEFAULT);
					}
					else {
						$output["result"] = "Your current password is incorrect.";
					}
				}
				else {
					$output["result"] = "Your new password and confirmation don't match.";
				}
			}
			else {
				$output["result"] = "You must enter your current password to change it.";
			}
		}
		
		if(!$sql_values["email"] || preg_match("/"."^[\w\.\-\+]+@[\w\.\-]+$"."/", $sql_values["email"])) {
			$sql_edit = "UPDATE users SET ".implode("=?, ", array_keys($sql_values))."=? WHERE id=? LIMIT 1";
			
			$sql_values = array_values($sql_values);
			$sql_values[] = $_SESSION["userID"];
			
			$stmt_edit = $pdo->prepare($sql_edit);
			
			if($stmt_edit->execute($sql_values)) {
				$output["status"] = "success";
				$output["result"] = $output["result"] ? $output["result"]." Other updates successful." : "Profile successfully updated.";
				
				if(!empty($sql_values["username"])) {
					setcookie("username", $sql_values["username"], time() + 60 * 60 * 24 * 40, "/", "vk.gy");
					$_SESSION["username"] = $sql_values["username"];
				}
			}
			else {
				$output["result"] = "Sorry, your profile couldn't be updated.";
			}
		}
		else {
			$output["result"] = "That email address isn't allowed. Please try a simpler one.";
		}
	}
	else {
		$output["result"] = "Sorry, you must sign in to edit your account.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>