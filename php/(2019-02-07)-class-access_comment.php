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
					$sql_select = ["id", "user_id", "thread_id", "item_id", "content", "date_occurred", "item_type", 'is_approved', 'is_deleted', 'name'];
					break;
				case "list" :
					$sql_select = ["id", "user_id", "item_id", "content", "date_occurred", "item_type"];
					break;
				case "count":
					$sql_select = ["COUNT(id) AS count"];
					break;
			}
			
			// FROM
			$sql_from = ["comments"];
			
			// WHERE
			if(is_numeric($args["id"])) {
				$sql_where[] = "item_id=?";
				$sql_where[] = "item_type=?";
				$sql_values[] = sanitize($args["id"]);
				$sql_values[] = array_flip($this->comment_types)[$args["type"]];
			}
			
			// GROUP
			if($args["get"] === "count") {
				$sql_group = "GROUP BY item_id";
			}
			
			// ORDER
			$sql_order = is_array($sql_order) ? $sql_order : ["date_occurred DESC"];
			
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
							$comments[$key]["user"] = $this->access_user->access_user(["id" => $comment["user_id"], "get" => "name"]);
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