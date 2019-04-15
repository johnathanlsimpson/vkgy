<?php
include_once("../php/include.php");

$login = new login($pdo);
$username = $_POST["register_username"];
$password = $_POST["register_password"];

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
				
				$login->sign_in(["username" => $username, "password" => $password]);
				
				// Set default avatar
				ob_start();
				include_once('../avatar/function-generate_default.php');
				ob_end_clean();
				
				$output["status"] = "success";
				$output["result"] = "Successfully registered. ".$login->get_status_message().'<meta http-equiv="refresh" content="0;url=/users/'.$username.'/" />';
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