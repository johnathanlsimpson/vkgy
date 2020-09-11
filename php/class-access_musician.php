<?php
	include_once("../php/class-access_artist.php");
	
	class access_musician {
		public  $pdo;
		private $artist_list;
		
		
		
		// ======================================================
		// Connect
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
				
				$this->pdo = $pdo;
			}
			else {
				$this->pdo = $pdo;
			}
			$this->access_artist = new access_artist($pdo);
			$this->access_label = new access_label($pdo);
		}
		
		
		
		// ======================================================
		// Parse band history
		// ======================================================
		function get_artist_references_from_band_history($band_history) {
			if(!empty($band_history)) {
				// Standardize returns, remove deprecated [*] notation, replace escaped brackets/pipes with entity notation
				$band_history = str_replace("\r\n", "\n", $band_history);
				$band_history = str_replace(["[*]\n", "[*]"], "", $band_history);
				$band_history = str_replace(["&#92;&#91;", "&#92;&#93;"], ["&#91;", "&#93;"], $band_history);
				$band_history = str_replace(['&#92;&#124;', '\\|'], '&#124;', $band_history);
				
				// Explode band history into periods of activity (each line)
				$band_history_periods = explode("\n", $band_history);
				if(is_array($band_history_periods)) {
					foreach($band_history_periods as $period_key => $band_history_period) {
						$x = -1;
						
						// If has !hide flag, remove for parsing
						if( strpos($band_history_period, '!hide ') === 0 ) {
							$period_is_hidden = true;
							$band_history_period = substr_replace($band_history_period, '', 0, 6);
						}
						else {
							$period_is_hidden = false;
						}
						
						// Explode each time period by normal bands and session bands (pre-| and post-|)
						$band_types = explode(' | ', $band_history_period);
						
						// Go through both chunks (first chunk: normal band, second: session)
						if(is_array($band_types) && !empty($band_types)) {
							foreach($band_types as $band_type_num => $band_type_chunk) {
								$band_type = $band_type_num ? 'session' : 'normal';
								
								// Explode each time period into bands active during that period (comma separated)
								$bands = explode(", ", $band_type_chunk);
								if(is_array($bands)) {
									foreach($bands as $band_tmp_key => $band) {
										
										$x++;
										$band_key = $band_type === 'normal' ? $band_tmp_key : $x;
										
										// Replace escaped parentheses with entity notations
										$band = str_replace(["\\(", "\\)"], ["&#40;", "&#41;"], $band);
										$band = str_replace(["&#92;(", "&#92;)"], ["&#40;", "&#41;"], $band);
										
										// Set up patterns for bands, un-added bands, and notes
										$regex_band_in_database = "(^\((\d+)\)(?:\/[^\/\[\]\(\)]+\/)?(?:\[([^\(]+?)(?: \((.+?)\))?\])?)(?:.*)?";
										$regex_not_in_database = "^((?:(?! \().)+)(?: \((?!as )([^\(\)]*(?=&)[^\(\)]+(?=;)[^\(\)]+)\))?";
										$regex_notes = " \((.+?)(?=(?:(?<!\?)\) \(|\)$))\)";
										$regex_simple_note = '^\((.+)\)$';
										
										// If group is band in DB
										if(preg_match_all("/".$regex_band_in_database."/", $band, $matches, PREG_SET_ORDER)) {
											$matches = $matches[0];
											
											// Re-convert escaped parentheses back into actual parentheses
											if(is_array($matches) && !empty($matches)) {
												foreach($matches as $match_key => $match) {
													$matches[$match_key] = str_replace(["&#40;", "&#41;"], ["(", ")"], $match);
												}
											}
											
											// Get id, display name/romaji if applicable
											if(!empty($matches[2])) {
												$tmp_band_history[$period_key][$band_key] = [
													"id" => $matches[2],
													"display_name" => ($matches[4] ?: ($matches[3] ?: NULL)),
													"display_romaji" => $matches[4] ? $matches[3] : NULL,
													'type' => $band_type,
												];
												$band = str_replace($matches[1], "", $band);
												
												// Get artist info from DB
												if(empty($this->artist_list[$matches[2]])) {
													$name = $this->access_artist->access_artist(["id" => $matches[2], "get" => "name"]);
													if(is_array($name)) {
														foreach($name as $name_key => $n) {
															$tmp_band_history[$period_key][$band_key][$name_key] = $n;
															$this->artist_list[$matches[2]][$name_key] = $n;
														}
													}
												}
												else {
													$tmp_band_history[$period_key][$band_key] = array_merge($tmp_band_history[$period_key][$band_key], $this->artist_list[$matches[2]]);
												}
											}
										}
										
										// If group is a note (or several notes)
										elseif(preg_match_all('/'.$regex_simple_note.'/', $band, $matches, PREG_SET_ORDER)) {
											
											// Above, once a band is parsed, it's removed from the string and the leftover notes are passed on to be parsed later
											// Take advantage of that here: create a false "band" string that starts with a space, and includes the note(s) found here
											// It will be dealt with after this if/else series
											if(!empty($matches[0][0])) {
												$band = ' '.$matches[0][0];
												$tmp_band_history[$period_key][$band_key]['type'] = $band_type;
											}
										}
										
										// Else if group is band not in DB
										elseif(preg_match_all("/".$regex_not_in_database."/", $band, $matches, PREG_SET_ORDER)) {
											$matches = $matches[0];
											if(!empty($matches[1])) {
												$tmp_band_history[$period_key][$band_key] = [
													"name" => ($matches[2] ?: $matches[1]),
													"romaji" => ($matches[2] ? $matches[1] : NULL),
													'type' => $band_type,
												];
												$band = str_replace($matches[0], "", $band);
											}
										}
										
										// Else assume period of inactivity
										else {
											$tmp_band_history[$period_key][$band_key]["notes"][0] = "unknown";
										}
										
										// Get notes from band chunk, add
										if(!empty($band)) {
											if(preg_match_all("/".$regex_notes."/", $band, $matches)) {
												if(is_array($matches)) {
													foreach($matches[1] as $match) {
														$tmp_band_history[$period_key][$band_key]["notes"][] = $match;
														
														// No clue why this is here? Removes note from band name, but shouldn't need to as $band isn't reused?
														//$band = str_replace($matches[1], "", $band);
													}
												}
											}
										}
										
										// Save 'is hidden' flag
										$tmp_band_history[$period_key][$band_key]['is_hidden'] = $period_is_hidden;
										
									}
								}
							}
						}
					}
				}
				
				return $tmp_band_history;
			}
		}
		
		
		
		// ======================================================
		// Build Musician object
		// ======================================================
		function access_musician($args = []) {
			// Select
			$sql_select = [];
			if(is_numeric($args["artist_id"])) {
				//array_push($sql_select, "COALESCE(artists_musicians.as_romaji, artists_musicians.as_name) AS as_quick_name", "artists_musicians.position", "artists_musicians.as_name", "artists_musicians.as_romaji");
				array_push($sql_select, "COALESCE(artists_musicians.as_romaji, artists_musicians.as_name, musicians.romaji, musicians.name) AS quick_name", "artists_musicians.position", 'artists_musicians.to_end', "artists_musicians.as_name", "artists_musicians.as_romaji");
			}
			if($args["get"] === "name") {
				array_push($sql_select, "musicians.id", "musicians.name", "musicians.romaji", "COALESCE(musicians.romaji, musicians.name) AS quick_name", "musicians.friendly");
			}
			if($args["get"] === "list") {
				array_push($sql_select, "musicians.id", "musicians.usual_position", 'musicians.blood_type', 'musicians.birth_date', "musicians.history", "musicians.name", "musicians.romaji", "COALESCE(musicians.romaji, musicians.name) AS quick_name", "musicians.friendly");
			}
			if($args["get"] === "all") {
				array_push($sql_select, "musicians.*", "COALESCE(musicians.romaji, musicians.name) AS quick_name");
			}
			if(is_numeric($args["artist_id"]) && $args["get"] === "all") {
				array_push($sql_select, "artists_musicians.*", "COALESCE(artists_musicians.as_romaji, artists_musicians.as_name, musicians.romaji, musicians.name) AS quick_name", "artists_musicians.musician_id AS id");
			}
			$sql_select = !empty($sql_select) ? $sql_select : ["musicians.*", "COALESCE(musicians.romaji, musicians.name) AS quick_name"];
			
			// From
			switch(true) {
				case (is_numeric($args["artist_id"])):
					$sql_from = ["artists_musicians", "LEFT JOIN musicians ON musicians.id=artists_musicians.musician_id"];
					break;
					
				default:
					$sql_from = ["musicians"];
					break;
			}
			
			// Where
			$sql_where = [];
			$sql_values = [];
			
			if(preg_match("/"."[\d\-]+"."/", $args["birth_date"])) {
				list($year, $month, $day) = explode("-", $args["birth_date"]);
				
				if($year && !$month) {
					$sql_where[] = "musicians.birth_date LIKE CONCAT(?, '%')";
					$sql_values[] = $year;
				}
				elseif($year && $month && !$day) {
					$sql_where[] = "musicians.birth_date LIKE CONCAT(?, '%')";
					$sql_values[] = $year."-".$month;
				}
				elseif($year && $month && $day) {
					$sql_where[] = "musicians.birth_date=?";
					$sql_values[] = $year."-".$month."-".$day;
				}
				elseif(!$year && $month && $day) {
					$sql_where[] = "musicians.birth_date LIKE CONCAT('%', ?)";
					$sql_values[] = $month."-".$day;
				}
				elseif(!$year && $month && !$day) {
					$sql_where[] = "musicians.birth_date LIKE CONCAT('%', ?, '%')";
					$sql_values[] = "-".$month."-";
				}
			}
			if(!empty($args["blood_type"])) {
				if(in_array($args["blood_type"], ["a", "ab", "b", "o"])) {
					$sql_where[] = "musicians.blood_type=?";
					$sql_values[] = $args["blood_type"];
				}
				elseif($args["blood_type"] === "other") {
					$sql_where[] = "musicians.blood_type IS NOT NULL AND musicians.blood_type!='a' AND musicians.blood_type!='b' AND musicians.blood_type!='ab' AND musicians.blood_type !='o'";
				}
			}
			if(!empty($args["history"])) {
				$artists_by_name = $this->access_artist->access_artist(["name" => sanitize($args["history"]), "get" => "id"]);
				if(is_array($artists_by_name) && !empty($artists_by_name)) {
					foreach($artists_by_name as $artist) {
						$sql_history[] = "musicians.history LIKE CONCAT('%(', ?, ')%')";
						$sql_values[] = $artist["id"];
					}
				}
					
					$sql_where[] = (is_array($sql_history) ? implode(" OR ", $sql_history)." OR " : null)."musicians.history LIKE CONCAT('%', ?, '%')";
					$sql_values[] = sanitize($args["history"]);
			}
			if(is_numeric($args["id"])) {
				$sql_where[] = "musicians.id=?";
				$sql_values[] = $args["id"];
			}
			if(is_numeric($args["artist_id"])) {
				$sql_where[] = "artists_musicians.artist_id=?";
				$sql_values[] = $args["artist_id"];
			}
			if(is_array($args['ids'])) {
				$sql_where[] = substr(str_repeat('musicians.id=? OR ', count($args['ids'])), 0, -4);
				$sql_values = array_merge((is_array($sql_values) ? $sql_values : []), $args['ids']);
			}
			/*if(preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $args["edit_history"])) {
				if($args["edit_history"] < date("Y-m-d")) {
					$sql_where[] = "musicians.edit_history<?";
					$sql_values[] = $args["edit_history"];
				}
				
				$sql_order[] = "edit_history DESC";
			}*/
			if($args["to_end"]) {
				$sql_where[] = "artists_musicians.to_end=?";
				$sql_values[] = 1;
			}
			if(is_numeric($args["gender"])) {
				$sql_where[] = "musicians.gender=?";
				$sql_values[] = $args["gender"];
			}
			if($args["birthplace"]) {
				$sql_where[] = "musicians.birthplace LIKE CONCAT('%', ?, '%')";
				$sql_values[] = sanitize($args["birthplace"]);
			}
			if($args["name"]) {
				$sql_name = "(SELECT musician_id AS id FROM artists_musicians WHERE as_name LIKE CONCAT('%', ?, '%') OR as_romaji LIKE CONCAT('%', ?, '%')) UNION (SELECT id FROM musicians WHERE name LIKE CONCAT('%', ?, '%') OR romaji LIKE CONCAT('%', ?, '%') OR friendly LIKE CONCAT('%', ?, '%'))";
				$stmt_name = $this->pdo->prepare($sql_name);
				$stmt_name->execute([sanitize($args["name"]), sanitize($args["name"]), sanitize($args["name"]), sanitize($args["name"]), (friendly($args["name"]) !== '-' ? friendly($args["name"]) : sanitize($args["name"])) ]);
				$rslt_name = $stmt_name->fetchAll();
				
				if(is_array($rslt_name) && !empty($rslt_name)) {
					$sql_where[] = "musicians.id=?".str_repeat(" OR musicians.id=?", count($rslt_name) - 1);
					foreach($rslt_name as $rslt) {
						$sql_values[] = $rslt["id"];
					}
				}
				else {
					$sql_where[] = "musicians.name=?";
					$sql_values[] = sanitize($args["name"]);
				}
			}
			if($args["letter"]) {
				$args["letter"] = sanitize($args["letter"]);
				$args["letter"] = (strlen($args["letter"]) === 1 ? $args["letter"] : "-");
				
				if(preg_match("/"."[A-z]"."/", $args["letter"])) {
					$sql_where[] = "friendly LIKE CONCAT(?, '%')";
					$sql_values[] = $args["letter"];
				}
				else {
					$sql_where[] = "friendly REGEXP '^[^A-z]'";
				}
			}
			if(is_numeric($args["position"])) {
				$sql_position = "(SELECT musician_id AS id FROM artists_musicians WHERE position=?) UNION ALL (SELECT id FROM musicians WHERE usual_position=?)";
				$stmt_position = $this->pdo->prepare($sql_position);
				$stmt_position->execute([$args["position"], $args["position"]]);
				$rslt_position = $stmt_position->fetchAll();
				
				if(is_array($rslt_position) && !empty($rslt_position)) {
					$sql_where[] = "musicians.id=?".str_repeat(" OR musicians.id=?", count($rslt_position) - 1);
					foreach($rslt_position as $rslt) {
						$sql_values[] = $rslt["id"];
					}
				}
				else {
					$sql_where[] = "musicians.usual_position=?";
					$sql_values[] = sanitize($args["position"]);
				}
			}
			
			// Order
			switch(true) {
				case (is_numeric($args["artist_id"])):
					$sql_order = ["artists_musicians.to_end DESC", "artists_musicians.position ASC"];
					break;
					
				default:
					$sql_order = ["musicians.friendly ASC"];
					break;
			}
			
			// Limit
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;
			
			// Query
			$sql_musician = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".(!empty($sql_where) ? "WHERE (".implode(") AND (", $sql_where).")" : null)." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
			$stmt_musician = $this->pdo->prepare($sql_musician);
			$stmt_musician->execute($sql_values);
			
			foreach($stmt_musician->fetchAll() as $row) {
				$musicians[$row["id"]] = $row;
			}
			
			// ADDITIONAL
			if($args["get"] === "all") {
				if(is_array($musicians)) {
					foreach($musicians as $musician_id => $musician) {
						$musicians[$musician_id]["raw_history"] = $musician["history"];
						$musicians[$musician_id]["history"] = $this->get_artist_references_from_band_history($musician["history"]);
						$musicians[$musician_id]["labels"] = $this->access_label->access_label(["president_id" => $musician["id"], "get" => "name"]);
					}
				}
			}
			
			if($args["get"] === "list") {
				if(is_array($musicians)) {
					foreach($musicians as $musician_id => $musician) {
						if(preg_match("/"."\((\d+)\)"."/", $musician["history"], $match)) {
							$match = $match[1];
							
							$artist = $this->access_artist->access_artist(["id" => $match, "get" => "name"]);
							
							$musicians[$musician_id]["hints"] = [
								["O", "V", "G", "B", "D", "K", 'O', 'S'][$musician["usual_position"]].".",
								$artist["quick_name"]
							];
							
							unset($musicians[$musician_id]["history"]);
						}
					}
				}
			}
			
			// RETURN
			if(is_numeric($args["id"]) && is_array($musicians)) {
				$musicians = reset($musicians);
			}
			
			return $musicians;
		}
	}
?>