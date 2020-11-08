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
		function access_tag($args = []) {
			$values_tag = [];
			
			if(is_numeric($args['id'])) {
				$where_tag = 'id=?';
				$values_tag[] = $args['id'];
			}
			if(strlen($args['friendly'])) {
				$where_tag = 'friendly=?';
				$values_tag[] = friendly($args['friendly']);
			}
			
			$sql_tag = 'SELECT * FROM tags'.($where_tag ? ' WHERE '.$where_tag : null);
			$stmt_tag = $this->pdo->prepare($sql_tag);
			$stmt_tag->execute($values_tag);
			$rslt_tag = $stmt_tag->fetchAll();
			$rslt_tag = is_array($rslt_tag) ? $rslt_tag : [];
			$num_tags = count($rslt_tag);
			
			if($args['associative']) {
				for($i=0; $i<$num_tags; $i++) {
					$output[$rslt_tag[$i]['friendly']] = $rslt_tag[$i];
				}
			}
			else {
				$output = $rslt_tag;
			}
			
			if(count($output) === 1) {
				$output = reset($output);
			}
			
			return $output;
		}
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
			$output = $this->access_blog([ 'artist' => $args['artist'], 'tag' => $args['tag'], 'get' => 'num_entries' ]);
			
			if($output['remainder'] < 5) {
				if($args['page'] === 'latest' && $output['penultimate_page_num'] > 1) {
					$output['prev']['page'] = $output['penultimate_page_num'] - 1;
				}
				elseif(is_numeric($args['page'])) {
					if($args["page"] > 1) {
						$output["prev"]["page"] = $args["page"] - 1;
					}
					if($args["page"] < $output["latest_page_num"] - 1) {
						$output["next"]["page"] = $args["page"] + 1;
					}
				}
				$output["latest_page_num"] = $output["latest_page_num"] - 1;
			}
			else {
				if($args['page'] === 'latest') {
					$output['prev']['page'] = $output['penultimate_page_num'];
				}
				elseif(is_numeric($args['page'])) {
					if($args["page"] > 1) {
						$output["prev"]["page"] = $args["page"] - 1;
					}
					if($args["page"] < $output["latest_page_num"]) {
						$output["next"]["page"] = $args["page"] + 1;
					}
				}
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Build query
		// ======================================================
		function access_blog($args = []) {
			
			// If searching by friendly, and friendly ends in language code, get translation and original entry
			if( strlen($args['friendly']) && preg_match('/'.'-(ja|jp)$'.'/', $args['friendly'], $language_match) ) {
				
				// Get translations
				$sql_translation = 'SELECT * FROM blog_translations WHERE friendly=? LIMIT 1';
				$stmt_translation = $this->pdo->prepare($sql_translation);
				$stmt_translation->execute([ friendly($args['friendly']) ]);
				$translation = $stmt_translation->fetch();
				
				if(is_array($translation) && !empty($translation)) {
					
					// Change getter from friendly to ID to get parent article
					unset($args['friendly']);
					$args['id'] = $translation['blog_id'];
					
				}
				
			}
			
			// SELECT
			$sql_select = [];
			if($args["get"] === "all") {
				$sql_select[] = 'blog.*';
			}
			elseif($args["get"] === "list") {
				array_push($sql_select,
					"blog.date_occurred",
					"blog.title",
					"blog.friendly",
					"blog.user_id",
					"blog.id",
					"SUBSTRING_INDEX(blog.content, '\n', 1) AS content",
					'blog.image_id',
					'blog.artist_id'
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
					'blog.image_id',
					'blog.artist_id'
				);
			}
			if($args['get'] === 'name') {
				array_push($sql_select, 'blog.title', 'blog.friendly', 'blog.id');
			}
			if($args['get'] === 'num_entries') {
				$sql_select = [
					'COUNT(1) AS num_entries',
					'CEIL(COUNT(1) / 10) AS latest_page_num',
					'LEAST(FLOOR(COUNT(1) / 10), (CEIL(COUNT(1) / 10) - 1)) AS penultimate_page_num',
					'TRUNCATE((((COUNT(1) / 10) - FLOOR(COUNT(1) / 10)) * 10), 0) AS remainder'
				];
			}
			
			
			// FROM
			$sql_from = ["blog"];
			
			if(strlen($args['artist'])) {
				$sql_from = [
					'artists',
					'LEFT JOIN blog_artists ON blog_artists.artist_id=artists.id',
					'LEFT JOIN blog ON blog.id=blog_artists.blog_id',
				];
			}
			if(strlen($args['tag'])) {
				$sql_from = [
					'tags',
					'LEFT JOIN blog_tags ON blog_tags.tag_id=tags.id',
					'LEFT JOIN blog ON blog.id=blog_tags.blog_id',
				];
			}
			
			
			// WHERE
			if(strlen($args['artist'])) {
				$sql_where[] = 'artists.friendly=?';
				$sql_values[] = friendly($args['artist']);
			}
			if(strlen($args['tag'])) {
				$sql_where[] = 'tags.friendly=?';
				$sql_values[] = friendly($args['tag']);
			}
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
			if($args['queued']) {
				$sql_where[] = 'blog.is_queued=?';
				$sql_values[] = 1;
			}
			if(!$args['show_queued']) {
				$sql_where[] = 'blog.is_queued=?';
				$sql_values[] = 0;
			}
			
			// Group
			
			
			// LIMIT
			if($args["page"] === "latest") {
				$total_num_entries = $this->get_prev_next($args);
				
				if($total_num_entries['remainder'] <= 4) {
					$sql_limit = 'LIMIT '.(10 + $total_num_entries['remainder']);
				}
				elseif($total_num_entries['remainder'] >= 5) {
					$sql_limit = 'LIMIT '.$total_num_entries['remainder'];
				}
				else {
					$sql_limit = 'LIMIT 10';
				}
			}
			if(is_numeric($args['page'])) {
				$total_num_entries = $this->get_prev_next($args);
				
				if($args['page'] == $total_num_entries['latest_page_num'] - 1 && $total_num_entries['remainder'] < 5) {
					$sql_limit = 'LIMIT '.(10 + $total_num_entries['remainder']);
				}
				elseif($args["page"] < $total_num_entries["latest_page_num"]) {
					$sql_limit = "LIMIT ".((($total_num_entries["penultimate_page_num"] * 10) + $total_num_entries["remainder"]) - ($args["page"] * 10)).", 10";
				}
				else {
					$sql_limit = "LIMIT ".($total_num_entries["remainder"] > 5 ? $total_num_entries["remainder"] : 5);
				}
			}
			if(is_numeric($args['limit'])) {
				$sql_limit = 'LIMIT '.$args['limit'];
			}
			
			
			// Execute query
			$sql_blog = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".($sql_where ? "WHERE (".implode(") AND (", $sql_where).")" : null)." ORDER BY blog.date_occurred DESC ".$sql_limit;
			$stmt_blog = $this->pdo->prepare($sql_blog);
			$stmt_blog->execute($sql_values);
			$blogs = $stmt_blog->fetchAll();
			$num_blogs = count($blogs);
			
			// Get list of returned IDs
			for($i=0; $i<$num_blogs; $i++) {
				$blog_ids[] = $blogs[$i]['id'];
			}
			
			// Add'l data
			if($args["get"] === "basics" || $args["get"] === "all" || $args["get"] === "list") {
				if(is_array($blogs)) {
					foreach($blogs as $row_key => $row) {
						$blogs[$row_key]['user'] = $this->access_user->access_user( [ 'id' => $row['user_id'], 'get' => 'name' ] );
					}
				}
			}
			
			// Get images
			if($args['get'] === 'all') {
				for($i=0; $i<$num_blogs; $i++) {
					$blogs[$i]['images'] = $this->access_image->access_image([ 'blog_id' => $blog_ids, 'get' => 'most', 'associative' => true, 'show_queued' => true ]);
				}
			}
			elseif($args['get'] === 'basics' || $args['get'] === 'list') {
				$images = $this->access_image->access_image([ 'blog_id' => $blog_ids, 'get' => 'name', 'default' => true, 'associative' => true ]);
				
				for($i=0; $i<$num_blogs; $i++) {
					$blogs[$i]['image'] = $images[$blogs[$i]['image_id']];
				}
			}
			
			// Get edit history
			if($args['get'] === 'all') {
				for($i=0; $i<$num_blogs; $i++) {
					$sql_edit_history = 'SELECT edits_blog.date_occurred, edits_blog.user_id FROM edits_blog WHERE edits_blog.blog_id=? ORDER BY edits_blog.date_occurred DESC';
					$stmt_edit_history = $this->pdo->prepare($sql_edit_history);
					$stmt_edit_history->execute([ $blogs[$i]['id'] ]);
					$blogs[$i]['edit_history'] = $stmt_edit_history->fetchAll();
					
					foreach($blogs[$i]['edit_history'] as $edit_key => $edit) {
						$blogs[$i]['edit_history'][$edit_key]['user'] = $this->access_user->access_user([ 'id' => $blogs[$i]['edit_history'][$edit_key]['user_id'], 'get' => 'name' ]);
					}
				}
			}
			
			// Get previous/next entry
			if($args['get'] === 'all') {
				for($i=0; $i<$num_blogs; $i++) {
					$sql_prev_next = "(SELECT title, friendly, 'prev' AS type FROM blog WHERE date_occurred<? AND is_queued=? ORDER BY date_occurred DESC LIMIT 1) UNION (SELECT title, friendly, 'next' AS type FROM blog WHERE date_occurred>? AND is_queued=? ORDER BY date_occurred ASC LIMIT 1)";
					$stmt_prev_next = $this->pdo->prepare($sql_prev_next);
					$stmt_prev_next->execute([ $row["date_occurred"], 0, $row["date_occurred"], 0 ]);
					$blogs[$row_key]["prev_next"] = $stmt_prev_next->fetchAll();
				}
			}
			
			if($args["get"] === "all") {
				
				if(is_array($blogs)) {
					foreach($blogs as $row_key => $row) {
						
						// Get tags
						$sql_tags = 'SELECT tags.tag AS name, tags.friendly, tags.id FROM blog_tags LEFT JOIN tags ON tags.id=blog_tags.tag_id WHERE blog_tags.blog_id=?';
						$stmt_tags = $this->pdo->prepare($sql_tags);
						$stmt_tags->execute([ $row['id'] ]);
						$blogs[$row_key]['tags'] = $stmt_tags->fetchAll();
						
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
							
							$blogs[$row_key]['tags_artists'] = $this->access_artist->access_artist([ 'id' => $artist_tags, 'get' => 'name' ]);
						}
						
						
						$blogs[$row_key]["comments"] = $this->access_comment->access_comment(["id" => $row["id"], 'get_user_likes' => true, "type" => "blog", "get" => "all"]);
					}
				}
			}
			
			// Get all translations
			if($args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'list') {
				for($i=0; $i<$num_blogs; $i++) {
					$sql_translations = 'SELECT friendly, language, id, title FROM blog_translations WHERE blog_id=?';
					$stmt_translations = $this->pdo->prepare($sql_translations);
					$stmt_translations->execute([ $blogs[$i]['id'] ]);
					$rslt_translations = $stmt_translations->fetchAll();
					
					if(is_array($rslt_translations) && !empty($rslt_translations)) {
						$blogs[$i]['translations'] = $rslt_translations;
					}
					else {
						$blogs[$i]['translations'] = [];
					}
					
					// Make sure original English version is listed
					array_unshift( $blogs[$i]['translations'], [ 'id' => $blogs[$i]['id'], 'language' => 'en', 'friendly' => $blogs[$i]['friendly'], 'title' => $blogs[$i]['title'] ] );
					
				}
			}
			
			// If translation, overwrite original fields with translation fields (but save some)
			if(is_array($translation) && !empty($translation)) {
				for($i=0; $i<$num_blogs; $i++) {
					//$original_id = $blogs[$i]['id'];
					$blogs[$i]['english_friendly'] = $blogs[$i]['friendly'];
					$blogs[$i]['translation_id'] = $translation['id'];
					$blogs[$i]['is_translation'] = true;
					$blogs[$i] = array_merge( $blogs[$i], $translation );
					//$blogs[$i]['id'] = $original_id;
				}
			}
			else {
				for($i=0; $i<$num_blogs; $i++) {
					$blogs[$i]['language'] = 'en';
				}
			}
			
			// Get main artist
			if($args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'list') {
				for($i=0; $i<$num_blogs; $i++) {
					if(is_numeric($blogs[$i]['artist_id'])) {
						$blogs[$i]['artist'] = $this->access_artist->access_artist([ 'id' => $blogs[$i]['artist_id'], 'get' => 'name' ]);
					}
				}
			}
			
			// Reformat into associative
			if($args['associative']) {
				for($i=0; $i<$num_blogs; $i++) {
					$tmp_blogs[$blogs[$i]['id']] = $blogs[$i];
				}
				
				$blogs = $tmp_blogs;
				
				unset($tmp_blogs);
			}
			
			// Return result
			$blogs = is_array($blogs) ? $blogs : [];
			
			if($args["friendly"] || is_numeric($args["id"]) || $args['get'] === 'num_entries') {
				$blogs = reset($blogs);
			}
			
			return $blogs;
			
		}
	}
?>