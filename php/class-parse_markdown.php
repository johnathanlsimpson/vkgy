<?php
	include_once("../php/include.php");
	include_once('../php/class-access_video.php');
	include_once("../php/external/class-parsedown.php");
	include_once('../php/function-script.php');
	include_once('../php/function-image_exists.php');

	class parse_markdown {
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// SETUP
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		
		// ======================================================
		// Set regex patterns
		// ======================================================
		private $live_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?weloveucp\.com\/lives\/(\d+)\/?";
		private $artist_pattern = "(?<=[^\w\/]|^)(?:\((\d+)\))?\/(?! )([^\/\n]+)(?! )\/(?:\[([^\[\]\/\n]+)\])?(?=\W|$)";
		private $artist_block_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?vk\.gy\/artists\/([A-z0-9-]+)\/?";
		private $label_pattern = "(?<=[^\w\/\=]|^)(?:\{(\d+)\})?\=(?! )([^\=\/\n]+)(?! )\=(?:\[([^\[\]\/\=\n]+)\])?(?=\W|$)";
		private $release_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?(?:weloveucp\.com|vk\.gy)\/releases\/[\-\w]+\/(\d+)\/?[\-\w]*\/?";
		private $video_pattern = "(?:(?:https?\:)?\/\/(?:[A-z]+\.)?)?vk\.gy\/videos\/(\d+)\/?";
		private $youtube_pattern = "(?:<iframe[^>]+)?(?<=\s|\"|^)(?:https?:\/\/)?(?:[A-z]+\.)?youtu\.?be.*?[\/|=]([\-\w]{11})(?=\s|$|\")(?:\".+<\/iframe>)?";
		private $twitter_pattern = "(?<!\()(?:<blockquote class=\"twitter.+)?(?:(?:https?:\/\/(?:\w+\.)?)?twitter\.com\/(\w+)\/status\/(\d{10,20}))(?:[^\s]+)?(?:.+twitter\.com.+\/script>)?(?!\))";
		private $image_pattern = "\[?!\[([^\]]*)\]\(([^\)\s]+)\)(?:\]\((.+)?\))?";
		private $user_pattern = "(?<=^| )(@[A-z0-9-]+)(?=$|[\.,;\/ :\s\']|&#39;)";
		private $spotify_pattern = '(https:\/\/open\.spotify\.com\/)((?:artist|track|album|playlist)\/[A-z0-9]{22})(?:\?si=[\-\w]+)?';
		private $linkcore_pattern = '(https:\/\/linkco\.re\/)([A-z0-9]{8})(?:\?[A-z0-9\=\&]*)?';
		private $lnkto_pattern = '(https:\/\/lnk\.to\/[A-z0-9]+)';
		
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
			$input_content = preg_replace("/".$this->spotify_pattern."/", "$1$2", $input_content);
			$input_content = preg_replace("/".$this->linkcore_pattern."/", "$1$2", $input_content);
			
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
			$access_video = new access_video($this->pdo);
			
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
							$output['display_name'] = $display_name;
							$output['display_romaji'] = $display_romaji;
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
			
			// Artist link >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->artist_block_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$friendlies   = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$friendly   = $friendlies[ $key ][0];
						
						$artist = $access_artist->access_artist([ 'friendly' => $friendly, 'get' => 'basics' ]);
						
						// Reset lineup
						$lineup = [];
						$lineup_romaji = [];
						$tags = [];
						
						// This should be a function in artist model
						if( is_array($artist['musicians']) && !empty($artist['musicians']) ) {
							foreach($artist['musicians'] as $musician) {
								
								$position = [ '?', 'V', 'G', 'B', 'D', 'K', 'O', 'S' ][ $musician['position'] ];
								$lineup[] = $position.'. '.$musician['name'];
								$lineup_romaji[] = $position.'. '.($musician['romaji'] ?: $musician['name']);
								
							}
						}
						unset($artist['musicians']);
						
						// Format tags--should be in artist model
						if($artist['tag_names']) {
							$tag_names = explode(',', $artist['tag_names']);
							$tag_romajis = explode(',', $artist['tag_romajis']);
							$tag_friendlies = explode(',', $artist['tag_friendlys']);
							
							foreach($tag_names as $tag_key => $tag_name) {
								$tags[] = [
									'name' => $tag_name,
									'romaji' => $tag_romajis[$tag_key] ?: $tag_name,
									'url' => '/search/artists/?tags[]='.$tag_friendlies[$tag_key],
								];
							}
						}
						unset($artist['tag_names'], $artist['tag_romajis'], $artist['tag_friendlys']);
						
						$output = $artist;
						$output['url'] = '/artists/'.$artist['friendly'].'/';
						$output['lineup'] = is_array($lineup) ? implode(' / ', $lineup) : null;
						$output['lineup_romaji'] = is_array($lineup_romaji) ? implode(' / ', $lineup_romaji) : null;
						$output['image'] = '/artists/'.$artist['friendly'].'/'.'main.medium.jpg';
						$output['tags'] = $tags ?: null;
						$output["offset"] = $offset;
						$output["length"] = $length;
						$output["type"] = 'artist_block';
						
						$references[] = $output;
						
					}
				}
			}
			unset($matches);
			
			// YouTube link >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->youtube_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$ids          = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$youtube_id = $ids[$key][0];
						
						$video = $access_video->access_video([ 'youtube_id' => $youtube_id, 'get' => 'basics' ]);
						
						// If video is in database, run that embed instead
						if( is_array($video) && !empty($video) ) {
							$output = $video;
							$output['video_type'] = $video['type'];
							$output['name'] = $access_video->clean_title($video['youtube_name'], $video['artist']);
							$output['user'] = $access_user->render_username($video['user']);
							$output['type'] = 'video';
						}
						else {
							$output['type'] = 'youtube';
						}
						
						$output['youtube_id'] = $youtube_id;
						$output['offset'] = $offset;
						$output['length'] = $length;
						
						$references[] = $output;
						
					}
				}
			}
			unset($matches);
			
			// Video link >> data object
			// -----------------------------------------------------
			preg_match_all("/".$this->video_pattern."/", $input_content, $matches, PREG_OFFSET_CAPTURE);
			if(is_array($matches)) {
				$full_matches = $matches[0];
				$ids          = $matches[1];
				
				if(is_array($full_matches)) {
					foreach($full_matches as $key => $match) {
						
						$full_match = $match[0];
						$offset     = $match[1];
						$length     = strlen($match[0]);
						$id         = $ids[$key][0];
						
						$video = $access_video->access_video([ 'id' => $id, 'get' => 'basics' ]);
						
						$output = $video;
						$output['video_type'] = $video['type'];
						$output['name'] = $access_video->clean_title($video['youtube_name'], $video['artist']);
						$output['user'] = $access_user->render_username($video['user']);
						
						$output['url'] = $full_match;
						$output['offset'] = $offset;
						$output['length'] = $length;
						$output['type'] = 'video';
						
						$references[] = $output;
						
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
							$output['user'] = $user;
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
			$input_content = str_replace(["&#47;", "&#92;/", '&#62;'], ["/", "&#47;", '>'], $input_content);
			
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
			
			// Allow manual breaks on lines that end in ' \'
			$input_content = str_replace(" &#92;\n", "<br />\n", $input_content);
			
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
			$access_release = new access_release($this->pdo);
			
			// Artists, releases, lives
			if(is_array($reference_data)) {
				foreach($reference_data as $reference_datum) {
					$output = "";
					$reference_datum['type'] = $reference_datum['item_type'] ?: $reference_datum['type'];
					
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
						$output = '<a class="user" data-icon="'.$reference_datum['user']['icon'].'" data-is-vip="'.$reference_datum['user']['is_vip'].'" href="'.$reference_datum['user']['url'].'">'.$reference_datum['user']['username'].'</a>';
					}
					
					elseif($reference_datum['type'] === 'artist_block') {
						ob_start();
						?>
							<div class="module module--artist">
								<div class="card__container text">
									
									<a class="card__link" href="<?= $reference_datum['url']; ?>"></a>
									
									<div class="artist-card__image h5 lazy" data-src="<?= $reference_datum['image']; ?>"></div>
									
									<div class="artist-card__navs any--flex">
										
										<a class="artist-card__artist card--subject artist" href="<?= $reference_datum['url']; ?>">
											<?= $reference_datum['romaji'] ? lang($reference_datum['romaji'], $reference_datum['name'], 'parentheses') : $reference_datum['name']; ?>
										</a>
										
										<a class="artist-card__nav card--clickable symbol__artist symbol--standalone" href="<?= $reference_datum['url']; ?>"></a>
										<a class="artist-card__nav card--clickable symbol__release symbol--standalone" href="<?= '/releases/'.$reference_datum['friendly'].'/'; ?>"></a>
										<a class="artist-card__nav card--clickable symbol__video symbol--standalone" href="<?= '/artists/'.$reference_datum['friendly'].'/videos/'; ?>"></a>
										<a class="artist-card__nav card--clickable symbol__news symbol--standalone" href="<?= '/blog/artists/'.$reference_datum['friendly'].'/'; ?>"></a>
										<a class="artist-card__nav card--clickable symbol__edit symbol--standalone" href="<?= '/artists/'.$reference_datum['friendly'].'/edit/'; ?>"></a>
										
									</div>
									
									<div class="artist-card__details any--flex any--weaken">
										
										<span class="artist-card__tags"><?= $reference_datum['lineup'].' '; ?><?php
											if( is_array($reference_datum['tags']) && !empty($reference_datum['tags']) ) {
												foreach($reference_datum['tags'] as $tag) {
													echo '<a class="artist-card__tag card--clickable a--inherit symbol__tag" href="'.$tag['url'].'">'.lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden').'</a>&nbsp;';
												}
											}
										?></span>
										
										<span class="artist-card__date">
											<?= substr($reference_datum['date_occurred'], 0, 4); ?>
											<?= $reference_datum['date_occurred'] || $reference_datum['date_ended'] ? '~' : null; ?>
											<?= substr($reference_datum['date_ended'], 0, 4); ?>
										</span>
										
										<span class="artist-card__status <?= ($reference_datum['active'] == 1 ? 'artist-card__status--active' : null); ?>"></span>
										
									</div>
									
								</div>
							</div>
						<?php
						$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
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
						
						script([
							'/lists/script-list.js',
						]);
						
						ob_start();
						?>
							<div class="module module--release">
								
								<div class="release-card__container card__container text any--flex">
									
									<a class="card__link" href="<?= '/releases/'.$reference_datum['artist']['friendly'].'/'.$reference_datum['id'].'/'.$reference_datum['friendly'].'/'; ?>"></a>
									
									<div class="release-card__artist-image lazy h5" data-src="<?= '/artists/'.$reference_datum['artist']['friendly'].'/main.small.jpg'; ?>"></div>
									
									<div class="release-card__left card--clickable any--flex">
										
										<!-- Cover -->
										<a class="release-card__cover-link h5" href="<?= $reference_datum['image']['url']; ?>" target="_blank"><img class="release-card__cover" src="<?= str_replace('.', '.thumbnail.', $reference_datum['image']['url']); ?>" /></a>
										
										<div class="release-card__stores any--weaken any--flex">
											<?php foreach([ 'amazon.png' => 'Amazon', 'cdj.gif' => 'CDJapan', 'rh.gif' => 'RarezHut' ] as $store_image => $store): ?>
											<a class="release-card__store a--inherit symbol__search" href="<?= $access_release->get_store_url($store, $reference_datum); ?>" target="_blank" style="<?= 'background-image:url(https://vk.gy/releases/'.$store_image.');'; ?>">
												<?= $store; ?>
											</a>
											<?php endforeach; ?>
											
											<label class="release-card__list list__button card--clickable input__checkbox" data-list-id="-2" data-item-id="<?= $reference_datum['id']; ?>" data-item-type="release" style="margin:0;margin-top:1rem;align-self:stretch;">
												<input class="list__choice input__choice" type="checkbox" <?= $reference_datum['is_wanted'] ? 'checked' : null; ?> />
												<span class="symbol__checkbox--unchecked" data-role="status">wishlist</span>
											</label>
											
										</div>
										
									</div>
									
									<div class="release-card__right any--flex">
										
										<div class="release-card__date h5"><?= $reference_datum['date_occurred']; ?></div>
										
										<a class="release-card__artist card--clickable artist" href="<?= '/artists/'.$reference_datum['artist']['friendly'].'/'; ?>">
											<?= lang($reference_datum['artist']['romaji'] ?: $reference_datum['artist']['name'], $reference_datum['artist']['name'], 'hidden'); ?>
										</a>
										
										<a class="release-card__title card--subject symbol__release" href="<?= '/releases/'.$reference_datum['artist']['friendly'].'/'.$reference_datum['id'].'/'.$reference_datum['friendly'].'/'; ?>">
											<?= lang($reference_datum['romaji'] ?: $reference_datum['name'], $reference_datum['name'], 'hidden'); ?>
											<?php
												if( $reference_datum['press_name'] ) {
													echo '&nbsp;<span class="a--outlined">'.lang($reference_datum['press_romaji'] ?: $reference_datum['press_name'], $reference_datum['press_name'], 'hidden').'</span>';
												}
												if( $reference_datum['type_name'] ) {
													echo '&nbsp;<span class="a--outlined">'.lang($reference_datum['type_romaji'] ?: $reference_datum['type_name'], $reference_datum['type_name'], 'hidden').'</span>';
												}
											?>
										</a>
										
										<ol class="release-card__tracklist card--subject ol--inline any--weaken">
											<?php
												foreach($reference_datum['tracklist'] as $discs) {
													foreach($discs as $disc) {
														echo $disc['disc_name'] ? '<span class="release-card__break"></span><span class="any__note">'.($disc['disc_romaji'] ?: $disc['disc_name']).'</span> ' : null;
														foreach($disc['sections'] as $section) {
															foreach($section['tracks'] as $track_num => $track) {
																?>
																	<li style="<?= $track_num == 1 ? 'counter-reset: defaultcounter;' : null; ?>">
																		<?= $track['romaji'] ? lang($track['romaji'], $track['name'], 'parentheses') : $track['name']; ?>
																	</li>
																<?php
															}
														}
													}
												}
											?>
										</ol>
										
									</div>
									
								</div>
								
							</div>
							
						<?php
						$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
					}
					
					// YouTube
					elseif($reference_datum["type"] === 'youtube' && !$ignore_references) {
						ob_start();
						?>
							<div class="module module--youtube">
								<a class="lazy video__thumbnail" data-id="<?= $reference_datum['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$reference_datum['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$reference_datum['youtube_id']; ?>" target="_blank"></a>
							</div>
						<?php
						$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
					}
					
					// Video
					elseif($reference_datum["type"] === 'video' && !$ignore_references) {
						ob_start();
						?>
							<div class="module module--youtube">
								
								<div class="video-card__container card__container text">
									
									<a class="card__link" href="<?= '/videos/'.$reference_datum['id'].'/'; ?>"></a>
									
									<a class="video-card__embed card--clickable video__thumbnail lazy" data-id="<?= $reference_datum['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$reference_datum['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$reference_datum['youtube_id']; ?>" target="_blank"></a>
									
									<div class="video-card__details">
										
										<span class="video-card__date any--weaken"><?= substr($reference_datum['date_occurred'], 0, 10); ?><br /><?= $reference_datum['num_views']; ?> views</span>
										
										<a class="video-card__name card--subject symbol__video" href="<?= '/videos/'.$reference_datum['id'].'/'; ?>"><?= $reference_datum['name']; ?></a>
										
										&nbsp;
										
										<span class="any__note"><?= $reference_datum['video_type']; ?></span>
										
										<br />
										
										<a class="video-card__artist card--clickable symbol__artist" href="<?= '/artists/'.$reference_datum['artist']['friendly'].'/'; ?>">
											<?= lang($reference_datum['artist']['romaji'] ?: $reference_datum['artist']['name'], $reference_datum['artist']['name'], 'hidden'); ?>
										</a>
										
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
				// Twitter
				$input_content = preg_replace_callback("/".$this->twitter_pattern."/", function($match) {
					return '<div class="module module--twitter"><blockquote class="twitter-tweet" data-lang="en"><a href="https://twitter.com/'.$match[1].'/status/'.$match[2].'"></a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script></div>';
				}, $input_content);
				
				// Spotify
				$input_content = preg_replace_callback("/".$this->spotify_pattern."/", function($match) {
					return '<div class="module module--spotify"><iframe src="'.$match[1].'embed/'.$match[2].'" width="300" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe></div>';
				}, $input_content);
				
				// Linkcore
				$input_content = preg_replace_callback("/".$this->linkcore_pattern."/", function($match) {
					return '<div class="module module--linkcore"><iframe src="'.$match[1].'embed/'.$match[2].'" width="300" height="600" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe></div>';
				}, $input_content);
				
				// Lnkto
				$input_content = preg_replace_callback("/".$this->lnkto_pattern."/", function($match) {
					return '<div class="module module--lnkto"><iframe src="'.$match[1].'" width="300" height="600" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe></div>';
				}, $input_content);
				
				// Image
				$input_content = preg_replace_callback("/".$this->image_pattern."/", function($match) {
					
					if( strlen($match[2]) && image_exists($match[2], $this->pdo) ) {
						
						list($width, $height) = getimagesize($match[2]);
						$image_class = $width > $height ? 'module--landscape' : 'module--portrait';
						
						$image_src = $match[2];
						$image_src = preg_replace('/'.'(^(?:https?:)?(?:\/\/)?(?:vk\.gy)?\/images\/\d+(?:-[A-z0-9-]*)?)(\.[A-z]+)$'.'/', '$1.medium$2', $image_src);
						
						return '<div class="module module--image '.$image_class.' any--weaken any--align-center"><a href="'.($match[3] ?: $match[2]).'"><img alt="'.strip_tags($match[1]).'" class="lazy" data-src="'.$image_src.'" /></a><p>'.$match[1].'</p></div>';
						
					}
					
					unset($height, $width, $image_class);
				}, $input_content);
			}
			
			return $input_content;
		}
	}
?>