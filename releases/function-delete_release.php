<?php
	session_start();
	
	include_once("../php/database-connect.php");
	include_once("../php/class-login.php");

	$login = new login($pdo);
	$login->check_login();
	
	if(!empty($_POST)) {
		if(is_numeric($_POST["id"])) {
			if($_SESSION["loggedIn"]) {
				$sql_check = "SELECT user_id FROM releases WHERE id=? LIMIT 1";
				$stmt = $pdo->prepare($sql_check);
				$stmt->execute([$_POST["id"]]);
				$user_id = $stmt->fetchColumn();
				
				if($_SESSION["userID"] === $user_id || $_SESSION["is_editor"]) {
					$sql_delete = "DELETE FROM releases WHERE id=? LIMIT 1";
					$stmt = $pdo->prepare($sql_delete);
					if($stmt->execute([$_POST["id"]])) {
						$sql_delete_tracks = "DELETE FROM releases_tracklists WHERE release_id=?";
						$stmt = $pdo->prepare($sql_delete_tracks);
						if($stmt->execute([$_POST["id"]])) {
							$output["status"] = "success";
							$output["result"] = "Release successfully deleted.";
						}
						else {
							$output["result"] = "The release's tracklist could not be deleted.";
						}
					}
					else {
						$output["result"] = "The release could not be deleted.";
					}
				}
				else {
					$output["result"] = "You don't have permission to delete this release.";
				}
			}
			else {
				$output["result"] = "You must be signed in to delete a release.";
			}
		}
		else {
			$output["result"] = "No release was specified.";
		}
	}
	else {
		$output["result"] = "No release was specified.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>