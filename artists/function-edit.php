<?php

include_once('../php/include.php');
include_once('../php/class-auto_blogger.php');

$access_artist = new access_artist($pdo);
$access_points = new access_points($pdo);
$markdown_parser = new parse_markdown($pdo);

// Make artist is provided and user is signed in
if(is_numeric($_POST['id']) && $_SESSION['is_signed_in']) {
	
	// Set id
	$artist_id = $_POST['id'];
	
	// Loop through some stats and do basic cleaning
	$update_keys = [
		'name',
		'romaji',
		'friendly',
		'type',
		'active',
		'concept_name',
		'concept_romaji',
		'description',
		'label_history',
		'is_exclusive',
		'official_links'
	];
	foreach($update_keys as $key) {
		$value = $_POST[$key];
		$value = sanitize($value);
		$value = strlen($value) > 0 ? $value : null;
		$update_values[$key] = $value;
	}
	
	// For some stats, do specific cleaning
	$update_values['type'] = is_numeric($update_values["type"]) ? $update_values["type"] : 0;
	$update_values['active'] = is_numeric($update_values["active"]) ? $update_values["active"] : 0;
	$update_values['friendly'] = $update_values["friendly"] ? friendly($update_values["friendly"]) : (friendly($update_values["romaji"] ?: $update_values["name"]));
	$update_values['description'] = $update_values["description"] ? sanitize($markdown_parser->validate_markdown($update_values["description"])) : null;
	$update_values['is_exclusive'] = $update_values['is_exclusive'] ? 1 : 0;
	$update_values['official_links'] = $access_artist->clean_websites( $update_values['official_links'] );
	$update_values['id'] = $artist_id;
	
	// Try artist update, if at least name provided
	if(strlen($update_values['name'])) {
		$sql_artist = "UPDATE artists SET ".implode("=?, ", $update_keys)."=? WHERE id=? LIMIT 1";
		$stmt_artist = $pdo->prepare($sql_artist);
		if($stmt_artist->execute( array_values($update_values) )) {
			
			// Success
			$update_successful = true;
			$output['status'] = "success";
			$output['artist_quick_name'] = $update_values["romaji"] ?: $update_values["name"];
			$output['artist_url'] = "/artists/".$update_values["friendly"]."/";
			
		}
	}
	
	// Update musicians
	if($update_successful) {
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
					$output["result"] = "There was an error when updating the musician/artist link.";
				}
			}
		}
	}
	
	// Update the edit log
	if($update_successful) {
		
		// Cycle through edits and update edit history
		if(strlen($_POST['changes'])) {
			
			// Explode changes input and clean
			$changes = $_POST['changes'];
			$changes = preg_match('/'.'^[\w-\[\]\,]+$'.'/', $changes) ? $changes : null;
			$changes = explode(',', $changes);
			$changes = array_filter($changes);
			$changes = array_unique($changes);
			
			if(is_array($changes) && !empty($changes)) {
				
				// Prepare SQL statements
				$sql_artist_edits = 'INSERT INTO edits_artists (artist_id, user_id, content) VALUES (?, ?, ?)';
				$stmt_artist_edits = $pdo->prepare($sql_artist_edits);
				
				$sql_musician_edits = 'INSERT INTO edits_musicians (musician_id, user_id, content) VALUES (?, ?, ?)';
				$stmt_musician_edits = $pdo->prepare($sql_musician_edits);
				
				foreach($changes as $change_key => $change) {
					if($change !== 'changes') {
						
						// If change to musician, clean and insert into separate DB
						if(preg_match('/'.'^musicians\[(\d+)\]\[(\w+)\]$'.'/', $change, $change_match)) {
							$musician_id = $change_match[1];
							
							if(is_array($_POST['musicians']) && is_array($_POST['musicians'][$musician_id]) && strlen($change_match[2])) {
								if($stmt_musician_edits->execute([ $musician_id, $_SESSION['user_id'], sanitize($change_match[2]) ])) {
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
							
							// Award a point for editing musician
							$output['points'] += $access_points->award_points([ 'point_type' => 'edited-musician', 'allow_multiple' => false, 'item_id' => sanitize($musician_id) ]);
							
						}
						
						// Award a point for changing artist
						else {
							$output['points'] += $access_points->award_points([ 'point_type' => 'edited-artist', 'allow_multiple' => true, 'item_id' => $artist_id ]);
						}
						
						// Clean change again
						$change = str_replace('_', ' ', $change);
						$change = sanitize($change);
						
						// Insert change into edits DB
						if(strlen($change)) {
							if($stmt_artist_edits->execute([ $_POST['id'], $_SESSION['user_id'], $change ])) {
							}
						}
					}
				}
			}
		}
	}
	
	// Update biography
	if($update_successful) {
		// ======================================================
		// Update artist biography
		// ======================================================
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
				
				if(is_numeric($history[$i]['parsed_live']['id']) && is_array($history[$i]['parsed_live']['lineup']) && count($history[$i]['parsed_live']['lineup']) < 2) {
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
			
			// Look for pronunciation, save for later (last pronunciation should be the only on relevant to current artist name)
			if(strlen($history[$i]['pronunciation'])) {
				$pronunciation = $history[$i]['pronunciation'];
			}
			
			// Look for artist alternate name, add to DB if necessary
			if(is_array($history[$i]['display_name']) && !empty($history[$i]['display_name']) && strlen($history[$i]['display_name']['name'])) {
				
				// Make sure that display name != current name & not already in DB
				// Since MySQL can't handle =null, we'll have to check if romaji is null and change query/values accordingly
				if($history[$i]['display_name']['romaji'] === null) {
					$sql_check = 'SELECT 1 FROM ( (SELECT 1 FROM artists WHERE id=? AND name=? AND romaji IS NULL) UNION (SELECT 1 FROM artists_names WHERE artist_id=? AND name=? AND romaji IS NULL) ) current_names';
					$values_check = [
						$artist_id, $history[$i]['display_name']['name'],
						$artist_id, $history[$i]['display_name']['name']
					];
				}
				else {
					$sql_check = 'SELECT 1 FROM ( (SELECT 1 FROM artists WHERE id=? AND name=? AND romaji=?) UNION (SELECT 1 FROM artists_names WHERE artist_id=? AND name=? AND romaji=?) ) current_names';
					$values_check = [
						$artist_id, $history[$i]['display_name']['name'], $history[$i]['display_name']['romaji'],
						$artist_id, $history[$i]['display_name']['name'], $history[$i]['display_name']['romaji']
					];
				}
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute($values_check);
				$rslt_check = $stmt_check->fetchColumn();
				
				if(!$rslt_check) {
					
					// Add this to the table of artists' display names
					$sql_display_name = 'INSERT INTO artists_names (artist_id, name, romaji, friendly, pronunciation, date_occurred, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)';
					$stmt_display_name = $pdo->prepare($sql_display_name);
					$stmt_display_name->execute([ $artist_id, $history[$i]['display_name']['name'], $history[$i]['display_name']['romaji'], $history[$i]['display_name']['friendly'], $history[$i]['display_name']['pronunciation'], $history[$i]['date_occurred'], $_SESSION['user_id'] ]);
					
				}
				
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
		$stmt_history->execute([ $artist_id ]);
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
	
	// If *not* successful, send error
	if(!$update_successful) {
		$output['status'] = 'error';
		$output['result'] = print_r($pdo->errorInfo(), true);
	}
	
}
	
$output['status'] = $output['status'] ?: 'error';
	
echo json_encode($output);