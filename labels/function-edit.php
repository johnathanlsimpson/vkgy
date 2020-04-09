<?php
	include_once("../php/include.php");
	include_once("../php/class-access_label.php");
	
	$access_label = new access_label($pdo);
	
	if($_SESSION["can_add_data"]) {
		if(is_numeric($_POST["id"])) {
			if(!empty($_POST["name"])) {
				
				$sql_values = [
					"name"             => sanitize($_POST["name"]),
					"romaji"           => sanitize($_POST["romaji"]) ?: null,
					"friendly"         => $_POST["friendly"] ?: friendly($_POST["romaji"] ?: $_POST["name"]),
					"parent_label_id"  => is_numeric($_POST["parent_label_id"]) ? $_POST["parent_label_id"] : null,
					"president_id"     => is_numeric($_POST["president_id"]) ? $_POST["president_id"] : null,
					"president_name"   => sanitize($_POST["president_name"]) ?: null,
					"president_romaji" => sanitize($_POST["president_romaji"]) ?: null,
					"date_started"     => sanitize(preg_replace("/"."[A-z]"."/", "0", $_POST["date_started"])) ?: null,
					"date_ended"       => sanitize(preg_replace("/"."[A-z]"."/", "0", $_POST["date_ended"])) ?: null,
					"official_links"   => sanitize($_POST["official_links"]) ?: null
				];
				
				$id                 =  sanitize($_POST["id"]);
				
				$check_label = $access_label->access_label(["friendly" => $sql_values["friendly"], "get" => "name"]);
				$check_label = is_array($check_label) && !empty($check_label[0]) ? $check_label[0] : $check_label;
				
				if(is_array($check_label) && $check_label["id"] !== $id) {
					$output["result"] = '<a class="symbol__company" href="/labels/'.$check_label["friendly"].'/">'.$check_label["quick_name"].'</a> already exists. (If two labels share the same name, please give each one a unique &ldquo;friendly name.&rdquo;)';
				}
				else {
					$check_label = $access_label->access_label(["id" => $id, "get" => "name"]);
					
					if($check_label) {
						$sql_edit = "UPDATE labels SET ".implode("=?, ", array_keys($sql_values))."=? WHERE id=? LIMIT 1";
						$sql_stmt = $pdo->prepare($sql_edit);
						
						if($sql_stmt->execute(array_merge(array_values($sql_values), [$id]))) {
							
							$sql_edit_history = 'INSERT INTO edits_labels (label_id, user_id) VALUES (?, ?)';
							$stmt_edit_history = $pdo->prepare($sql_edit_history);
							$stmt_edit_history->execute([ $id, $_SESSION['user_id'] ]);
							
							$output["status"] = "success";
							$output["result"] = '<a class="symbol__company" href="/labels/'.$sql_values["friendly"].'/">'.($sql_values["romaji"] ?: $sql_values["name"]).'</a> successfully edited.';
							$output["edit_url"] = "/labels/".$sql_values["friendly"]."/edit/";
							$output["quick_name"] = $sql_values["romaji"] ?: $sql_values["name"];
							
							// Award point
							$access_points = new access_points($pdo);
							$access_points->award_points([ 'point_type' => 'edited-label', 'allow_multiple' => false, 'item_id' => $id ]);
						}
						else {
							$output["result"] = "Sorry, the label couldn't be updated.";
						}
					}
				}
			}
			else {
				$output["result"] = "Sorry, the label name may not be empty.";
			}
		}
		else {
			$output["result"] = "Sorry, something's wrong with the label ID.";
		}
	}
	else {
		$output["result"] = "Sorry, only administrators may add labels.";
	}
	
	if(is_array($output["result"])) {
		$output["result"] = implode('<br />', $output["result"]);
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>