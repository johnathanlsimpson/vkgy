<?php
	include_once("../php/class-access_artist.php");
	include_once("../php/class-access_musician.php");
	include_once("../php/class-parse_markdown.php");
	include_once("../php/function-sanitize.php");
	include_once("../php/function-friendly.php");
	
	class access_label {
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
		// Build and return 'release(s)' object(s)
		// ======================================================
		function access_label($args = []) {
			// SELECT
			switch($args["get"]) {
				case "name" :
					$sql_select = "id, COALESCE(romaji, name) AS quick_name, name, romaji, friendly";
					break;
				case "list" :
					$sql_select = "id, COALESCE(romaji, name) AS quick_name, name, romaji, friendly";
					break;
				case "all"  :
					$sql_select = "*, COALESCE(romaji, name) AS quick_name";
					break;
			}
			
			// WHERE
			if($args["friendly"]) {
				$sql_where[] = "friendly=?";
				$sql_values = [friendly($args["friendly"])];
			}
			// Fuzzy name
			if($args["name"]) {
				$sql_where[] = "name LIKE CONCAT('%', ?, '%') OR romaji LIKE CONCAT('%', ?, '%') OR friendly LIKE CONCAT('%', ?, '%')";
				$sql_values = [
					sanitize($args["name"]),
					sanitize($args["name"]),
					( friendly($args["friendly"]) !== '-' ? friendly($args["friendly"]) : (friendly($args["name"]) !== '-' ? friendly($args["name"]) : sanitize($args["name"])) )
				];
			}
			// Exact name
			if($args["exact_name"]) {
				$sql_where[] = "name=? OR romaji=? OR friendly=?";
				$sql_values = [
					sanitize($args["exact_name"]),
					sanitize($args["exact_name"]),
					friendly($args["friendly"] ?: $args["exact_name"])
				];
			}
			if(is_numeric($args["president_id"])) {
				$sql_where[] = "president_id=?";
				$sql_values[] = sanitize($args["president_id"]);
			}
			if(is_numeric($args["parent_label_id"])) {
				$sql_where[] = "parent_label_id=?";
				$sql_values[] = sanitize($args["parent_label_id"]);
			}
			if(is_numeric($args["id"])) {
				$sql_where[] = "id=?";
				$sql_values = [sanitize($args["id"])];
			}
			/*if(preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $args["edit_history"])) {
				if($args["edit_history"] < date("Y-m-d")) {
					$sql_where[] = "edit_history<?";
					$sql_values[] = $args["edit_history"];
				}
				
				$sql_order[] = "edit_history DESC";
			}*/
			
			// ORDER
			$sql_order = is_array($sql_order) ? $sql_order : ["friendly ASC"];
			
			
			// LIMIT
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;
			
			
			// QUERY
			if($sql_select) {
				$sql_label = "SELECT ".$sql_select." FROM labels ".($sql_where ? "WHERE (".implode(") AND (", $sql_where).")" : "")." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
				$stmt = $this->pdo->prepare($sql_label);
				$stmt->execute($sql_values);
				
				foreach($stmt->fetchAll() as $row) {
					if($args["get"] === "all") {
						// ARTIST LIST
						$access_artist = new access_artist($this->pdo);
						$row["artists"] = $access_artist->access_artist(["label_id" => $row["id"], "get" => "list"]);
						
						// RELEASE LIST
						$access_release = new access_release($this->pdo);
						$row["releases"] = $access_release->access_release(["label_id" => $row["id"], "get" => "list"]);
						
						// PARENT LABEL
						if(is_numeric($row["parent_label_id"])) {
							$row["parent_label"] = $this->access_label(["id" => $row["parent_label_id"], "get" => "name"]);
						}
						
						// SUBLABELS
						$row["sublabels"] = $this->access_label(["parent_label_id" => $row["id"], "get" => "name"]);
						
						// PRESIDENT
						if(is_numeric($row["president_id"])) {
							$access_musician = new access_musician($this->pdo);
							$row["president"] = $access_musician->access_musician(["id" => $row["president_id"], "get" => "name"]);
						}
						if(!empty($row["president_name"])) {
							$row["president"]["name"] = $row["president_name"];
							$row["president"]["romaji"] = $row["president_romaji"];
							$row["president"]["quick_name"] = $row["president_romaji"] ?: $row["president_name"];
						}
					}
					
					$labels[] = $row;
				}
				
				if(is_array($labels)) {
					if(is_numeric($args["id"]) || $args["friendly"]) {
						$labels = reset($labels);
					}
				}
				
				return $labels;
			}
		}
	}
?>