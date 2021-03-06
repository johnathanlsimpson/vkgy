<?php
  include_once("../php/include.php");
	
	class parse_live {
		public  $pdo;
		private $access_artist;
		private $regex_livehouse_nickname = "^[A-z0-9 ]+$";
		private $regex_livehouse_name = "(?:([\-\w&#; ]+) \(([\-\w&#; ]+)\) )?([\-\w&#; ]+)(?: \(([\-\w&#; ]+)\))?";
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
				$this->pdo = $pdo;
			}
			else {
				$this->pdo = $pdo;
			}
		}
		
		// ======================================================
		// Get livehouse name
		// ======================================================
		function get_livehouse($input) {
			$input = sanitize($input);
			
			$sql_nickname = "
				SELECT
					livehouse_id 
				FROM lives_nicknames
				WHERE
					nickname=? OR 
					REPLACE(nickname, ' ', '')=?
				LIMIT 1";
			$values_nickname = [
				$input,
				str_replace(" ", "", $input)
			];
			$stmt_nickname = $this->pdo->prepare($sql_nickname);
			$stmt_nickname->execute($values_nickname);
			$rslt_nickname = $stmt_nickname->fetchColumn();
			
			if(!is_numeric($rslt_nickname)) {
				$sql_name = "
					SELECT
						lives_livehouses.*,
						areas.name AS area_name,
						areas.romaji AS area_romaji,
						CONCAT(areas.romaji, ' ', COALESCE(lives_livehouses.romaji, lives_livehouses.name)) AS test
					FROM lives_livehouses
					LEFT JOIN areas ON areas.id=lives_livehouses.area_id
					WHERE
						lives_livehouses.name=? OR
						lives_livehouses.romaji=? OR
						lives_livehouses.friendly=? OR
						REPLACE(lives_livehouses.name, ' ', '')=? OR
						REPLACE(lives_livehouses.romaji, ' ', '')=? OR
						CONCAT(areas.name, lives_livehouses.name)=? OR
						CONCAT(areas.romaji, ' ', COALESCE(lives_livehouses.romaji, lives_livehouses.name))=? OR
						CONCAT(areas.name, REPLACE(lives_livehouses.name, ' ', ''))=? OR
						CONCAT(areas.romaji, REPLACE(COALESCE(lives_livehouses.romaji, lives_livehouses.name), ' ', ''))=?
					LIMIT 1
				";
				$values_name = [
					$input,
					$input,
					$input,
					str_replace(" ", "", $input),
					str_replace(" ", "", $input),
					$input,
					$input,
					$input,
					$input
				];
				$stmt_name = $this->pdo->prepare($sql_name);
				$stmt_name->execute($values_name);
				$rslt_name = $stmt_name->fetchColumn();
			}
			
			$rslt_id = is_numeric($rslt_nickname) ? $rslt_nickname : (is_numeric($rslt_name) ? $rslt_name : null);
			
			if(is_numeric($rslt_id)) {
				$sql_livehouse = "
					SELECT
						lives_livehouses.id,
						areas.romaji AS area_romaji,
						areas.name AS area_name,
						lives_livehouses.romaji,
						lives_livehouses.name
					FROM
						lives_livehouses
					LEFT JOIN areas ON areas.id=lives_livehouses.area_id
					WHERE lives_livehouses.id=?
					LIMIT 1
				";
				$stmt_livehouse = $this->pdo->prepare($sql_livehouse);
				$stmt_livehouse->execute([ $rslt_id ]);
				$rslt_livehouse = $stmt_livehouse->fetch();
				
				if(is_array($rslt_livehouse) && !empty($rslt_livehouse) && is_numeric($rslt_livehouse['id'])) {
					return $rslt_livehouse;
				}
			}
		}
		
		// ======================================================
		// Remove artist from all lives
		// 
		// This function should be run when updating an artist's
		// biography--it removes the artist from all lives in the
		// database, and then parse_raw_input() and update_live()
		// will re-add them. This ensures that an artist can be
		// removed from a live by removing that line from its
		// biography.
		// ======================================================
		function remove_artist_lives($artist_id) {
			if(is_numeric($artist_id)) {
				$sql_remove = "DELETE FROM lives_artists WHERE artist_id=?";
				$stmt_remove = $this->pdo->prepare($sql_remove);
				if($stmt_remove->execute([ $artist_id ])) {
					return true;
				}
			}
		}
		
		// ======================================================
		// Parse raw text input
		//
		// Used on lines taken from an artist's bio. Takes the
		// bio string, formatted "livehouse - lineup" (lineup
		// being optional), and the date, and parses that into
		// live data.
		// ======================================================
		function parse_raw_input($raw_input, $date_occurred, $artist_id = null, $extant_lives = []) {
			if(!is_object($this->access_artist)) {
				$this->access_artist = new access_artist($this->pdo);
			}
			
			// Explode raw bio entry content to get only livehouse portion (e.g. 'o-west - megaromania')
			$raw_input = explode(" - ", $raw_input, 2);
			
			if(is_array($raw_input) && !empty($raw_input)) {
				
				if(!empty($raw_input[0])) {
					
					// If current (extant) lives provided, loop through and see if this one is already in there
					// Assumes that extant lives are formated in $lives[year][month][day][i] format
					if(is_array($extant_lives) && !empty($extant_lives)) {
						
						// Get year/month/date of date occurred
						list($y, $m, $d) = explode('-', $date_occurred);
						
						// Check if extant lives contains a live from this date
						if( is_array($extant_lives[$y]) && is_array($extant_lives[$y][$m]) && is_array($extant_lives[$y][$m][$d]) ) {
							
							// Loop through extant lives on this date and check for one that matches this venue name
							foreach($extant_lives[$y][$m][$d] as $extant_live) {
								
								// If livehouse name of extant live matches raw input, then we'll return the livehouse info and avoid an extra DB call
								$extant_live_livehouse = ($extant_live['area_romaji'] ?: $extant_live['area_name']).' '.($extant_live['livehouse_romaji'] ?: $extant_live['livehouse_name']);
								if($raw_input[0] === $extant_live_livehouse) {
									
									// Return only area name and livehouse name, in the format that's used by in access_artist
									$livehouse = [ 'id' => $extant_live['livehouse_id'], 'name' => $extant_live['livehouse_name'], 'romaji' => $extant_live['livehouse_romaji'], 'area_name' => $extant_live['area_name'], 'area_romaji' => $extant_live['area_romaji'] ];
									$live_id = $extant_live['id'];
									
								}
								
							}
							
						}
						
					}
					
					// If livehouse isn't already found, let's do a DB call and search for it
					if(!is_array($livehouse) || empty($livehouse)) {
						$raw_input[0] = sanitize($raw_input[0]);
						$livehouse = $this->get_livehouse($raw_input[0]);
					}
					
				}
				
				if(is_numeric($artist_id)) {
					$artist = $this->access_artist->access_artist([ 'id' => $artist_id, 'get' => 'name', 'limit' => 1 ]);
					
					if(is_array($artist) && !empty($artist)) {
						$lineup[] = $artist;
						$lineup_contains_linked_artist = true;
					}
				}
				
				if(isset($raw_input[1]) && strlen($raw_input[1]) > 0) {
					$raw_input[1] = explode(", ", str_replace(" / ", ", ", $raw_input[1]));
					
					if(is_array($raw_input[1]) && !empty($raw_input[1])) {
						foreach($raw_input[1] as $lineup_artist) {
							$tmp_lineup_artist = $this->access_artist->access_artist([ 'name' => $lineup_artist, 'get' => 'name', 'limit' => 1 ]);
							$tmp_lineup_artist = is_array($tmp_lineup_artist) ? $tmp_lineup_artist : null;
							
							if(is_array($tmp_lineup_artist[0]) && !empty($tmp_lineup_artist[0])) {
								$lineup[] = $tmp_lineup_artist[0];
								$lineup_contains_linked_artist = true;
							}
							else {
								$additional_lineup[] = $lineup_artist;
							}
						}
					}
				}
				
				$lineup = (is_array($lineup) && !empty($lineup) && $lineup_contains_linked_artist) ? $lineup : null;
			}
				
			if(!empty($livehouse["name"]) && $lineup && preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $date_occurred)) {
				return ['id' => is_numeric($live_id) ? $live_id : null, "livehouse" => $livehouse, "date_occurred" => $date_occurred, "lineup" => $lineup, "additional_lineup" => $additional_lineup];
			}
			else {
				return false;
			}
			
		}
		
		// ======================================================
		// Update lives
		//
		// Takes live data as provided by parse_raw_input and
		// updates the live database accordingly, removing
		// artists when necessary.
		// ======================================================
		function update_live($input) {
			if(is_array($input) && !empty($input)) {
				
				// Set variables
				$livehouse_id = $input["livehouse"]["id"];
				$date_occurred = $input["date_occurred"];
				$lineup = $input["lineup"];
				$additional_lineup = is_array($input["additional_lineup"]) ? $input["additional_lineup"] : [];
				
				// If livehouse ID and date provided
				if(is_numeric($livehouse_id) && preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $date_occurred)) {
					
					// Get the live ID, if exists
					$sql_check_live = "SELECT id, lineup FROM lives WHERE livehouse_id=? AND date_occurred=? LIMIT 1";
					$stmt_check_live = $this->pdo->prepare($sql_check_live);
					$stmt_check_live->execute([ $livehouse_id, $date_occurred ]);
					$rslt_check_live = $stmt_check_live->fetch();
					
					// If live already exists...
					if(is_array($rslt_check_live) && !empty($rslt_check_live)) {
						$output = $rslt_check_live['id'];
						
						// Get current lineup of live (lineup of artists that exist in database)
						$sql_lineup = "SELECT * FROM lives_artists WHERE live_id=?";
						$stmt_lineup = $this->pdo->prepare($sql_lineup);
						$stmt_lineup->execute([ $rslt_check_live["id"] ]);
						$rslt_lineup = $stmt_lineup->fetchAll();
						
						// For any artists that are already linked to the live, remove them from the list of artists that need to be linked
						if(is_array($rslt_lineup) && !empty($rslt_lineup)) {
							foreach($rslt_lineup as $lineup_artist) {
								
								if(is_array($lineup) && !empty($lineup)) {
									foreach($lineup as $provided_lineup_key => $provided_lineup_artist) {
										if($provided_lineup_artist['id'] === $lineup_artist['artist_id']) {
											unset($lineup[$provided_lineup_key]);
										}
									}
								}
							}
						}
						
						// For any remaining artists (that exist in the database), link them to the live
						if(is_array($lineup) && !empty($lineup)) {
							foreach($lineup as $lineup_artist) {
								
								$sql_update_lineup = "INSERT INTO lives_artists (live_id, artist_id) VALUES (?, ?)";
								$stmt_update_lineup = $this->pdo->prepare($sql_update_lineup);
								$stmt_update_lineup->execute([ $rslt_check_live["id"], $lineup_artist['id'] ]);
								
							}
						}
						
						// Get "additional" lineup (artists not in database) and update accordingly (plain text in live entry)
						$extant_additional_lineup = explode("\n", $rslt_check_live["lineup"]);
						if(is_array($additional_lineup) && !empty($additional_lineup)) {
							if(is_array($extant_additional_lineup) && !empty($extant_additional_lineup)) {
								foreach($additional_lineup as $additional_lineup_artist) {
									if(!in_array($additional_lineup_artist, $extant_additional_lineup)) {
										$extant_additional_lineup[] = $additional_lineup_artist;
									}
								}
							}
						}
						$extant_additional_lineup = implode("\n", array_unique(array_filter($extant_additional_lineup)));
						
						$sql_update = "UPDATE lives SET lineup=? WHERE id=? LIMIT 1";
						$stmt_update = $this->pdo->prepare($sql_update);
						if($stmt_update->execute([ $extant_additional_lineup, $rslt_check_live["id"] ])) {
						}
					}
					
					// Else if live ID not provided, insert into DB and link artists accordingly
					else {
						$additional_lineup = implode("\n", $additional_lineup);
						$additional_lineup = $additional_lineup ?: null;
						
						$sql_add_live = "INSERT INTO lives (livehouse_id, date_occurred, lineup) VALUES (?, ?, ?)";
						$stmt_add_live = $this->pdo->prepare($sql_add_live);
						if($stmt_add_live->execute([ $livehouse_id, $date_occurred, $additional_lineup ])) {
							$rslt_add_live_id = $this->pdo->lastInsertId();
							$output = $rslt_add_live_id;
						}
						
						if(is_array($lineup) && !empty($lineup) && is_numeric($rslt_add_live_id)) {
							foreach($lineup as $lineup_artist) {
								$values_add_lineup[] = $rslt_add_live_id;
								$values_add_lineup[] = $lineup_artist['id'];
							}
								
							$sql_add_lineup = "INSERT INTO lives_artists (live_id, artist_id) VALUES ".substr(str_repeat('(?,?), ', count($lineup)), 0, -2);
							$stmt_add_lineup = $this->pdo->prepare($sql_add_lineup);
							
							if($stmt_add_lineup->execute($values_add_lineup)) {
							}
						}
					}
				}
			}
			
			// Return ID of live if successful
			if(is_numeric($output)) {
				return $output;
			}
		}
		
	}
?>