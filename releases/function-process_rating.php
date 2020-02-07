<?php
	if(is_numeric($_POST["score"]) && is_numeric($_POST["release_id"])) {
		session_start();
		
		include_once("../php/database-connect.php");
		
		$identifier      = $_SESSION["loggedIn"] ? $_SESSION["userID"] : $_SERVER["REMOTE_ADDR"];
		$identifier_type = $_SESSION["loggedIn"] ? "user_id" : "ip_address";
		$release_id      = $_POST["release_id"];
		$rating          = $_POST["score"];
		
		$sql_check_rating = "SELECT id FROM releases_ratings WHERE ".$identifier_type."=? AND release_id=?";
		$stmt = $pdo->prepare($sql_check_rating);
		$stmt->execute([$identifier, $release_id]);
		$id = $stmt->fetchColumn();
		
		if(is_numeric($id)) {
			$sql_set_rating = "UPDATE releases_ratings SET rating=? WHERE id=?";
			$sql_values = [$rating, $id];
		}
		else {
			$sql_set_rating = "INSERT INTO releases_ratings (".$identifier_type.", release_id, rating) VALUES (?, ?, ?)";
			$sql_values = [$identifier, $release_id, $rating];
		}
		
		$stmt = $pdo->prepare($sql_set_rating);
		if($stmt->execute($sql_values)) {
			$output["status"] = "success";
			
			$sql_check_rating = "SELECT AVG(rating) as current_rating FROM releases_ratings WHERE release_id=? GROUP BY release_id";
			
			$stmt = $pdo->prepare($sql_check_rating);
			$stmt->execute([$release_id]);
			
			$output["current_rating"] = round($stmt->fetchColumn());
			$output["user_rating"]    = $rating;
			
			// Award point
			$access_points = new access_points($pdo);
			$access_points->award_points([ 'point_type' => 'rated-release', 'allow_multiple' => false, 'item_id' => $release_id ]);
		}
		else {
			$output["status"] = "error";
		}
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>