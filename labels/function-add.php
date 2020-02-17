<?php
	include_once("../php/include.php");
	include_once("../php/class-access_label.php");
	
	$access_label = new access_label($pdo);
	
	if($_SESSION["admin"]) {
		if(is_array($_POST["name"])) {
			foreach($_POST["name"] as $key => $name) {
				$name = sanitize($name);
				
				if(!empty($name)) {
					$romaji = sanitize($_POST["romaji"][$key]) ?: null;
					$friendly = friendly($romaji ?: $name);
					$parent_label_id = is_numeric($_POST["parent_label_id"][$key]) ? $_POST["parent_label_id"][$key] : null;
					$president_id = is_numeric($_POST["president_id"][$key]) ? $_POST["president_id"][$key] : null;
					$president_name = sanitize($_POST["president_name"][$key]) ?: null;
					$president_romaji = sanitize($_POST["president_romaji"][$key]) ?: null;
					$date_started = sanitize(preg_replace("/"."[A-z]"."/", "0", $_POST["date_started"][$key])) ?: null;
					$date_ended = sanitize(preg_replace("/"."[A-z]"."/", "0", $_POST["date_ended"][$key])) ?: null;
					$official_links = sanitize($_POST["official_links"][$key]) ?: null;
					
					$check_label = $access_label->access_label(["friendly" => $friendly, "get" => "name"]);
					$check_label = !is_array($check_label) || empty($check_label) ? $access_label->access_label(["exact_name" => $name, "get" => "name", "limit" => 1]) : $check_label;
					$check_label = is_array($check_label) && !empty($check_label[0]) ? $check_label[0] : $check_label;
					
					if($check_label) {
						$output["status"] = "error";
						$output["result"][] = '<a class="symbol__company" href="/labels/'.$check_label["friendly"].'/">'.$check_label["quick_name"].'</a> already exists.';
					}
					else {
						$sql_add = "INSERT INTO labels (name, romaji, friendly, parent_label_id, president_id, president_name, president_romaji, date_started, date_ended, official_links) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
						$stmt_add = $pdo->prepare($sql_add);
						if($stmt_add->execute([$name, $romaji, $friendly, $parent_label_id, $president_id, $president_name, $president_romaji, $date_started, $date_ended, $official_links])) {
							
							$label_id = $pdo->lastInsertId();
							
							$sql_edit_history = 'INSERT INTO edits_labels (label_id, user_id, content) VALUES (?, ?, ?)';
							$stmt_edit_history = $pdo->prepare($sql_edit_history);
							$stmt_edit_history->execute([ $label_id, $_SESSION['userID'], 'Created.' ]);
							
							$output["status"] = "success";
							$output["result"][] = '<a class="symbol__company" href="/labels/'.$friendly.'/">'.($romaji ?: $name).'</a> added.';
							
							// Award point
							$access_points = new access_points($pdo);
							$access_points->award_points([ 'point_type' => 'added-label' ]);
						}
						else {
							$output["status"] = "error";
							$output["result"][] = '<a class="symbol__company" href="/labels/'.$friendly.'/">'.($romaji ?: $name).'</a> could not be added.';
						}
					}
				}
			}
		}
	}
	else {
		$output["result"] = "Only administrators may add labels.";
	}
	
	if(is_array($output["result"])) {
		$output["result"] = implode('<br />', $output["result"]);
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>