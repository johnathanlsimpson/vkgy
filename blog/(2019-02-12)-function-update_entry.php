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
				//$tags = (is_array($_POST["tags"]) && !empty($_POST["tags"]) ? sanitize("(".implode(")(", $_POST["tags"]).")") : null);
				$is_edit = strlen($id) > 0 ? true : false;
				
				/*if(is_array($references)) {
					foreach($references as $reference) {
						if($reference["type"] === "artist") {
							$tags_artists .= "(".$reference["id"].")";
						}
					}
				}
				$tags_artists = $tags_artists ?: null;*/
				
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
						$sql_entry = "INSERT INTO blog (title, content, friendly, tags, tags_artists, image_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
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
						
						// Cycle through tags in POST, get blog entry x tag pairings in DB, add/delete accordingly
						function update_tags($tag_table, $id_column, $entry_id, $tag_column, $new_tag_array, $pdo) {
							
							// Unset any non-numeric tags (array_filter would remove id's of 0)
							if(is_array($new_tag_array) && !empty($new_tag_array)) {
								foreach($new_tag_array as $key => $tag_id) {
									if(!is_numeric($tag_id)) {
										unset($new_tag_array[$key]);
									}
								}
							}
							
							// Remove non-unique tag values
							if(is_array($new_tag_array) && !empty($new_tag_array)) {
								$new_tag_array = array_unique($new_tag_array);
							}
							
							// Get current tags
							$sql_current_tags = 'SELECT id, '.$tag_column.' FROM '.$tag_table.' WHERE '.$id_column.'=?';
							$stmt_current_tags = $pdo->prepare($sql_current_tags);
							$stmt_current_tags->execute([ $entry_id ]);
							$rslt_current_tags = $stmt_current_tags->fetchAll();
							
							// Unset duplicate tags, set up delete for tags that are no longer wanted
							if(is_array($rslt_current_tags) && !empty($rslt_current_tags)) {
								foreach($rslt_current_tags as $tag) {
									if(in_array($tag[$tag_column], $new_tag_array)) {
										$ignore_key = array_search($tag[$tag_column], $new_tag_array);
										unset($new_tag_array[$ignore_key]);
									}
									else {
										$tags_to_delete[] = $tag[$tag_column];
									}
								}
							}
							
							// Add new tags
							if(is_array($new_tag_array) && !empty($new_tag_array)) {
								$sql_values = [];
								
								foreach($new_tag_array as $tag_id) {
									$sql_values[] = $tag_id;
									$sql_values[] = $entry_id;
									$sql_values[] = $_SESSION['userID'];
								}
								
								$sql_add = 'INSERT INTO '.$tag_table.' ('.$tag_column.', '.$id_column.', user_id) VALUES '.substr(str_repeat('(?, ?, ?), ', count($new_tag_array)), 0, -2);
								$stmt_add = $pdo->prepare($sql_add);
								$stmt_add->execute($sql_values);
								
								//echo $sql_add;
								//print_r($sql_values);
							}
							
							// Delete tags
							if(is_array($tags_to_delete) && !empty($tags_to_delete)) {
								$sql_delete = 'DELETE FROM '.$tag_table.' WHERE '.$id_column.'=? AND ('.substr(str_repeat($tag_column.'=? OR ', count($tags_to_delete)), 0, -4).')';
								$stmt_delete = $pdo->prepare($sql_delete);
								$stmt_delete->execute(array_merge([ $entry_id ], $tags_to_delete));
								//echo $sql_delete;
								//print_r(array_merge([ $entry_id ], $tags_to_delete));
							}
						}
						
						/*if(is_array($_POST['tags']) && !empty($_POST['tags'])) {
							foreach($_POST['tags'] as $key => $tag) {
								if(!is_numeric($tag)) {
									unset($_POST['tags'][$key]);
								}
							}
							
							$_POST['tags'] = array_unique($_POST['tags']);
							
							if(is_array($_POST['tags']) && !empty($_POST['tags'])) {
								$sql_check_tags = 'SELECT id, tag_id FROM blog_tags WHERE blog_id=?';
								$stmt_check_tags = $pdo->prepare($sql_check_tags);
								$stmt_check_tags->execute([ $id ]);
								$rslt_check_tags = $stmt_check_tags->fetchAll();
								
								if(is_array($rslt_check_tags) && !empty($rslt_check_tags)) {
									foreach($rslt_check_tags as $tag) {
										if(in_array($tag['tag_id'], $_POST['tags'])) {
											$ignore_key = array_search($tag['tag_id'], $_POST['tags']);
											unset($_POST['tags'][$ignore_key]);
										}
										else {
											$tags_to_delete[] = $tag['id'];
										}
									}
								}
								
								if(is_array($rslt_check_tags) && !empty($rslt_check_tags)) {
									$sql_add_tags = 'INSERT INTO blog_tags (blog_id, tag_id, user_id) VALUES (?, ?, ?)'
								}
							}
						}*/
						
						if($_SESSION['username'] === 'inartistic') {
							update_tags('blog_tags', 'blog_id', $id, 'tag_id', $_POST['tags'], $pdo);
							
							// Get artist ID's
							if(is_array($references) && !empty($references)) {
								foreach($references as $reference) {
									if($reference["type"] === "artist" && is_numeric($reference['id'])) {
										$artist_tags[] = $reference["id"];
									}
								}
							}
							
							if(is_array($artist_tags)) {
								$artist_tags = array_unique($artist_tags);
							}
							
							if(is_array($artist_tags) && !empty($artist_tags)) {
								update_tags('blog_artists', 'blog_id', $id, 'artist_id', $artist_tags, $pdo);
							}
						}
						//update_tags('blog_artists', 'blog_id', $id, 'artist_id', $artist_tags);
						
						$sql_edit_history = 'INSERT INTO edits_blog (blog_id, user_id, content) VALUES (?, ?, ?)';
						$stmt_edit_history = $pdo->prepare($sql_edit_history);
						$stmt_edit_history->execute([ $id, $_SESSION['userID'], ($is_edit ? null : 'Created.') ]);
						
						if(!$is_edit && !empty($title) && !empty($friendly)) {
							$social_post = $access_social_media->build_post([ 'title' => $title, 'url' => 'https://vk.gy'.$output['url'], 'id' => $id ], 'blog_post');
							$access_social_media->queue_post($social_post, 'both', date('Y-m-d H:i:s', strtotime('+15 minutes')));
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