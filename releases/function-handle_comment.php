<?php
	session_start();

	include_once("../php/database-connect.php");
	include_once("../php/function-sanitize.php");
	include_once("../php/class-parse_markdown.php");
	
	$method          = sanitize($_POST["method"]);
	$comment_content = sanitize($_POST["comment"]);
	$release_id      = sanitize($_POST["release_id"]);
	$comment_id      = sanitize($_POST["comment_id"]);
	$user_id         = $_SESSION["user_id"];
	$ip_address      = inet_pton($_SERVER["REMOTE_ADDR"]);
	$like_action     = $_POST["action"] === "remove" ? "remove" : "add";
	
	if($method === "delete") {
		if($_SESSION["is_signed_in"] && is_numeric($comment_id)) {
			$sql_check_permission = "SELECT 1 FROM releases_comments WHERE user_id=? AND id=? LIMIT 1";
			$stmt = $pdo->prepare($sql_check_permission);
			$stmt->execute([$user_id, $comment_id]);
			
			if(!empty($stmt->fetch())) {
				$sql_delete_comment = "DELETE FROM releases_comments WHERE id=? LIMIT 1";
				$stmt = $pdo->prepare($sql_delete_comment);
				
				if($stmt->execute([$comment_id])) {
					$output["status"] = "success";
				}
			}
		}
	}
	elseif($method === "like") {
		$sql_check_comment_like = "SELECT 1 FROM releases_likes WHERE ".($_SESSION["is_signed_in"] ? "user_id" : "ip_address")."=? AND comment_id=? LIMIT 1";
		$stmt = $pdo->prepare($sql_check_comment_like);
		$stmt->execute([$_SESSION["is_signed_in"] ? $user_id : $ip_address, $comment_id]);
		
		if($stmt->fetch()) {
			if($like_action === "add") {
				$like_action = "remove";
			}
		}
		
		if($like_action === "add") {
			$sql_like = "INSERT INTO releases_likes (".($_SESSION["is_signed_in"] ? "user_id" : "ip_address").", comment_id) VALUES (?, ?)";
		}
		else {
			$sql_like = "DELETE FROM releases_likes WHERE ".($_SESSION["is_signed_in"] ? "user_id" : "ip_address")."=? AND comment_id=? LIMIT 1";
		}
		
		$stmt = $pdo->prepare($sql_like);
		
		if($stmt->execute([$_SESSION["is_signed_in"] ? $user_id : $ip_address, $comment_id])) {
			$output["status"] = "success";
			$output["action"] = $like_action;
		}
	}
	else {
		if($_SESSION["is_signed_in"] && is_numeric($release_id) && !empty($comment_content)) {
			$markdown_parser = new parse_markdown($pdo);
			
			$sql_fields = [
				$user_id,
				$release_id,
				sanitize($markdown_parser->validate_markdown($comment_content))
			];
			
			$sql_check_comment = "SELECT 1 FROM releases_comments WHERE user_id=? AND release_id=? AND content=? LIMIT 1";
			$stmt = $pdo->prepare($sql_check_comment);
			$stmt->execute($sql_fields);
			
			if(empty($stmt->fetch())) {
				$sql_add_comment = "INSERT INTO releases_comments (user_id, release_id, content) VALUES (?, ?, ?)";
				$stmt = $pdo->prepare($sql_add_comment);
				if($stmt->execute($sql_fields)) {
					$output["result"] = "Comment successfully added.";
					$output["status"] = "success";
					
					$output["username"]   = $_SESSION["username"];
					$output["user_url"]   = "/user/".$_SESSION["username"]."/";
					$output["comment"]    = $markdown_parser->parse_markdown(sanitize($markdown_parser->validate_markdown($comment_content)));
					$output["date_added"] = date("Y-m-d H:i:s");
					$output["comment_id"] = $pdo->lastInsertId();
				}
				else {
					$output["status"] = "error";
					$output["result"] = "The comment couldn't be added.";
				}
			}
			else {
				$output["status"] = "error";
				$output["result"] = "This comment already exists.";
			}
		}
	}
	
	//$output["result"] = $_POST;
	

	/*if(!empty($_POST["comment"]) && is_numeric($_POST["release_id"])) {
		session_start();
		
		if($_SESSION["is_signed_in"]) {
			include_once("../php/database-connect.php");
			include_once("../php/function-sanitize.php");
			include_once("../php/class-parse_markdown.php");
			
			$markdown_parser = new parse_markdown($pdo);
			
			$sql_fields = [
				$_SESSION["user_id"],
				$_POST["release_id"],
				sanitize($_POST["comment"])
			];
			
			$sql_check_comment = "SELECT 1 FROM releases_comments WHERE user_id=? AND release_id=? AND content=? LIMIT 1";
			$stmt = $pdo->prepare($sql_check_comment);
			$stmt->execute($sql_fields);
			
			if(empty($stmt->fetch())) {
				$sql_add_comment = "INSERT INTO releases_comments (user_id, release_id, content) VALUES (?, ?, ?)";
				$stmt = $pdo->prepare($sql_add_comment);
				if($stmt->execute($sql_fields)) {
					$output["result"] = "Comment successfully added.";
					$output["status"] = "success";
					
					$output["username"] = $_SESSION["username"];
					$output["user_url"] = "/user/".$_SESSION["username"]."/";
					$output["comment"] = $markdown_parser->parse_markdown(sanitize($_POST["comment"]));
					$output["date_added"] = date("Y-m-d H:i:s");
					$output["comment_id"] = $pdo->lastInsertId();
				}
				else {
					$output["status"] = "error";
					$output["result"] = "The comment couldn't be added.";
				}
			}
			else {
					$output["status"] = "error";
				$output["result"] = "This comment already exists.";
			}
		}
		else {
					$output["status"] = "error";
			$output["result"] = "Please sign in.";
		}
	}
	else {
					$output["status"] = "error";
		$output["result"] = "The comment field is empty.";
	}*/
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>