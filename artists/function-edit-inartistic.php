<?php
	include_once("../php/include.php");
	include_once("../php/class-auto_blogger.php");
	include_once("../php/class-parse_live.php");
	$markdown_parser = new parse_markdown($pdo);
	
	if(is_numeric($_POST["id"]) && $_SESSION["loggedIn"]) {
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
		
		
		if(!empty($update_values["name"])) {
			$sql_artist = "UPDATE artists SET ".implode("=?, ", $update_keys)."=? WHERE id=? LIMIT 1";
			$sql_artist_values = array_values($update_values);
			//$sql_artist_values[] = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")\n";
			$sql_artist_values[] = sanitize($_POST["id"]);
			
			//echo $_SESSION['username'] === 'inartistic' ? print_r($sql_artist, true).print_r($sql_artist_values, true) : null;
			
			$stmt = $pdo->prepare($sql_artist);
			
			if($stmt->execute($sql_artist_values)) {
				
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
								}
								
								// Clean change again
								$change = str_replace('_', ' ', $change);
								$change = sanitize($change);
								
								// Insert change into edits DB
								if(strlen($change)) {
									if($stmt_artist_edits->execute([ $_POST['id'], $_SESSION['userID'], $change ])) {
									}
								}
							}
						}
					}
				}
				
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
								
								$musician_values = [];
								foreach($musician_keys as $key) {
									if($key === "history") {
										$musician["history"] = $markdown_parser->validate_markdown($musician["history"]);
									}
									$musician_values[] = sanitize($musician[$key]) ?: null;
								}
								
								$sql_musician = "UPDATE musicians SET ".implode("=?, ", $musician_keys)."=? WHERE id=? LIMIT 1";
								//$musician_values[] = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")\n";
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
									preg_match_all("/"."\((\d+)\)"."/", $musician["history"], $matches);
									if(is_array($matches) && !empty($matches)) {
										foreach($matches[1] as $match) {
											if(!in_array($match, $extant_links)) {
												$sql_add_link = "INSERT INTO artists_musicians (artist_id, musician_id, position, unique_id) VALUES (?, ?, ?, ?)";
												$values_add_link = [$match, sanitize($musician["id"]), sanitize($musician["usual_position"]), $match."-".sanitize($musician["id"])];
												$stmt_add_link = $pdo->prepare($sql_add_link);
												$stmt_add_link->execute($values_add_link);
											}
										}
									}
									
									// Remove any duplicate artist-musician links
									
									
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
			else {
				$output["status"] = "error";
			}
		}
		
		
		
		// ======================================================
		// Validate artist biography
		// ======================================================
		function validate_bio($artist_id, $content) {
			
			// Artist biography types
			$artist_bio_types = [
				'cancellation', // live or release (etc) cancellation
				'activity',     // activity change
				'live',         // live event or tour
				'release',      // release
				'name',         // band name change
				'disbandment',  // disbandment or activity end
				'media',        // media, fanclub, etc
				'other',        // ?:?
				'label',        // label change or start
				'lineup',       // notes current lineup (not for lineup change)
				'formation',    // formation-related
				'member',       // member change, member name change, etc.
				'setlist',      // setlist,
				'trouble',      // death, injury, arrest, etc
				'schedule',     // non-special live schedule
				's',            // duplicate for schedule
			];
			
			if(is_numeric($artist_id) && !empty($content)) {
				
				// Format raw bio data and break lines into array
				$content = str_replace(["\r\n", "\r"], "\n", $content);
				$break_pattern = "\n\n(?=(?:\d{4}-)?(?:\d{2}-)?\d{2} )";
				$bio_lines = preg_split("/".$break_pattern."/", $content);
				
				if(is_array($bio_lines)) {
					foreach($bio_lines as $line) {
						$line_type = [];
						
						// Auto-fill date
						$date_pattern = "^(?:(\d{4})-?)?(?:(\d{2})-?)?(\d{2}) ";
						if(preg_match("/".$date_pattern."/", $line, $match)) {
							if(is_array($match)) {
								$this_date["y"] = $match[1];
								$this_date["m"] = $match[2];
								$this_date["d"] = $match[3];
								foreach($this_date as $key => $value) {
									if(empty($value)) {
										$this_date[$key] = $prev_date[$key] ?: ($key === "y" ? "0000" : "00");
									}
									$prev_date[$key] = $this_date[$key];
								}
							}
							$date = implode("-", $this_date);
							$line = preg_replace("/".$date_pattern."/", "", $line);
						}
						
						// Validate manually-added event type
						$type_pattern = " -([\w,]+)$";
						if(preg_match("/".$type_pattern."/", $line, $match)) {
							if(is_array($match) && !empty($match[1])) {
								$line = preg_replace("/".$match[0]."$"."/", "", $line);
								$matches = explode(",", $match[1]);
								if(is_array($matches)) {
									foreach($matches as $type) {
										if(in_array($type, $artist_bio_types)) {
											$line_type[] = $type;
											//$line_type[] = array_flip($artist_bio_types)[$type];
										}
									}
								}
							}
						}
						
						// Or, guess event type
						else {
							$tag_patterns = [
								"formation"    => "\bforms\b|\bformed\b|\bformation\b",
								"label"        => "\blabels?\b|\bgraduates?\b|\bsublabel\b",
								"live"         => "\blive\b|\btour\b|\bevent\b|\boneman\b|\btwoman\b|\bthreeman\b|\bfourman\b",
								"name"         => "\bchanges?\b.*\bnames?\b",
								"activity"     => "(?:\bpauses?\b|\bactivit(?:y|ies)\b|\bfreezes?\b)",
								"disbandment"  => "\bdisbands\b",
								"lineup"       => "lineup| \/ [A-z]\.",
								"member"       => "\bmember\b|\bjoins?\b|\bsecedes?\b|\bsupports?\b|\bvocalist\b|\bguitarist\b|\bbassist\b|\bdrummer\b",
								"release"      => "\breleases?\b",
								"cancellation" => "\bcancel(?:s|led)?\b",
								"media"        => "\bTV\b|\bradio\b|\bmagazine\b|\btheme\b|\bfanclub\b|\bfan\b",
								"trouble"      => "\bdeath\b|\bdies\b|\binjur|\barrest|\bjail\b|\bscandal|\bcancel",
								"setlist"      => "setlist| \/ \d\.",
								"schedule"     => "^[A-z0-9 ]+$",
							];
							foreach($tag_patterns as $type => $pattern) {
								if(preg_match("/".$pattern."/i", $line)) {
									$line_type[] = $type;
									//$line_type[] = array_flip($artist_bio_types)[$type];
								}
							}
							if(empty($line_type)) {
								$line_type[] = 'other';
							}
						}
						
						// Parse additions to live schedule
						if(is_array($line_type) && (in_array('s', $line_type) || in_array('schedule', $line_type))) {
							if(!is_object($live_parser)) {
								$live_parser = new parse_live($pdo);
							}
							
							$parsed_live = $live_parser->parse_raw_input($line, $date, $artist_id);
							
							if($parsed_live && is_array($parsed_live) && !empty($parsed_live)) {
								$line = $parsed_live["livehouse"]["name"];
								
								$type_key = array_search('s', $line_type);
								if(is_numeric($type_key)) {
									$line_type[$type_key] = 'schedule';
								}
							}
							else {
								$note = '<div class="any--weaken symbol__error">Unable to add to schedule. Livehouse may be missing from the database, or line may be tagged improperly. Switching tag to -live.</div>';
								
								$type_key = array_search('s', $line_type);
								if(is_numeric($type_key)) {
									unset($line_type[$type_key]);
								}
								
								$type_key = array_search('schedule', $line_type);
								if(is_numeric($type_key)) {
									unset($line_type[$type_key]);
								}
								
								$line_type[] = 'live';
							}
						}
						
						// Clean line
						$line = sanitize($line);
						$line = str_replace(["&#12304;setlist&#12305; ", "&#12304;lineup&#12305; "], "", $line);
						$line = preg_replace("/"." &#47; (\d+\. )"."/", "\n$1", $line);
						$line_type = array_filter(array_unique($line_type));
						$line_type = is_array($line_type) ? $line_type : ['other'];
						
						// Parse activity area
						if(in_array('formation', $line_type)) {
							if(preg_match('/'.'in ([A-z0-9&#;]+)(?: \(([A-z0-9&#;]+)\))?\.'.'/', $line, $area_match)) {
								if(is_array($area_match) && !empty($area_match)) {
									$area_name_to_search_for = $area_match[2] ?: $area_match[1];
									
									// SQL to confirm area here
									
									// Then... something to pass area to artist?
								}
							}
						}
						
						// Switch line type(s) to numeric forms
						if(is_array($line_type) && !empty($line_type)) {
							foreach($line_type as $type_key => $type_name) {
								$line_type[$type_key] = array_search($type_name, $artist_bio_types);
							}
						}
						
						// Output
						$output[] = [
							"content" => $line,
							"date_occurred" => $date,
							"type" => "(".implode(")(", $line_type).")",
							"artist_id" => $artist_id,
							"user_id" => $_SESSION["userID"],
							"parsed_live" => $parsed_live,
							"note" => $note
						];
						
						unset($parsed_live, $note);
					}
				}
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Update artist biography
		// ======================================================
		$artist_id = sanitize($_POST["id"]);
		$access_artist = new access_artist($pdo);
		$markdown_parser = new parse_markdown($pdo);
		$bio = $markdown_parser->validate_markdown($_POST["bio"]);
		$history = validate_bio($artist_id, $bio);
		$num_history = count($history);
		
		// Pull additions to bio, send through auto poster
		if(!is_object($auto_blogger)) {
			$auto_blogger = new auto_blogger($pdo);
		}
		
		$exploded_original_bio = explode("\n\n", str_replace(["\r\n", "\r"], "\n", sanitize($_POST["original_bio"])));
		$exploded_new_bio = explode("\n\n", str_replace(["\r\n", "\r"], "\n", sanitize($bio)));
		$bio_differences = array_diff($exploded_new_bio, $exploded_original_bio);
		$bio_differences = $markdown_parser->validate_markdown(implode("\n\n", $bio_differences));
		$bio_differences = validate_bio($artist_id, $bio_differences);
		
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
		
		if(!is_object($live_parser)) {
			$live_parser = new parse_live($pdo);
			$live_parser->remove_artist_lives($artist_id);
		}
		
		for($i=0; $i < $num_history; $i++) {
			if(is_array($history[$i]["parsed_live"])) {
				$live_parser->update_live($history[$i]["parsed_live"]);
				
				unset($history[$i]);
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