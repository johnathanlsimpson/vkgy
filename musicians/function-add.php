<?php
	include_once("../php/include.php");
	include_once("../php/class-parse_markdown.php");
	
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["can_add_data"]) {
		if(!empty(array_filter($_POST["name"])) && is_array($_POST["name"])) {
			foreach($_POST["name"] as $key => $name) {
				$name = sanitize($name) ?: null;
				$romaji = sanitize($_POST["romaji"][$key]) ?: null;
				$friendly = friendly($romaji ?: $name);
				$position = sanitize($_POST["position"][$key]) ?: 6;
				$blood_type = sanitize($_POST['blood_type'][$key]) ?: null;
				$birth_date = null;
				
				if($name) {
					
					// Format birth date
					if( strlen($_POST['birth_year'][$key]) || strlen($_POST['birth_date'][$key]) ) {
						
						$b = $_POST['birth_year'][$key].( strlen($_POST['birth_year'][$key]) && strlen($_POST['birth_date'][$key]) ? '-' : null ).$_POST['birth_date'][$key];
						
						if(preg_match('/'.'^\d{4}-\d{2}-\d{2}$'.'/', $b)) {
						}
						elseif(preg_match('/'.'^\d{2}-\d{2}$'.'/', $b)) {
							$b = '0000-'.$b;
						}
						elseif(preg_match('/'.'^[Ss](\d{2})'.'/', $b, $match)) {
							$b = str_replace($match[0], $match[1] + 1925, $b);
						}
						elseif(preg_match('/'.'^[Hh](\d{2})'.'/', $b, $match)) {
							$b = str_replace($match[0], $match[1] + 1988, $b);
						}
						elseif(preg_match('/'.'^[Rr](\d{2})'.'/', $b, $match)) {
							$b = str_replace($match[0], $match[1] + 2019, $b);
						}
						if(preg_match('/'.'^\d{4}$'.'/', $b)) {
							$b .= '-00-00';
						}
						
						$birth_date = $b;
						
					}
					
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
						$sql_musician = "INSERT INTO musicians (name, romaji, friendly, usual_position, history, blood_type, birth_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
						$stmt_musician = $pdo->prepare($sql_musician);
						
						if($stmt_musician->execute([ $name, $romaji, $friendly, $position, $history, $blood_type, $birth_date ])) {
							$_POST_id = $pdo->lastInsertId();
							
							// Update edits table
							$sql_edit_history = 'INSERT INTO edits_musicians (musician_id, user_id, content) VALUES (?, ?, ?)';
							$stmt_edit_history = $pdo->prepare($sql_edit_history);
							if($stmt_edit_history->execute([ $_POST_id, $_SESSION['user_id'], 'created' ])) {
							}
							
							$output["result"]["artists"][] = '<a class="artist" href="/musicians/'.$_POST_id.'/'.$friendly.'/">'.($romaji ?: $name).'</a>';
							
							if(is_array($history_lines)) {
								foreach($history_lines as $line) {
									
									preg_match_all('/'.'\((\d+)\)(?:\/.+?\/)?(?:\[.+?\])?((?: \((?!\d+).+?\))*)'.'/', $line, $bands_in_database);
									
									if(is_array($bands_in_database) && !empty($bands_in_database)) {
										
										$bands_in_database['full_matches'] = $bands_in_database[0];
										$bands_in_database['ids'] = $bands_in_database[1];
										$bands_in_database['notes'] = $bands_in_database[2];
										
										unset($bands_in_database[0], $bands_in_database[1], $bands_in_database[2]);
										
										foreach($bands_in_database['ids'] as $band_in_db_key => $band_in_db_id) {
											
											if(strpos($bands_in_database['notes'][$band_in_db_key], '(roadie)') !== false) {
												$position_name = 'roadie';
												$link_position = 6;
											}
											
											// Check if on different position
											if(preg_match('/'.'\(on (vocals|guitar|bass|drums|keys)\)'.'/', $bands_in_database['notes'][$band_in_db_key], $position_match)) {
												$link_position = array_search($position_match[1], ['other', 'vocals', 'guitar', 'bass', 'drums', 'keys']);
											}
											
											$link_position = is_numeric($link_position) ? $link_position : $position;
											
											// Check if support
											if(strpos($bands_in_database['notes'][$band_in_db_key], 'support') !== false) {
												$position_name = 'support '.['other', 'vocals', 'guitar', 'bass', 'drums', 'keys'][$link_position];
											}
											
											// Check if pseudonym
											if(preg_match('/'.'\(as ([A-z0-9&#;]+)(?: \(([A-z0-9&#;]+))?\)'.'/', $bands_in_database['notes'][$band_in_db_key], $as_name_match)) {
												$as_name = $as_name_match[2] ?: $as_name_match[1];
												$as_romaji = $as_name_match[2] ? $as_name_match[1] : null;
											}
											
											$sql_link = "INSERT INTO artists_musicians (artist_id, musician_id, position, position_name, as_name, as_romaji, to_end) VALUES (?, ?, ?, ?, ?, ?, ?)";
											$stmt_link = $pdo->prepare($sql_link);
											
											if($stmt_link->execute([ $band_in_db_id, $_POST_id, $link_position, $position_name, $as_name, $as_romaji, 1 ])) {
												$output["status"] = "success";
												$linked_artists[] = $band_in_db_id;
											}
											else {
												$output["status"] = "error";
												if(!in_array($band_in_db_id, $linked_artists)) {
													$output["result"][] = ($romaji ?: $name)." could not be linked to artist #".$band_in_db_id.".";
												}
											}
											
											unset($link_position, $position_name, $as_name, $as_romaji);
										}
									}
								}
							}
							
							// Award point
							$access_points = new access_points($pdo);
							$output['points'] += $access_points->award_points([ 'point_type' => 'added-musician' ]);
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