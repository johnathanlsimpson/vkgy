<?php
	// Object/array to plain array
	function object_to_array($obj) {
		if(is_object($obj)) $obj = (array) $obj;
		if(is_array($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = object_to_array($val);
			}
		}
		else $new = $obj;
		return $new;       
	}
	
	function update_development($pdo, $args = []) {
		if($pdo instanceof PDO) {
			$title         = strlen($args["title"]) ? sanitize($args["title"]) : 'Development '.date('n/j');
			$friendly      = strlen($args["friendly"]) ? friendly($args["friendly"]) : friendly($title);
			$user_id       = is_numeric($args["user_id"]) ? $args["user_id"] : 0;
			$header        = "Here are today's development updates. As always, thank you for supporting vkgy!\n\n---\n\n";
			$flyer_str     = 'Added 1 flyer to queue.';
			$content       = sanitize($args["content"]) ?: ($args["type"] === "flyer" ? $flyer_str : null);
			$content       = $content ? '1. '.$content : null;
			$replace_regex = '\r?\n?'.$content;
			
			if($content) {
				if($args["type"] === "flyer") {
					$sql_curr_log = "SELECT * FROM vip WHERE friendly=? LIMIT 1";
					$stmt_curr_log = $pdo->prepare($sql_curr_log);
					$stmt_curr_log->execute([ $friendly ]);
					$rslt_curr_log = $stmt_curr_log->fetch();
					
					if(is_array($rslt_curr_log) && preg_match('/'.str_replace(['1', 'flyer'], ['(\d+)', 'flyers?'], $flyer_str).'/', $rslt_curr_log["content"], $matches)) {
						$content = '1. '.str_replace(['1', 'flyer'], [($matches[1] + 1), 'flyers'], $flyer_str);
						$replace_regex  = '\r?\n?1. '.$matches[0];
					}
				}
				
				$sql_log_commit = "INSERT INTO vip (title, friendly, content, user_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content = CONCAT(REGEXP_REPLACE(content, '".$replace_regex."', ''), ?, ?)";
				$stmt_log_commit = $pdo->prepare($sql_log_commit);
				
				if($stmt_log_commit->execute([ $title, $friendly, $header.$content, $user_id, "\n", $content ])) {
					return true;
				}
			}
		}
	}

	// Post deploy
	function post_deploy() {
		global $payload;
		global $pdo;
		
		$array_payload = object_to_array($payload);
		
		if(is_array($array_payload) && is_array($array_payload['commits'])) {
			$commits = $array_payload['commits'];
			
			if(is_array($commits)) {
				foreach($commits as $commit) {
					$content = trim($commit["message"]);
					
					if(strpos($content, 'Merge branch') === false) {
						if(strlen($commit['author']['email'])) {
							$sql_user = "SELECT id FROM users WHERE email=? LIMIT 1";
							$stmt_user = $pdo->prepare($sql_user);
							$stmt_user->execute([ $commit['author']['email'] ]);
							$user_id = $stmt_user->fetchColumn();
						}
						
						$user_id = is_numeric($user_id) ? $user_id : 1;
						
						if(update_development($pdo, ["content" => $content, "user_id" => $user_id])) {
							file_put_contents('deploy/log.txt', date("Y-m-d H:i:s").' Success updating VIP section.', FILE_APPEND | LOCK_EX);
						}
						else {
							file_put_contents('deploy/log.txt', date("Y-m-d H:i:s").' Error updating VIP section.', FILE_APPEND | LOCK_EX);
						}
					}
				}
			}
		}
	}
?>