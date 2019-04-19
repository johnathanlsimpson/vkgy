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
			$this->access_image = new access_image($pdo);
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
			if($args['get'] === 'name') {
				array_push($sql_select, 'blog.title', 'blog.friendly', 'blog.id');
			}
			
			
			// FROM
			$sql_from = ["blog"];
			
			if($args["get"] === "all" || $args["get"] === "basics" || $args["get"] === "list") {
				$sql_from[] = "LEFT JOIN images_blog ON images_blog.blog_id=blog.id LEFT JOIN images ON images.id=images_blog.image_id";
			}
			
			
			// WHERE
			if(is_numeric($args["id"])) {
				$sql_where[] = "blog.id = ?";
				$sql_values[] = $args["id"];
			}
			if(is_array($args['ids'])) {
				$sql_where[] = substr(str_repeat('blog.id=? OR ', count($args['ids'])), 0, -4);
				$sql_values = array_merge((is_array($sql_values) ? $sql_values : []), $args['ids']);
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
				$sql_values[] = sanitize($args["friendly"]);
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
			
			//echo $sql_blog;
			//print_r($sql_values);
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
				
				if(is_array($rows)) {
					foreach($rows as $row_key => $row) {
						
						// Get tags
						$sql_tags = 'SELECT tags.tag AS name, tags.friendly, tags.id FROM blog_tags LEFT JOIN tags ON tags.id=blog_tags.tag_id WHERE blog_tags.blog_id=?';
						$stmt_tags = $this->pdo->prepare($sql_tags);
						$stmt_tags->execute([ $row['id'] ]);
						$rows[$row_key]['tags'] = $stmt_tags->fetchAll();
						
						// Get artist tags
						$sql_artists = 'SELECT blog_artists.artist_id FROM blog_artists WHERE blog_artists.blog_id=?';
						$stmt_artists = $this->pdo->prepare($sql_artists);
						$stmt_artists->execute([ $row['id'] ]);
						$rslt_artists = $stmt_artists->fetchAll();
						
						// Get artists
						if(is_array($rslt_artists) && !empty($rslt_artists)) {
							foreach($rslt_artists as $artist) {
								$artist_tags[] = $artist['artist_id'];
							}
							
							$rows[$row_key]['tags_artists'] = $this->access_artist->access_artist([ 'id' => $artist_tags, 'get' => 'name' ]);
						}
						
						// Get images
						$rows[$row_key]['images'] = $this->access_image->access_image([ 'blog_id' => $row['id'], 'get' => 'all' ]);
						
						$sql_prev_next = "(SELECT title, friendly, 'prev' AS type FROM blog WHERE date_occurred<? ORDER BY date_occurred DESC LIMIT 1) UNION (SELECT title, friendly, 'next' AS type FROM blog WHERE date_occurred>? ORDER BY date_occurred ASC LIMIT 1)";
						$stmt_prev_next = $this->pdo->prepare($sql_prev_next);
						$stmt_prev_next->execute([$row["date_occurred"], $row["date_occurred"]]);
						$rows[$row_key]["prev_next"] = $stmt_prev_next->fetchAll();
						
						$rows[$row_key]["comments"] = $this->access_comment->access_comment(["id" => $row["id"], 'user_id' => $_SESSION['userID'], "type" => "blog", "get" => "all"]);
					}
				}
			}
			
			$num_blogs = count($rows);
			
			for($i=0; $i<$num_blogs; $i++) {
				if($args['associative']) {
					$blogs[$rows[$i]['id']] = $rows[$i];
				}
				else {
					$blogs[] = $rows[$i];
				}
			}
			
			// Return result
			$blogs = is_array($blogs) ? $blogs : [];
			
			if($args["friendly"] || is_numeric($args["id"])) {
				$blogs = reset($blogs);
			}
			
			return $blogs;
			
		}
	}
?>