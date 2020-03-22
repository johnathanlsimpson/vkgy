<?php
	include_once("../php/include.php");
	
	if($_SESSION['is_admin']) {
		if(is_numeric($_POST["id"])) {
			$id = sanitize($_POST["id"]);
			
			$sql_musician = "DELETE FROM musicians WHERE id=? LIMIT 1";
			$stmt_musician = $pdo->prepare($sql_musician);
			if($stmt_musician->execute([$id])) {
				
				$sql_links = "DELETE FROM artists_musicians WHERE musician_id=?";
				$stmt_links = $pdo->prepare($sql_links);
				if($stmt_links->execute([$id])) {
					
					$output["status"] = "success";
				}
				else {
					$output["result"] = "Musician links could not be deleted.";
				}
			}
			else {
				$output["result"] = "Musician could not be deleted.";
			}
		}
		else {
			$output["result"] = "The musician ID is malformed.";
		}
	}
	else {
		$output["result"] = "Only administrators may delete musicians.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>