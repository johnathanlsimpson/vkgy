<?php
	include_once("../php/include.php");
	
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
			$this->access_comment = new access_comment($this->pdo);
			$this->access_artist = new access_artist($this->pdo);
		}
		
		
		
		// ======================================================
		// Extract notes from track
		// ======================================================
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
		
		
		
		// ======================================================
		// Previous and next items in artist's discography
		// ======================================================
		function get_prev_next($release_id, $artist_friendly = "") {
			if(is_numeric($release_id)) {
				$sql = "SELECT 'prev' AS type, CONCAT_WS(' ', COALESCE(r2.romaji, r2.name), COALESCE(r2.press_romaji, r2.press_name), COALESCE(r2.type_romaji, r2.type_name)) AS quick_name, CONCAT_WS('/', '', 'releases', ?, r2.id, r2.friendly, '') AS url FROM releases r1, releases r2 WHERE r1.id=? AND r2.artist_id=r1.artist_id AND (r2.date_occurred<r1.date_occurred OR (r2.date_occurred=r1.date_occurred AND r2.friendly<r1.friendly)) ORDER BY r2.date_occurred DESC, r2.friendly DESC LIMIT 1";
				
				$sql_prev_next = "(".$sql.") UNION (".str_replace(["prev", "<", "DESC"], ["next", ">", "ASC"], $sql).")";
				$stmt_prev_next = $this->pdo->prepare($sql_prev_next);
				$stmt_prev_next->execute([$artist_friendly, $release_id, $artist_friendly, $release_id]);
				
				return $stmt_prev_next->fetchAll();
			}
		}
		
		
		
		// ======================================================
		// Build and return 'release(s)' object(s)
		// ======================================================
		function access_release($args = []) {
			
			// [PRE-SELECT] Artist name
			if(!empty($args["artist_display_name"])) {
				$artist_id = $this->access_artist->access_artist(["name" => $args["artist_display_name"], "get" => "id"]);
				
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
				$sql_pre = "SELECT releases_tags.release_id AS id FROM tags_releases LEFT JOIN releases_tags ON releases_tags.tag_id=tags_releases.id WHERE tags_releases.friendly=? GROUP BY releases_tags.release_id";
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
			
			// SELECT
			if(1) {
				if($args["get"] === "all") {
					$sql_select = [
						"releases.*",
						"CONCAT_WS(' ', COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name), COALESCE(releases.type_romaji, releases.type_name)) AS quick_name",
						"AVG(releases_ratings.rating) AS rating",
						//"IF(images.id IS NOT NULL AND images.is_exclusive = '1', '1', '') AS cover_is_exclusive",
						//"IF(images.id IS NOT NULL, CONCAT('/images/', images.id, '-', COALESCE(images.friendly, ''), '.', images.extension), '') AS cover"
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
						"releases.medium",
						"releases.format",
						"releases.upc",
						"releases.artist_display_name",
						"releases.artist_display_romaji",
						"AVG(releases_ratings.rating) AS rating",
						'releases.image_id',
						//"IF(images.id IS NOT NULL AND images.is_exclusive = '1', '1', '') AS cover_is_exclusive",
						//"IF(images.id IS NOT NULL, CONCAT('/images/', images.id, '-', COALESCE(images.friendly, ''), '.', images.extension), '') AS cover"
					];
				}
				if(($args["get"] === "all" || $args["get"] === "basics") && $_SESSION["loggedIn"]) {
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
						"releases.medium",
					];
					if($args['edit_ids']) {
						$sql_select[] = 'users.username';
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
						"releases.medium",
						'releases.image_id',
						//"IF(images.id IS NOT NULL, CONCAT('/images/', images.id, '-', COALESCE(images.friendly, ''), '.', images.extension), '') AS cover"
					];
				}
				elseif($args['get'] === 'id') {
					$sql_select = [
						'releases.id',
					];
				}
				if(is_numeric($args["user_id"]) && $args['get'] === 'quick_name') {
					$sql_select[] = "REPLACE(REPLACE(releases.price, ',', ''), ' yen', '') AS price";
					$sql_select[] = "releases_collections.is_for_sale";
				}
			}
			
			// FROM
			if(is_numeric($args["user_id"])) {
				$sql_from[] = "releases_collections";
				$sql_from[] = "LEFT JOIN releases ON releases.id=releases_collections.release_id";
			}
			elseif(is_array($args['edit_ids'])) {
				$sql_from[] = 'edits_releases';
				$sql_from[] = 'LEFT JOIN releases ON releases.id=edits_releases.release_id';
				$sql_from[] = 'LEFT JOIN users ON users.id=edits_releases.user_id';
			}
			else {
				$sql_from[] = "releases";
			}
			if($args['get'] === 'all') {
				$sql_select[] = 'date_edited';
				$sql_from[] = '
					LEFT JOIN
						(
							SELECT
								MAX(edits_releases.date_occurred) AS date_edited, release_id
							FROM
								edits_releases
							GROUP BY
								release_id
						)
					AS tmp_edits
					ON tmp_edits.release_id=releases.id
				';
			}
			if(($args["get"] === "all" || $args["get"] === "basics") && $_SESSION["loggedIn"]) {
				$sql_from[] = "LEFT JOIN releases_collections ON releases_collections.release_id=releases.id AND releases_collections.user_id=?";
				$sql_from[] = "LEFT JOIN releases_wants ON releases_wants.release_id=releases.id AND releases_wants.user_id=?";
				$sql_values[] = $_SESSION["userID"];
				$sql_values[] = $_SESSION["userID"];
			}
			if($args["get"] === "list") {
				$sql_from[] = 'LEFT JOIN artists ON artists.id=releases.artist_id';
			}
			$sql_from = is_array($sql_from) ? $sql_from : ["releases"];
			
			// WHERE
			$sql_values = is_array($sql_values) ? $sql_values : [];
			
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
				$f = friendly($args["release_name"]);
				$f = $f !== "-" ? $f : sanitize($args["release_name"]);
				$sql_where[] = "releases.name LIKE CONCAT('%', ?, '%') OR releases.romaji LIKE CONCAT('%', ?, '%') OR releases.friendly LIKE CONCAT('%', ?, '%')";
				array_push($sql_values, sanitize($args["release_name"]), sanitize($args["release_name"]), $f);
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
			if(!empty($args["medium"])) {
				$sql_where[] = "releases.medium LIKE CONCAT('%', ?, '%')";
				$sql_values[] = sanitize($args["medium"]);
			}
			if(!empty($args["format"])) {
				$sql_where[] = "releases.format LIKE CONCAT('%', ?, '%') OR releases.format_name LIKE CONCAT('%', ?, '%') OR releases.format_romaji LIKE CONCAT('%', ?, '%')";
				$sql_values = array_merge($sql_values, array_fill(0, 3, sanitize($args["format"])));
			}
			if(!empty($args["upc"])) {
				$sql_where[] = "releases.upc LIKE CONCAT('%', ?, '%')";
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
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;
			
			if(is_array($sql_select) && !empty($args["get"])) {
				$sql_releases  = "SELECT ".implode(", ", $sql_select)." ";
				$sql_releases .= "FROM ".implode(" ", $sql_from)." ";
				if($args["get"] === "basics" || $args["get"] === "all") {
					$sql_releases .= "LEFT JOIN releases_ratings ON releases_ratings.release_id = releases.id ";
					$sql_releases .= "LEFT JOIN releases_ratings AS user_rating ON user_rating.release_id=releases.id AND user_rating.".($_SESSION["loggedIn"] ? "user_id" : "ip_address")."=? ";
					$sql_releases .= "LEFT JOIN images ON images.id=releases.image_id ";
					array_unshift($sql_values, ($_SESSION["userID"] ?: ip2long($_SERVER["REMOTE_ADDR"])));
				}
				
				$sql_releases .= is_array($sql_where) && !empty($sql_where) ? "WHERE (".implode(") AND (", $sql_where).") ".$sql_force.' ' : null;
				$sql_releases .= ($sql_group ? 'GROUP BY '.implode(', ', $sql_group) : null).' ORDER BY '.implode(', ', $sql_order);
				$sql_releases .= " ".$sql_limit;
				
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
					$sql_tracklist = "SELECT release_id, name, romaji, disc_num, disc_name, disc_romaji, section_num, section_name, section_romaji, track_num, artist_id, artist_display_name, artist_display_romaji FROM releases_tracklists WHERE release_id IN (".str_repeat("?,", count((is_array($args["release_id"]) ? $args["release_id"] : [$args["release_id"]])) - 1)."?".") ORDER BY release_id ASC, id ASC, disc_num ASC, section_num ASC, track_num ASC";
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
					
					for($i = 0; $i < $num_rslt_releases; $i++) {
						$release_id = $release_ids[$i];
						
						if(is_array($usernames) && !empty($usernames)) {
							$releases[$release_id]['username'] = $usernames[$release_id];
						}
						
						// Add artist info to releases
						if(in_array($args["get"], ["all", "basics", "list", 'calendar'])) {
							$releases[$release_id]['artist'] = $release_artists[$releases[$release_id]['artist_id']];
						}
						
						if(in_array($args["get"], ["all", "basics"])) {
							foreach(["medium", "format"] as $key) {
								if($releases[$release_id][$key]) {
									$releases[$release_id][$key] = explode(")", str_replace("(", "", $releases[$release_id][$key]));
								}
							}
							
							$releases[$release_id]["artist"]["display_name"] = $releases[$release_id]["artist_display_name"];
							$releases[$release_id]["artist"]["display_romaji"] = $releases[$release_id]["artist_display_romaji"];
							unset($releases[$release_id]["artist_display_name"], $releases[$release_id]["artist_display_romaji"]);
						}
						
						// Get prev/next
						if($args['get'] === 'all' && is_numeric($args['release_id'])) {
							$releases[$release_id]["prev_next"] = $this->get_prev_next($release_id, $releases[$release_id]["artist"]["friendly"]);
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
							
							$releases[$release_id]["comments"] = $this->access_comment->access_comment(["id" => $releases[$release_id]["id"], 'user_id' => $_SESSION['userID'], "type" => "release", "get" => "all"]);
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