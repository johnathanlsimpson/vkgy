<?php
	include_once("../php/include.php");
	include_once("../php/class-parse_markdown.php");
	
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["admin"]) {
		if(!empty(array_filter($_POST["name"])) && is_array($_POST["name"])) {
			foreach($_POST["name"] as $key => $name) {
				$name = sanitize($name) ?: null;
				$romaji = sanitize($_POST["romaji"][$key]) ?: null;
				$friendly = friendly($romaji ?: $name);
				$position = sanitize($_POST["position"][$key]) ?: 6;
				//$edit_history = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")";
				
				if($name) {
					$history = $_POST["history"][$key];
					$history = str_replace("\r\n", "\n", $history);
					$history_lines = explode("\n", $history);
					
					if(is_array($history_lines)) {
						foreach($history_lines as $line_key => $line) {
							$line = $markdown_parser->validate_markdown($line);
							$references = $markdown_parser->get_reference_data($line);
							$history_lines[$line_key] = $line;
						}
					}
					
					$history = implode("\n", $history_lines);
					
					if(preg_match("/"."\(\d+\)"."/", $history)) {
						$sql_musician = "INSERT INTO musicians (name, romaji, friendly, usual_position, history) VALUES (?, ?, ?, ?, ?)";
						$stmt_musician = $pdo->prepare($sql_musician);
						
						if($stmt_musician->execute([$name, $romaji, $friendly, $position, $history])) {
							$musician_id = $pdo->lastInsertId();
							
							$output["result"]["artists"][] = '<a class="artist" href="/musicians/'.$musician_id.'/'.$friendly.'/">'.($romaji ?: $name).'</a>';
							
							if(is_array($history_lines)) {
								foreach($history_lines as $line) {
									preg_match_all("/"."\((\d+)\)"."/", $line, $matches, PREG_PATTERN_ORDER);
									
									if(is_array($matches) && !empty($matches[1])) {
										foreach($matches[1] as $artist_id) {
											$sql_link = "INSERT INTO artists_musicians (artist_id, musician_id, position, to_end, unique_id) VALUES (?, ?, ?, ?, ?)";
											$stmt_link = $pdo->prepare($sql_link);
											
											if($stmt_link->execute([$artist_id, $musician_id, $position, 1, $artist_id."-".$musician_id])) {
												$output["status"] = "success";
											}
											else {
												$output["status"] = "error";
												$output["result"][] = ($romaji ?: $name)." could not be linked to artist #".$artist_id.".";
											}
										}
									}
								}
							}
						}
						else {
							$output["result"][] = ($romaji ?: $name)." could not be added.";
						}
					}
					else {
						$output["result"][] = ($romaji ?: $name)." was not added; each musician's band history must include at least one band in the database.";
					}
				}
			}
		}
		else {
			$output["result"] = "No musicians were added.";
		}
	}
	else {
		$output["result"] = "Only administrators may add musicians.";
	}
	
	if(is_array($output) && is_array($output["result"]) && $output["result"]["artists"] && is_array($output["result"]["artists"])) {
		$output["result"][] = implode(", ", $output["result"]["artists"])." successfully added.";
		unset($output["result"]["artists"]);
	}
	
	if(is_array($output["result"])) {
		$output["result"] = implode("<br />", $output["result"]);
	}
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>