<?php
	include_once("../php/include.php");
	
	if($_SESSION["loggedIn"]) {
		if(is_numeric($_POST["id"])) {
			$id = sanitize($_POST["id"]);
			
			$sql_check = "SELECT user_id FROM comments WHERE id=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([$id]);
			
			if($_SESSION["admin"] || $stmt_check->fetchColumn() === $_SESSION["userID"]) {
				$sql_delete = "DELETE FROM comments WHERE id=? LIMIT 1";
				$stmt_delete = $pdo->prepare($sql_delete);
				if($stmt_delete->execute([$id])) {
					$output["status"] = "success";
				}
				else {
					$output["result"] = "The comment couldn't be deleted.";
				}
			}
			else {
				$output["result"] = "You don't have permission to delete that entry.";
			}
		}
		else {
			$output["result"] = "That entry can't be deleted.";
		}
	}
	else {
		$output["result"] = "Please sign in.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>