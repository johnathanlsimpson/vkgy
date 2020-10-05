<?php
	include_once("../php/include.php");
	
	if(!$_SESSION["is_signed_in"]) {
		$login = new login($pdo);
		$login->check_login();
	}
	
	if($_SESSION["can_delete_data"]) {
		if(is_numeric($_POST["id"])) {
			$id = sanitize($_POST["id"]);
			
			$sql_artist = "DELETE FROM artists WHERE id=? LIMIT 1";
			$stmt_artist = $pdo->prepare($sql_artist);
			if($stmt_artist->execute([$id])) {
				
				$output["status"] = "success";
				
				foreach(["artists_musicians", "artists_bio", "artists_urls"] as $key) {
					$sql = "DELETE FROM ".$key." WHERE artist_id=?";
					$stmt = $pdo->prepare($sql);
					if($stmt->execute([$id])) {
						$output["status"] = "success";
					}
					else {
						$output["status"] = "error";
					}
				}
				
				$sql_musicians = "SELECT id, history FROM musicians WHERE history LIKE CONCAT('%(', ?, ')%')";
				$stmt_musicians = $pdo->prepare($sql_musicians);
				$musicians = $stmt_musicians->fetchAll();
				
				if(is_array($musicians)) {
					foreach($musicians as $musician) {
						preg_match_all("/"."(\(\d+\))"."/", $musician["history"], $matches);
						
						if(count($matches[0]) === 1) {
							$sql_musician = "DELETE FROM musicians WHERE id=? LIMIT 1";
							$stmt_musician = $pdo->prepare($sql_musician);
							if($stmt_musician->execute([$musician["id"]])) {
								
								$sql_link = "DELETE FROM artists_musicians WHERE musician_id=? LIMIT 1";
								$stmt_link = $pdo->prepare($sql_link);
								if($stmt_link->execute([$musician["id"]])) {
									
									$output["status"] = "success";
								}
							}
							else {
								$output["status"] = "error";
								$output["result"] = "Musician #".$musician["id"]." couldn't be deleted.";
							}
						}
					}
				}
				
				$output["status"] = $output["status"] ?: "error";
			}
			else {
				$output["status"] = "error";
				$output["result"] = "The artist couldn't be deleted.";
			}
		}
		else {
			$output["status"] = "error";
			$output["result"] = "That artist doesn't exist.";
		}
	}
	else {
		$output["status"] = "error";
		$output["result"] = "Only administrators may delete artists.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>