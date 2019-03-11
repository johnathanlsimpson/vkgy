<?php
	include_once("../php/include.php");
	include_once('../php/class-access_social_media.php');
	$access_social_media = new access_social_media($pdo);
	$markdown_parser = new parse_markdown($pdo);
	
	if($_SESSION["loggedIn"]) {
		$content = sanitize($markdown_parser->validate_markdown($_POST["content"]));
		$references = $markdown_parser->get_reference_data($content);
		$title = sanitize($_POST["title"]) ?: null;
		
		if($content) {
			if($title) {
				$id = strlen($_POST["id"]) > 0 ? sanitize($_POST["id"]) : null;
				$id = strlen($id) > 0 && is_numeric($id) ? $id : null;
				$image_id = sanitize($_POST["image_is_entry_default"]);
				$image_id = is_numeric($image_id) ? $image_id : null;
				$friendly = $_POST["friendly"] ? friendly($_POST["friendly"]) : friendly($title);
				$tags = (is_array($_POST["tags"]) && !empty($_POST["tags"]) ? sanitize("(".implode(")(", $_POST["tags"]).")") : null);
				//$edit_history = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")\n";
				$is_edit = strlen($id) > 0 ? true : false;
				
				if(is_array($references)) {
					foreach($references as $reference) {
						if($reference["type"] === "artist") {
							$tags_artists .= "(".$reference["id"].")";
						}
					}
				}
				$tags_artists = $tags_artists ?: null;
				
				$sql_friendly = "SELECT 1 FROM blog WHERE friendly=? LIMIT 1";
				$stmt_friendly = $pdo->prepare($sql_friendly);
				$stmt_friendly->execute([$friendly]);
				if(!$is_edit && $stmt_friendly->fetchColumn()) {
					$output["result"] = "An entry with that title/url already exists. Please modify the title.";
				}
				else {
					if($is_edit) {
						$sql_check = "SELECT user_id FROM blog WHERE id=? LIMIT 1";
						$stmt_check = $pdo->prepare($sql_check);
						$stmt_check->execute([$id]);
						$user_id = $stmt_check->fetchColumn();
						
						if($user_id === $_SESSION["userID"] || $_SESSION["admin"] || strpos($tags, '(auto-generated)') !== false) {
							$sql_entry = "UPDATE blog SET title=?, content=?, tags=?, tags_artists=?, image_id=? WHERE id=? LIMIT 1";
							$sql_values = [$title, $content, $tags, $tags_artists, $image_id, $id];
						}
						else {
							$output["result"] = "Sorry, you don't have permission to edit this entry.";
						}
					}
					else {
						$sql_entry = "INSERT INTO blog (title, content, friendly, tags, tags_artists, image_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
						$sql_values = [$title, $content, $friendly, $tags, $tags_artists, $image_id, $_SESSION["userID"]];
					}
				}
				
				if($sql_entry && $sql_values && is_array($sql_values)) {
					$stmt_entry = $pdo->prepare($sql_entry);
					if($stmt_entry->execute($sql_values)) {
						$id = $is_edit ? $id : $pdo->lastInsertId();
						
						$output["status"] = "success";
						$output["url"] = "/blog/".$friendly."/";
						$output["edit_url"] = "/blog/".$friendly."/edit/";
						$output["id"] = $id;
						$output["friendly"] = $friendly;
						
						if(!$is_edit && !empty($title) && !empty($friendly)) {
							$social_post = $access_social_media->build_post([ 'title' => $title, 'url' => 'https://vk.gy'.$output['url'], 'id' => $id ], 'blog_post');
							$access_social_media->queue_post($social_post, 'both', date('Y-m-d H:i:s', strtotime('+15 minutes')));
							
							$sql_edit_history = 'INSERT INTO edits_blog (blog_id, user_id, content) VALUES (?, ?, ?)';
							$stmt_edit_history = $pdo->prepare($sql_edit_history);
							$stmt_edit_history->execute([ $id, $_SESSION['userID'], ($is_edit ? null : 'Created.') ]);
						}
					}
					else {
						$output["result"] = "Sorry, there was an error ".($is_edit ? "editing" : "adding")." the entry.";
					}
				}
				else {
					$output["result"] = $output["result"] ?: "Sorry, something went wrong.";
				}
			}
			else {
				$output["result"] = "Title may not be blank.";
			}
		}
		else {
			$output["result"] = "Entry may not be blank.";
		}
	}
	else {
		$output["result"] = "Please sign in to update the blog.";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>