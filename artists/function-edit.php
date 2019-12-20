<?php
	include_once("../php/include.php");
if($_SESSION['username'] === 'inartistic') { //include('../artists/function-edit-inartistic.php'); 
} else { } ?>

<?php
	include_once("../php/class-auto_blogger.php");
	$markdown_parser = new parse_markdown($pdo);
	
	if(is_numeric($_POST["id"]) && $_SESSION["loggedIn"]) {
		
		//
		// Get differences
		//
		function stringify_differences($differences) {
			
			if(is_array($differences)) {
				array_walk_recursive($differences, function(&$value, $key) {
					$value = sanitize($value);
				});
			}
			else {
				$differences = sanitize($differences);
			}
			
			$differences = json_encode($differences);
			return $differences;
			
		}
		
		function get_differences($original_data, $new_data) {
			
			// Replace line breaks in new data
			array_walk_recursive($new_data, function(&$value, $key) {
				$value = str_replace(["\r\n", "\r"], "\n", $value);
				$value .= 'z';
			});
			
			// If given string for original data, assume it was urlencoded
			if(!is_array($original_data)) {
				parse_str($original_data, $original_data);
			}
			
			unset($original_data['changes'], $original_data['original'], $new_data['changes'], $new_data['original']);
			
			$diff = array_diff(array_map('json_encode', $original_data), array_map('json_encode', $new_data));
			
			global $output;
			//$output['result'] = print_r($original_data['musicians'][155]['position'], true).print_r($new_data['musicians'][155]['position'], true);
			ob_start(); var_dump(reset($original_data['musicians'])); $x = ob_get_clean();
						ob_start(); var_dump(reset($new_data['musicians'])); $y = ob_get_clean();
		$output['result'] .= print_r($x, true);
			$output['result'] .= "<br /><br />";
		$output['result'] .= print_r($y, true);
		$x = ['position' => 2, 'x' => ['x']];
$new = ['position' => 1, 'x' => ['x']];
//$output['result'] = print_r( array_udiff($new_data, $original_data, function($a, $b) {return $a <=> $b;}), true );
			// Get differences between most values
			$differences = array_diff( $new_data, $original_data );
			//unset($differences['original']);
			
			//$output['result'] .= 'orig value '.$original_data['musicians'][156]['position']."<br />";
			//$output['result'] .= 'new value '.$new_data['musicians'][156]['position']."<br />";
			
			// Loop through data and clean up
			foreach($original_data as $original_key => $original_value) {
				
				// Remove any inputs named original*
				if(strpos($original_key, 'original') === 0) {
					unset($differences[$original_key]);
				}
				
				else {
					
					// Try to look through and catch any changes that were made in nested arrays
					// This could be a thorough for arrays that are super nested, but I'm tired
					if(is_array($original_value)) {
						
						foreach($original_value as $nested_key => $nested_value) {
							
							if(is_array($nested_value)) {
								
			//$output['result'] .= 'orig value 2 '.print_r($original_data[$original_key][$nested_key], true)."<br />";
			//$output['result'] .= 'new value 2 '.print_r($new_data[$original_key][$nested_key], true)."<br />";
			//$output['result'] .= 'diff '.($new_data[$original_key][$nested_key] === $original_data[$original_key][$nested_key])."<br /><br />";
								
			//$output['result'] = print_r($original_data[$original_key][$nested_key], true).'*'.print_r($new_data[$original_key][$nested_key], true);
								
								// We have to account for the possibility that orig value was an array, but was removed, so new value isn't array
								// Make sure we make note of strings that have been removed or arrays which have been removed, but we don't want to add "empty difference arrays"
								$diff = array_diff( $new_data[$original_key][$nested_key], $original_data[$original_key][$nested_key] );
								
								
								
								if($nested_key === 155) {
								//	$output['result'] .= 'orig array<pre>'.print_r($original_data[$original_key][$nested_key],true).'</pre>'."\n\n";
									//$output['result'] .= 'new array<pre>'.print_r($new_data[$original_key][$nested_key],true).'</pre>'."\n\n";
									//$output['result'] .= 'array diff<pre>'.print_r(array_diff( $new_data[$original_key][$nested_key], $original_data[$original_key][$nested_key] ),true).'</pre>'."\n\n";
								//	$output['result'] .= 'array diff test<pre>'.print_r(array_diff( ['position' => $new_data[$original_key][$nested_key]['position']], ['position' => $original_data[$original_key][$nested_key]['position']] ),true).'</pre>'."\n\n";
								}
								
								
								
								
								
								
								$output['result'] .= 'rawdiff<pre>'.print_r($diff, true).'</pre>';
								
								if(!empty($diff) || !isset($new_data[$original_key][$nested_key])) {
									$differences[$original_key][$nested_key] = $diff;
								}
								
							}
							else {
								
								$diff = array_diff( $new_data[$original_key], $original_data[$original_key] );
								$output['result'] .= 'rawdiff2<pre>'.print_r($diff, true).'</pre>';
								if(!empty($diff) || !isset($new_data[$original_key])) {
									$differences[$original_key] = $diff;
								}
								
							}
							
						}
						
					}
					
					// Or if text, let's see if it's a long field, let's separate it and compare each line
					elseif(strpos($original_value, "\n") !== false) {
						
						$diff = array_diff( explode("\n", $new_data[$original_key]), explode("\n", $original_value) );
						
						if(!empty($diff) || strpos($new_data[$original_key], "\n") === false) {
							$differences[$original_key] = $diff;
						}
						
					}
					
				}
				
			}
			
			
			$output['result'] .= 'diffs<pre>'.print_r($differences, true).'</pre>';
			return $differences;
			
		}
		
		$changes = get_differences($_POST['original'], $_POST);
		
		//$output['result'] = print_r($changes, true);
		
		$update_keys = [
			"name",
			"romaji",
			"friendly",
			"type",
			"active",
			"concept_name",
			"concept_romaji",
			"description",
			"label_history",
			"official_links",
			"is_exclusive"
		];
		
		foreach($update_keys as $key) {
			$value = $_POST[$key];
			$value = sanitize($value);
			$value = strlen($value) > 0 ? $value : null;
			$update_values[$key] = $value;
		}
		
		$update_values["type"] = is_numeric($update_values["type"]) ? $update_values["type"] : 0;
		$update_values["active"] = is_numeric($update_values["active"]) ? $update_values["active"] : 0;
		$update_values["friendly"] = $update_values["friendly"] ? friendly($update_values["friendly"]) : (friendly($update_values["romaji"] ?: $update_values["name"]));
		$update_values["description"] = $update_values["description"] ? sanitize($markdown_parser->validate_markdown($update_values["description"])) : null;
		$update_values['is_exclusive'] = $update_values['is_exclusive'] ? 1 : 0;
		
		if(!empty($update_values["name"])) {
			$sql_artist = "UPDATE artists SET ".implode("=?, ", $update_keys)."=? WHERE id=? LIMIT 1";
			$sql_artist_values = array_values($update_values);
			$sql_artist_values[] = sanitize($_POST["id"]);
			
			$stmt = $pdo->prepare($sql_artist);
			
			if($stmt->execute($sql_artist_values)) {
				
				// Cycle through edits and update edit history
				//if(strlen($_POST['changes'])) {
					
					// Explode changes input and clean
					/*$changes = $_POST['changes'];
					$changes = preg_match('/'.'^[\w-\[\]\,]+$'.'/', $changes) ? $changes : null;
					$changes = explode(',', $changes);
					$changes = array_filter($changes);
					$changes = array_unique($changes);*/
				
					if(is_array($changes) && !empty($changes)) {
						
						// Prepare SQL statements
						$sql_artist_edits = 'INSERT INTO edits_artists (artist_id, user_id, name, content) VALUES (?, ?, ?, ?)';
						$stmt_artist_edits = $pdo->prepare($sql_artist_edits);
						
						$sql_musician_edits = 'INSERT INTO edits_musicians (musician_id, user_id, name, content) VALUES (?, ?, ?, ?)';
						$stmt_musician_edits = $pdo->prepare($sql_musician_edits);
						
						foreach($changes as $change_key => $change) {
							if($change_key !== 'changes') {
								
								// If change to musician, clean and insert into separate DB
								if($change_key === 'musicians') {
									foreach($change as $musician_id => $musician_changes) {
										foreach($musician_changes as $musician_change_key => $musician_change) {
											if($stmt_musician_edits->execute([ sanitize($musician_id), $_SESSION['userID'], sanitize($musician_change_key), stringify_differences($musician_change) ])) {
											}
										}
										
										$musician_name = 
											$_POST['musicians'][$musician_id]['as_romaji'] ?:
											($_POST['musicians'][$musician_id]['as_name'] ?:
											($_POST['musicians'][$musician_id]['romaji'] ?:
											($_POST['musicians'][$musician_id]['name'] ?:
											$_POST['musicians'][$musician_id]['friendly'])));
										
										$change[$musician_name] = $musician_changes;
										unset($change[$musician_id]);
									}
								}
								/*if(preg_match('/'.'^musicians\[(\d+)\]\[(\w+)\]$'.'/', $change, $change_match)) {
									$musician_id = $change_match[1];
									
									if(is_array($_POST['musicians']) && is_array($_POST['musicians'][$musician_id])) {
										if($stmt_musician_edits->execute([ $musician_id, $_SESSION['userID'], sanitize($change_match[2]) ])) {
										}
										
										$musician_name = 
											$_POST['musicians'][$musician_id]['as_romaji'] ?:
											($_POST['musicians'][$musician_id]['as_name'] ?:
											($_POST['musicians'][$musician_id]['romaji'] ?:
											($_POST['musicians'][$musician_id]['name'] ?:
											$_POST['musicians'][$musician_id]['friendly'])));
										
										$change = 'musician ('.$musician_name.') '.$change_match[2];
									}
									else {
										$change = null;
									}
								}*/
								
								// Clean change again
								//$change = str_replace('_', ' ', $change);
								//$change = sanitize($change);
								
								// Insert change into edits DB
								if(is_array($change) || strlen($change)) {
									if($stmt_artist_edits->execute([ $_POST['id'], $_SESSION['userID'], sanitize($change_key), stringify_differences($change) ])) {
									}
								}
							}
						}
					}
				//}
				
				$output["status"] = "success";
				$output["artist_quick_name"] = $update_values["romaji"] ?: $update_values["name"];
				$output["artist_url"] = "/artists/".$update_values["friendly"]."/";
				
				if(is_array($_POST["musicians"])) {
					foreach($_POST["musicians"] as $musician) {
						$artist_musician_keys = [
							"as_name",
							"as_romaji",
							"position",
							"position_name",
							"position_romaji",
							"to_end",
							"dates_active"
						];
						
						$musician['as_name'] = trim($musician['as_name']);
						$musician['as_romaji'] = trim($musician['as_romaji']);
						
						$artist_musician_values = [];
						foreach($artist_musician_keys as $key) {
							$artist_musician_values[] = $musician[$key] ? sanitize($musician[$key]) : null;
						}
						
						$sql_artist_musician = "UPDATE artists_musicians SET ".implode("=?, ", $artist_musician_keys)."=? WHERE artist_id=? AND musician_id=? LIMIT 1";
						
						array_push($artist_musician_values, sanitize($_POST["id"]), sanitize($musician["id"]));
						
						$stmt = $pdo->prepare($sql_artist_musician);
						
						if($stmt->execute($artist_musician_values)) {
							if(!empty($musician["name"])) {
								
								// Format musician birthdate
								if(strlen($musician['birth_date'])) {
									$b = $musician['birth_date'];
									
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
									if(preg_match('/'.'^\d{4}$'.'/', $b)) {
										$b .= '-00-00';
									}
									
									$musician['birth_date'] = $b;
								}
								
								$musician_keys = [
									"name",
									"romaji",
									"usual_position",
									"gender",
									"blood_type",
									"birth_date",
									"birthplace",
									"friendly",
									"history"
								];
								
								$musician["history"] = $markdown_parser->validate_markdown($musician["history"]);
								$musician['name'] = trim($musician['name']);
								$musician['romaji'] = trim($musician['romaji']);
								$musician['friendly'] = !$musician['friendly'] || $musician['friendly'] === '-' ? friendly($musician['romaji'] ?: $musician['name']) : $musician['friendly'];
								
								$musician_values = [];
								foreach($musician_keys as $key) {
									$musician_values[] = sanitize($musician[$key]) ?: null;
								}
								
								$sql_musician = "UPDATE musicians SET ".implode("=?, ", $musician_keys)."=? WHERE id=? LIMIT 1";
								$musician_values[] = sanitize($musician["id"]);
								$stmt = $pdo->prepare($sql_musician);
								
								if($stmt->execute($musician_values)) {
									// Check for and delete any artist-musician links that were removed from the musician's band history
									$sql_links = "SELECT id, artist_id, musician_id FROM artists_musicians WHERE musician_id=?";
									$stmt_links = $pdo->prepare($sql_links);
									$stmt_links->execute([sanitize($musician["id"])]);
									
									$extant_links = [];
									foreach($stmt_links->fetchAll() as $row) {
										if(strpos($musician["history"], "(".$row["artist_id"].")") === false) {
											$sql_delete_links = "DELETE FROM artists_musicians WHERE id=? LIMIT 1";
											$stmt_delete_links = $pdo->prepare($sql_delete_links);
											$stmt_delete_links->execute([$row["id"]]);
										}
										
										$extant_links[] = $row["artist_id"];
									}
									
									// Add any new artist-musician links
									preg_match_all('/'.'\((\d+)\)(?:\/.+?\/)?(?:\[.+?\])?((?: \((?!\d+).+?\))*)'.'/', $musician['history'], $bands_in_database);
									
									if(is_array($bands_in_database) && !empty($bands_in_database)) {
										$bands_in_database['full_matches'] = $bands_in_database[0];
										$bands_in_database['ids'] = $bands_in_database[1];
										$bands_in_database['notes'] = $bands_in_database[2];
										
										unset($bands_in_database[0], $bands_in_database[1], $bands_in_database[2]);
										
										foreach($bands_in_database['ids'] as $band_in_db_key => $band_in_db_id) {
											if(!in_array($band_in_db_id, $extant_links)) {
												
												// Check if roadie
												if(strpos($bands_in_database['notes'][$band_in_db_key], 'roadie') !== false) {
													$position_name = 'roadie';
													$position = 6;
												}
												
												// Check if on different position
												if(preg_match('/'.'\(on (vocals|guitar|bass|drums|keys)\)'.'/', $bands_in_database['notes'][$band_in_db_key], $position_match)) {
													$position = array_search($position_match[1], ['other', 'vocals', 'guitar', 'bass', 'drums', 'keys']);
												}
												
												$position = is_numeric($position) ? $position : $musician['usual_position'];
												
												// Check if support
												if(strpos($bands_in_database['notes'][$band_in_db_key], 'support') !== false) {
													$position_name = 'support '.['other', 'vocals', 'guitar', 'bass', 'drums', 'keys'][$position];
												}
												
												// Check if pseudonym
												if(preg_match('/'.'\(as ([A-z0-9&#;]+)(?: \(([A-z0-9&#;]+))?\)'.'/', $bands_in_database['notes'][$band_in_db_key], $as_name_match)) {
													$as_name = $as_name_match[2] ?: $as_name_match[1];
													$as_romaji = $as_name_match[2] ? $as_name_match[1] : null;
												}
												
												$sql_add_link = "INSERT INTO artists_musicians (artist_id, musician_id, position, position_name, as_name, as_romaji, unique_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
												$values_add_link = [ $band_in_db_id, sanitize($musician['id']), $position, $position_name, $as_name, $as_romaji, $band_in_db_id.'-'.sanitize($musician['id']) ];
												$stmt_add_link = $pdo->prepare($sql_add_link);
												$stmt_add_link->execute($values_add_link);
												
												unset($position, $position_name, $as_name, $as_romaji);
											}
										}
									}
									
									$output["status"] = "success";
								}
								else {
									$output["status"] = "error";
									$output["result"] = "There was an error updating a musician.";
								}
							}
						}
						else {
							$output["status"] = "error";
							//$output["result"] = "There was an error when updating the musician/artist link.";
						}
					}
				}
			}
			else {
				$output["status"] = "error";
				$output['result'] = print_r($pdo->errorInfo(), true);
			}
		}
		
		
		
		// ======================================================
		// Update artist biography
		// ======================================================
		$artist_id = sanitize($_POST["id"]);
		$access_artist = new access_artist($pdo);
		$markdown_parser = new parse_markdown($pdo);
		$bio = $markdown_parser->validate_markdown($_POST["bio"]);
		$history = $access_artist->validate_bio($artist_id, $bio);
		$history = is_array($history) ? $history : [ $history ];
		$num_history = count($history);
		
		// Pull additions to bio, send through auto poster
		if(!is_object($auto_blogger)) {
			$auto_blogger = new auto_blogger($pdo);
		}
		
		$exploded_original_bio = explode("\n\n", str_replace(["\r\n", "\r"], "\n", sanitize($_POST["original_bio"])));
		$exploded_new_bio = explode("\n\n", str_replace(["\r\n", "\r"], "\n", sanitize($bio)));
		$bio_differences = array_diff($exploded_new_bio, $exploded_original_bio);
		$bio_differences = $markdown_parser->validate_markdown(implode("\n\n", $bio_differences));
		$bio_differences = $access_artist->validate_bio($artist_id, $bio_differences);
		
		if(is_array($bio_differences) && !empty($bio_differences)) {
			foreach($bio_differences as $bio_difference) {
				$auto_post_url[] = $auto_blogger->auto_post('bio', $bio_difference);
			}
			
			if(is_array($auto_post_url)) {
				$auto_post_url = array_unique(array_filter($auto_post_url));
				$auto_post_url = is_array($auto_post_url) ? $auto_post_url[0] : null;
				
				if($auto_post_url) {
					$output["result"] =
						'A blog entry has been auto-generated, and will be shared to social media in 15 minutes. Any user may edit the entry; edits will be reflected in the social media posts.'.
						'<br /><br/ >'.
						'<a class="a--outlined a--padded symbol__edit" href="'.$auto_post_url.'edit/">Edit blog entry</a>'.
						'<a class="a--padded symbol__news" href="'.$auto_post_url.'">View blog entry</a>';
				}
			}
		}
		
		// Init schedule updater
		if(!is_object($live_parser)) {
			$live_parser = new parse_live($pdo);
		}
		
		for($i=0; $i < $num_history; $i++) {
			
			// If history item was successfully interpreted as a schedule,
			// add to full live schedule list so we can reference it later and delete any
			// entries that aren't still present. Also, remove from history array,
			// so that no other transforms are performed on it
			if(is_array($history[$i]["parsed_live"])) {
				
				if(is_numeric($history[$i]['parsed_live']['id'])) {
					$all_extant_lives[] = $history[$i]['parsed_live']['id'];
				}
				else {
					$all_extant_lives[] = $live_parser->update_live($history[$i]["parsed_live"]);
				}
				
				if($history[$i]['type'] === '(14)') {
					unset($history[$i]);
				}
			}
			
			// Loop through and scrape activity areas
			if(is_numeric($history[$i]['area_id'])) {
				$area_ids[] = $history[$i]['area_id'];
			}
			
			// Loop through, look for pronunciation, save for later (use final pronunciation, for now)
			if(strlen($history[$i]['pronunciation'])) {
				$pronunciation = $history[$i]['pronunciation'];
			}
			
		}
		
		// Find and remove lives which are no longer mentioned in the artist's bio
		if(!is_object($access_live)) {
			$access_live = new access_live($pdo);
		}
		$access_live->batch_delete_live_links([ 'artist_id' => $artist_id, 'lives_to_ignore' => $all_extant_lives ]);
		
		
		// Update activity areas in DB
		if(is_array($area_ids) && !empty($area_ids)) {
			$sql_current_areas = 'SELECT * FROM areas_artists WHERE artist_id=?';
			$stmt_current_areas = $pdo->prepare($sql_current_areas);
			$stmt_current_areas->execute([ $artist_id ]);
			$rslt_current_areas = $stmt_current_areas->fetchAll();
			
			if(is_array($rslt_current_areas) && !empty($rslt_current_areas)) {
				foreach($rslt_current_areas as $current_area) {
					if(in_array($current_area['area_id'], $area_ids)) {
						$area_key = array_search($current_area['area_id'], $area_ids);
						unset($area_ids[$area_key]);
					}
					else {
						$sql_delete_area = 'DELETE FROM areas_artists WHERE id=? LIMIT 1';
						$stmt_delete_area = $pdo->prepare($sql_delete_area);
						if($stmt_delete_area->execute([ $current_area['id'] ])) {
						}
					}
				}
			}
			
			if(is_array($area_ids) && !empty($area_ids)) {
				foreach($area_ids as $area_id) {
					$sql_add_area = 'INSERT INTO areas_artists (artist_id, area_id) VALUES (?, ?)';
					$stmt_add_area = $pdo->prepare($sql_add_area);
					if($stmt_add_area->execute([ $artist_id, $area_id ])) {
					}
				}
			}
		}
		
		// Update pronunciation
		if($pronunciation) {
			$sql_pronunciation = 'UPDATE artists SET pronunciation=? WHERE id=? LIMIT 1';
			$stmt_pronunciation = $pdo->prepare($sql_pronunciation);
			if($stmt_pronunciation->execute([ $pronunciation, $artist_id ])) {
			}
		}
		
		$sql_history = "SELECT id FROM artists_bio WHERE artist_id=?";
		$stmt_history = $pdo->prepare($sql_history);
		$stmt_history->execute([$artist_id]);
		$extant_history = $stmt_history->fetchAll();
		$sql_history = [];
		
		if(is_array($extant_history)) {
			foreach($extant_history as $i => $extant_line) {
				if(!empty($history[$i])) {
					$sql_history[] = "UPDATE artists_bio SET content=?, date_occurred=?, type=?, user_id=? WHERE id=? LIMIT 1";
					$sql_history_values[] = [$history[$i]["content"], $history[$i]["date_occurred"], $history[$i]["type"], $history[$i]["user_id"], $extant_line["id"]];
					
					unset($history[$i]);
				}
				else {
					$sql_history[] = "DELETE FROM artists_bio WHERE id=? LIMIT 1";
					$sql_history_values[] = [$extant_line["id"]];
				}
			}
		}
		
		if(is_array($history) && !empty($history)) {
			foreach($history as $history_line) {
				if(is_array($history_line) && !empty($history_line)) {
					$sql_history[] = "INSERT INTO artists_bio (content, date_occurred, type, user_id, artist_id) VALUES (?, ?, ?, ?, ?)";
					$sql_history_values[] = [$history_line["content"], $history_line["date_occurred"], $history_line["type"], $history_line["user_id"], $history_line["artist_id"]];
				}
			}
		}
		
		if(is_array($sql_history) && is_array($sql_history_values)) {
			foreach($sql_history as $i => $sql_line) {
				$stmt_line = $pdo->prepare($sql_line);
				if(!$stmt_line->execute($sql_history_values[$i])) {
					$output["status"] = "error";
				}
			}
		}
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>

<?php ?>