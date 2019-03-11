<?php
	include_once("../php/include.php");
	
	$login = new login($pdo);
	$username = $_POST["register_username"];
	$password = $_POST["register_password"];
	$avatar =
		'{"head__base":"default","head__base-color":"i","makeup__base":"none","lips__base":"classic","lips__base-color":"maroon","decoration__base":"none","hair__base":"classic","hair__base-color":"caramel","eyeshadow__makeup":"none","eyeshadow__makeup-color":"caramel","eyebrow__shape":"stub","eyebrow__shape-color":"caramel","eye__shape":"'.
		($_POST["register_avatar"] === 'gecko' ? 'gecko' : 'bat').
		'","eye__accent-color":"maroon","eye__lashes-color":"black","eye__sclera-color":"white","eye__iris-color":"black","masks__base":"none","bangs__base":"split","bangs__base-color":"caramel","hats__base":"none"}';
	
	if($username && $password) {
		if(preg_match("/"."^[A-z0-9-]+$"."/", $username)) {
			$sql_check = "SELECT 1 FROM users WHERE username=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $username ]);
			
			if(!$stmt_check->fetchColumn()) {
				
				$sql_register = "INSERT INTO users (username, password) VALUES (?, ?)";
				$stmt_register = $pdo->prepare($sql_register);
				
				if($stmt_register->execute([ $username, password_hash($password, PASSWORD_DEFAULT) ])) {
					$user_id = $pdo->lastInsertId();
					
					$sql_avatar = 'INSERT INTO users_avatars (user_id, content) VALUES (?, ?)';
					$stmt_avatar = $pdo->prepare($sql_avatar);
					if($stmt_avatar->execute([ $user_id, $avatar ])) {
						
						$login->sign_in(["username" => $username, "password" => $password]);
						
						$output["status"] = "success";
						$output["result"] = "Successfully registered. ".$login->get_status_message().'<meta http-equiv="refresh" content="0;url=/users/'.$username.'/" />';
					}
				}
				else {
					$output["result"] = "Sorry, something went wrong.".print_r($pdo->errorInfo(), true);
				}
			}
			else {
				$output["result"] = "Sorry, that username is already taken.";
			}
		}
		else {
			$output["result"] = "Sorry, usernames can only contain <strong>letters</strong>, <strong>numbers</strong>, and <strong>hyphens</strong>.";
		}
	}
	else {
		$output["result"] = "Please fill out all fields.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>