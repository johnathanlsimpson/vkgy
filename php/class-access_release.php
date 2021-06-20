<?php
	include_once("../php/include.php");
include_once('../php/class-song.php');
	
	class access_release {
		private $artists = [];
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
			
			$this->access_user = new access_user($this->pdo);
			$this->access_comment = new access_comment($this->pdo);
			$this->access_artist = new access_artist($this->pdo);
			$this->access_song = new song($this->pdo);
		}
		
		
		
		// ======================================================
		// Get possible release attributes
		// ======================================================
		function get_possible_attributes($return_associative = false) {
			$sql_attributes = 'SELECT * FROM releases_attributes ORDER BY type ASC, friendly ASC';
			$stmt_attributes = $this->pdo->prepare($sql_attributes);
			$stmt_attributes->execute();
			$rslt_attributes = $stmt_attributes->fetchAll();
			
			$num_attributes = count($rslt_attributes);
			
			for($i=0; $i<$num_attributes; $i++) {
				$rslt_attributes[$i]['type'] = ['medium', 'format', 'venue_limitation', 'press_limitation_name'][$rslt_attributes[$i]['type']];
				
				if($rslt_attributes[$i]['is_default']) {
					$output = [ $rslt_attributes[$i]['id'] => $rslt_attributes[$i] ] + $output;
				}
				else {
					$output[$rslt_attributes[$i]['id']] = $rslt_attributes[$i];
				}
			}
			
			if(!$return_associative) {
				$output = array_values($output);
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Generate store search URLs
		// ======================================================
		function get_store_url($store_name, $release_data) {
			
			$store_name = strtolower($store_name);
			
			$allowed_stores = [
				'amazon',
				'cdjapan',
				'rarezhut',
			];
			
			if( strlen($store_name) && in_array($store_name, $allowed_stores) ) {
				
				if( is_array($release_data) && strlen($release_data['name']) ) {
					
					if( $store_name === 'amazon' ) {
						$tracking_link_name = $release_data['artist']['name'].' '.$release_data['name'];
					}
					
					elseif( $store_name === 'cdjapan' ) {
						$tracking_link_name = $release_data['quick_name'] ?: $release_data['name'];
					}
					
					elseif( $store_name === 'rarezhut' ) {
						$tracking_link_name = $release_data['artist']['name'].' '.$release_data['name'];
					}
					
					return tracking_link( $store_name, [ $release_data['upc'] ?: $tracking_link_name, $tracking_link_name ], 'release card' );
					
				}
				
			}
			
		}
		
		
		
		// ======================================================
		// Extract notes from track
		// ======================================================
		// Eventually we should move this into a newer function in the songs class
		function get_notes_from_track($input_track) {
			if(!empty($input_track["name"]) || $input_track["name"] === "0") {	
				$note_pattern = "\((.+?)\)";
				
				$input_track["name"] = str_replace("\\(", "&#40;", $input_track["name"]);
				$input_track["name"] = str_replace("\\)", "&#41;", $input_track["name"]);
				$input_track["romaji"] = str_replace("\\(", "&#40;", $input_track["romaji"]);
				$input_track["romaji"] = str_replace("\\)", "&#41;", $input_track["romaji"]);
				
				preg_match_all("/".$note_pattern."/", $input_track["name"], $notes_jp, PREG_OFFSET_CAPTURE);
				preg_match_all("/".$note_pattern."/", $input_track["romaji"], $notes_ro, PREG_OFFSET_CAPTURE);
				
				foreach($notes_jp[0] as $note_key => $note) {
					$output["notes"][$note_key] = [
						"name" => $notes_jp[1][$note_key][0],
						"name_offset" => $notes_jp[0][$note_key][1],
						"name_length" => strlen($notes_jp[0][$note_key][0]),
						"romaji" => $notes_ro[1][$note_key][0],
						"romaji_offset" => $notes_ro[0][$note_key][1],
						"romaji_length" => strlen($notes_ro[0][$note_key][0]),
					];
				}
			}
			
			return (is_array($output) ? $output : []);
		}
		
		
		
		// ======================================================
		// Format 'credits' field
		// ======================================================
		function format_credits($input) {
			if(!empty($input)) {
				$input = str_replace(["\r\n", "\r"], "\n", $input);
				$credit_lines = explode("\n", $input);
				
				if(is_array($credit_lines)) {
					foreach($credit_lines as $key => $line) {
						$line = explode(" - ", $line);
						
						if(isset($line[1])) {
							$credits[$key]["title"] = $line[0];
							unset($line[0]);
						}
						
						$credits[$key]["credit"] = implode(" - ", $line);
					}
				}
			}
			
			return $credits;
		}
		
		
		
		// ======================================================
		// Rearrange tracklist into discs/sections
		// ======================================================
		function prepare_tracklist($input_tracklist, $flatten = false, $release_artist_id= null) {
			$output_tracklist = [];
			
			if(is_array($input_tracklist)) {
				foreach($input_tracklist as $track) {
					
					$track = array_merge($track, $this->get_notes_from_track($track));
					
					$track["artist"] = [ "id" => $track["artist_id"] ];
					
					if($track["artist_id"] !== $release_artist_id) {
						$track["artist"] = $this->access_artist->access_artist(["get" => "name", "id" => $track["artist_id"]]);
					}
					
					foreach(["name", "romaji"] as $key) {
						$track[$key] = str_replace(["\(", "\&#40;", "\&#41;", "\)"], ["&#40;", "&#40;", "&#41;", "&#41;"], $track[$key]);
						
						$track["artist"]["display_".$key] = $track["artist_display_".$key];
						unset($track["artist_display_".$key]);
					}
					
					unset($track["artist_id"], $track["release_id"]);
					
					if(!$flatten) {
						foreach(["num", "name", "romaji"] as $key) {
							$output_tracklist["discs"][$track["disc_num"]]["disc_".$key] = $track["disc_".$key];
						}
						foreach(["num", "name", "romaji"] as $key) {
							$output_tracklist["discs"][$track["disc_num"]]["sections"][$track["section_num"]]["section_".$key] = $track["section_".$key];
						}
						
						$output_tracklist["discs"][$track["disc_num"]]["sections"][$track["section_num"]]["tracks"][$track["track_num"]] = $track;
						
						foreach(["num", "name", "romaji"] as $key) {
							unset($output_tracklist["discs"][$track["disc_num"]]["sections"][$track["section_num"]]["tracks"][$track["track_num"]]["disc_".$key]);
							unset($output_tracklist["discs"][$track["disc_num"]]["sections"][$track["section_num"]]["tracks"][$track["track_num"]]["section_".$key]);
						}
					}
					else {
						$output_tracklist[] = $track;
					}
				}
				
				return (is_array($output_tracklist) ? $output_tracklist : []);
			}
		}
	
	
	
	// =======================================================
	// Update tracklist
	// =======================================================
	public function update_tracklist( $release_id, $tracklist ) {
		
		// Make sure we have release
		if( is_numeric($release_id) ) {
			
			// Make sure we have tracklist
			if( is_array($tracklist) && !empty($tracklist) ) {
				
				// Get keys and count as a helper
				$track_keys = array_keys( $tracklist[0] );
				$num_tracks = count( $tracklist );
				$num_extant_tracks = 0;
				
				// Loop through tracks and get associated songs if necessary
				foreach( $tracklist as $track_key => $track ) {
					
					// If no song ID and title not custom, attempt to find song from custom title
					if( !is_numeric( $track['song_id'] ) && !$track['is_custom'] ) {
						
						// Try to guess song--will automatically create song if necessary
						$song_id = $this->access_song->get_song_from_track([
							'name' => $track['name'],
							'romaji' => $track['romaji'],
							'artist_id' => $track['artist_id'],
							'release_id' => $track['release_id'],
							'date_occurred' => $track['date_occurred'],
						]);
						
						// If we found a song, then update track with it--unnecessary titles will be removed in next step
						if( is_numeric($song_id) ) {
							$track['song_id'] = $song_id;
						}
						
					}
					
					// If has song ID but title not custom, remove custom title
					if( is_numeric( $track['song_id'] ) && !$track['is_custom'] ) {
						//$track['name'] = null;
						//$track['romaji'] = null;
					}
					
					// Update master tracklist
					$tracklist[ $track_key ] = $track;
					
				}
				
				// Get extant tracks on release
				$sql_extant = 'SELECT id FROM releases_tracklists WHERE release_id=?';
				$stmt_extant = $this->pdo->prepare($sql_extant);
				if( $stmt_extant->execute([ $release_id ]) ) {
					$extant_track_ids = $stmt_extant->fetchAll(PDO::FETCH_COLUMN);
					$num_extant_tracks = count( $extant_track_ids );
				}
				else {
					$output['result'] = 'Couldn\'t get extant tracklist.';
				}
				
				// If we have extant tracks, we'll either need to update them w/ new info or delete them (if release now has fewer tracks)
				if( is_array($extant_track_ids) && !empty($extant_track_ids) ) {
					foreach( $extant_track_ids as $track_index => $extant_track_id ) {
						
						// Update extant track
						if( isset( $tracklist[ $track_index ] ) ) {
							$sql_tracks[] = 'UPDATE releases_tracklists SET '.implode( '=?, ', $track_keys ).'=? WHERE id=? LIMIT 1';
							$values_tracks[] = array_merge( $tracklist[ $track_index ], [ $extant_track_id ] );
						}
						
						// Delete unneeded track
						else {
							$sql_tracks[] = 'DELETE FROM releases_tracklists WHERE id=? LIMIT 1';
							$values_tracks[] = [ $extant_track_id ];
						}
						
					}
				}
				
				// Now we have to see if there are any tracks we have to create (if more tracks in the new tracklist than were in extant tracks)
				if( $num_tracks > $num_extant_tracks ) {
					foreach( array_slice( $tracklist, $num_extant_tracks ) as $track ) {
						$sql_tracks[] = 'INSERT INTO releases_tracklists ('.implode( ', ', $track_keys ).', release_id) VALUES ('.str_repeat( '?, ', count($track_keys) ).'?)';
						$values_tracks[] = array_merge( $track, [ $release_id ] );
					}
				}
				
				// Now if we have some queries to run, run them
				if( is_array($sql_tracks) && !empty($sql_tracks) && count($sql_tracks) === count($values_tracks) ) {
					foreach( $sql_tracks as $sql_index => $sql_track ) {
						
						// Attempt to prepare
						if( $stmt_track = $this->pdo->prepare( $sql_track ) ) {
							
							// Make sure values aren't associative
							$values_track = array_values( $values_tracks[ $sql_index ] );
							
							// Attempt to run query
							if( $stmt_track->execute( $values_track ) ) {
								$output['status'] = 'success';
							}
							else {
								$output['result'] = 'Couldn\'t run track query.'.$sql_track.print_r($values_track,true);
							}
							
						}
						else {
							$output['result'] = 'Couldn\'t prepare track query.'.$sql_track;
						}
						
					}
				}
				
			}
			else {
				$output['result'] = 'Tracklist can\'t be empty.';
			}
			
		}
		else {
			$output['result'] = 'Release is missing.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
		
		
		
		// ======================================================
		// Previous and next items in artist's discography
		// ======================================================
		function get_prev_next($release_id, $artist_id) {
			if(is_numeric($release_id)) {
				
				// Get sortable name of current record
				$sql_current = 'SELECT CONCAT_WS("-", releases.date_occurred, releases.friendly, releases.id) AS sort_name, artist_id FROM releases WHERE releases.id=? LIMIT 1';
				$stmt_current = $this->pdo->prepare($sql_current);
				$stmt_current->execute([ $release_id ]);
				$current = $stmt_current->fetch();
				
				// If sortable name found, continue
				if(is_array($current) && !empty($current)) {
					
					if(is_numeric($artist_id)) {
						$current['artist_id'] = sanitize($artist_id);
					}
				
					// Search for tracks featuring artist (to account for omnibuses),
					// create sortable name and sort by that, then get previous and next records.
					// For selected records, do another join to get URL, artist ID, etc.
					$sql_prev_next = '
						SELECT
							prev_next.type,
							artists.id AS artist_id,
							CONCAT(CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, ""), IF(releases.artist_id<>?, CONCAT("&prev_next_artist=", ?), "")) AS url,
							CONCAT_WS(" ", COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS romaji,
							CONCAT_WS(" ", releases.name, releases.press_name, releases.type_name) AS name
							
						FROM
							(
								(
									SELECT
										"prev" AS type,
										CONCAT_WS("-", releases.date_occurred, releases.friendly, releases.id) AS sort_name,
										releases_tracklists.release_id
									FROM
										releases_tracklists
									LEFT JOIN
										releases ON releases.id=releases_tracklists.release_id
									WHERE
										releases_tracklists.artist_id=?
										AND
										CONCAT_WS("-", releases.date_occurred, releases.friendly, releases.id)<?
									GROUP BY
										releases_tracklists.release_id
									ORDER BY
										sort_name DESC
									LIMIT
										1
								)
								
								UNION
								
								(
									SELECT
										"next" AS type,
										CONCAT_WS("-", releases.date_occurred, releases.friendly, releases.id) AS sort_name,
										releases_tracklists.release_id
									FROM
										releases_tracklists
									LEFT JOIN
										releases ON releases.id=releases_tracklists.release_id
									WHERE
										releases_tracklists.artist_id=?
										AND
										CONCAT_WS("-", releases.date_occurred, releases.friendly, releases.id)>?
									GROUP BY
										releases_tracklists.release_id
									ORDER BY
										sort_name ASC
									LIMIT
										1
								)
							) prev_next
							
						LEFT JOIN
							releases ON releases.id=prev_next.release_id
						LEFT JOIN
							artists ON artists.id=releases.artist_id
					';
					
					$stmt_prev_next = $this->pdo->prepare($sql_prev_next);
					$stmt_prev_next->execute([ $current['artist_id'], $current['artist_id'], $current['artist_id'], $current['sort_name'], $current['artist_id'], $current['sort_name'] ]);
					$rslt_prev_next = $stmt_prev_next->fetchAll();
					
					return $rslt_prev_next ?: false;
				}
			}
		}
		
		
		
		// ======================================================
		// Build and return 'release(s)' object(s)
		// ======================================================
		function access_release($args = []) {
			
			//
			// Pre-setup
			//
			// If medium/format/venue/press-type specified
			if($args['medium'] || $args['format'] || $args['venue_limitation'] || $args['press_limitation_name']) {
				$args['release_attributes'] = [];
				
				// For each attribute, transform into an array if not already, then combine
				foreach(['medium', 'format', 'venue_limitation', 'press_limitation_name'] as $attribute_key) {
					if(!is_array($args[$attribute_key]) && strlen($args[$attribute_key])) {
						$args[$attribute_key] = [ $args[$attribute_key] ];
					}
					
					if(is_array($args[$attribute_key]) && !empty($args[$attribute_key])) {
						$args['release_attributes'] = array_merge($args['release_attributes'], $args[$attribute_key]);
					}
				}
				
				// If only friendly name given for each attribute, get ID
				if(is_array($args['release_attributes']) && !empty($args['release_attributes'])) {
					foreach($args['release_attributes'] as $attribute_key => $attribute) {
						if(!is_numeric($attribute) && strlen($attribute)) {
							$this->possible_attributes = $this->possible_attributes ?: $this->get_possible_attributes();
							foreach($this->possible_attributes as $possible_attribute) {
								if($possible_attribute['friendly'] === $attribute) {
									$args['release_attributes'][$attribute_key] = $possible_attribute['id'];
								}
							}
						}
					}
				}
			}
			// [PRE-SELECT] Artist name
			if(!empty($args["artist_display_name"])) {
				$artist_id = $this->access_artist->access_artist(["name" => $args["artist_display_name"], 'exact_name' => true, "get" => "id"]);
				
				if(is_numeric($artist_id) || (is_array($artist_id) && !empty($artist_id))) {
				}
				else {
					$artist_id = $this->access_artist->access_artist(["name" => $args["artist_display_name"], "get" => "id"]);
				}
				
				if(is_array($artist_id) && is_numeric($artist_id[0]["id"])) {
					$args["artist_id"] = $artist_id[0]["id"];
				}
				
				$sql_pre = "SELECT id FROM releases WHERE releases.artist_display_name=? OR releases.artist_display_romaji=?";
				$stmt_pre = $this->pdo->prepare($sql_pre);
				$stmt_pre->execute([sanitize($args["artist_display_name"]), sanitize($args["artist_display_name"])]);
				$rslt_pre = $stmt_pre->fetchAll();
				
				if(is_array($rslt_pre) && !empty($rslt_pre)) {
					$args["release_id"] = [];
					
					for($i = 0; $i < count($rslt_pre); $i++) {
						$args["release_id"][] = $rslt_pre[$i]["id"];
					}
				}
				
				if(!is_numeric($args["artist_id"]) && (!is_array($args["release_id"]) || empty($args["release_id"]))) {
					unset($args["get"]);
				}
			}
			// [PRE-SELECT] Artist ID
			if(is_numeric($args["artist_id"])) {
				$sql_pre = "(SELECT id, date_occurred FROM releases WHERE artist_id=?) UNION (SELECT releases.id, releases.date_occurred FROM releases_tracklists LEFT JOIN releases ON releases.id=releases_tracklists.release_id WHERE releases_tracklists.artist_id=? GROUP BY releases_tracklists.release_id) ORDER BY date_occurred DESC ".($args["limit"] ? "LIMIT ".$args["limit"] : null);
				$stmt_pre = $this->pdo->prepare($sql_pre);
				$stmt_pre->execute([$args["artist_id"], $args["artist_id"]]);
				$rslt_pre = $stmt_pre->fetchAll();
				
				if(is_array($rslt_pre) && !empty($rslt_pre)) {
					$args["release_id"] = is_array($args["release_id"]) ? $args["release_id"] : [];
					
					for($i = 0; $i < count($rslt_pre); $i++) {
						$args["release_id"][] = $rslt_pre[$i]["id"];
					}
					
					unset($rslt_pre);
				}
				else {
					if(empty($rslt_pre) || !is_array($rslt_pre)) {
						unset($args["get"]);
					}
				}
			}
			// [PRE-SELECT] Tag
			if(strlen($args["tag"]) > 0) {
				$sql_pre = "SELECT releases_tags.release_id AS id FROM tags_releases LEFT JOIN releases_tags ON releases_tags.tag_id=tags_releases.id WHERE ((releases_tags.mod_score>-1 AND releases_tags.score>0) OR releases_tags.mod_score=1) AND tags_releases.friendly=? GROUP BY releases_tags.release_id";
				$stmt_pre = $this->pdo->prepare($sql_pre);
				$stmt_pre->execute([ sanitize($args["tag"]) ]);
				$rslt_pre = $stmt_pre->fetchAll();
				
				if(is_array($rslt_pre) && !empty($rslt_pre)) {
					$tmp_release_ids = [];
					
					for($i = 0; $i < count($rslt_pre); $i++) {
						$tmp_release_ids[] = $rslt_pre[$i]["id"];
					}
					
					if(is_array($args['release_id'])) {
						$args['release_id'] = array_intersect($args['release_id'], $tmp_release_ids);
					}
					else {
						$args['release_id'] = $tmp_release_ids;
					}
					
					unset($rslt_pre, $tmp_release_ids);
				}
				else {
					if(empty($rslt_pre) || !is_array($rslt_pre)) {
						unset($args["get"]);
					}
				}
			}
			
			//
			// SELECT
			//
			if(1) {
				if($args["get"] === "all") {
					$sql_select = [
						"releases.*",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"AVG(releases_ratings.rating) AS rating",
						'date_edited',
					];
				}
				if($args["get"] === "basics") {
					$sql_select = [
						"releases.id",
						"releases.artist_id",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"releases.name",
						"releases.romaji",
						"releases.press_name",
						"releases.press_romaji",
						"releases.type_name",
						"releases.type_romaji",
						"releases.friendly",
						"releases.date_occurred",
						"releases.upc",
						"releases.artist_display_name",
						"releases.artist_display_romaji",
						"AVG(releases_ratings.rating) AS rating",
						'releases.image_id',
					];
				}
				if(($args["get"] === "all" || $args["get"] === "basics") && $_SESSION["is_signed_in"]) {
					$sql_select[] = "user_rating.rating AS user_rating";
					$sql_select[] = "IF(releases_collections.id, 1, 0) AS is_owned";
					$sql_select[] = "IF(releases_wants.id, 1, 0) AS is_wanted";
				}
				elseif($args["get"] === "name") {
					$sql_select = [
						"releases.id",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"CONCAT_WS(' ', releases.name, releases.press_name, releases.type_name) AS name",
						"releases.friendly",
						"releases.artist_id"
					];
				}
				elseif($args["get"] === "quick_name") {
					$sql_select = [
						"releases.id",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"releases.friendly",
						"releases.artist_id"
					];
				}
				elseif($args["get"] === "list") {
					$sql_select = [
						"releases.id",
						"releases.artist_id",
						"releases.date_occurred",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"releases.name",
						"releases.romaji",
						"releases.friendly",
						"releases.upc",
					];
					$sql_select[] = 'releases.press_name';
					$sql_select[] = 'releases.press_romaji';
					$sql_select[] = 'releases.type_name';
					$sql_select[] = 'releases.type_romaji';
					if($args['edit_ids']) {
						$sql_select[] = 'edits_releases.user_id';
						$sql_select[] = 'edits_releases.date_occurred AS date_edited';
					}
					
					if(substr($args['order'], 0, 10) === 'order_name') {
						$sql_select[] = 'CONCAT_WS("-", artists.friendly, releases.friendly) AS order_name';
					}
				}
				elseif($args["get"] === "calendar") {
					$sql_select = [
						"releases.id",
						"releases.artist_id",
						"releases.date_occurred",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"releases.name",
						"releases.romaji",
						"releases.friendly",
						'releases.image_id',
					];
				}
				elseif($args['get'] === 'id') {
					$sql_select = [
						'releases.id',
					];
				}
				if(is_numeric($args["user_id"]) && $args['get'] === 'name') {
					$sql_select[] = "REPLACE(REPLACE(releases.price, ',', ''), ' yen', '') AS price";
					$sql_select[] = "releases_collections.is_for_sale";
				}
				if(is_numeric($args["user_id"]) && $args['get'] === 'list') {
					$sql_select[] = "REPLACE(REPLACE(releases.price, ',', ''), ' yen', '') AS price";
					$sql_select[] = "releases_collections.is_for_sale";
				}
			}
			
			//
			// FROM / JOINS
			//
			// If getting release by medium/format/venue/press type
			if(is_array($args['release_attributes']) && !empty($args['release_attributes'])) {
				$num_attributes = count($args['release_attributes']);
				$sql_from = '(SELECT releases_releases_attributes.release_id FROM releases_releases_attributes WHERE releases_releases_attributes.attribute_id IN ('.substr(str_repeat('?, ', $num_attributes), 0, -2).') GROUP BY releases_releases_attributes.release_id HAVING COUNT(DISTINCT releases_releases_attributes.attribute_id)=?) release_candidates';
				$sql_values = array_merge((is_array($sql_values) ? $sql_values : []), $args['release_attributes']);
				$sql_values[] = $num_attributes;
				$sql_join[] = 'LEFT JOIN releases ON releases.id=release_candidates.release_id';
			}
			// If getting user's collection
			if(is_numeric($args['user_id'])) {
				$sql_from = 'releases_collections';
				$sql_join[] = 'LEFT JOIN releases ON releases.id=releases_collections.release_id';
			}
			// If getting recently edited releases
			if(is_array($args['edit_ids']) && !empty($args['edit_ids'])) {
				$sql_from = 'edits_releases';
				$sql_join[] = 'LEFT JOIN releases ON releases.id=edits_releases.release_id';
				//$sql_join[] = 'LEFT JOIN users ON users.id=edits_releases.user_id';
			}
			// If returning list view
			if($args['get'] === 'list') {
				$sql_join[] = 'LEFT JOIN artists ON artists.id=releases.artist_id';
			}
			// If returning current rating and cover art
			if($args['get'] === 'basics' || $args['get'] === 'all') {
				$sql_join[] = 'LEFT JOIN releases_ratings ON releases_ratings.release_id=releases.id';
				$sql_join[] = 'LEFT JOIN images ON images.id=releases.image_id';
			}
			// If returning user's rating/collected status/want status
			if($args['get'] === 'basics' || $args['get'] === 'all') {
				if($_SESSION['is_signed_in']) {
					$sql_join[] = 'LEFT JOIN releases_ratings AS user_rating ON user_rating.release_id=releases.id AND user_rating.user_id=?';
					$sql_join[] = 'LEFT JOIN releases_collections ON releases_collections.release_id=releases.id AND releases_collections.user_id=?';
					$sql_join[] = 'LEFT JOIN releases_wants ON releases_wants.release_id=releases.id AND releases_wants.user_id=?';
					$sql_values[] = $_SESSION['user_id'];
					$sql_values[] = $_SESSION['user_id'];
					$sql_values[] = $_SESSION['user_id'];
				}
				else {
					$sql_join[] = 'LEFT JOIN releases_ratings AS user_rating ON user_rating.release_id=releases.id AND user_rating.ip_address=?';
					$sql_values[] = ip2long($_SERVER['REMOTE_ADDR']);
				}
			}
			// If getting recently-edited releases
			if($args['get'] === 'all') {
				$sql_join[] = 'LEFT JOIN (SELECT MAX(edits_releases.date_occurred) AS date_edited, release_id FROM edits_releases GROUP BY release_id) AS tmp_edits ON tmp_edits.release_id=releases.id';
			}
			$sql_from = $sql_from ?: 'releases';
			
			//
			// WHERE
			//
			// Default: empty
			$sql_values = is_array($sql_values) ? $sql_values : [];
			// If getting user's collection
			if(is_numeric($args["user_id"])) {
				$sql_where[] = "releases_collections.user_id=?";
				$sql_values[] = $args["user_id"];
			}
			if(is_array($args['edit_ids'])) {
				$sql_where[] = substr(str_repeat('edits_releases.id=? OR ', count($args['edit_ids'])), 0, -4);
				$sql_values = array_merge($sql_values, $args['edit_ids']);
			}
			if(is_array($args["release_id"]) && !empty($args["release_id"])) {
				$sql_where[] = "releases.id=".implode(" OR releases.id=", array_fill(0, count($args["release_id"]), "?"));
				$sql_values = is_array($sql_values) ? array_merge($sql_values, $args["release_id"]) : $args["release_id"];
			}
			
			if(!empty($args["release_name"])) {
				$typeless_name = html_entity_decode($args['release_name']);
				$typeless_name = strtolower($typeless_name);
				$typeless_name = str_replace([' - single', ' - ep'], '', $typeless_name);
				$typeless_name = preg_replace('/'.' [\(\[].*(press|type|限定|盤|edition).*[\)\]]$'.'/', '', $typeless_name);
				$typeless_name = preg_replace('/'.' [^ ]*(限定|盤)[^ ]*$'.'/', '', $typeless_name);
				$typeless_name = preg_replace('/'.' ([a-z] )?type( [a-z])?$'.'/', '', $typeless_name);
				$typeless_name = preg_replace('/'.' [a-z0-9]( press|プレス)$'.'/', '', $typeless_name);
				
				$typeless_friendly = friendly($typeless_name);
				$typeless_friendly = $typeless_friendly === '-' ? $typeless_name : $typeless_friendly;
				
				$typeless_name = sanitize($typeless_name);
				$typeless_friendly = sanitize($typeless_friendly);
				
				$sql_where[] = "releases.name LIKE CONCAT('%', ?, '%') OR releases.romaji LIKE CONCAT('%', ?, '%') OR releases.friendly LIKE CONCAT('%', ?, '%')";
				array_push($sql_values, $typeless_name, $typeless_name, $typeless_friendly);
			}
			if(is_numeric($args["release_id"])) {
				$sql_where[] = "releases.id=?";
				$sql_values[] = $args["release_id"];
			}
			if(is_numeric($args["label_id"])) {
				if(!empty($args["label_involvement"]) && in_array($args["label_involvement"], ["label", "publisher", "distributor", "marketer", "manufacturer", "organizer"])) {
					$sql_where[] = "releases.".$args["label_involvement"]."_id LIKE CONCAT('%(', ?, ')%')";
					$sql_values[] = $args["label_id"];
				}
				else {
					$sql_where[] = "releases.label_id LIKE CONCAT('%(', ?, ')%') OR releases.publisher_id LIKE CONCAT('%(', ?, ')%') OR releases.distributor_id LIKE CONCAT('%(', ?, ')%') OR releases.marketer_id LIKE CONCAT('%(', ?, ')%') OR releases.manufacturer_id LIKE CONCAT('%(', ?, ')%') OR releases.organizer_id LIKE CONCAT('%(', ?, ')%')";
					$sql_values = array_merge($sql_values, array_fill(0, 6, $args["label_id"]));
				}
			}
			if(!empty($args["date_occurred"])) {
				$sql_where[] = "releases.date_occurred=?";
				$sql_values[] = sanitize($args["date_occurred"]);
			}
			if(!empty($args["start_date"])) {
				$sql_where[] = "releases.date_occurred>=?";
				$sql_values[] = sanitize(str_replace(["y", "m", "d"], "0", $args["start_date"]));
			}
			if(!empty($args["end_date"])) {
				$sql_where[] = "releases.date_occurred<=?";
				$sql_values[] = sanitize(str_replace(["y", "m", "d"], "0", $args["end_date"]));
			}
			if(!empty($args["upc"])) {
				$sql_where[] = "releases.upc LIKE CONCAT(?, '%')";
				$sql_values[] = sanitize($args["upc"]);
			}
			if(!empty($args["jan_code"])) {
				$sql_where[] = "releases.jan_code = ?";
				$sql_values[] = sanitize($args["jan_code"]);
			}
			if(!empty($args["notes"])) {
				$sql_where[] = "releases.credits LIKE CONCAT('%', ?, '%') OR releases.notes LIKE CONCAT('%', ?, '%')";
				$sql_values[] = sanitize($args["notes"]);
				$sql_values[] = sanitize($args["notes"]);
			}
			if(!empty($args["date_added"]) && preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $args["date_added"])) {
				if($args["date_added"] === date("Y-m-d")) {
					$sql_order[] = "releases.id DESC";
				}
			}
			if(is_array($args["ids"]) && !empty($args["ids"])) {
				$sql_where[] = "releases.id=".implode(" OR releases.id=", array_fill(0, count($args["ids"]), "?"));
				$sql_values = is_array($sql_values) ? array_merge($sql_values, array_values($args["ids"])) : array_values($args["ids"]);
			}
			
			
			// GROUP
			if($args['get'] === 'basics' || $args['get'] === 'all') {
				$sql_group[] = 'releases.id';
			}
			
			
			// ORDER
			if($args["order"]) {
				$sql_order[] = $args["order"];
			}
			else {
				$sql_order[] = "releases.date_occurred DESC";
				$sql_order[] = "releases.upc DESC";
			}
			
			
			// LIMIT
			if($args["get"] === "basics" || $args["get"] === "all") {
			}
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : ($sql_limit ?: null);
			
			if(is_array($sql_select) && !empty($args["get"])) {
				$sql_releases =
					'SELECT '.implode(', ', $sql_select).' '.
					'FROM '.$sql_from.' '.
					(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join).' ' : null).
					(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).') ' : null).
					($sql_force ?: null).
					(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group).' ' : null).
					($sql_order ? 'ORDER BY '.implode(', ', $sql_order).' ' : null).
					($sql_limit ?: null);
				
				// Run query
				$stmt_releases = $this->pdo->prepare($sql_releases);
				$stmt_releases->execute($sql_values);
				$rslt_releases = $stmt_releases->fetchAll();
				$num_rslt_releases = is_array($rslt_releases) ? count($rslt_releases) : 0;
				
				$artist_ids = [];
				
				for($i = 0; $i < $num_rslt_releases; $i++) {
					$id = $rslt_releases[$i]["id"];
					$artist_id = $rslt_releases[$i]['artist_id'];
					
					$releases[$id] = $rslt_releases[$i];
					$release_ids[] = $id;
					$artist_ids[$artist_id] = $artist_id;
					
					unset($rslt_releases[$i]);
				}
				
				$artist_ids = array_values($artist_ids);
				
				// Check if tracklist needed, get tracklist, append to release
				if($args["get"] === "basics" || $args["get"] === "all") {
					$sql_tracklist = "SELECT release_id, song_id, name, romaji, disc_num, disc_name, disc_romaji, section_num, section_name, section_romaji, track_num, artist_id, artist_display_name, artist_display_romaji FROM releases_tracklists WHERE release_id IN (".str_repeat("?,", count((is_array($args["release_id"]) ? $args["release_id"] : [$args["release_id"]])) - 1)."?".") ORDER BY release_id ASC, id ASC, disc_num ASC, section_num ASC, track_num ASC";
					$stmt_tracklist = $this->pdo->prepare($sql_tracklist);
					$stmt_tracklist->execute(is_array($args["release_id"]) ? $args["release_id"] : [$args["release_id"]]);
					$rslt_tracklist = $stmt_tracklist->fetchAll();
					$num_rslt_tracklist = count($rslt_tracklist);
					
					for($i = 0; $i < $num_rslt_tracklist; $i++) {
						if(!$args["artist_id"] || $args["artist_id"] === $rslt_tracklist[$i]["artist_id"] || $releases[$rslt_tracklist[$i]["release_id"]]["artist_id"] === $args["artist_id"]) {
							$releases[$rslt_tracklist[$i]["release_id"]]["tracklist"][] = $rslt_tracklist[$i];
						}
						
						unset($rslt_tracklist[$i]);
					}
					
					// Go back through releases and format tracklist
					for($i = 0; $i < $num_rslt_releases; $i++) {
						$releases[$release_ids[$i]]["tracklist"] = $this->prepare_tracklist($releases[$release_ids[$i]]["tracklist"], ($args["tracklist"] === "flat"), $releases[$release_ids[$i]]["artist_id"]);
					}
				}
				
				// Get additional data
				if(is_array($releases) && !empty($releases)) {
					
					// Get medium/format/venue/limitation
					if($args['get'] === 'basics' || $args['get'] === 'all' || $args['get'] === 'calendar' || $args['get'] === 'list') {
						$possible_attributes = $this->get_possible_attributes(true);
						
						$sql_attributes = 'SELECT releases_releases_attributes.* FROM releases_releases_attributes WHERE '.substr(str_repeat('releases_releases_attributes.release_id=? OR ', count($release_ids)), 0, -4);
						$stmt_attributes = $this->pdo->prepare($sql_attributes);
						$stmt_attributes->execute($release_ids);
						$rslt_attributes = $stmt_attributes->fetchAll();
						
						if(is_array($rslt_attributes) && !empty($rslt_attributes)) {
							foreach($rslt_attributes as $attribute) {
								
								// This gives an array like attribute_id, release_id, name, romaji, type
								// And type is a string, thanks to get_possible_attributes
								$attribute = array_merge($attribute, $possible_attributes[$attribute['attribute_id']]);
								
								// Make sure attribute type is an array in $release object
								// Can remove this whenever these fields are removed from releases table
								if(!is_array($releases[$attribute['release_id']][$attribute['type']])) {
									$releases[$attribute['release_id']][$attribute['type']] = [];
								}
								
								// Add attribute to appropriate field
								$releases[$attribute['release_id']][$attribute['type']][] = $attribute;
							}
						}
					}
					
					// Get *just* cover image
					if($args['get'] === 'basics' || $args['get'] === 'calendar') {
						$this->access_image = $this->access_image ?: new access_image($this->pdo);
						
						$images = $this->access_image->access_image([ 'release_id' => $release_ids, 'get' => 'name', 'default' => true, 'associative' => true ]);
						
						for($i=0; $i<$num_rslt_releases; $i++) {
							$releases[$release_ids[$i]]['image'] = $images[$releases[$release_ids[$i]]['image_id']];
							unset($images[$releases[$release_ids[$i]]['image_id']]);
						}
					}
					
					// Get artist info
					if(is_array($artist_ids)) {
						$release_artists = $this->access_artist->access_artist([ 'get' => 'name', 'id' => $artist_ids, 'associative' => true ]);
					}
					
					// Get user info
					if($args['edit_ids'] && $args['get'] === 'list') {
						for($i=0; $i<$num_rslt_releases; $i++) {
							
							// Array is associative so refer to separate array of IDs
							$release_id = $release_ids[$i];
							$releases[$release_id]['user'] = $this->access_user->access_user([ 'id' => $releases[$release_id]['user_id'], 'get' => 'name' ]);
							
						}
					}
					
					for($i = 0; $i < $num_rslt_releases; $i++) {
						$release_id = $release_ids[$i];
						
						// Not sure what this does
						/*if(is_array($usernames) && !empty($usernames)) {
							$releases[$release_id]['username'] = $usernames[$release_id];
						}*/
						
						// Add artist info to releases
						if(in_array($args["get"], ["all", "basics", "list", 'calendar'])) {
							$releases[$release_id]['artist'] = $release_artists[$releases[$release_id]['artist_id']];
						}
						
						if(in_array($args["get"], ["all", "basics"])) {
							$releases[$release_id]["artist"]["display_name"] = $releases[$release_id]["artist_display_name"];
							$releases[$release_id]["artist"]["display_romaji"] = $releases[$release_id]["artist_display_romaji"];
							unset($releases[$release_id]["artist_display_name"], $releases[$release_id]["artist_display_romaji"]);
						}
						
						// Get prev/next
						if($args['get'] === 'all' && is_numeric($args['release_id'])) {
							$releases[$release_id]["prev_next"] = $this->get_prev_next($release_id, is_numeric($_GET['prev_next_artist']) ? sanitize($_GET['prev_next_artist']) : $releases[$release_id]['artist']['id'] );
						}
						
						// Get images
						if($args['get'] === 'all' && is_numeric($args['release_id'])) {
							$this->access_image = $this->access_image ?: new access_image($this->pdo);
							
							$releases[$release_id]['images'] = $this->access_image->access_image([ 'release_id' => $release_id, 'get' => 'most', 'associative' => true ]);
						}
						
						if($args["get"] === "all") {
							if(!empty($releases[$release_id]["notes"])) {
								$markdown_parser = new parse_markdown($this->pdo);
								
								$releases[$release_id]["notes"] = explode("\n---\n", $releases[$release_id]["notes"]);
							}
							
							if(!empty($releases[$release_id]["credits"])) {
								$releases[$release_id]["credits"] = $this->format_credits($releases[$release_id]["credits"]);
							}
							
							foreach(["label", "publisher", "marketer", "distributor", "manufacturer", "organizer"] as $company_type) {
								$company_type_key = $company_type."_id";
								
								if(!empty($releases[$release_id][$company_type_key])) {
									$id_pattern = "\((\d+?)\)";
									
									preg_match_all("/".$id_pattern."/", $releases[$release_id][$company_type_key], $matches, PREG_SET_ORDER);
									
									if(is_array($matches)) {
										$access_label = new access_label($this->pdo);
										
										foreach($matches as $match) {
											$match = $match[1];
											
											$company = $access_label->access_label(["id" => $match, "get" => "name"]);
											
											$releases[$release_id][$company_type][$company["id"]] = $company;
										}
									}
								}
							}
							
							$releases[$release_id]["comments"] = $this->access_comment->access_comment(["id" => $releases[$release_id]["id"], 'get_user_likes' => true, "type" => "release", "get" => "all"]);
						}
					}
					
					if(is_numeric($args["release_id"])) {
						$releases = reset($releases);
					}
					
					return $releases;
				}
			}
		}
	}
?>