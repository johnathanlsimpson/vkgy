<?php
	include("../php/include.php");
	
	if($_SESSION["loggedIn"]) {
		$musician_id = is_numeric($_POST["id"]) ? $_POST["id"] : null;
		$user_id = $_SESSION["userID"];
		$tag_id = is_numeric($_POST["tag_id"]) ? $_POST["tag_id"] : null;
		
		if(is_numeric($musician_id)) {
			
			// Check if user has permission to add/remove
			$sql_is_admin_tag = 'SELECT is_admin_tag FROM tags_musicians WHERE id=? LIMIT 1';
			$stmt_is_admin_tag = $pdo->prepare($sql_is_admin_tag);
			$stmt_is_admin_tag->execute([ $tag_id ]);
			$rslt_is_admin_tag = $stmt_is_admin_tag->fetchColumn();
			
			$user_is_allowed = $rslt_is_admin_tag <= $_SESSION['is_editor'];
			
			if($user_is_allowed) {
				
				if($_POST["action"] === "delete" && $_SESSION["is_editor"] > 0 && is_numeric($musician_id) && is_numeric($tag_id)) {
					
					// Perform delete
					$sql_delete = "DELETE FROM musicians_tags WHERE musician_id=? AND tag_id=?";
					$stmt_delete = $pdo->prepare($sql_delete);
					
					if($stmt_delete->execute([ $musician_id, $tag_id ])) {
						$output["status"] = "success";
						$output["result"] = "Admin tag has been removed.";
					}
				}
				else {
					
					$sql_check = "SELECT id FROM musicians_tags WHERE user_id=? AND musician_id=? AND tag_id=? LIMIT 1";
					$stmt_check = $pdo->prepare($sql_check);
					$stmt_check->execute([$user_id, $musician_id, $tag_id]);
					
					$record_id = $stmt_check->fetchColumn();
					$is_tagged = is_numeric($record_id);
					
					if($is_tagged) {
						$sql_update = "DELETE FROM musicians_tags WHERE id=? LIMIT 1";
						$sql_values = [$record_id];
					}
					else {
						$sql_update = "INSERT INTO musicians_tags (user_id, musician_id, tag_id) VALUES (?, ?, ?)";
						$sql_values = [$user_id, $musician_id, $tag_id];
						$output["is_checked"] = "1";
					}
					
					$stmt_update = $pdo->prepare($sql_update);
					if($stmt_update->execute($sql_values)) {
						$output["status"] = "success";
					}
					else {
						$output["result"] = "Sorry, the musician tags couldn't be updated.";
					}
				}
				
			}
			else {
				$output['result'] = 'You don\'t have permission to use that tag.';
			}
		}
		else {
			$output["result"] = "Sorry, something went wrong.";
		}
	}
	else {
		$output["result"] = "Please sign in to tag musicians.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>