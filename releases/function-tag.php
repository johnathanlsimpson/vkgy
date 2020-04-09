<?php
	include("../php/include.php");
	
	if($_SESSION["is_signed_in"]) {
		$release_id = is_numeric($_POST["id"]) ? $_POST["id"] : null;
			$user_id = $_SESSION["user_id"];
		$tag_id = is_numeric($_POST["tag_id"]) ? $_POST["tag_id"] : null;
		
		if(is_numeric($release_id)) {
			
			// Check if user has permission to add/remove
			$sql_is_admin_tag = 'SELECT is_admin_tag FROM tags_releases WHERE id=? LIMIT 1';
			$stmt_is_admin_tag = $pdo->prepare($sql_is_admin_tag);
			$stmt_is_admin_tag->execute([ $tag_id ]);
			$rslt_is_admin_tag = $stmt_is_admin_tag->fetchColumn();
			
			$user_is_allowed = !$rslt_is_admin_tag || ($rslt_is_admin_tag && $_SESSION['is_moderator']);
			
			if($user_is_allowed) {
				
				if($_POST["action"] === "delete" && $_SESSION["is_moderator"] && is_numeric($release_id) && is_numeric($tag_id)) {
					
					// Perform delete
					$sql_delete = "DELETE FROM releases_tags WHERE release_id=? AND tag_id=?";
					$stmt_delete = $pdo->prepare($sql_delete);
					
					if($stmt_delete->execute([ $release_id, $tag_id ])) {
						$output["status"] = "success";
						$output["result"] = "Admin tag has been removed.";
					}
				}
				else {
					
					$sql_check = "SELECT id FROM releases_tags WHERE user_id=? AND release_id=? AND tag_id=? LIMIT 1";
					$stmt_check = $pdo->prepare($sql_check);
					$stmt_check->execute([$user_id, $release_id, $tag_id]);
					
					$record_id = $stmt_check->fetchColumn();
					$is_tagged = is_numeric($record_id);
					
					if($is_tagged) {
						$sql_update = "DELETE FROM releases_tags WHERE id=? LIMIT 1";
						$sql_values = [$record_id];
					}
					else {
						$sql_update = "INSERT INTO releases_tags (user_id, release_id, tag_id) VALUES (?, ?, ?)";
						$sql_values = [$user_id, $release_id, $tag_id];
						$output["is_checked"] = "1";
						
						// Award point
						$access_points = new access_points($pdo);
						$access_points->award_points([ 'point_type' => 'tagged-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
					}
					
					$stmt_update = $pdo->prepare($sql_update);
					if($stmt_update->execute($sql_values)) {
						$output["status"] = "success";
					}
					else {
						$output["result"] = "Sorry, the release tags couldn't be updated.";
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
		$output["result"] = "Please sign in to tag releases.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>