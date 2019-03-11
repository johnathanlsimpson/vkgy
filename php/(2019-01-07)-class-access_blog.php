<?php
	include_once("../php/include.php");
	
	class access_blog {
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
			$this->access_artist = new access_artist($pdo);
			$this->access_comment = new access_comment($pdo);
			$this->access_user = new access_user($pdo);
		}
		
		
		
		// ======================================================
		// Get tags
		// ======================================================
		function list_tags($flat = true) {
			$sql_tags = "SELECT * FROM tags ORDER BY friendly ASC";
			$stmt_tags = $this->pdo->prepare($sql_tags);
			$stmt_tags->execute();
			$rslt_tags = $stmt_tags->fetchAll();
			
			if($flat) {
				foreach($rslt_tags as $tag) {
					$output[$tag["friendly"]] = $tag["id"];
				}
			}
			
			return $output ?: $rslt_tags;
		}
		
		
		// ======================================================
		// Prev/next links
		// ======================================================
		function get_prev_next($args = []) {
			$sql_count = "SELECT COUNT(1) AS total_count, CEIL(COUNT(1) / 10) AS latest_page_num, LEAST(FLOOR(COUNT(1) / 10), (CEIL(COUNT(1) / 10) - 1)) AS penultimate_page_num, TRUNCATE((((COUNT(1) / 10) - FLOOR(COUNT(1) / 10)) * 10), 0) AS remainder FROM blog";
			$stmt_count = $this->pdo->prepare($sql_count);
			$stmt_count->execute();
			$counts = $stmt_count->fetch();

			if($args["page"] === "latest") {
				$output["prev"]["page"] = $counts["penultimate_page_num"];
			}
			
			elseif(is_numeric($args["page"])) {
				if($args["page"] > 1) {
					$output["prev"]["page"] = $args["page"] - 1;
				}
				if($args["page"] < $counts["latest_page_num"]) {
					$output["next"]["page"] = $args["page"] + 1;
				}
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Build query
		// ======================================================
		function access_blog($args = []) {
			
			// SELECT
			$sql_select = [];
			if($args["get"] === "all") {
				array_push($sql_select,
					"blog.*",
					"CONCAT('/images/', images.id, '-', COALESCE(images.friendly, ''), '.', images.extension) AS image",
					"images.description AS image_description",
					"images.credit AS image_credit",
					"images.artist_id AS image_artist_id",
					"images.is_exclusive AS image_is_exclusive",
					"images.friendly AS image_friendly"
				);
			}
			elseif($args["get"] === "list") {
				array_push($sql_select,
					"CONCAT('/images/', images.id, '-', COALESCE(images.friendly, ''), '.', images.extension) AS image",
					"blog.date_occurred",
					"blog.title",
					"blog.friendly",
					//"blog.edit_history",
					"blog.user_id",
					"blog.id",
					"SUBSTRING_INDEX(blog.content, '\n', 1) AS content"
				);
			}
			elseif($args["get"] === "basics") {
				array_push($sql_select,
					"blog.date_occurred",
					"blog.title",
					"blog.friendly",
					"blog.content",
					"blog.user_id",
					"blog.id",
					"CONCAT('/images/', images.id, '-', IF(images.friendly, images.friendly, ''), '.', images.extension) AS image"
				);
			}
			
			
			// FROM
			$sql_from = ["blog"];
			
			if($args["get"] === "all" || $args["get"] === "basics" || $args["get"] === "list") {
				$sql_from[] = "LEFT JOIN images ON images.id=blog.image_id";
			}
			
			
			// WHERE
			if(is_numeric($args["id"])) {
				$sql_where[] = "blog.id = ?";
				$sql_values[] = $args["id"];
			}
			if(!empty($args["start_date"])) {
				$sql_where[] = "blog.date_occurred <= ?";
				$sql_values[] = friendly(str_replace(["y", "m", "d"], $args["start_date"]));
			}
			if(!empty($args["end_date"])) {
				$sql_where[] = "blog.date_occurred >= ?";
				$sql_values[] = friendly(str_replace(["y", "m", "d"], $args["end_date"]));
			}
			if(!empty($args["tag"])) {
				foreach(explode(" ", $args["tag"]) as $tag) {
					$sql_where[] = "blog.tags LIKE CONCAT('%(', ?, ')%')";
					$sql_values[] = sanitize($tag);
				}
			}
			if(!empty($args["artist_id"])) {
				foreach(explode(" ", $args["artist_id"]) as $tag) {
					$sql_where[] = "blog.tags_artists LIKE CONCAT('%(', ?, ')%')";
					$sql_values[] = sanitize($tag);
				}
			}
			if(!empty($args["content"])) {
				$artist = $this->access_artist->access_artist(["get" => "name", "name" => $args["content"]]);
				if(!empty($artist)) {
					$sql_where[] = "blog.tags_artists LIKE CONCAT('%(', ?, ')%') OR blog.content LIKE CONCAT('%', ?, '%')";
					$sql_values[] = $artist["id"];
					$sql_values[] = sanitize($args["content"]);
				}
				else {
					$sql_where[] = "blog.content LIKE CONCAT('%', ?, '%')";
					$sql_values[] = sanitize($args["content"]);
				}
			}
			if(!empty($args["friendly"])) {
				$sql_where[] = "blog.friendly=?";
				$sql_values[] = friendly($args["friendly"]);
			}
			
			
			// LIMIT
			if($args["page"] === "latest") {
				$sql_limit = "LIMIT 10";
			}
			elseif(is_numeric($args["page"])) {
				$sql_count = "SELECT CEIL(COUNT(1) / 10) AS latest_page_num, LEAST(FLOOR(COUNT(1) / 10), (CEIL(COUNT(1) / 10) - 1)) AS penultimate_page_num, TRUNCATE((((COUNT(1) / 10) - FLOOR(COUNT(1) / 10)) * 10), 0) AS remainder FROM blog";
				$stmt = $this->pdo->prepare($sql_count);
				$stmt->execute();
				$count = $stmt->fetch();
				
				if($args["page"] < $count["latest_page_num"]) {
					$sql_limit = "LIMIT ".((($count["penultimate_page_num"] * 10) + $count["remainder"]) - ($args["page"] * 10)).", 10";
				}
				else {
					$sql_limit = "LIMIT ".($count["remainder"] > 5 ? $count["remainder"] : 5);
				}
			}
			
			
			// Execute query
			$sql_blog = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".($sql_where ? "WHERE (".implode(") AND (", $sql_where).")" : null)." ORDER BY blog.date_occurred DESC ".$sql_limit;
			$stmt = $this->pdo->prepare($sql_blog);
			$stmt->execute($sql_values);
			$rows = $stmt->fetchAll();
			
			// Add'l data
			if($args["get"] === "basics" || $args["get"] === "all" || $args["get"] === "list") {
				if(is_array($rows)) {
					foreach($rows as $row_key => $row) {
						$rows[$row_key]["username"] = $this->access_user->access_user(["id" => $row["user_id"], "get" => "name"])["username"];
					}
				}
			}
			if($args["get"] === "all") {
				$sql_tags = "SELECT id, tag, friendly FROM tags";
				$stmt_tags = $this->pdo->prepare($sql_tags);
				$stmt_tags->execute();
				
				foreach($stmt_tags->fetchAll() as $extant_tag) {
					$extant_tags[$extant_tag["id"]] = ["name" => $extant_tag["tag"], "friendly" => $extant_tag["friendly"]];
				}
				
				if(is_array($rows)) {
					foreach($rows as $row_key => $row) {
						$tags = $row["tags"];
						$tags = str_replace(")", "", $tags);
						$tags = array_filter(array_unique(explode("(", $tags)));
						
						$tags_artists = $row["tags_artists"];
						$tags_artists = str_replace(")", "", $tags_artists);
						$tags_artists = array_filter(array_unique(explode("(", $tags_artists)));
						
						if(is_array($tags)) {
							foreach($tags as $tag) {
								$tmp_tags[$tag] = $extant_tags[$tag];
							}
						}
						
						if(is_array($tags_artists)) {
							foreach($tags_artists as $tag_artist) {
								$tmp_tags_artists[$tag_artist] = $this->access_artist->access_artist(["id" => $tag_artist, "get" => "name"]);
							}
						}
						
						$sql_prev_next = "(SELECT title, friendly, 'prev' AS type FROM blog WHERE date_occurred<? ORDER BY date_occurred DESC LIMIT 1) UNION (SELECT title, friendly, 'next' AS type FROM blog WHERE date_occurred>? ORDER BY date_occurred ASC LIMIT 1)";
						$stmt_prev_next = $this->pdo->prepare($sql_prev_next);
						$stmt_prev_next->execute([$row["date_occurred"], $row["date_occurred"]]);
						$rows[$row_key]["prev_next"] = $stmt_prev_next->fetchAll();
						
						$rows[$row_key]["comments"] = $this->access_comment->access_comment(["id" => $row["id"], "type" => "blog", "get" => "all"]);
					}
					
					$rows[$row_key]["tags"] = $tmp_tags;
					$rows[$row_key]["tags_artists"] = $tmp_tags_artists;
				}
			}
			
			// Return result
			$rows = is_array($rows) ? $rows : [];
			
			if($args["friendly"] || is_numeric($args["id"])) {
				$rows = reset($rows);
			}
			
			return $rows;
			
		}
	}
?>