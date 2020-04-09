<?php
	include_once("../php/include.php");
	include_once("../php/class-access_label.php");
	
	if($_SESSION["can_delete_data"]) {
		$id = sanitize($_POST["id"]);
		
		if(is_numeric($id)) {
			$access_label = new access_label($pdo);
			$check_label = $access_label->access_label(["id" => $id, "get" => "name"]);
			if($check_label) {
				$sql_delete = "DELETE FROM labels WHERE id=? LIMIT 1";
				$stmt_delete = $pdo->prepare($sql_delete);
				
				if($stmt_delete->execute([$id])) {
					$output["status"] = "success";
				}
				else {
					$output["result"] = "The label couldn't be deleted.";
				}
			}
			else {
				$output["result"] = "That label doesn't exist.";
			}
		}
		else {
			$output["result"] = "Something's wrong with the label ID.";
		}
	}
	else {
		$output["result"] = "Only administrators may delete labels.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>