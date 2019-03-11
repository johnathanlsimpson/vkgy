<?php
	include_once("../php/include.php");
	include_once("../php/function-post_deploy.php");
	
	if(is_numeric($_POST["id"])) {
		$id = $_POST["id"];
		$description = sanitize($_POST["description"]) ?: null;
		$friendly = friendly($description) ?: null;
		$artist_id = strlen($_POST["artist_id"]) > 0 && is_numeric(str_replace(",", "", $_POST["artist_id"])) ? "(".str_replace(",", ")(", sanitize($_POST["artist_id"])).")" : null;
		$musician_id = strlen($_POST["musician_id"]) > 0 && is_numeric(str_replace(",", "", $_POST["musician_id"])) ? "(".str_replace(",", ")(", sanitize($_POST["musician_id"])).")" : null;
		$release_id = strlen($_POST["release_id"]) > 0 && is_numeric(str_replace(",", "", $_POST["release_id"])) ? "(".str_replace(",", ")(", sanitize($_POST["release_id"])).")" : null;
		$is_release = $_POST["is_release"] ? 1 : null;
		$is_exclusive = $_POST["is_exclusive"] ? 1 : null;
		$is_default = $_POST["is_default"] ? 1 : null;
		$credit = sanitize($_POST["credit"]) ?: null;
		$queued = $_POST["queued"] ? true : false;
		
		$sql_update = "UPDATE ".($queued ? "queued_flyers" : "images")." SET description=?, friendly=?, credit=?, artist_id=?, musician_id=?, release_id=?, is_release=?, is_exclusive=?, is_default=? WHERE id=? LIMIT 1";
		$stmt_update = $pdo->prepare($sql_update);
		
		if($stmt_update->execute([$description, $friendly, $credit, $artist_id, $musician_id, $release_id, $is_release, $is_exclusive, $is_default, $id])) {
			$output["status"] = "success";
			
			if($is_default && preg_match("/"."^(\(\d+\))$"."/", $artist_id, $match)) {
				if($is_release) {
					$sql_not_default = "UPDATE ".($queued ? "queued_flyers" : "images")." SET is_default=? WHERE artist_id=? AND release_id=? AND is_release=? AND id<>?";
					$values_not_default = [null, $artist_id, $release_id, '1', $id];
				}
				else {
					$sql_not_default = "UPDATE ".($queued ? "queued_flyers" : "images")." SET is_default=? WHERE artist_id=? AND is_release IS NULL AND id<>?";
					$values_not_default = [null, $artist_id, $id];
				}
				
				$stmt_not_default = $pdo->prepare($sql_not_default);
				
				if($stmt_not_default->execute($values_not_default)) {
					$output["status"] = "success";
					$output["image_markdown"] = "![](/images/".$id.".".$extension.")";
				}
			}
			
			if($queued) {
				update_development($pdo, ["type" => "flyer"]);
			}
		}
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>