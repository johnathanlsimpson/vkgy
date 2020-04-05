<?php
	include_once("../php/include.php");
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["is_signed_in"]) {
		$id = sanitize($_POST["id"]) ?: null;
		$id = is_numeric($id) ? $id : null;
		$image_id = sanitize($_POST["image_is_entry_default"]);
		$image_id = is_numeric($image_id) ? $image_id : null;
		
		if(is_numeric($id) && is_numeric($image_id)) {
			$sql_update = "UPDATE blog SET image_id=? WHERE id=? LIMIT 1";
			$stmt_update = $pdo->prepare($sql_update);
			if($stmt_update->execute([$image_id, $id])) {
				$output["status"] = "success";
			}
			else {
				$output["result"] = "Entry image could not be updated.";
			}
		}
		else {
			$output["result"] = "There's something wrong with the image or entry.";
		}
	}
	else {
		$output["result"] = "Please sign in to update the blog.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>