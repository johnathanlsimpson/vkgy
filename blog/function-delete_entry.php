<?php
include_once("../php/include.php");

if($_SESSION["is_signed_in"]) {
	$id = strlen($_POST["id"]) > 0 ? sanitize($_POST["id"]) : null;
	$id = strlen($id) > 0 && is_numeric($id) ? $id : null;
	
	if(is_numeric($id)) {
		if(!$_POST['is_translation']) {
			$sql_check = "SELECT user_id FROM blog WHERE id=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([$id]);
			$user_id = $stmt_check->fetchColumn();
		}
		
		if($user_id === $_SESSION["user_id"] || $_SESSION["can_delete_data"]) {
			$sql_delete = "DELETE FROM ".($_POST['is_translation'] ? 'blog_translations' : 'blog')." WHERE id=? LIMIT 1";
			$stmt_delete = $pdo->prepare($sql_delete);
			if($stmt_delete->execute([$id])) {
				$output["status"] = "success";
			}
			else {
				$output["result"] = $sql_delete.$id."* Sorry, the entry couldn't be deleted.";
			}
		}
		else {
			$output["result"] = "You don't have permission to delete this entry.";
		}
	}
	else {
		$output["result"] = "Sorry, this entry cannot be deleted.";
	}
}
else {
	$output["result"] = "Please sign in to continue.";
}

$output["status"] = $output["status"] ?: "error";

echo json_encode($output);