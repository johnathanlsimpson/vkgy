<?php
	include_once("../php/include.php");
	
	class access_comment {
		public  $pdo;
		public  $comment_types;
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
			
			$this->comment_types = ["blog", "release", "vip", "artist"];
		}
		
		
		
		// ======================================================
		// User data object
		// ======================================================
		function access_comment($args = []) {
			// SELECT
			switch($args["get"]) {
				case "all" :
					$sql_select = ["comments.id", "comments.user_id", "comments.thread_id", "comments.item_id", "comments.content", "comments.date_occurred", 'comments.anonymous_id', "comments.item_type", 'comments.is_approved', 'comments.is_deleted', 'comments.name'];
					$sql_select[] = 'num_likes.num_likes';
					break;
				case "list" :
					$sql_select = ["comments.id", "comments.user_id", "comments.item_id", "comments.content", "comments.date_occurred", "comments.item_type", 'comments.is_approved'];
					break;
				case "count":
					$sql_select = ["COUNT(comments.id) AS count"];
					break;
			}
			if(is_numeric($args['id']) && is_numeric($args['user_id'])) {
				$sql_select[] = 'comments_likes.user_id AS liked_by_user_id';
			}
			
			// FROM
			$sql_from = ["comments"];
			if($args['get'] === 'all') {
				$sql_from[] = 'LEFT JOIN (SELECT comment_id, COUNT(*) AS num_likes FROM comments_likes GROUP BY comment_id) num_likes ON num_likes.comment_id=comments.id';
			}
			if(is_numeric($args['id']) && is_numeric($args['user_id'])) {
				$sql_from[] = 'LEFT JOIN comments_likes ON comments_likes.comment_id=comments.id AND comments_likes.user_id=?';
				$sql_values[] = $args['user_id'];
			}
			
			// WHERE
			if(is_numeric($args["id"])) {
				$sql_where[] = "comments.item_id=?";
				$sql_where[] = "comments.item_type=?";
				$sql_values[] = sanitize($args["id"]);
				$sql_values[] = array_flip($this->comment_types)[$args["type"]];
			}
			if(is_numeric($args['is_approved'])) {
				$sql_where[] = 'comments.is_approved=?';
				$sql_values[] = $args['is_approved'];
			}
			if(is_numeric($args['is_deleted'])) {
				$sql_where[] = 'comments.is_deleted=?';
				$sql_values[] = $args['is_deleted'];
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
				
				if($args["get"] === "all" || $args["get"] === "list") {
					if(is_array($comments)) {
						foreach($comments as $key => $comment) {
							$comments[$key]["user"] = $this->access_user->access_user(["id" => (is_numeric($comment["user_id"]) ? $comment['user_id'] : 0), "get" => "name"]);
							$comments[$key]["item_type"] = $this->comment_types[$comment["item_type"]];
						}
					}
				}
				if($args["get"] === "all") {
					if(is_array($comments)) {
						foreach($comments as $key => $comment) {
							$comment["thread_id"] = $comment["thread_id"] ?: $comment["id"];
							
							if(!is_array($tmp_comments[$comment["thread_id"]])) {
								$tmp_comments[$comment["thread_id"]] = [];
							}
							array_unshift($tmp_comments[$comment["thread_id"]], $comment);
						}
					}
					
					$comments = $tmp_comments;
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