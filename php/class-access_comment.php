​<?php
	include_once("../php/include.php");
	
	class access_comment {
		public  $pdo;
		public  $comment_types = [
			'blog',
			'release',
			'development',
			'artist',
			'video',
			'none',
			'vip'
		];
		private $access_user;
		
		
		
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
			$this->access_user = new access_user($pdo);
		}
		
		
		
		// ======================================================
		// User data object
		// ======================================================
		function access_comment($args = []) {
			// SELECT
			switch($args["get"]) {
				case "all" :
					$sql_select = ["comments.id", "comments.user_id", "comments.thread_id", "comments.item_id", "comments.date_occurred", 'comments.anonymous_id', "comments.item_type", 'comments.is_approved', 'comments.is_deleted', 'comments.name'];
					$sql_select[] = 'IF(comments.item_type=6, '.($_SESSION['is_vip'] ? 'content' : '"Only VIP members may view this."').', comments.content) AS content';
					$sql_select[] = 'IF(comments.item_type=6, 1, 0) AS is_vip';
					$sql_select[] = 'num_likes.num_likes';
					break;
				case "list" :
					$sql_select = [
						"comments.id",
						"comments.user_id",
						"comments.item_id",
						"comments.date_occurred",
						"comments.item_type",
						'comments.is_approved'
					];
					$sql_select[] = 'IF(comments.item_type=6, '.($_SESSION['is_vip'] ? 'content' : '"Only VIP members may view this."').', comments.content) AS content';
					$sql_select[] = 'IF(comments.item_type=6, 1, 0) AS is_vip';
					break;
				case "count":
					$sql_select = ["COUNT(comments.id) AS count"];
					break;
			}
			if(is_numeric($args['id']) && $args['get_user_likes']) {
				$sql_select[] = 'comments_likes.user_id AS liked_by_user_id';
			}
			
			// FROM
			$sql_from = ["comments"];
			if($args['get'] === 'all') {
				$sql_from[] = 'LEFT JOIN (SELECT comment_id, COUNT(*) AS num_likes FROM comments_likes GROUP BY comment_id) num_likes ON num_likes.comment_id=comments.id';
			}
			if(is_numeric($args['id']) && $args['get_user_likes']) {
				$sql_from[] = 'LEFT JOIN comments_likes ON comments_likes.comment_id=comments.id AND comments_likes.user_id=?';
				$sql_values[] = $_SESSION['user_id'];
			}
			
			// WHERE
			if(is_numeric($args["id"])) {
				$sql_where[] = "comments.item_id=?";
				$sql_where[] = "comments.item_type=?";
				$sql_values[] = sanitize($args["id"]);
				$sql_values[] = array_flip($this->comment_types)[$args["type"]];
			}
			if(is_array($args['thread_ids']) && !empty($args['thread_ids'])) {
				$sql_where[] = 'comments.thread_id=?'.str_repeat(' OR comments.thread_id=?', count($args['thread_ids']) - 1);
				foreach($args['thread_ids'] as $thread_id) {
					$sql_values[] = $thread_id;
				}
			}
			if(is_numeric($args['is_approved'])) {
				$sql_where[] = 'comments.is_approved=?';
				$sql_values[] = $args['is_approved'];
			}
			if(is_numeric($args['is_deleted'])) {
				$sql_where[] = 'comments.is_deleted=?';
				$sql_values[] = $args['is_deleted'];
			}
			if($args['limit_threads']) {
				$sql_where[] = 'comments.thread_id IS NULL';
			}
			if(is_numeric($args['user_id'])) {
				$sql_where[] = 'comments.user_id=?';
				$sql_values[] = $args['user_id'];
			}
			
			// GROUP
			if($args["get"] === "count") {
				$sql_group = "GROUP BY comments.item_id";
			}
			
			// ORDER
			$sql_order = is_array($sql_order) ? $sql_order : ["comments.date_occurred DESC"];
			
			// LIMIT
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;

			
			if($sql_select && $sql_from) {
				
				// QUERY
				$sql_comment = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".($sql_where ? "WHERE (".implode(") AND (", $sql_where).")" : null)." ".$sql_group." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
				$stmt_comment = $this->pdo->prepare($sql_comment);
				
				$stmt_comment->execute($sql_values);
				$comments = $stmt_comment->fetchAll();
				$num_comments = count($comments);
				
				if($args['limit_threads']) {

					for($i=0; $i<$num_comments; $i++) {
						$thread_ids[$comments[$i]['id']] = '';
					}
					
					$thread_ids = array_keys($thread_ids);
					
					$access_replies = new access_comment($this->pdo);
					
					$reply_comments = $access_replies->access_comment([ 'thread_ids' => $thread_ids, 'get' => 'all' ]);
					
					$reply_comments = is_array($reply_comments) ? $reply_comments : [];
					
					$comments = array_merge($comments, $reply_comments);
				}
				
				$num_comments = count($comments);
				
				if($args["get"] === "all" || $args["get"] === "list") {
					if(is_array($comments)) {
						for($i=0; $i<$num_comments; $i++) {
							$comments[$i]["user"] = $this->access_user->access_user(["id" => (is_numeric($comments[$i]["user_id"]) ? $comments[$i]['user_id'] : 0), "get" => "name"]);
							$comments[$i]["item_type"] = is_numeric($comments[$i]["item_type"]) ? $this->comment_types[$comments[$i]["item_type"]] : $comments[$i]["item_type"];
						}
					}
				}
				if($args["get"] === "all" || $args['get'] === 'list') {
					if(is_array($comments)) {
						
						// Loop through comments and set up SQL to get link to page that comment was made on
						for($i=0; $i<$num_comments; $i++) {
							
							// Set a default URL for the comment
							$comments[$i]['item_url'] = '/comments/';
							
							$item_type = $comments[$i]['item_type'];
							
							// If item type specified, try to find the URL for the corresponding page
							if($item_type != 'none') {
								
								// For each item type, we'll define table, parameters to be used w/in CONCAT_WS for url, and joins
								$table_name = $table_is_plural = $concat_params = $joins = $item_name = $item_romaji = null;
								
								switch($item_type) {
										
									case 'artist':
										$table_is_plural = true;
										$concat_params = '"artists", artists.friendly';
										$item_name = 'artists.name';
										$item_romaji = 'COALESCE(artists.romaji, artists.name)';
										break;
										
									case 'blog':
										$concat_params = '"blog", blog.friendly';
										$item_name = 'blog.title';
										break;
										
									case 'development':
										$concat_params = '"about", "development", development.id';
										$item_name = 'development.title';
										break;
										
									case 'release':
										$table_is_plural = true;
										$concat_params = '"releases", artists.friendly, releases.id, releases.friendly';
										$joins = 'LEFT JOIN artists ON artists.id=releases.artist_id';
										$item_name = 'CONCAT_WS(" ", artists.name, "-", releases.name, COALESCE(releases.press_name, ""), COALESCE(releases.type_name, ""))';
										$item_romaji = 'CONCAT_WS(" ", COALESCE(artists.romaji, artists.name), "-", COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name, ""), COALESCE(releases.type_romaji, releases.type_name, ""))';
										break;
										
									case 'video':
										$table_is_plural = true;
										$concat_params = '"videos", videos.id';
										$item_name = 'videos.youtube_name';
										break;
										
									case 'vip':
										$concat_params = '"vip"';
										$item_name = '"a VIP post"';
										break;
										
									default:
										$concat_params = '"'.$item_type.($item_type === 'artist' ? 's' : null).'", friendly';
										$table_is_plural = $item_type === 'artist' ? true : false;
										$item_name = 'name';
									
								}
								
								$table_name = $item_type.($table_is_plural ? 's' : null);
								
								// Set up the SELECT for each item (they should all be similar), and save key 
								// of comment within comments array so we can add url to appropriate comment later
								// (if item isn't in database, no row will be returned, so we have to save key here)
								$tmp_sql_comment_links[] = 'SELECT "'.$i.'" AS comment_key, CONCAT_WS("/", "", '.$concat_params.', "") AS url, '.($item_name ?: '""').' AS item_name, '.($item_romaji ?: '""').' AS item_romaji FROM '.$table_name.' '.$joins.' WHERE '.$table_name.'.id=?';
								
								$values_comment_links[] = $comments[$i]['item_id'];
								
							}
							
						}
						
						// Loop through SQL generated in last step and get URLs to pages that comments were left on
						if(is_array($tmp_sql_comment_links)) {
							$sql_comment_links = 'SELECT * FROM (('.implode(') UNION (', $tmp_sql_comment_links).')) a';
							$stmt_comment_links = $this->pdo->prepare($sql_comment_links);
							$stmt_comment_links->execute( $values_comment_links );
							$rslt_comment_links = $stmt_comment_links->fetchAll();
							
							foreach($rslt_comment_links as $rslt_comment_link) {
								
								$comments[ $rslt_comment_link['comment_key'] ]['item_name'] = $rslt_comment_link['item_name'];
								$comments[ $rslt_comment_link['comment_key'] ]['item_romaji'] = $rslt_comment_link['item_romaji'] && $rslt_comment_link['item_romaji'] != $rslt_comment_link['item_name'] ? $rslt_comment_link['item_romaji'] : null;
								$comments[ $rslt_comment_link['comment_key'] ]['item_url'] = $rslt_comment_link['url'];
								
							}
						}
						
						// Loop through comments and restructure into threads
						if($args['threads'] !== false && !$args['thread_ids']) {
							for($i=0; $i<$num_comments; $i++) {
								$comments[$i]["thread_id"] = $comments[$i]["thread_id"] ?: $comments[$i]["id"];
								
								if(!is_array($tmp_comments[$comments[$i]["thread_id"]])) {
									$tmp_comments[$comments[$i]["thread_id"]] = [];
								}
								array_unshift($tmp_comments[$comments[$i]["thread_id"]], $comments[$i]);
							}
						}
						
					}
					
					if($args['threads'] !== false && !$args['thread_ids']) {
						$comments = $tmp_comments;
					}
					
				}
			}
				
			// RETURN
			$comments = is_array($comments) ? $comments : [];
			if($args["get"] === "count") {
				$comments = $comments[0]["count"];
			}
			
			return $comments;
		}
	}
?>