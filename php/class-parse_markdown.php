<?php
	include_once("../php/include.php");
	include_once("../php/external/class-parsedown.php");

	class parse_markdown {
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// SETUP
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Set regex patterns
		// ======================================================
		private $live_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?weloveucp\.com\/lives\/(\d+)\/?";
		private $artist_pattern = "(?<=[^\w\/]|^)(?:\((\d+)\))?\/(?! )([^\/\n]+)(?! )\/(?:\[([^\[\]\/\n]+)\])?(?=\W|$)";
		private $label_pattern = "(?<=[^\w\/\=]|^)(?:\{(\d+)\})?\=(?! )([^\=\/\n]+)(?! )\=(?:\[([^\[\]\/\=\n]+)\])?(?=\W|$)";
		private $release_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?(?:weloveucp\.com|vk\.gy)\/releases\/[\w-]+\/(\d+)\/?[\w-]*\/?";
		private $youtube_pattern = "(?:<iframe[^>]+)?(?<=\s|\"|^)(?:https?:\/\/)?(?:[A-z]+\.)?youtu\.?be.*?[\/|=]([\w-]{11})(?=\s|$|\")(?:\".+<\/iframe>)?";
		private $twitter_pattern = "(?<!\()(?:<blockquote class=\"twitter.+)?(?:(?:https?:\/\/(?:\w+\.)?)?twitter\.com\/(\w+)\/status\/(\d{10,20}))(?:[^\s]+)?(?:.+twitter\.com.+\/script>)?(?!\))";
		private $image_pattern = "\[?!\[([^\]]*)\]\(([^\)\s]+)\)(?:\]\((.+)?\))?";
		private $user_pattern = "(?<=^| )(@[A-z0-9-]+)(?=$|[\.,;\/ :\s\']|&#39;)";
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			$this->pdo = $pdo;
		}
		
		
		
		
		
		
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// NOTES
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Flow
		// ======================================================
		
		/*
		   (raw input)
		     ↡
		   html_to_markdown() (optional)
		     ↡
		   validate_markdown()
		     ↡
		   (to database)
		     ↡
		   get_reference_data()
		     ↡
		   parse_markdown(reference_data)
		*/
		
		
		
		
		
		
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// HTML➥MARKDOWN
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Parse HTML into markdown
		// ======================================================
		function html_to_markdown($input_text) {
			include_once("../php/external/class-markdownify-converter.php");
			include_once("../php/external/class-markdownify-parser.php");
			
			$converter = new Markdownify\Converter;
			
			$input_text = str_replace(["<artist>", "</artist>"], "/", $input_text);
			$input_text = $converter->parseString($input_text);
			$input_text = str_replace("_", "*", $input_text);
			
			return $input_text;
		}
		
		
		
		
		
		
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// MARKDOWN➥MARKDOWN
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Validate all markdown before insertion in DB
		// ======================================================
		function validate_markdown($input_content) {
			$input_content = str_replace("\r\n", "\n", $input_content);
			$input_content = str_replace("&lt;/blockquote&gt;\n&lt;script", "&lt;/blockquote&gt;&lt;script", $input_content);
			$input_content = str_replace(["\\*", "&#92;*"], "&#92;&#42;", $input_content);
			$input_content = str_replace(["\\[", "\\]"], ["&#92;&#91;", "&#92;&#93;"], $input_content);
			
			$input_content_lines = explode("\n", $input_content);
			
			if(is_array($input_content_lines)) {
				foreach($input_content_lines as $line_key => $line) {
					$line = $this->validate_artist_markdown($line);
					$line = $this->validate_label_markdown($line);
					$line = $this->validate_social_markdown($line);
					
					$input_content_lines[$line_key] = $line;
				}
			}
			
			$input_content_lines = is_array($input_content_lines) ? $input_content_lines : [];
			
			$output_content = implode("\n", $input_content_lines);
			$output_content = sanitize($output_content);
			
			return $output_content;
		}
		
		// ======================================================
		// Validate artist ref markdown before insertion in DB
		// ======================================================
		function validate_artist_markdown($input_content) {
			$access_artist = new access_artist($this->pdo);
			
			$output_content = preg_replace_callback("/".$this->artist_pattern."/", function($match) use($access_artist) {
				$full_match   = $match[0];
				$id           = $match[1];
				$name         = $match[2];
				$display_name = $match[3];
				
				if(!is_numeric($id)) {
					$id = $access_artist->access_artist(["name" => $name, "get" => "id"])[0]["id"];
				}
				
				if(!is_numeric($id)) {
					$output_artist_markdown = "/".$name."/";
				}
				else {
					$output_artist_markdown = '('.$id.')/'.$name.'/'.($display_name ? '['.$display_name.']' : null);
				}
				
				return $output_artist_markdown;
			}, $input_content);
			
			return $output_content;
		}
		
		// ======================================================
		// Validate label ref markdown before insertion in DB
		// ======================================================
		function validate_label_markdown($input_content) {
			$access_label = new access_label($this->pdo);
			
			$output_content = preg_replace_callback("/".$this->label_pattern."/", function($match) use($access_label) {
				$full_match   = $match[0];
				$id           = $match[1];
				$name         = $match[2];
				$display_name = $match[3];
				
				
				
				if(!is_numeric($id)) {
					$id = $access_label->access_label(["name" => $name, "get" => "id"])[0]["id"];
				}
				
				if(!is_numeric($id)) {
					$output_label_markdown = "=".$name."=";
				}
				else {
					$output_artist_markdown = '{'.$id.'}='.$name.'='.($display_name ? '['.$display_name.']' : null);
				}
				
				return $output_artist_markdown;
			}, $input_content);
			
			return $output_content;
		}
		
		// ======================================================
		// Validate social media markdown
		// ======================================================
		function validate_social_markdown($input_content) {
			
			$input_content = preg_replace("/".$this->youtube_pattern."/", "https://youtu.be/$1", $input_content);
			$input_content = preg_replace("/".$this->twitter_pattern."/", "https://twitter.com/$1/status/$2", $input_content);
			
			return $input_content;
		}
		
		
		
		
		
		
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// MARKDOWN➥DATA
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// After validating markdown, get reference data
		// ======================================================
		function get_reference_data($input_content) {
			$access_live = new access_live($this->pdo);
			$access_artist = new access_artist($this->pdo);
			$access_label = new access_label($this->pdo);
			$access_release = new access_release($this->pdo);
			$access_user = new access_user($this->pdo);
			
			$references = [];
			
			// Artist markdown >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->artist_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches  = $matches[0];
				$ids           = $matches[1];
				$names         = $matches[2];
				$display_names = $matches[3];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						$full_match   = $match[0];
						$offset       = $match[1];
						$length       = strlen($match[0]);
						$id           = $ids[$key][0];
						$name         = $names[$key][0];
						$display_name = $display_names[$key][0];
						$display_name = str_replace(["&#92;&#91;", "&#92;&#93;"], ["&#91;", "&#93;"], $display_name);
						
						if(is_numeric($id)) {
							$artist = $access_artist->access_artist(["id" => $id, "get" => "name"]);
						}
						else {
							$artist = $access_artist->access_artist(["name" => $name, "get" => "name"]);
						}
						
						if(is_array($artist)) {
							if(preg_match("/"."(.+?) \((.+?)\)"."/", $display_name, $match)) {
								$name = $match[2] ?: $match[1];
								$romaji = $match[2] ? $match[1] : null;
							}
							elseif($display_name) {
								$name = $display_name;
								$romaji = null;
							}
							else {
								$name = $artist["name"];
								$romaji = $artist["romaji"];
							}
							
							$output["name"] = $name;
							$output["romaji"] = $romaji;
							$output["quick_name"] = $romaji ?: $name;
							$output["friendly"] = $artist["friendly"];
							$output["id"] = $id;
						}
						else {
							$output["name"] = $name;
							$output["not_found"] = true;
						}
						
						$output["offset"] = $offset;
						$output["length"] = $length;
						$output["type"] = "artist";
						
						$references[] = $output;
					}
				}
			}
			unset($matches);
			
			// Label markdown >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->label_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches  = $matches[0];
				$ids           = $matches[1];
				$names         = $matches[2];
				$display_names = $matches[3];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						$full_match   = $match[0];
						$offset       = $match[1];
						$length       = strlen($match[0]);
						$id           = $ids[$key][0];
						$name         = $names[$key][0];
						$display_name = $display_names[$key][0];
						$display_name = str_replace(["&#92;&#91;", "&#92;&#93;"], ["&#91;", "&#93;"], $display_name);
						
						if(is_numeric($id)) {
							$label = $access_label->access_label(["id" => $id, "get" => "name"]);
						}
						else {
							$label = $access_label->access_label(["name" => $name, "get" => "name"]);
						}
						
						if(is_array($label)) {
							if(preg_match("/"."(.+?) \((.+?)\)"."/", $display_name, $match)) {
								$name = $match[2] ?: $match[1];
								$romaji = $match[2] ? $match[1] : null;
							}
							elseif($display_name) {
								$name = $display_name;
								$romaji = null;
							}
							else {
								$name = $label["name"];
								$romaji = $label["romaji"];
							}
							
							$output["name"] = $name;
							$output["romaji"] = $romaji;
							$output["quick_name"] = $romaji ?: $name;
							$output["friendly"] = $label["friendly"];
							$output["id"] = $id;
						}
						else {
							$output["name"] = $name;
							$output["not_found"] = true;
						}
						
						$output["offset"] = $offset;
						$output["length"] = $length;
						$output["type"] = "label";
						
						$references[] = $output;
					}
				}
			}
			unset($matches);
			
			// Release markdown >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->release_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$ids          = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$id         = $ids[$key][0];
						
						$release = $access_release->access_release(["release_id" => $id, "get" => "basics"]);
						
						if(is_array($release)) {
							$output = $release;
							
							$output["tracklist"] = is_array($release["tracklist"]) ? $release["tracklist"] : [];
							$output["inline"] = substr($input_content, ($offset - 2), 2) === "](" ? true : false;
							$output["id"] = $id;
							$output["offset"] = $offset;
							$output["length"] = $length;
							$output["type"] = "release";
							
							if(is_numeric($output["id"]) && $output["id"] >= 0) {
								$references[] = $output;
							}
						}
					}
				}
			}
			unset($matches);
			
			// Concert markdown >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->live_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$ids          = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$id         = $ids[$key][0];
						
						$live = $access_live->access_live(["id" => $id, "get" => "basics"]);
						
						if(is_array($live)) {
							$output = $live;
							$output["inline"] = substr($input_content, ($offset - 2), 2) === "](" ? true : false;
							$output["id"] = $id;
							$output["offset"] = $offset;
							$output["length"] = $length;
							$output["type"] = "live";
							
							$references[] = $output;
						}
					}
				}
			}
			unset($matches);
			
			// User markdown >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->user_pattern."/m", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$usernames    = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$username   = substr($usernames[$key][0], 1);
						
						$user = $access_user->access_user([ 'username' => $username, 'get' => 'name' ]);
						
						if(is_array($user)) {
							$output = $user;
							$output["inline"] = substr($input_content, ($offset - 2), 2) === "](" ? true : false;
							$output["id"] = $user['id'];
							$output['username'] = $user['username'];
							$output["offset"] = $offset;
							$output["length"] = $length;
							$output["type"] = "user";
							
							$references[] = $output;
						}
					}
				}
			}
			unset($matches);
			
			usort($references, function($a, $b) {
				return $a["offset"] <=> $b["offset"];
			});
			
			krsort($references);
			
			return $references;
		}
		
		
		
		
		
		
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// MARKDOWN➥HTML
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Parse markdown to HTML
		// ======================================================
		function parse_markdown($input_content, $ignore_references = false) {
			$markdown_parser = new Parsedown();
			
			$input_content = sanitize($input_content);
			$input_content = str_replace(["&#47;", "&#92;/"], ["/", "&#47;"], $input_content);
			
			$reference_data = $this->get_reference_data($input_content);
			
			if(!$ignore_references) {
				$input_content = $this->references_to_html($input_content, $reference_data);
			}
			else {
				$input_content = $this->references_to_html($input_content, $reference_data, true);
			}
			
			$input_content = $markdown_parser->text($input_content);
			$input_content = str_replace("&#92;&#42;", "*", $input_content);
			$input_content = str_replace('<ul>', '<ul class="ul--bulleted">', $input_content);
			
			return $input_content;
		}
		
		// ======================================================
		// Alter image urls
		// ======================================================
		function alter_image_urls($input_content) {
			
			if(strstr($input_content, "![") !== false && strstr($input_content, ".gif") === false) {
				$image_markdown_pattern = "\!\[(.*)\]\((?:https?:\/\/)?(?:[^@\/\n]+@)?(?:www\.)?([^:\/\n]+)\/(.*)\)";
				$replacement_pattern = "![$1](//$2.rsz.io/$3?format=jpg&w=500&quality=80)";
				$input_content = preg_replace("/".$image_markdown_pattern."/", $replacement_pattern, $input_content);
			}
			
			return $input_content;
		}
		
		// ======================================================
		// References ➥ HTML
		// ======================================================
		function references_to_html($input_content, $reference_data = null, $ignore_references = null) {
			
			// Artists, releases, lives
			if(is_array($reference_data)) {
				foreach($reference_data as $reference_datum) {
					$output = "";
					
					// Artist
					if($reference_datum["type"] === "artist") {
						if($reference_datum["not_found"]) {
							$output = $reference_datum["name"];
						}
						else {
							$output = '<a class="artist" href="/artists/'.$reference_datum["friendly"].'/" data-name="'.$reference_datum["name"].'">'.$reference_datum["quick_name"].'</a>'.($reference_datum["romaji"] ? ' ('.$reference_datum["name"].')' : null);
						}
					}
					
					// Label
					if($reference_datum["type"] === "label") {
						if($reference_datum["not_found"]) {
							$output = $reference_datum["name"];
						}
						else {
							$output = '<a class="symbol__company" href="/labels/'.$reference_datum["friendly"].'/" data-name="'.$reference_datum["name"].'">'.$reference_datum["quick_name"].'</a>'.($reference_datum["romaji"] ? ' ('.$reference_datum["name"].')' : null);
						}
					}
					
					// User
					elseif($reference_datum['type'] === 'user') {
						$output = '<a class="user" href="/users/'.$reference_datum['username'].'/">'.$reference_datum['username'].'</a>';
					}
					
					// Live
					elseif($reference_datum["type"] === "live" && !$ignore_references) {
						ob_start();
						?>
							<div class="module module--live">
								<h3>
									<div class="h5">
										<a class="a--inherit" href=""><?php echo substr($reference_datum["date_occurred"], 0, 4); ?></a>-<a class="a--inherit" href=""><?php echo substr($reference_datum["date_occurred"], 5, 2); ?></a>-<a class="a--inherit" href=""><?php echo substr($reference_datum["date_occurred"], 8, 2); ?></a>
										at
										<a class="a--inherit" href=""><?php echo $reference_datum["livehouse_quick_name"]; ?></a>
									</div>
									<a class="symbol__live" href=""><?php echo $reference_datum["quick_name"]; ?></a>
								</h3>
								<ul class="ul--inline">
									<?php
										if(is_array($reference_datum["lineup"]) && !empty($reference_datum["lineup"])) {
											foreach($reference_datum["lineup"] as $lineup_chunk) {
												?>
													<li>
														<?php
															if(is_array($lineup_chunk["references"])) {
																foreach($lineup_chunk["references"] as $reference) {
																	if($reference["type"] === "artist") {
																		$output = '<a class="artist" href="/artists/'.$reference["friendly"].'/">'.$reference["quick_name"].'</a>';
																	}
																	elseif($reference["type"] === "musician") {
																		$output = '<a class="symbol__musician" href="/musicians/'.$reference["id"].'/'.$reference["friendly"].'/">'.$reference["quick_name"].'</a>';
																	}
																	
																	$lineup_chunk["lineup"] = substr_replace($lineup_chunk["lineup"], $output, $reference["offset"], $reference["length"]);
																}
															}
															
															echo $lineup_chunk["lineup"];
														?>
													</li>
												<?php
											}
										}
									?>
								</ul>
							</div>
						<?php
						$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
					}
					
					// Release
					elseif($reference_datum["type"] === "release" && !$ignore_references) {
						ob_start();
						?>
							<div class="module module--release">
								<div class="any--flex">
									<?php
										if(strlen($reference_datum['cover'])) {
											?>
												<a href="<?php echo $reference_datum['cover']; ?>" target="_blank">
													<img alt="<?php echo $reference_datum['quick_name']; ?>" class="lazy" data-src="<?php echo str_replace('.', '.thumbnail.', $reference_datum['cover']); ?>" />
												</a>
												&nbsp;
											<?php
										}
									?>
									<div style="overflow: hidden;">
										<h3>
											<div class="h5">
												<?php echo $reference_datum["date_occurred"]; ?>
											</div>
											<div>
												<a class="artist a--inherit any--en" href="/artists/<?php echo $reference_datum["artist"]["friendly"]; ?>/"><?php echo $reference_datum["artist"]["quick_name"]; ?></a>
												<a class="artist a--inherit any--ja any--hidden" href="/artists/<?php echo $reference_datum["artist"]["friendly"]; ?>/"><?php echo $reference_datum["artist"]["name"]; ?></a>
											</div>
											<div>
												<a class="symbol__release any--en" href="/releases/<?php echo $reference_datum["artist"]["friendly"]."/".$reference_datum["id"]."/".$reference_datum["friendly"]; ?>/"><?php echo $reference_datum["quick_name"]; ?></a>
												<a class="symbol__release any--ja any--hidden" href="/releases/<?php echo $reference_datum["artist"]["friendly"]."/".$reference_datum["id"]."/".$reference_datum["friendly"]; ?>/"><?php echo $reference_datum["name"].' '.$reference_datum["press_name"].' '.$reference_datum["type_name"]; ?></a>
											</div>
										</h3>
										<ol class="ol--inline" style="text-align: left;">
											<?php
												foreach($reference_datum["tracklist"] as $discs) {
													foreach($discs as $n => $disc) {
														echo ($disc['disc_romaji'] ?: $disc['disc_name']).' ';
														foreach($disc['sections'] as $section) {
															foreach($section['tracks'] as $track) {
																?>
																	<li style="<?php echo $track['track_num'] == 1 ? 'counter-reset: defaultcounter;' : null; ?>">
																		<?php echo '<span class="any--en">'.($track['romaji'] ?: $track['name']).'</span><span class="any--ja any--hidden">'.$track['name'].'</span>'; ?>
																	</li>
																<?php
															}
														}
													}
												}
											?>
										</ol>
									</div>
									<div>
										<a class="a--padded a--outlined" style="white-space: nowrap;" href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q=<?php echo $reference_datum["upc"] ? str_replace(["-000", "-00", "-0"], "-", $reference_datum["upc"]) : str_replace(" ", "+", $reference_datum["quick_name"]); ?>">CDJapan</a>
									</div>
								</div>
							</div>
						<?php
						$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
					}
					
					if($reference_datum["inline"]) {
						$reference_datum["offset"] = strpos($input_content, "\n", $reference_datum["offset"]);
						$reference_datum["length"] = 0;
					}
					
					$input_content = substr_replace($input_content, $output, $reference_datum["offset"], $reference_datum["length"]);
				}
			}
			
			if(!$ignore_references) {
				$input_content = preg_replace_callback("/".$this->youtube_pattern."/", function($match) {
					return '<div class="module module--youtube"><iframe src="https://www.youtube.com/embed/'.$match[1].'" frameborder="0" allowfullscreen></iframe></div>';
				}, $input_content);
				
				$input_content = preg_replace_callback("/".$this->twitter_pattern."/", function($match) {
					return '<div class="module module--twitter"><blockquote class="twitter-tweet" data-lang="en"><a href="https://twitter.com/'.$match[1].'/status/'.$match[2].'"></a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script></div>';
				}, $input_content);
				
				// Image
				$input_content = preg_replace_callback("/".$this->image_pattern."/", function($match) {
					$image_src = $match[2];
					$image_src = preg_replace('/'.'(^(?:https?:)?(?:\/\/)?(?:vk\.gy)?\/images\/\d+(?:-[A-z0-9-]*)?)(\.[A-z]+)$'.'/', '$1.medium$2', $image_src);
					
					return '<div class="module module--image any--weaken any--align-center"><a href="'.($match[3] ?: $match[2]).'"><img alt="'.$match[1].'" data-src="'.$image_src.'" /></a><div>'.$match[1].'</div></div>';
				}, $input_content);
			}
			
			return $input_content;
		}
	}
?>