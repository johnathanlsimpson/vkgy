<?php
	include("../php/include.php");
	
	if($_SESSION["is_signed_in"]) {
		$action = sanitize($_POST["action"]);
		$release_id = is_numeric($_POST["id"]) ? $_POST["id"] : null;
		$user_id = $_SESSION["user_id"];
		
		if(is_numeric($release_id) && in_array($action, ["own", "want", "sell"])) {
			if($action === "own") {
				$sql_check = "SELECT id FROM releases_collections WHERE user_id=? AND release_id=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$user_id, $release_id]);
				
				$record_id = $stmt_check->fetchColumn();
				$is_owned = is_numeric($record_id);
				
				if($is_owned) {
					$sql_update = "DELETE FROM releases_collections WHERE id=? LIMIT 1";
					$sql_values = [$record_id];
				}
				else {
					$sql_update = "INSERT INTO releases_collections (user_id, release_id) VALUES (?, ?)";
					$sql_values = [$user_id, $release_id];
					$output["is_checked"] = "1";
					
					// Award point
					$access_points = new access_points($pdo);
					$access_points->award_points([ 'point_type' => 'collected-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
				}
			}
			elseif($action === "want") {
				$sql_check = "SELECT id FROM releases_wants WHERE user_id=? AND release_id=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$user_id, $release_id]);
				
				$record_id = $stmt_check->fetchColumn();
				$is_wanted = is_numeric($record_id);
				
				if($is_wanted) {
					$sql_update = "DELETE FROM releases_wants WHERE id=? LIMIT 1";
					$sql_values = [$record_id];
				}
				else {
					$sql_update = "INSERT INTO releases_wants (user_id, release_id) VALUES (?, ?)";
					$sql_values = [$user_id, $release_id];
					$output["is_checked"] = "1";
					
					// Award point
					$access_points = new access_points($pdo);
					$access_points->award_points([ 'point_type' => 'wanted-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
				}
			}
			elseif($action === "sell") {
				$sql_check = "SELECT id FROM releases_collections WHERE user_id=? AND release_id=? AND is_for_sale=? LIMIT 1";
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([$user_id, $release_id, 1]);
				
				$record_id = $stmt_check->fetchColumn();
				$is_for_sale = is_numeric($record_id);
				
				if($is_for_sale) {
					$sql_update = "UPDATE releases_collections SET is_for_sale=? WHERE user_id=? AND release_id=? LIMIT 1";
					$sql_values = [ 0, $user_id, $release_id ];
				}
				else {
					$sql_update = "UPDATE releases_collections SET is_for_sale=? WHERE user_id=? AND release_id=? LIMIT 1";
					$sql_values = [ 1, $user_id, $release_id ];
					$output["is_checked"] = "1";
					
					// Award point
					$access_points = new access_points($pdo);
					$access_points->award_points([ 'point_type' => 'sold-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
				}
			}
			
			$stmt_update = $pdo->prepare($sql_update);
			if($stmt_update->execute($sql_values)) {
				$output["status"] = "success";
			}
			else {
				$output["result"] = "Sorry, your collection couldn't be updated.";
			}
		}
		else {
			$output["result"] = "Sorry, something went wrong.";
		}
	}
	else {
		$output["result"] = "Please sign in to collect releases.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>