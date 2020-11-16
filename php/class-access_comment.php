<?php
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
							$comments[$i]['item_url'] = '/comments/';
							
							if($comments[$i]['item_type'] != 'none') {
								if($comments[$i]['item_type'] === 'release') {
									$tmp_sql_comment_links[$i] = 'SELECT releases.id, CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "") AS url FROM releases LEFT JOIN artists ON artists.id=releases.artist_id WHERE releases.id=?';
								}
								elseif($comments[$i]['item_type'] === 'video') {
									$tmp_sql_comment_links[$i] = 'SELECT videos.id, CONCAT_WS("/", "", "videos", videos.id, "") AS url FROM videos WHERE videos.id=?';
								}
								elseif($comments[$i]['item_type'] === 'development') {
									$tmp_sql_comment_links[$i] = 'SELECT development.id, CONCAT_WS("/", "", "about", "development", development.id, "") AS url FROM development WHERE development.id=?';
								}
								elseif($comments[$i]['item_type'] === 'vip') {
									$tmp_sql_comment_links[$i] = 'SELECT ? AS id, "/vip/" AS url LIMIT 1';
								}
								else {
									$tmp_sql_comment_links[$i] = 'SELECT id, CONCAT_WS("/", "", "'.$comments[$i]['item_type'].($comments[$i]['item_type'] === 'artist' ? 's' : null).'", friendly, "") AS url FROM '.$comments[$i]['item_type'].($comments[$i]['item_type'] === 'artist' ? 's' : null).' WHERE id=?';
								}
								
								$comment_array_keys[] = $i;
								
								$values_comment_links[$i] = $comments[$i]['item_id'];
								
							}
						}
						
						// Loop through SQL generated in last step and get URLs to pages that comments were left on
						if(is_array($tmp_sql_comment_links)) {
							$sql_comment_links = 'SELECT * FROM (('.implode(') UNION (', $tmp_sql_comment_links).')) a';
							$stmt_comment_links = $this->pdo->prepare($sql_comment_links);
							$stmt_comment_links->execute( array_values($values_comment_links) );
							$rslt_comment_links = $stmt_comment_links->fetchAll();
							
							foreach($rslt_comment_links as $rslt_link_key => $rslt_comment_link) {
								
								$key = $comment_array_keys[ $rslt_link_key ];
								
								$comments[$key]['item_url'] = $rslt_comment_link['url'];
								
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