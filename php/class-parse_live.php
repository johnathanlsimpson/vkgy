<?php
  include_once("../php/include.php");
	
	class parse_live {
		public  $pdo;
		private $access_artist;
		private $regex_livehouse_nickname = "^[A-z0-9 ]+$";
		private $regex_livehouse_name = "(?:([\w-&#; ]+) \(([\w-&#; ]+)\) )?([\w-&#; ]+)(?: \(([\w-&#; ]+)\))?";
		
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
				$sql_get_name = "
					SELECT
						CONCAT_WS(' ', COALESCE(areas.romaji, areas.name), COALESCE(lives_livehouses.romaji, lives_livehouses.name)) AS name
					FROM
						lives_livehouses
					LEFT JOIN areas ON areas.id=lives_livehouses.area_id
					WHERE lives_livehouses.id=?
					LIMIT 1
				";
				$stmt_get_name = $this->pdo->prepare($sql_get_name);
				$stmt_get_name->execute([ $rslt_id ]);
				$rslt_get_name = $stmt_get_name->fetchColumn();
				
				if(strlen($rslt_get_name) > 0) {
					$livehouse_id = $rslt_id;
					$livehouse_name = $rslt_get_name;
				}
			}
			
			if(is_numeric($livehouse_id) && strlen($livehouse_name) > 0) {
				return ["id" => $livehouse_id, "name" => $livehouse_name];
			}
			else {
				return null;
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
		function parse_raw_input($raw_input, $date_occurred, $artist_id = null, $sanitized = false) {
			if(!is_object($this->access_artist)) {
				$this->access_artist = new access_artist($this->pdo);
			}
			
			$raw_input = explode(" - ", $raw_input, 2);
			
			if(is_array($raw_input) && !empty($raw_input)) {
				if(!empty($raw_input[0])) {
					$raw_input[0] = sanitize($raw_input[0]);
					$livehouse = $this->get_livehouse($raw_input[0]);
				}
				
				if(is_numeric($artist_id)) {
					$lineup[] = $artist_id;
					$lineup_contains_linked_artist = true;
				}
				
				if(isset($raw_input[1]) && strlen($raw_input[1]) > 0) {
					$raw_input[1] = explode(", ", str_replace(" / ", ", ", $raw_input[1]));
					
					if(is_array($raw_input[1]) && !empty($raw_input[1])) {
						foreach($raw_input[1] as $lineup_artist) {
							$tmp_lineup_artist = friendly($lineup_artist);
							$tmp_lineup_artist = $this->access_artist->access_artist(["friendly" => $tmp_lineup_artist, "get" => "id", "limit" => "1"]);
							$tmp_lineup_artist = is_array($tmp_lineup_artist) ? $tmp_lineup_artist["id"] : null;
							
							if(is_numeric($tmp_lineup_artist)) {
								$lineup[] = $tmp_lineup_artist;
								$lineup_contains_linked_artist = true;
							}
							else{
								$tmp_lineup_artist = $this->access_artist->access_artist(["name" => $lineup_artist, "get" => "id", "limit" => "1"]);
								$tmp_lineup_artist = is_array($tmp_lineup_artist) ? $tmp_lineup_artist["id"] : null;
								
								if(is_numeric($tmp_lineup_artist)) {
									$lineup[] = $tmp_lineup_artist;
									$lineup_contains_linked_artist = true;
								}
								else {
									$additional_lineup[] = $lineup_artist;
								}
							}
						}
					}
				}
				
				$lineup = (is_array($lineup) && !empty($lineup) && $lineup_contains_linked_artist) ? $lineup : null;
			}
				
			if(!empty($livehouse["name"]) && $lineup && preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $date_occurred)) {
				return ["livehouse" => ["id" => $livehouse["id"], "name" => $livehouse["name"]], "date_occurred" => $date_occurred, "lineup" => $lineup, "additional_lineup" => $additional_lineup];
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
						
						// Get current lineup of live (lineup of artists that exist in database)
						$sql_lineup = "SELECT * FROM lives_artists WHERE live_id=?";
						$stmt_lineup = $this->pdo->prepare($sql_lineup);
						$stmt_lineup->execute([ $rslt_check_live["id"] ]);
						$rslt_lineup = $stmt_lineup->fetchAll();
						
						// For any artists that are already linked to the live, remove them from the list of artists that need to be linked
						if(is_array($rslt_lineup) && !empty($rslt_lineup)) {
							foreach($rslt_lineup as $lineup_artist) {
								if(in_array($lineup_artist["artist_id"], $lineup)) {
									unset($lineup[array_search($lineup_artist["artist_id"], $lineup)]);
								}
							}
						}
						
						// For any remaining artists (that exist in the database), link them to the live
						if(is_array($lineup) && !empty($lineup)) {
							foreach($lineup as $lineup_artist_id) {
								$sql_update_lineup = "INSERT INTO lives_artists (live_id, artist_id) VALUES (?, ?)";
								$stmt_update_lineup = $this->pdo->prepare($sql_update_lineup);
								$stmt_update_lineup->execute([ $rslt_check_live["id"], $lineup_artist_id ]);
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
							return true;
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
						}
						
						if(is_array($lineup) && !empty($lineup)) {
							foreach($lineup as $lineup_artist) {
								if(is_numeric($rslt_add_live_id)) {
									$sql_add_lineup = "INSERT INTO lives_artists (live_id, artist_id) VALUES (?, ?)";
									$stmt_add_lineup = $this->pdo->prepare($sql_add_lineup);
									if($stmt_add_lineup->execute([ $rslt_add_live_id, $lineup_artist ])) {
										return true;
									}
								}
							}
						}
					}
				}
			}
		}
	}
?>