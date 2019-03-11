<?php
	include("../php/include.php");
	
	if($_SESSION["loggedIn"]) {
		$artist_id = is_numeric($_POST["id"]) ? $_POST["id"] : null;
		$user_id = $_SESSION["userID"];
		$tag_id = is_numeric($_POST["tag_id"]) ? $_POST["tag_id"] : null;
		
		if(is_numeric($artist_id)) {
			if($_POST["action"] === "delete" && $_SESSION["admin"] > 0 && is_numeric($artist_id) && is_numeric($tag_id)) {
				$sql_delete = "DELETE FROM artists_tags WHERE artist_id=? AND tag_id=?";
				$stmt_delete = $pdo->prepare($sql_delete);
				
				if($stmt_delete->execute([ $artist_id, $tag_id ])) {
					$output["status"] = "success";
					$output["result"] = "Admin tag has been removed.";
				}
			}
			else {
				$sql_check = "SELECT id FROM artists_tags WHERE user_id=? AND artist_id=? AND tag_id=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$user_id, $artist_id, $tag_id]);
				
				$record_id = $stmt_check->fetchColumn();
				$is_tagged = is_numeric($record_id);
				
				if($is_tagged) {
					$sql_update = "DELETE FROM artists_tags WHERE id=? LIMIT 1";
					$sql_values = [$record_id];
				}
				else {
					$sql_update = "INSERT INTO artists_tags (user_id, artist_id, tag_id) VALUES (?, ?, ?)";
					$sql_values = [$user_id, $artist_id, $tag_id];
					$output["is_checked"] = "1";
				}
				
				$stmt_update = $pdo->prepare($sql_update);
				if($stmt_update->execute($sql_values)) {
					$output["status"] = "success";
				}
				else {
					$output["result"] = "Sorry, the artist tags couldn't be updated.";
				}
			}
		}
		else {
			$output["result"] = "Sorry, something went wrong.";
		}
	}
	else {
		$output["result"] = "Please sign in to tag artists.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>