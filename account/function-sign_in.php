<?php
	include_once('../php/include.php');
	
	if(strlen($_POST["username"]) && strlen($_POST["password"])) {
		$sign_in = new login($pdo);
		
		$sign_in->sign_in($_POST);
		
		if($sign_in->check_login()) {
			$output["status"] = "success";
			$output["username"] = $_SESSION["username"];
			$output["user_url"] = "/users/".$_SESSION["username"]."/";
		}
		else {
			$output["status"] = "error";
		}
		
		$output["status_code"] = $sign_in->status;
		$output["result"] = $sign_in->get_status_message();
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>