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
		
			// SELECT ----------------------------------------------
			
			if( $args['get'] === 'all' ) {
				$sql_select[] = 'labels.*';
			}
			
			if( $args['get'] === 'all' || $args['get'] === 'name' || $args['get'] === 'list' ) {
				$sql_select[] = 'COALESCE( labels.romaji, labels.name ) AS quick_name';
				$sql_select[] = 'CONCAT_WS("/", "", "labels", labels.friendly, "") AS url';
			}
			
			if( $args['get'] === 'name' || $args['get'] === 'list' ) {
				$sql_select[] = 'labels.id';
				$sql_select[] = 'labels.name';
				$sql_select[] = 'labels.romaji';
				$sql_select[] = 'labels.friendly';
			}
			
			// FROM ------------------------------------------------
			
			// Get label by magazine
			if( is_numeric($args['magazine_id']) ) {
				$sql_from = 'magazines_labels';
			}
			
			// Default
			else {
				$sql_from = 'labels';
			}
			
			// JOIN ------------------------------------------------
			
			// Get by magazine
			if( is_numeric($args['magazine_id']) ) {
				$sql_join[] = 'LEFT JOIN labels ON labels.id=magazines_labels.label_id';
			}
			
			// WHERE -----------------------------------------------
			
			// Friendly
			if($args["friendly"]) {
				$sql_where[] = "labels.friendly=?";
				$sql_values[] = friendly($args["friendly"]);
			}
			
			// Fuzzy name
			if($args["name"]) {
				$sql_where[] = "labels.name LIKE CONCAT('%', ?, '%') OR labels.romaji LIKE CONCAT('%', ?, '%') OR labels.friendly LIKE CONCAT('%', ?, '%')";
				$sql_values[] = sanitize($args["name"]);
				$sql_values[] = sanitize($args["name"]);
				$sql_values[] = friendly($args["friendly"]) !== '-' ? friendly($args["friendly"]) : (friendly($args["name"]) !== '-' ? friendly($args["name"]) : sanitize($args["name"]));
			}
			
			// Exact name
			if( strlen($args["exact_name"]) ) {
				$sql_where[] = "labels.name=? OR labels.romaji=? OR labels.friendly=?";
				$sql_values[] = sanitize($args["exact_name"]);
				$sql_values[] = sanitize($args["exact_name"]);
				$sql_values[] = friendly($args["friendly"] ?: $args["exact_name"]);
			}
			
			// President
			if( is_numeric($args["president_id"]) ) {
				$sql_where[] = "labels.president_id=?";
				$sql_values[] = $args["president_id"];
			}
			
			// Parent label
			if( is_numeric($args["parent_label_id"]) ) {
				$sql_where[] = "labels.parent_label_id=?";
				$sql_values[] = $args["parent_label_id"];
			}
			
			// Label ID
			if(is_numeric($args["id"])) {
				$sql_where[] = "labels.id=?";
				$sql_values[] = $args["id"];
			}
			
			// Array of label IDs
			if(is_array($args['ids'])) {
				$sql_where[] = substr(str_repeat('labels.id=? OR ', count($args['ids'])), 0, -4);
				foreach( $args['ids'] as $id ) {
					$sql_values[] = $id;
				}
			}
			
			// Magazine ID
			if( is_numeric($args['magazine_id']) ) {
				$sql_where[] = 'magazines_labels.magazine_id=? AND labels.id IS NOT NULL';
				$sql_values[] = $args['magazine_id'];
			}
			
			// ORDER -----------------------------------------------
			$sql_order = is_array($sql_order) ? $sql_order : ["labels.friendly ASC"];
			
			// LIMIT -----------------------------------------------
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : ($sql_limit ?: null);
			
			// QUERY
			if($sql_select) {
				
				// BUILD QUERY ----------------------------------------
				
				$sql_label = '
					SELECT '.implode(', ', $sql_select).'
					FROM '.$sql_from.' '.
					(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
					(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
					(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' 
					ORDER BY '.implode(', ', $sql_order).' '.$sql_limit.'
				';
				
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
					
					if($args['associative']) {
						$labels[$row['id']] = $row;
					}
					else {
						$labels[] = $row;
					}
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