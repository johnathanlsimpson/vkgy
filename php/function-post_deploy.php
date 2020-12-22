<?php
	
	// Helper function to turn object into array
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
	
	// Handles updating the VIP blog with the update that just occurred
	function update_development($pdo, $args = []) {
		if($pdo instanceof PDO) {
			$title         = strlen($args["title"]) ? sanitize($args["title"]) : 'Development '.date('n/j');
			$friendly      = strlen($args["friendly"]) ? friendly($args["friendly"]) : 'development-'.date('Y-m-d');
			$user_id       = is_numeric($args["user_id"]) ? $args["user_id"] : 0;
			$header        = "Here are today's development updates. As always, thank you for supporting vkgy!\n\n---\n\n";
			$flyer_str     = 'Added 1 flyer to queue.';
			$content       = sanitize($args["content"]) ?: ($args["type"] === "flyer" ? $flyer_str : null);
			$content       = $content ? '1. '.$content : null;
			$replace_regex = '\r?\n?'.$content;
			
			if($content) {
				
				// If flyers were uploaded to DB, add line about it (or increase count if line already added)
				if($args["type"] === "flyer") {
					$sql_curr_log = "SELECT * FROM development WHERE friendly=? LIMIT 1";
					$stmt_curr_log = $pdo->prepare($sql_curr_log);
					$stmt_curr_log->execute([ $friendly ]);
					$rslt_curr_log = $stmt_curr_log->fetch();
					
					if(is_array($rslt_curr_log) && preg_match('/'.str_replace(['1', 'flyer'], ['(\d+)', 'flyers?'], $flyer_str).'/', $rslt_curr_log["content"], $matches)) {
						$content = '1. '.str_replace(['1', 'flyer'], [($matches[1] + 1), 'flyers'], $flyer_str);
						$replace_regex  = '\r?\n?1. '.$matches[0];
					}
				}
				
				// Get current VIP post if it exists
				$sql_curr_post = 'SELECT * FROM development WHERE friendly=? LIMIT 1';
				$stmt_curr_post = $pdo->prepare($sql_curr_post);
				$stmt_curr_post->execute([ $friendly ]);
				$rslt_curr_post = $stmt_curr_post->fetch();
				
				// If post already exists, make sure we're not adding the same update multiple times
				if(is_array($rslt_curr_post) && !empty($rslt_curr_post)) {
					$updated_content = $rslt_curr_post['content'];
					
					if(strpos($updated_content, $content) === false) {
						$updated_content .= "\n".$content;
						
						/*// Since entry was updated, make sure it shows as new for everyone
						$sql_views = 'DELETE FROM vip_views WHERE post_id=?';
						$stmt_views = $pdo->prepare($sql_views);
						$stmt_views->execute([ $rslt_curr_post['id'] ]);*/
					}
				}
				
				$sql_log_commit = "INSERT INTO development (title, friendly, content, user_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content=?";
				$stmt_log_commit = $pdo->prepare($sql_log_commit);
				
				if($stmt_log_commit->execute([ $title, $friendly, $header.$content, $user_id, $updated_content ])) {
					return true;
				}
				else {
					file_put_contents('deploy/log.txt', "Couldn't update DB.\n", FILE_APPEND | LOCK_EX);
				}
			}
			else {
				file_put_contents('deploy/log.txt', "No contents:\n".print_r($contents, true)."\n", FILE_APPEND | LOCK_EX);
			}
		}
		else {
			file_put_contents('deploy/log.txt', "No PDO.\n", FILE_APPEND | LOCK_EX);
		}
		
		file_put_contents('deploy/log.txt', "Args:\n".print_r($args, true), FILE_APPEND | LOCK_EX);
	}

	// Fires after files are deployed from Github; triggers log and blog update
	function post_deploy() {
		global $payload;
		global $pdo;
		
		$array_payload = object_to_array($payload);
		
		if(is_array($array_payload) && is_array($array_payload['commits'])) {
			$commits = $array_payload['commits'];
			
			if(is_array($commits)) {
				foreach($commits as $commit) {
					$content = trim($commit["message"]);
					
					// Add folders affected to commit message
					$files_affected = $commit['modified'];
					
					if( is_array($files_affected) && !empty($files_affected) ) {
						
						// Only save folder name
						foreach($files_affected as $file_key => $file) {
							$files_affected[$file_key] = reset(explode('/', $file));
						}
						
						$content .= "\n\n".' ('.implode(', ', $files_affected).')';
					}
					
					if($array_payload['ref'] === 'refs/heads/master' && strpos($content, 'Merge') !== 0) {
						if(strlen($commit['author']['email'])) {
							$sql_user = "SELECT id FROM users WHERE email=? LIMIT 1";
							$stmt_user = $pdo->prepare($sql_user);
							$stmt_user->execute([ $commit['author']['email'] ]);
							$user_id = $stmt_user->fetchColumn();
						}
						
						$user_id = is_numeric($user_id) ? $user_id : 1;
						
						if(update_development($pdo, ["content" => $content, "user_id" => $user_id])) {
							file_put_contents('deploy/log.txt', date("Y-m-d H:i:s").' Success updating development section.', FILE_APPEND | LOCK_EX);
						}
						else {
							file_put_contents('deploy/log.txt', date("Y-m-d H:i:s").' Error updating development section.', FILE_APPEND | LOCK_EX);
						}
					}
				}
			}
		}
	}
?>