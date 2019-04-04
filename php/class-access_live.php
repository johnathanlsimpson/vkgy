<?php
	include_once("../php/include.php");
	
	class access_live {
		public  $pdo;
		
		
		
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
		// Build 'concert' object
		// ======================================================
		function access_live($args = []) {
			
			// SELECT ----------------------------------------------
			if($args["get"] === "basics") {
				$sql_select = [
					"lives.date_occurred",
					"lives_livehouses.id AS livehouse_id",
					"CONCAT_WS(' ', areas.name, lives_livehouses.name) AS livehouse_name",
					"CONCAT_WS(' ', IF(lives_livehouses.romaji, COALESCE(areas.romaji, areas.name), ''), lives_livehouses.romaji) AS livehouse_romaji",
					"COALESCE(lives_livehouses.romaji, lives_livehouses.name) AS livehouse_quick_name",
					"lives.name",
					"lives.romaji",
					"COALESCE(lives.romaji, lives.name) AS quick_name",
					"lives.friendly",
					"lives.id",
					"lives.lineup"
				];
			}
			
			// FROM ------------------------------------------------
			if($args["get"] === "basics") {
				$sql_from = [
					"lives",
					"LEFT JOIN lives_livehouses ON lives_livehouses.id = lives.livehouse_id",
					"LEFT JOIN areas ON areas.id = lives_livehouses.area_id"
				];
			}
			
			// WHERE -----------------------------------------------
			if(is_numeric($args["id"])) {
				$sql_where = [
					"lives.id=?"
				];
				$sql_values = [
					$args["id"]
				];
			}
			
			// ORDER -----------------------------------------------
			$sql_order = [
				"lives.date_occurred ASC"
			];
			
			// QUERY -----------------------------------------------
			$sql_live = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".($sql_where ? "WHERE (".implode(")(", $sql_where).")" : null)." ORDER BY ".implode(", ", $sql_order);
			$stmt_live = $this->pdo->prepare($sql_live);
			$stmt_live->execute($sql_values);
			
			$lives = $stmt_live->fetchAll();
			
			// ADDITIONAL DATA -------------------------------------
			if(is_array($lives)) {
				if($args["get"] === "basics") {
					$access_artist = new access_artist($this->pdo);
					$access_musician = new access_musician($this->pdo);
					
					if(is_array($lives)) {
						foreach($lives as $live_key => $live) {
							
							$lineup_lines = explode(" / ", str_replace(" &#47; ", " / ", $live["lineup"]));
							
							if(is_array($lineup_lines)) {
								$output_lineup = [];
								
								foreach($lineup_lines as $line) {
									$line_references = [];
									
									// Get artist references
									preg_match_all("/"."\((\d+)\)"."/", $line, $matches, PREG_OFFSET_CAPTURE);
									if(is_array($matches)) {
										$full_matches = $matches[0];
										$ids = $matches[1];
										
										foreach($full_matches as $key => $match) {
											$artist = $access_artist->access_artist(["id" => $ids[$key][0], "get" => "name"]);
											
											$tmp_history = $artist;
											$tmp_history["type"] = "artist";
											$tmp_history["full_match"] = $match[0];
											$tmp_history["offset"] = $match[1];
											$tmp_history["length"] = strlen($match[0]);
											
											$line_references[] = $tmp_history;
										}
									}
									
									// Get musician references
									preg_match_all("/"."\[(\d+)\]"."/", $line, $matches, PREG_OFFSET_CAPTURE);
									if(is_array($matches)) {
										$full_matches = $matches[0];
										$ids = $matches[1];

										foreach($full_matches as $key => $match) {
											$musician = $access_musician->access_musician(["id" => $ids[$key][0], "get" => "name"]);

											$tmp_history = $musician;
											$tmp_history["type"] = "musician";
											$tmp_history["full_match"] = $match[0];
											$tmp_history["offset"] = $match[1];
											$tmp_history["length"] = strlen($match[0]);

											$line_references[] = $tmp_history;
										}
									}
									
									uasort($line_references, function($a, $b) {
										return $a["offset"] <=> $b["offset"];
									});
									
									arsort($line_references);
									
									// Output
									$output_lineup[] = [
										"lineup" => $line,
										"references" => $line_references
									];
									
									$lives[$live_key]["lineup"] = $output_lineup;
								}
							}
						}
					}
				}
			}
			
			if(is_numeric($args["id"])) {
				$lives = reset($lives);
			}
			
			$lives = $lives ?: [];
			
			return $lives;
		}
		
	}
?>