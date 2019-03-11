<?php
	include_once("../php/include.php");
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["loggedIn"]) {
		$id = sanitize($_POST["id"]);
		$title = sanitize($_POST["title"]);
		$friendly = friendly($title);
		$content = sanitize($markdown_parser->validate_markdown($_POST["content"]));
		
		if(!empty($title) && !empty($content)) {
			if(is_numeric($id)) {
				$sql_check = "SELECT user_id FROM vip WHERE id=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$id]);
				
				if($stmt_check->fetchColumn() === $_SESSION["userID"] || $_SESSION["admin"]) {
					$sql_update = "UPDATE vip SET title=?, content=?, friendly=? WHERE id=?";
					$stmt_update = $pdo->prepare($sql_update);
					if($stmt_update->execute([$title, $content, $friendly, $id])) {
						$output["status"] = "success";
						$output["friendly"] = $friendly;
						$output["title"] = $title;
						$output["url"] = "/vip/".$friendly."/";
						$output["edit_url"] = "/vip/".$friendly."/edit/";
					}
				}
				else {
					$output["result"] = "You don't have permission to edit this entry.";
				}
			}
			else {
				$sql_add = "INSERT INTO vip (title, content, friendly, user_id) VALUES (?, ?, ?, ?)";
				$stmt_add = $pdo->prepare($sql_add);
				if($stmt_add->execute([$title, $content, $friendly, $_SESSION["userID"]])) {
					$output["status"] = "success";
					$output["friendly"] = $friendly;
					$output["title"] = $title;
					$output["url"] = "/vip/".$friendly."/";
					$output["edit_url"] = "/vip/".$friendly."/edit/";
				}
			}
		}
		else {
			$output["result"] = "All fields must be filled in.";
		}
	}
	else {
		$output["result"] = "Please sign in.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>