<?php
	include_once("../php/include.php");
	include_once("../php/database-connect.php");
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["is_signed_in"]) {
		if(is_numeric($_POST["item_id"])) {
			if(in_array($_POST["item_type"], ['blog', 'release', 'vip', 'artist', 'video'])) {
				if(!empty($_POST["content"])) {
					$item_id = sanitize($_POST["item_id"]);
					$item_type = array_flip(["blog", "release", "vip", "artist", 'video'])[$_POST["item_type"]];
					$comment_id = is_numeric($_POST["comment_id"]) ? $_POST["comment_id"] : null;
					$thread_id = is_numeric($_POST["thread_id"]) ? $_POST["thread_id"] : null;
					$user_id = sanitize($_SESSION["user_id"]);
					
					$content = $_POST["content"];
					$content = str_replace(["\r\n", "\r"], "\n", $content);
					$content = explode("\n", $content);
					
					if(preg_match("/"."^>(\d+)$"."/", $content[0], $match)) {
						$thread_id = $match[1];
						unset($content[0]);
					}
					
					$content = implode("\n", $content);
					$content = sanitize($markdown_parser->validate_markdown($content));
					
					if(is_numeric($comment_id)) {
						$sql_check = "SELECT user_id FROM comments WHERE id=? LIMIT 1";
						$stmt_check = $pdo->prepare($sql_check);
						$stmt_check->execute([$comment_id]);
						
						if($stmt_check->fetchColumn() === $user_id) {
							$sql_update = "UPDATE comments SET item_id=?, thread_id=?, content=? WHERE id=? LIMIT 1";
							$stmt_update = $pdo->prepare($sql_update);
							if($stmt_update->execute([$item_id, $thread_id, $content, $comment_id])) {
								$output["status"] = "success";
							}
							else {
								$output["result"] = "The comment couldn't be updated.";
							}
						}
						else {
							$output["result"] = "You don't have permission that edit that comment.";
						}
					}
					else {
						$sql_add = "INSERT INTO comments (item_type, item_id, thread_id, content, user_id) VALUES (?, ?, ?, ?, ?)";
						$stmt_add = $pdo->prepare($sql_add);
						if($stmt_add->execute([$item_type, $item_id, $thread_id, $content, $user_id])) {
							$comment_id = $pdo->lastInsertId();
							$output["status"] = "success";
						}
						else {
							$output["result"] = "The comment couldn't be added.";
						}
					}
					
					if($output["status"] === "success") {
						$output["status"] = "success";
						$output["thread_id"] = $thread_id;
						$output["comment_id"] = $comment_id;
						$output["date_occurred"] = date("Y-m-d H:i:s");
						$output["content"] = $markdown_parser->parse_markdown($content);
						$output["markdown"] = $content;
					}
				}
				else {
					$output["result"] = "Please enter a comment.";
				}
			}
			else {
				$output["result"] = "Comments aren't allowed on this page.";
			}
		}
		else {
			$output["result"] = "Please specify an entry.";
		}
	}
	else {
		$output["result"] = "Please sign in.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>