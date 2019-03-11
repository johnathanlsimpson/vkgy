<?php
	include_once("../php/include.php");
	
	$image_id = $_POST["id"];
	
	if(is_numeric($image_id) && $_SESSION["admin"]) {
		$sql_get = "SELECT extension FROM images WHERE id=? LIMIT 1";
		$stmt_get = $pdo->prepare($sql_get);
		$stmt_get->execute([$image_id]);
		
		$extension = $stmt_get->fetchColumn();
		if(!empty($extension)) {
			$sql_delete = "DELETE FROM images WHERE id=? LIMIT 1";
			$stmt_delete = $pdo->prepare($sql_delete);
			
			if($stmt_delete->execute([$image_id])) {
				if(file_exists("../images/image_files/".$image_id.".".$extension) && unlink("../images/image_files/".$image_id.".".$extension)) {
					$output["status"] = "success";
				}
				else {
					$output["result"] = "Couldn't be deleted.";
				}
			}
			else {
				$output["result"] = "Couldn't be deleted from database.";
			}
		}
		else {
			$output["result"] = "Not found in database.";
		}
	}
	else {
		$output["result"] = 'Only editors can delete images. Please comment and ask an editor to help.';
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>