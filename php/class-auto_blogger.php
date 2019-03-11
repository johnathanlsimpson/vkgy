<?php
	include_once('../php/include.php');
	include_once('../php/class-access_social_media.php');

	class auto_blogger {
		private $wanted_bio_types;
		private $content_types;
		private $blog_tag_id;
		private $access_social_media;
		public  $blog_tags;
		public  $magic_strings;
		
		// ======================================================
		// Connect
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			
			$this->pdo = $pdo;
			
			$this->markdown_parser = new parse_markdown($pdo);
			$this->access_artist = new access_artist($pdo);
			$this->access_blog = new access_blog($pdo);
			$this->cutoff_date = date("Y-m-d", strtotime("-1 week"));
			$this->blog_tags = $this->access_blog->list_tags();
			
			$this->wanted_bio_types = [
				"formation",
				"disbandment",
				"member"
			];
			foreach($this->wanted_bio_types as $key => $type) {
				$this->wanted_bio_types[$type] = '('.array_search($type, $this->access_artist->artist_bio_types).')';
			}
			
			$this->content_types = [
				"bio",
				"release"
			];
			
			$this->access_social_media = new access_social_media($pdo);
			
			// Set up "magic string" replacements for bio entries
			$this->magic_strings = [
				"lineup" => [
					'secedes' => ['will secede', 'has seceded'],
					'secede'  => ['will secede', 'have seceded'],
					'joins'   => ['will join', 'has joined'],
					'join'    => ['will join', 'have joined'],
				],
			];
		}
		
		
		
		// ======================================================
		// Master auto post function
		// ======================================================
		public function auto_post($content_type, $content) {
			
			// If content provided
			if(is_array($content) && !empty($content)) {
				
				// If content type provided
				if(strlen($content_type) && in_array($content_type, $this->content_types)) {
					$artist_id = $content["artist_id"];
					
					// And artist provided
					if(is_numeric($artist_id)) {
						
						// And artist exists
						$artist = $this->access_artist->access_artist([ "id" => $artist_id, "get" => "id" ]);
						if(is_array($artist) && !empty($artist)) {
							
							// Content should be type bio or release, handle accordingly
							if($content_type === "bio") {
								
								// Confirm that the bio content is of a type that we care about (e.g. formation)
								foreach($this->wanted_bio_types as $type) {
									if(strpos($content["type"], $type) !== false) {
										$continue = true;
									}
								}
								
								// If bio event occurred within cutoff
								if($continue && $content["date_occurred"] >= $this->cutoff_date) {
									$is_future = $content["date_occurred"] > date("Y-m-d");
									
									// Send to update post function, which checks if post exists then adds/edits accordingly
									$post_url = $this->update_post('bio', $content, $is_future);
									
									// If post url, return, else false
									return $post_url ?: false;
								}
							}
							
							// If content is release type, should be one simple array
							elseif($content_type === "release") {
								
								// If release was within cutoff date
								if($content["date_occurred"] >= $this->cutoff_date) {
									$is_future = $content["date_occurred"] > date("Y-m-d");
									
									// Send to update post function, which checks if post exists then adds/edits accordingly
									$post_url = $this->update_post('release', $content, $is_future);
									
									// If post url, return, else false
									return $post_url ?: false;
								}
							}
						}
					}
				}
			}
		}
		
		
		
		// ======================================================
		// Check if auto post needs edit or add
		// ======================================================
		private function update_post($content_type, $content, $is_future) {
			// If content exists and isn't empty, and artist ID is provided
			if(is_array($content) && !empty($content) && is_numeric($content["artist_id"])) {
				
				// Attempt to get corresponding auto post
				$sql_post = "SELECT id FROM blog WHERE tags_artists LIKE CONCAT('%(', ?, ')%') AND tags LIKE CONCAT('%(', ?, ')%') AND date_occurred LIKE CONCAT(?, '%') LIMIT 1";
				$stmt_post = $this->pdo->prepare($sql_post);
				$stmt_post->execute([ $content["artist_id"], $this->blog_tags["auto-generated"], '2018-11-21' ]);
				$post_id = $stmt_post->fetchColumn();
				
				// Either create post or edit post
				if(is_numeric($post_id)) {
					$post = $this->edit_post($post_id, $content_type, $content, $is_future);
				}
				else {
					$post = $this->create_post($content_type, $content, $is_future);
				}
				
				// Return url if post made
				if(is_array($post) && !empty($post)) {
					
					// Create social media post and queue
					$post['artist'] = $this->access_artist->access_artist([ 'get' => 'name', 'id' => $content['artist_id'] ]);
					$social_post = $this->access_social_media->build_post($post, 'blog_post');
					$social_result = $this->access_social_media->queue_post($social_post, 'both', date('Y-m-d H:i:s', strtotime('+ 15 minutes')));
					
					return ($post["url"] ?: false);
				}
			}
		}
		
		
		
		// ======================================================
		// Create post
		// ======================================================
		private function create_post($content_type, $content, $is_future) {
			// If there's some content to post
			if(is_array($content) && !empty($content)) {
				
				// And an artist ID is provided
				$artist_id = $content["artist_id"];
				
				if(is_numeric($artist_id)) {
					
					// And the artist exists
					$artist = $this->access_artist->access_artist([ "id" => $artist_id, "get" => "basics" ]);
					
					if(is_array($artist) && !empty($artist)) {
						
						// Get artist photo
						$sql_image = "SELECT id FROM images WHERE artist_id=? AND is_default=? AND is_release IS NULL ORDER BY id DESC LIMIT 1";
						$stmt_image = $this->pdo->prepare($sql_image);
						$stmt_image->execute([ '('.$artist_id.')', 1 ]);
						$image_id = $stmt_image->fetchColumn();
						$image_id = is_numeric($image_id) ? $image_id : null;
						
						// Go through each potential post type, and write the post
						if($content_type === "release") {
							$title = $artist["quick_name"].' new '.(str_replace([')(', '(', ')'], [' ', '', ''], $content["format"]) ?: 'release').' &ldquo;'.($content["romaji"] ?: $content["name"]).'&rdquo;';
							$post = [
								'('.$artist["id"].')/'.$artist["friendly"].'/ '.($is_future ? 'will' : 'has').' put out a new '.(str_replace([')(', '(', ')'], [' ', '', ''], $content["format"]) ?: 'release').'.',
								'*'.($content["romaji"] ?: $content["name"]).'* '.($content["romaji"] ? ' (*'.$content["name"].'*) ' : null).($is_future ? 'will be' : 'was').' released '.($content["type_name"] ? 'in multiple types' : null).' on '.date('F jS', strtotime($content["date_occurred"])).'.',
								'https://vk.gy/releases/'.$artist["friendly"].'/'.$content["id"].'/'.$content["friendly"].'/'
							];
							$tag = 'release';
						}
						elseif($content_type === "bio") {
							if(strpos($content["type"], $this->wanted_bio_types["member"]) !== false) {
								$title = 'Lineup change for '.$artist["quick_name"];
								$post[] = '('.$artist["id"].')/'.$artist["friendly"].'/\'s lineup '.($is_future ? 'will change' : 'has changed').':';
								
								$string = $content["content"];
								foreach($this->magic_strings["lineup"] as $string_key => $string_replacements) {
									$string = str_replace($string_key, ($is_future ? $string_replacements[0] : $string_replacements[1]), $string);
								}
								$string  = substr($string, -1) === '.' ? substr($string, 0, -1) : $string;
								$string .= ' on '.date('F jS', strtotime($content["date_occurred"])).'.';
								
								$post[] = $string;
								$tag = 'lineup';
							}
							elseif(strpos($content["type"], $this->wanted_bio_types["disbandment"]) !== false) {
								$title = $artist["quick_name"].' '.($is_future ? 'will disband' : 'has disbanded');
								$post = [
									'('.$artist["id"].')/'.$artist["friendly"].'/ '.($is_future ? 'will disband' : 'has disbanded').' on '.date('F jS', strtotime($content["date_occurred"])).'.',
								];
								$tag = 'disbandment-revival';
							}
							elseif(strpos($content["type"], $this->wanted_bio_types["formation"]) !== false) {
								$title = 'New band: '.$artist["quick_name"];
								$post[] = 'New band ('.$artist["id"].')/'.$artist["friendly"].'/ '.($is_future ? 'began' : 'will begin').' activity on '.date('F jS', strtotime($content["date_occurred"])).'.';
								
								$lineup = $this->format_lineup($artist);
								if(strlen($lineup)) {
									$post[] = '---';
									$post[] = $lineup;
								}
								
								$tag = 'new-band';
							}
						}
						
						// Prepare additional post info, format
						if(strlen($title) && is_array($post) && !empty($post) && strlen($tag)) {
							$friendly = friendly($title);
							//$tags = '('.$this->blog_tags["auto-generated"].')'.'('.$this->blog_tags[$tag].')';
							//$artist_tags = '('.$artist_id.')';
							$post = sanitize(implode("\n\n", $post));
							$user_id = is_numeric($_SESSION["userID"]) ? $_SESSION["userID"] : '0';
							$edit_history = date('Y-m-d H:i:s').' ('.$user_id.')'."\n";
							
							// Create post
							$sql_add = "INSERT INTO blog (title, friendly, content, image_id, tags, tags_artists, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
							$stmt_add = $this->pdo->prepare($sql_add);
							if($stmt_add->execute([ $title, $friendly, $post, $image_id, $tags, $artist_tags, $user_id ])) {
								$blog_id = $this->pdo->lastInsertId();
								
								$sql_tags = 'INSERT INTO blog_tags (blog_id, tag_id, user_id) VALUES (?, ?, ?), (?, ?, ?)';
								$stmt_tags = $this->pdo->prepare($sql_tags);
								$stmt_tags->execute([ $blog_id, $this->blog_tags['auto-generated'], $_SESSION['userID'], $blog_id, $this->blog_tags[$tag], $_SESSION['userID'] ]);
								
								$sql_artist_tags = 'INSERT INTO blog_artists (blog_id, artist_id, user_id) VALUES (?, ?, ?)';
								$stmt_artist_tags = $this->pdo->prepare($sql_artist_tags);
								$stmt_artist_tags->execute([ $blog_id, $artist_id, $_SESSION['userID'] ]);
								
								// Return URL if successful
								return ["title" => $title, "url" => 'https://vk.gy/blog/'.$friendly.'/', "id" => $blog_id];
							}
						}
					}
				}
			}
		}
		
		
		
		// ======================================================
		// Edit post
		// ======================================================
		private function edit_post($post_id, $content_type, $content, $is_future) {
			// If the post ID was provided
			if(is_numeric($post_id)) {
				$post = $this->access_blog->access_blog([ "id" => $post_id, "get" => "all" ]);
				
				// If post exists
				if(is_array($post) && !empty($post)) {
					
					// Switch tags ID to word representation
					if(is_array($post["tags"]) && !empty($post["tags"])) {
						foreach($post["tags"] as $key => $tag) {
							$post["tags"][$key] = $tag["friendly"];
						}
					}
					else {
						$post["tags"] = [];
					}
					
					// If still auto post (not yet edited by user)
					if(in_array('auto-generated', $post["tags"])) {
						
						// If there's some content to post
						if(is_array($content) && !empty($content)) {
							
							// Quickly determine content type, then check that artist exists
							$artist_id = $content["artist_id"];
							
							// If artist ID is provided
							if(is_numeric($artist_id)) {
								
								// And the artist exists
								$artist = $this->access_artist->access_artist([ "id" => $artist_id, "get" => "basics" ]);
								if(is_array($artist) && !empty($artist)) {
									
									// Go through each potential post type, and edit the post
									if($content_type === "release") {
										
										// If post already has a release
										if(in_array("release", $post["tags"])) {
											$url = 'https://vk.gy/releases/'.$artist["friendly"].'/'.$content["id"].'/'.$content["friendly"].'/';
											
											// Make sure post doesn't already have *this* release
											if(strpos($post["content"], $url) === false) {
												
												// If release is multi-type, and post already mentions release, add URL to new type afterward
												if(strlen($content["type_name"]) && strpos($post["content"], '*'.$content["name"].'*') !== false) {
													$release_is_additional_type = true;
												}
												else {
													$release_is_additional_type = false;
												}
												
												// Turn post into array
												$post["content"] = str_replace(["\r\n", "\r"], "\n", $post["content"]);
												$post["content"] = explode("\n\n", $post["content"]);
												
												foreach($post["content"] as $line_key => $line) {
													// If additional type of release that's already in post, add url
													if($release_is_additional_type) {
														if(preg_match('/'.'^https\:\/\/vk\.gy\/releases\/.+'.'/', $line)) {
															$post["content"][$line_key] .= "\n".$url;
															break;
														}
													}
													// Otherwise, add a new section explaining the release, then add the url under the previous url
													else {
														if(preg_match('/'.'put out'.'/', $line)) {
														}
														
														if(preg_match('/'.'^https\:\/\/vk\.gy\/releases\/[A-z0-9-]+'.'/', $line)) {
															$post["content"][$line_key] =
																'They '.($is_future ? 'will' : 'have').' also put out a a new '.(str_replace([')(', '(', ')'], [' ', '', ''], $content["format"]) ?: 'release').', '.
																'*'.($content["romaji"] ?: $content["name"]).'*'.($content["romaji"] ? ' (*'.$content["name"].'*)' : null).', '.
																($content["type_name"] ? 'in multiple types' : null).' on '.date('F jS', strtotime($content["date_occurred"])).'.'.
																"\n\n".
																$post["content"][$line_key].
																"\n".$url;
															break;
														}
													}
												}
											}
											
											$new_content = $post["content"];
											$new_content = is_array($new_content) ? $new_content : [$new_content];
										}
										else {
											$new_content = [
												$post["content"],
												'---',
												'The group '.
													($is_future ? 'will' : 'has').
													' put out a new '.
													(str_replace([')(', '(', ')'], [' ', '', ''], $content["format"]) ?: 'release').
													', *'.
													($content["romaji"] ?: $content["name"]).
													'*'.
													($content["romaji"] ? ' (*'.$content["name"].'*)' : null).
													','.
													($content["type_name"] ? ' in multiple types' : null).
													' on '.
													date('F jS', strtotime($content["date_occurred"])).
													'.',
												'https://vk.gy/releases/'.$artist["friendly"].'/'.$content["id"].'/'.$content["friendly"].'/'
											];
										}
										$tag = 'release';
									}
									elseif($content_type === "bio") {
										if(strpos($content["type"], $this->wanted_bio_types["member"]) !== false && !in_array("lineup", $post["tags"])) {
											$post["content"] = str_replace(["\r\n", "\r"], "\n", $post["content"]);
											$post["content"] = explode("\n\n", $post["content"]);
											$post["content"][0] .= "\n\n".'Furthermore, on '.date('F jS', strtotime($content["date_occurred"])).', '.str_replace(['secedes', 'joins'], [($is_future ? 'will secede' : 'seceded'), ($is_future ? 'will join' : 'has joined')], $content["content"]);
											$new_content = $post["content"];
											$tag = 'lineup';
										}
										elseif(strpos($content["type"], $this->wanted_bio_types["disbandment"]) !== false && !in_array("disbandment-revival", $post["tags"])) {
											$title = $artist["quick_name"].' '.($is_future ? 'will disband' : 'has disbanded');
											$new_content = [
												'('.$artist["id"].')/'.$artist["friendly"].'/ '.($is_future ? 'will disband' : 'has disbanded').' on '.date('F jS', strtotime($content["date_occurred"])).'.',
												'---',
												$post["content"]
											];
											$tag = 'disbandment-revival';
										}
										elseif(strpos($content["type"], $this->wanted_bio_types["formation"]) !== false && !in_array("new-band", $post["tags"])) {
											$title = 'New band: '.$artist["quick_name"];
											$new_content[] = 'New band ('.$artist["id"].')/'.$artist["friendly"].'/ '.($is_future ? 'began' : 'will begin').' activity on '.date('F jS', strtotime($content["date_occurred"])).'.';
											
											$lineup = $this->format_lineup($artist);
											if(strlen($lineup)) {
												$new_content[] = '---';
												$new_content[] = $lineup;
											}
											
											$tag = 'new-band';
										}
									}
									
									// Prepare additional post info, format
									if(is_array($post) && !empty($post) && is_numeric($post["id"]) && is_array($new_content) && !empty($new_content)) {
										$title = $title ?: $post["title"];
										$new_content = sanitize(implode("\n\n", $new_content));
										$user_id = $post["user_id"] === 0 && is_numeric($_SESSION["userID"]) ? $_SESSION["userID"] : $post["user_id"];
										//$edit_history = date('Y-m-d H:i:s').' ('.$user_id.')'."\n";
										
										
										/*$tags = '';
										foreach($post["tags"] as $tag_friendly) {
											$tags .= '('.$this->blog_tags[$tag_friendly].')';
										}
										$tags .= !in_array($tag, $post["tags"]) ? '('.$this->blog_tags[$tag].')' : null;*/
										
										// Edit post
										$sql_edit = "UPDATE blog SET title=?, content=?, user_id=? WHERE id=? LIMIT 1";
										$stmt_edit = $this->pdo->prepare($sql_edit);
										if($stmt_edit->execute([ $title, $new_content, $user_id, $post["id"] ])) {
											
											// Prepare tags
											if(!in_array($tag, $post['tags'])) {
												$sql_tags = 'INSERT INTO blog_tags (blog_id, tag_id, user_id) VALUES (?, ?, ?)';
												$stmt_tag = $this->pdo->prepare($sql_tags);
												$stmt_tag->execute([ $post['id'], $this->blog_tags[$tag], $_SESSION['userID'] ]);
											}
											/*foreach($post['tags'] as $tag_friendly_name) {
												$values_tags[] = $post['id'];
												$values_tags[] = $this->blog_tags[$tag_friendly_name];
												$values_tags[] = $_SESSION['userID'];
											}*/
											
											
											
											// Return URL if successful
											return ["title" => $title, "url" => 'https://vk.gy/blog/'.$post["friendly"].'/', "id" => $post_id];
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		
		
		// ======================================================
		// Cycle through artist lineup and format for post
		// ======================================================
		private function format_lineup($artist) {
			if(is_array($artist["musicians"]) && !empty($artist["musicians"])) {
				foreach($artist["musicians"] as $musician) {
					unset($lineup_line);
					
					if($musician["to_end"]) {
						$lineup_line  = '* ';
						$lineup_line .= substr($this->access_artist->positions[$musician["position"]], 0, 2).'. ';
						$lineup_line .=  ($musician["as_name"] ? ($musician["as_romaji"] ? $musician["as_romaji"].' ('.$musician["as_name"].')' : $musician["as_name"]) : ($musician["romaji"] ? $musician["romaji"].' ('.$musician["name"].')' : $musician["name"]));
						
						if(is_array($musician["history"]) && !empty($musician["history"])) {
							$num_history_chunks = count($musician["history"]);
							$num_history_chunks = $num_history_chunks - 2;
							$history_chunk_key = $num_history_chunks >= 0 ? $num_history_chunks : null;
							
							if(is_numeric($history_chunk_key)) {
								$history_chunk = $musician["history"][$history_chunk_key][0];
								
								if(strlen($history_chunk["name"])) {
									$lineup_line .= ' [ex-';
									if($history_chunk["friendly"] && is_numeric($history_chunk["id"])) {
										$lineup_line .= '('.$history_chunk["id"].')/'.$history_chunk["friendly"].'/';
										$lineup_line .= $history_chunk["display_name"] ? '['.($history_chunk["display_romaji"] ? $history_chunk["display_romaji"].' ('.$history_chunk["display_name"].')' : $history_chunk["display_name"]).']' : null;
									}
									else {
										$lineup_line .= ($history_chunk["romaji"] ? $history_chunk["romaji"].' ('.$history_chunk["name"].')' : $history_chunk["name"]);
									}
									$lineup_line .= ']';
								}
							}
						}
					}
					
					if(strlen($lineup_line)) {
						$lineup_lines[] = $lineup_line;
					}
				}
			}
			
			if(is_array($lineup_lines) && !empty($lineup_lines)) {
				return implode("\n", $lineup_lines);
			}
		}
	}
?>