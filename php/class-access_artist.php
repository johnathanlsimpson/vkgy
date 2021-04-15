<?php
	include_once("../php/include.php");
	include_once("../php/class-parse_live.php");

	class access_artist {
		private $live_parser;
		private $indexed_artists;
		
		// ======================================================
		// Connect
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
			}
			
			$this->pdo = $pdo;
			
			$this->access_image = new access_image($pdo);
			$this->access_live = new access_live($pdo);
			$this->markdown_parser = new parse_markdown($pdo);
		}
		
		
		
		// ======================================================
		// Artist card template
		// ======================================================
		function artist_card($artist, $show_title = false) {
			if(isset($artist) && is_array($artist) && !empty($artist)) {
				include("../artists/partial-card.php");
			}
		}
		
		
		
		// ======================================================
		// Clean artist websites
		// ======================================================
		function clean_websites($website_urls) {
			
			// Make sure we're working with array
			$website_urls = is_array($website_urls) ? $website_urls : explode("\n", $website_urls);
			$num_new_websites = count($website_urls);
			
			// Loop through new URLs, trim, add http, remove if blank
			if($num_new_websites) {
				for($i=0; $i<$num_new_websites; $i++) {
					$website_urls[$i] = trim($website_urls[$i]);
					
					// If trimmed URL empty, or doesn't have at least one ., remove it
					if(!$website_urls[$i] || strpos($website_urls[$i], '.') === false) {
						unset($website_urls[$i]);
					}
					
					// Otherwise, clean the URL
					else {
						
						// Make sure has protocol
						$website_urls[$i] = preg_replace('/'.'^(?!http)'.'/m', 'http://', $website_urls[$i]);
						
						// Remove archive.org prefix
						$website_urls[$i] = preg_replace('/'.'^(?:https?:\/\/)?web\.archive\.org\/web\/\d+\/'.'/m', '', $website_urls[$i]);
						
					}
				}
			}
			
			// If still have URLs, implode for output
			if(is_array($website_urls) && !empty($website_urls)) {
				$output = implode("\n", $website_urls);
			}
			
			return $output ?: null;
		}
		
		
		
		// ======================================================
		// Add artist website
		// ======================================================
		function update_url($artist_id, $url) {
		//function add_website($artist_id, $website_urls) {
			
			// Check that URL provided, make sure array if so
			$url = is_array($url) ? $url : (strlen($url) ? [ 'content' => $url ] : null);
			
			// Artist ID must be provided and URL must be array
			if(is_numeric($artist_id) && is_array($url) && !empty($url)) {
				
				// Set vars
				$url['id'] = is_numeric($url['id']) ? $url['id'] : null;
				$url['type'] = is_numeric($url['type']) ? sanitize($url['type']) : 0;
				$url['musician_id'] = is_numeric($url['musician_id']) ? sanitize($url['musician_id']) : null;
				$url['is_retired'] = $url['is_retired'] ? 1 : 0;
				
				// Do additional cleaning on URL
				$url['content'] = sanitize($url['content']);
				$url['content'] = $url['content'] ? $this->clean_websites( $url['content'] ) : null;
				
				// If at least URL supplied
				if(strlen($url['content'])) {
					
					// If ID not already set, check for it
					if(!is_numeric($url['id'])) {
						$sql_check_url = 'SELECT id FROM artists_urls WHERE artist_id=? AND content=? LIMIT 1';
						$stmt_check_url = $this->pdo->prepare($sql_check_url);
						$stmt_check_url->execute([ $artist_id, $url['content'] ]);
						$url['id'] = $stmt_check_url->fetchColumn();
					}
					
					// Build update query
					if(is_numeric($url['id'])) {
						$sql_url = 'UPDATE artists_urls SET content=?, type=?, artist_id=?, musician_id=?, is_retired=? WHERE id=? LIMIT 1';
						$values_url = [ $url['content'], $url['type'], $artist_id, $url['musician_id'], $url['is_retired'], $url['id'] ];
					}
					
					// Build add query
					else {
						$sql_url = 'INSERT INTO artists_urls (content, type, artist_id, musician_id, is_retired, user_id) VALUES (?, ?, ?, ?, ?, ?)';
						$values_url = [ $url['content'], $url['type'], $artist_id, $url['musician_id'], $url['is_retired'], $_SESSION['user_id'] ];
					}
					
					// Run query
					if(strlen($sql_url) && is_array($values_url) && count($values_url) === substr_count($sql_url, '?')) {
						$stmt_url = $this->pdo->prepare($sql_url);
						if($stmt_url->execute($values_url)) {
							return true;
						}
					}
					
				}
				
				// If ID supplied but no URL, delete existing record
				elseif(!strlen($url['content']) && is_numeric($url['id'])) {
					$sql_delete_url = 'DELETE FROM artists_urls WHERE id=? LIMIT 1';
					$stmt_delete_url = $this->pdo->prepare($sql_delete_url);
					if($stmt_delete_url->execute([ $url['id'] ])) {}
				}
				
			}
		}
		
		
		
		// ======================================================
		// Get related artists
		// ======================================================
		function get_related_artists($artist_id, $type = null) {
			$related_artists = [];
			
			// Set input to array and clean duplicate values
			if(is_array($artist_id) || strlen($artist_id)) {
				$artist_ids = is_array($artist_id) ? $artist_id : [ $artist_id ];
			}
			if(is_array($artist_ids) && !empty($artist_ids)) {
				foreach($artist_ids as $key => $id) {
					if(!is_numeric($id)) {
						unset($artist_ids[$key]);
					}
				}
			}
			
			if(is_array($artist_ids) && !empty($artist_ids)) {
				
				// Get label info for provided artist IDs
				$sql_artist_labels = 'SELECT label_history FROM artists WHERE '.substr(str_repeat('id=? OR ', count($artist_ids)), 0, -4);
				$stmt_artist_labels = $this->pdo->prepare($sql_artist_labels);
				$stmt_artist_labels->execute($artist_ids);
				$rslt_artist_labels = $stmt_artist_labels->fetchAll();
				
				// Use the label info
				if($type != 'tags') {
					if(is_array($rslt_artist_labels) && !empty($rslt_artist_labels)) {
						
						// Put all label info in one string chunk
						foreach($rslt_artist_labels as $result) {
							$label_text .= $result['label_history'];
						}
						
						// Search for IDs within the string chunk, then cycle through each ID and find artists that are also on that label
						preg_match_all('/'.'\{(\d+)\}'.'/', $label_text, $possible_labels, PREG_PATTERN_ORDER);
						
						if(is_array($possible_labels[1]) && !empty($possible_labels[1])) {
							$possible_labels = array_unique($possible_labels[1]);
							
							foreach($possible_labels as $label_id) {
								
								// Merge the newly found artists with the output array (we'll clean later)
								$related_artists = array_merge($related_artists, $this->access_artist([ 'label_id' => $label_id, 'get' => 'id' ]));
							}
						}
					}
					
				}
				
				// Get tag info for provided artist IDs
				if($type != 'label') {
					
					$sql_artist_tags = '
						SELECT a.artist_id 
						FROM artists_tags 
						LEFT JOIN artists_tags a ON a.tag_id=artists_tags.tag_id 
						WHERE 
							( (artists_tags.mod_score>-1 AND artists_tags.score>0) OR (artists_tags.mod_score=1) )
							AND ('.substr(str_repeat('artists_tags.artist_id=? OR ', count($artist_ids)), 0, -4).')';
					$stmt_artist_tags = $this->pdo->prepare($sql_artist_tags);
					$stmt_artist_tags->execute($artist_ids);
					$rslt_artist_tags = $stmt_artist_tags->fetchAll();
					
					// Use the artist tag info
					if(is_array($rslt_artist_tags) && !empty($rslt_artist_tags)) {
					
						foreach($rslt_artist_tags as $result) {
							$related_artist_ids[] = $result['artist_id'];
						}
						
						$related_artist_ids = array_unique($related_artist_ids);
						
						// Get artist info, merge with what we have
						$related_artists = array_merge($related_artists, $this->access_artist([ 'id' => $related_artist_ids, 'get' => 'id' ]));
					}
				}
				
				return $related_artists;
			}
		}
		
		
		
		// ======================================================
		// Format label history
		// ======================================================
		function format_label_history($label_history) {
			$access_label = new access_label($this->pdo);
			
			if(!empty($label_history)) {
				$label_history = str_replace(["\r\n", "\r", "&#62;"], ["\n", "\n", ">"], $label_history);
				$periods = explode("\n", $label_history);
				
				if(is_array($periods)) {
					foreach($periods as $period_key => $period) {
						$organizations = explode(", ", $period);
						
						if(is_array($organizations)) {
							foreach($organizations as $organization_key => $organization) {
								$companies = explode(" > ", $organization);
								
								if(is_array($companies)) {
									foreach($companies as $company_key => $company) {
										
										$label_pattern = "((?:^|\s)\{(\d+)\}(?:\[([^\(]+?)(?: \((.+?)\))?\])?)(?:.*)?";
										$label_not_in_db_pattern = "(?:^)((?:(?! \{).)+)(?: \((?!as )([^\(\)]*(?=&)[^\(\)]+(?=;)[^\(\)]+)\))?";
										$note_pattern = " \((.+?)(?=(?:(?<!\?)\) \(|\)$))\)";
										
										if(preg_match_all("/".$label_pattern."/", $company, $matches, PREG_SET_ORDER)) {
											if(is_numeric($matches[0][2])) {
												$label = $access_label->access_label(["id" => $matches[0][2], "get" => "name"]);
												$name = $matches[0][4] ?: ($matches[0][3] ?: $label["name"]);
												$romaji = $matches[0][3] && $matches[0][4] ? $matches[0][3] : $label["romaji"];
												$quick_name = $romaji ?: $name;
												$company = str_replace($matches[0][1], "", $company);
												
												$tmp_history[$period_key][$organization_key][$company_key] = ["id" => $label["id"], "name" => $name, "romaji" => $romaji, "quick_name" => $quick_name, "friendly" => $label["friendly"]];
											}
										}
										elseif(preg_match_all("/".$label_not_in_db_pattern."/", $company, $matches, PREG_SET_ORDER)) {
											if(!empty($matches[0][1])) {
												$name = $matches[0][2] ?: $matches[0][1];
												$romaji = $matches[0][2] ? $matches[0][1] : null;
												$quick_name = $romaji ?: $name;
												$company = str_replace($matches[0][1].($matches[0][2] ? " (".$matches[0][2].")" : ""), "", $company);
												
												$tmp_history[$period_key][$organization_key][$company_key] = ["name" => $name, "romaji" => $romaji, "quick_name" => $quick_name];
											}
										}
										else {
											$tmp_history[$period_key][$organization_key][$company_key]["notes"][] = "(independent)";
										}
										
										if(preg_match_all("/".$note_pattern."/", $company, $matches, PREG_SET_ORDER)) {
											if(is_array($matches)) {
												foreach($matches as $match) {
													if(!empty($match[1])) {
														$tmp_history[$period_key][$organization_key][$company_key]["notes"][] = $match[1];
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				
				return $tmp_history;
			}
		}
		
		
		
		// ======================================================
		// Get positions
		// ======================================================
		public $positions = [
			1 => 'Vocals',
			2 => 'Guitar',
			3 => 'Bass',
			4 => 'Drums',
			5 => 'Key',
			6 => 'Other',
			7 => 'Staff',
		];
		
		
		
		// ======================================================
		// Cleaning pronunciation string
		// ======================================================
		function clean_pronunciation($input) {
			
			$input = html_entity_decode($input);
			if(preg_match('/'.'[ぁ-んァ-ン]'.'/u', $input)) {
				$input = mb_convert_kana($input, 'sKC', 'utf-8');
				$input = preg_replace('/'.'\+|\.|:'.'/', ' ', $input);
				$input = preg_replace('/'.'\s+'.'/', ' ', $input);
				$input = trim($input);
				$input = str_replace(' ', '・', $input);
				$input = sanitize($input);
			}
			else {
				$input = null;
			}
			
			$output = strlen($input) ? $input : null;
			return $output;
		}
		
		
		
		// ======================================================
		// Artist biography types
		// ======================================================
		public $artist_bio_types = [
			0  => "cancellation", // live or release (etc) cancellation
			1  => "activity",     // activity change
			2  => "live",         // live event or tour
			3  => "release",      // release
			4  => "name",         // band name change
			5 => 'end',           // disbandment
			6  => "media",        // media, fanclub, etc
			7  => "other",        // ?:?
			8  => "label",        // label change or start
			9  => "lineup",       // notes current lineup (not for lineup change)
			10 => 'start',        // formation
			11 => "member",       // member change, member name change, etc.
			12 => "setlist",      // setlist,
			13 => "trouble",      // death, injury, arrest, etc
			14 => "schedule",     // non-special live schedule
			15 => "s",            // duplicate for schedule
			16 => 'note',         // off-topic note
		];
		
		
		
		// ======================================================
		// Validate artist biography
		// ======================================================
		function validate_bio($artist_id, $content) {
			if(is_numeric($artist_id) && !empty($content)) {
				
				$content = str_replace(["\r\n", "\r"], "\n", $content);
				$break_pattern = "\n\n(?=(?:\d{4}-)?(?:\d{2}-)?\d{2} )";
				$bio_lines = preg_split("/".$break_pattern."/", $content);
				
				// Get list of all of artist's lives; we'll use it later to make sure we're not parsing a live twice
				$all_current_lives = $this->access_live->access_live([ 'artist_id' => $artist_id, 'get' => 'name', 'keys' => 'date' ]);
				
				if(is_array($bio_lines)) {
					foreach($bio_lines as $line) {
						
						$line_type = [];
						
						// 2020-12-31: Replace -formation and -disbandment with -start and -end, respectively
						$line = preg_replace_callback('/'.'(-|,)(formation|disbandment)([a-z\,]*?)$'.'/', function($matches) {
							return $matches[1].($matches[2] === 'formation' ? 'start' : 'end').$matches[3];
						}, $line);
						
						// Auto-fill date
						$date_pattern = "^(?:(\d{4})-?)?(?:(\d{2})-?)?(\d{2}) ";
						if(preg_match("/".$date_pattern."/", $line, $match)) {
							if(is_array($match)) {
								$this_date["y"] = $match[1];
								$this_date["m"] = $match[2];
								$this_date["d"] = $match[3];
								foreach($this_date as $key => $value) {
									if(empty($value)) {
										$this_date[$key] = $prev_date[$key] ?: ($key === "y" ? "0000" : "00");
									}
									$prev_date[$key] = $this_date[$key];
								}
							}
							$date = implode("-", $this_date);
							$line = preg_replace("/".$date_pattern."/", "", $line);
						}
						
						// Validate manually-added event type
						$type_pattern = " -([\w,]+)$";
						if(preg_match("/".$type_pattern."/", $line, $match)) {
							if(is_array($match) && !empty($match[1])) {
								$line = preg_replace("/".$match[0]."$"."/", "", $line);
								$matches = explode(",", $match[1]);
								if(is_array($matches)) {
									foreach($matches as $type) {
										if(in_array($type, $this->artist_bio_types)) {
											$line_type[] = array_flip($this->artist_bio_types)[$type];
										}
									}
								}
							}
						}
						
						// Or, guess event type
						else {
							$tag_patterns = [
								//"formation"    => "\bforms\b|\bformed\b|\bformation\b",
'start' => '\bforms\b|\bformed\b|\bformation\b|revive|restart|resume',
								"label"        => "\blabels?\b|\bgraduates?\b|\bsublabel\b",
								"live"         => "\blive\b|\btour\b|\bevent\b|\boneman\b|\btwoman\b|\bthreeman\b|\bfourman\b",
								"name"         => "\bchanges?\b.*\bnames?\b",
								//"activity"     => "(?:\bpauses?\b|\bactivit(?:y|ies)\b|\bfreezes?\b)",
'activity' => 'moves to|formal band|area of activity|changes to|major|one(?:-| )(?:day|night)',
								//"disbandment"  => "\bdisbands\b",
'end' => '\bdisbands\b|breaks? up|(?:pauses?|ends?) activit',
								"lineup"       => "lineup| [\/\,] [VoGuBaDrKy]{1,2}\.| G: | Gu: ",
								"member"       => "\bmember\b|\bjoins?\b|\bsecedes?\b|\bbegins support\b|\bas support\b|\bon support\b|\bvocalist\b|\bguitarist\b|\bbassist\b|\bdrummer\b",
								"release"      => "\breleases?\b",
								"cancellation" => "\bcancel(?:s|led)?\b",
								"media"        => "\bTV\b|\bradio\b|\bmagazine\b|\btheme\b|\bfanclub\b|\bfan\b",
								"trouble"      => "\bdeath\b|\bdies\b|\binjur|\barrest|\bjail\b|\bscandal|\bcancel",
								"setlist"      => "setlist| \/ \d\.",
								"schedule"     => "^[A-z0-9 ]+$",
								'note'         => '^Note: ',
							];
							foreach($tag_patterns as $type => $pattern) {
								if(preg_match("/".$pattern."/i", $line)) {
									$line_type[] = array_flip($this->artist_bio_types)[$type];
								}
							}
							if(empty($line_type)) {
								$line_type[] = array_search("other", $this->artist_bio_types);
							}
						}
						
						// Activity area
						if(is_array($line_type) && (in_array(10, $line_type) || in_array(1, $line_type))) {
							$active_area_pattern = '(in|to) ([A-z0-9&#;]+)(?: \(([A-z0-9&#;]+)\))?';
							
							if(preg_match('/'.$active_area_pattern.'/', $line, $active_area_match)) {
								if(strlen($active_area_match[3])) {
									$area_name = $active_area_match[3];
									$area_romaji = $active_area_match[2];
								}
								elseif(strlen($active_area_match[2])) {
									$area_name = $active_area_match[2];
								}
								
								$sql_check_area = 'SELECT * FROM areas WHERE name=? OR romaji=? LIMIT 1';
								$stmt_check_area = $this->pdo->prepare($sql_check_area);
								$stmt_check_area->execute([ $area_name, ($area_romaji ?: $area_name) ]);
								$rslt_check_area = $stmt_check_area->fetch();
								
								if(is_numeric($rslt_check_area['id'])) {
									$line = str_replace($active_area_match[0], $active_area_match[1].' '.$rslt_check_area['romaji'].' ('.$rslt_check_area['name'].')', $line);
									$area_id = $rslt_check_area['id'];
								}
							}
						}
						
						// Note
						if(is_array($line_type) && in_array(array_search('note', $this->artist_bio_types), $line_type)) {
							$note_stem_pattern = '^[Nn]ote: ';
							
							$line = preg_replace('/'.$note_stem_pattern.'/', '', $line);
						}
						
						//
						// Name pronunciation
						//
						
						// If line type 4 (name change) or 10 (formation), look for pronunciation, clean, return
						if(is_array($line_type) && (in_array(10, $line_type) || in_array(4, $line_type))) {
							
							$pronunciation_pattern = '(?:\(\d+\)|\/|\]) \(([&#; A-z0-9\+\:\.]+)\)[ \.]';
							$katakana_pattern = '^[ぁ-んァ-ン 　\+:\.・]+$';
							
							if(preg_match('/'.$pronunciation_pattern.'/', $line, $pronunciation_match)) {
								if(strlen($pronunciation_match[1])) {
									$pronunciation_string = html_entity_decode($pronunciation_match[1], ENT_QUOTES, 'UTF-8');
									
									if(preg_match('/'.$katakana_pattern.'/u', $pronunciation_string)) {
										
										$pronunciation = $this->clean_pronunciation($pronunciation_string);
										
									}
								}
							}
						}
						
						//
						// Set alternate names
						//
						
						// If line is type 4 (name change), get possible display name
						if(is_array($line_type) && in_array(4, $line_type)) {
							
							// If line is probably *band* name change
							if(strpos($line, 'their name') !== false || strpos($line, 'its name') !== false) {
								
								// Split line at 'changes thier name to', since we only care about the newer name
								$temp_line = explode('name to', $line);
								$possible_display_name = strlen($temp_line[1]) ? $temp_line[1] : null;
								
							}
							
						}
						
						// If line is type 10 (formation), get possible display name
						if(is_array($line_type) && in_array(10, $line_type)) {
							
							$possible_display_name = $line;
							
						}
						
						// If possible display name from one of above, continue
						if(strlen($possible_display_name)) {
							
							// Modified version of artist regex; requires display name and captures only display name + pronunciation
							$display_name_pattern = '(?<=[^\w\/]|^)(?:\(\d+\))?\/(?! )(?:[^\/\n]+)(?! )\/(?:\[([^\[\]\/\n]+)\])(?=\W|$)(?: \(([&#; A-z0-9\+\:\.]+)\))?[ \.]|$';
							
							// If second chunk of line contains a display name, then we assume that the artist change their name to something which will change again, so we want this to be in the alternate names table
							if(preg_match('/'.$display_name_pattern.'/', $possible_display_name, $new_name_match)) {
								
								// If display name returned
								if(strlen($new_name_match[1])) {
									
									$display_name = str_replace(['\\[', '&#92;&#91;', '\\]', '&#92;&#93;'], ['[', '[', ']', ']'], $new_name_match[1]);
									$display_romaji = null;
									$display_pronunciation = $new_name_match[2] ? $this->clean_pronunciation($new_name_match[2]) : null;
									
									// If display name contains JP and romaji
									if(preg_match('/'.'(.+?) \((.+?)\)'.'/', $display_name, $display_name_chunks)) {
										$display_name = $display_name_chunks[2];
										$display_romaji = $display_name_chunks[1];
									}
									$display_friendly = friendly( $display_romaji ?: $display_name );
									
								}
								
							}
							
						}
						
						// Set up live parser
						if(!is_object($this->live_parser)) {
							$this->live_parser = new parse_live($this->pdo);
						}
						
						$parsed_live = null;
						$note = null;
						
						// Try to catch lines that should be tagged -schedule
						if(
							is_array($line_type)
							&&
							in_array(array_search('live', $this->artist_bio_types), $line_type)
						) {
							// Patern: blah blah at Shinuku (新宿) HOLIDAY (ホリデー).
							$pattern_schedule_ref_in_live = 'at(?: ([\-\w\. ]+?)(?:\(([\-\w\. &#;]+)\))?){1,2}\.?$';
							
							// If -live entry potentially has livehouse mentioned at end, grab that
							if(preg_match('/'.$pattern_schedule_ref_in_live.'/', $line, $schedule_match)) {
								$num_schedule_match = count($schedule_match) - 1;
								
								// EN: area? + livehouse
								if($num_schedule_match === 1) {
									$possible_livehouses[] = $schedule_match[1];
									$possible_livehouses[] = strstr($num_schedule_match[1], ' ');
								}
								// EN: area? + livehouse / JA: area? + livehouse
								elseif($num_schedule_match === 2) {
									$possible_livehouses[] = $schedule_match[1];
									$possible_livehouses[] = strstr($num_schedule_match[1], ' ');
									$possible_livehouses[] = $schedule_match[2];
								}
								// EN: area / JA: area / EN: livehouse
								elseif($num_schedule_match === 3) {
									$possible_livehouses[] = $schedule_match[3];
								}
								// EN: area / JA: area / EN: livehouse / JA: livehouse
								elseif($num_schedule_match === 4) {
									$possible_livehouses[] = $schedule_match[3];
									$possible_livehouses[] = $schedule_match[4];
								}
								
								// Try each possible livehouse name in livehouse parser until we get a match
								// $parsed_live is used in returned data
								if(is_array($possible_livehouses) && !empty($possible_livehouses)) {
									foreach($possible_livehouses as $possible_livehouse) {
										if(is_array($parsed_live)) {
											break;
										}
										else {
											$parsed_live = $this->live_parser->parse_raw_input($possible_livehouse, $date, $artist_id, $all_current_lives);
										}
									}
								}
								
								if(is_array($parsed_live) && !empty($parsed_live)) {
									$pattern_unnecessary_entry = '^[Ll]ive (?:is |was )?held '.preg_quote($schedule_match[0]).'$';
									
									// If whole entry is just 'live at xxx.', change to -schedule tag
									if(preg_match('/'.$pattern_unnecessary_entry.'/', $line)) {
										$line = ($parsed_live['livehouse']['area_romaji'] ?: $parsed_live['livehouse']['area_name']).' '.($parsed_live['livehouse']['romaji'] ?: $parsed_live['livehouse']['name']);
										$line_type = [ array_search('schedule', $this->artist_bio_types) ];
										$note = 'This was tagged -live, but it it looks like a -schedule entry. Add further information to retain the -live tag.';
									}
									else {
										// If found a corresponding livehouse, make sure the reference is correct, note that live was added
										$line = str_replace(
											$schedule_match[0],
											'at '.($parsed_live['livehouse']['area_romaji'] ?: $parsed_live['livehouse']['area_name']).($parsed_live['livehouse']['area_romaji'] ? ' ('.$parsed_live['livehouse']['area_name'].')' : null).' '.($parsed_live['livehouse']['romaji'] ?: $parsed_live['livehouse']['name']).($parsed_live['livehouse']['romaji'] ? ' ('.$parsed_live['livehouse']['name'].')' : null).'.',
											$line
										);
										
									}
								}
								
								unset($possible_livehouses);
							}
						}
						
						// Schedule
						if(
							is_array($line_type)
							&&
							(in_array(array_search('schedule', $this->artist_bio_types), $line_type) || in_array(array_search('s', $this->artist_bio_types), $line_type))
						) {
							
							$parsed_live = $this->live_parser->parse_raw_input($line, $date, $artist_id, $all_current_lives);
							
							if(is_array($parsed_live) && !empty($parsed_live)) {
								$line = ($parsed_live['livehouse']['area_romaji'] ?: $parsed_live['livehouse']['area_name']).' '.($parsed_live['livehouse']['romaji'] ?: $parsed_live['livehouse']['name']);
								
								// If user supplied other bands on that live date, show them so they can confirm that those bands were found in DB
								// Btw, skip first element of parsed lineup since it will always be the artist being edited
								if(is_array($parsed_live['lineup'][1]) || is_array($parsed_live['additional_lineup'])) {
									$note = 'Also linked: ';
									
									if(is_array($parsed_live['lineup']) && !empty($parsed_live['lineup'])) {
										foreach($parsed_live['lineup'] as $parsed_lineup_key => $parsed_lineup) {
											if(is_array($parsed_lineup) && $parsed_lineup_key > 0) {
												$note .= '<a class="artist" data-name="'.$parsed_lineup['name'].'" href="/artists/'.$parsed_lineup['friendly'].'/" target="_blank">'.lang($parsed_lineup['romaji'] ?: $parsed_lineup['name'], $parsed_lineup['name'], 'hidden').'</a>, ';
											}
										}
									}
									
									if(is_array($parsed_live['additional_lineup']) && !empty($parsed_live['additional_lineup'])) {
										foreach($parsed_live['additional_lineup'] as $parsed_additional_lineup) {
											$note .= $parsed_additional_lineup.', ';
										}
									}
									
									$note = substr($note, 0, -2);
								}
								
								$type_key = array_search(15, $line_type);
								if(is_numeric($type_key)) {
									$line_type[$type_key] = 14;
								}
							}
							else {
								$note = 'Unable to add to schedule. Livehouse may be missing from the database, or line may be tagged improperly. Switching tag to -live.';
								
								$type_key = array_search(14, $line_type);
								if(is_numeric($type_key)) {
									unset($line_type[$type_key]);
								}
								
								$type_key = array_search(15, $line_type);
								if(is_numeric($type_key)) {
									unset($line_type[$type_key]);
								}
								
								$line_type[] = array_search('live', $this->artist_bio_types);
							}
						}
						
						// Disbandment
						if(
							is_array($line_type)
							&&
							in_array(array_search('disbandment', $this->artist_bio_types), $line_type)
						) {
							$tmp_line = preg_replace('/'.'(?:\(\d+\))?(?:\/[^\/]+\/)?(?:\[[^\[\]]+\])?'.'/', '', $line);
							$tmp_line = strstr($tmp_line, ' ');
							
							if($tmp_line && strlen($tmp_line) > 30) {
								$note = 'Disbandment statements are usually short (“XXX disbands”). If appropriate, consider using a different tag here and making a separate, shorter -disbandment entry.';
							}
						}
						
						// Activity & disbandment -> disbandment
						if(
							is_array($line_type)
							&&
							$line_type[0] === array_search('activity', $this->artist_bio_types)
							&&
							$line_type[1] === array_search('disbandment', $this->artist_bio_types)
						) {
							unset($line_type[0]);
						}
						
						// Warn if many tags
						if(
							is_array($line_type)
							&&
							count($line_type) > 3
						) {
							$note = 'This entry has many auto tags; if appropriate, consider manually setting just one or two tags, or separating into multiple entries.';
						}
						
						// Clean line
						$line = sanitize($line);
						$line = str_replace(["&#12304;setlist&#12305; ", "&#12304;lineup&#12305; "], "", $line);
						$line = preg_replace("/"." &#47; (\d+\. )"."/", "\n$1", $line);
						$line_type = array_filter(array_unique($line_type));
						$line_type = is_array($line_type) ? $line_type : ["0"];
						
						// Output
						$output[] = [
							"content" => $line,
							"date_occurred" => $date,
							"type" => "(".implode(")(", $line_type).")",
							"artist_id" => $artist_id,
							"user_id" => $_SESSION["user_id"],
							'area_id' => $area_id,
							"parsed_live" => $parsed_live,
							"note" => $note ? '<div class="any--weaken symbol__help">'.$note.'</div>' : null,
							'pronunciation' => $pronunciation,
							'display_name' => strlen($display_name) ? [ 'name' => $display_name, 'romaji' => $display_romaji, 'friendly' => $display_friendly, 'pronunciation' => $display_pronunciation ] : null
						];
						
						// Unset some stuff
						unset($parsed_live, $note, $area_id, $area_name, $area_romaji, $pronunciation);
						
						// Unset display name stuff
						unset($possible_display_name, $display_name, $display_romaji, $display_friendly, $display_pronunciation, $tmp_line);
					}
				}
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Get history
		// ======================================================
		function get_history($artist_id) {
			$access_release = new access_release($this->pdo);
			
			$history = [];
			
			if(is_numeric($artist_id)) {
				// Get basic artist bio
				$sql_bio = "SELECT * FROM artists_bio WHERE artist_id=? ORDER BY date_occurred ASC";
				$stmt_bio = $this->pdo->prepare($sql_bio);
				$stmt_bio->execute([$artist_id]);
				
				foreach($stmt_bio->fetchAll() as $bio) {
					if($bio["type"] !== "(14)") {
						$history[] = [
							"date_occurred" => $bio["date_occurred"],
							"content" => $bio["content"],
							"type" => array_filter(explode("(", str_replace(")", "", $bio["type"])))
						];
					}
				}
				
				// Add releases to artist bio
				$releases = $access_release->access_release(["artist_id" => $artist_id, "get" => "list"]);
				if(is_array($releases)) {
					foreach($releases as $release) {
						$history[] = [
							"date_occurred" => $release["date_occurred"],
							"content" => array_merge($release, [ "url" => '/releases/'.$release["artist"]["friendly"].'/'.$release["id"].'/'.$release["friendly"].'/' ]),
							"type" => ["3", "is_uneditable"]
						];
					}
				}
			}
			
			// Sort by date ascending, then friendly name ascending
			usort($history, function($a, $b) {
				
				// If both parts being compared are on same date and have medium (are releases), then sort by friendly
				if(
					$a['date_occurred'] === $b['date_occurred'] &&
					is_array($a['content']) && is_array($b['content']) &&
					$a['content']['medium'] && $b['content']['medium']
				) {
					return $a['content']['friendly'] <=> $b['content']['friendly'];
				}
				
				// For everything else, sort by date occurred
				else {
					return $a['date_occurred'] <=> $b['date_occurred'];
				}
			});
			
			return $history;
		}
		
		
		
		// ======================================================
		// Core function
		// ======================================================
		function access_artist($args = []) {
			
			// SELECT
			$sql_select = [];
			switch($args["get"]) {
				case "all"         : array_push($sql_select, "artists.*", "COALESCE(artists.romaji, artists.name) AS quick_name"); break;
				case "prev"        :
				case "next"        :
				case "basics"      :
				case "name"        : array_push($sql_select, "artists.id", "artists.name", "artists.romaji", "COALESCE(artists.romaji, artists.name) AS quick_name", "artists.friendly"); break;
				case "id"          : array_push($sql_select, "artists.id"); break;
				case "list"        : array_push($sql_select, "artists.id", "artists.name", "artists.romaji", "COALESCE(artists.romaji, artists.name) AS quick_name", "artists.friendly", "artists.label_history"); break;
				case "artist_list" : array_push($sql_select, "artists.id", "artists.name", "artists.romaji", "COALESCE(artists.romaji, artists.name) AS quick_name", "artists.friendly", "artists.is_exclusive"); break;
				case 'profile'     : array_push($sql_select, 'artists.id', 'artists.name', 'artists.romaji', 'artists.friendly', 'artists.description', 'artists.pronunciation'); break;
			}
			
			if($args['get'] === 'basics') {
				$sql_select[] = 'artists.active';
				$sql_select[] = 'artists.date_occurred';
				$sql_select[] = 'artists.date_ended';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.name) AS tag_names';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.romaji) AS tag_romajis';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.friendly) AS tag_friendlys';
			}
			
			if( $args['get'] === 'basics' || $args['get'] === 'all' ) {
				$sql_select[] = 'GROUP_CONCAT(artists_years.year) AS years_active';
				$sql_group[] = 'artists.id';
			}
			
			if($args['get'] === 'artist_list') {
				$sql_select[] = 'artists.description';
				$sql_select[] = 'artists.pronunciation';
				$sql_select[] = 'artists.active';
				/*$sql_select[] = 'GROUP_CONCAT(tags_artists.name) AS tag_names';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.romaji) AS tag_romajis';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.friendly) AS tag_friendlys';*/
				
				// Approximate formation year
				$sql_select[] = 'MIN(date_occurred.year) AS date_occurred';
				$sql_group[] = 'artists.id';
				$sql_join[] = 'LEFT JOIN artists_years date_occurred ON date_occurred.artist_id=artists.id';
				
				
				
				///
				// The below makes the query too slow and I haven't figured out a workaround yet so let's just disable formation sorting for now
				//
				//
				
				
				/*// Approximate formation date
				$sql_select[] = 'MIN(artists_bio.date_occurred) AS date_formed';
				$sql_join[] = 'LEFT JOIN artists_bio ON artists_bio.artist_id=artists.id AND artists_bio.type LIKE CONCAT("%(10)%")';
				
				
				
				
				//$sql_select[] = 'MIN(date_occurred.year) AS aa';
				$sql_select[] = 'IF( MIN(artists_bio.date_occurred)>1, MIN(artists_bio.date_occurred), IF( MIN(date_occurred.year)>1, CONCAT(MIN(date_occurred.year),"-00-00"), "0000-00-00" ) ) AS date_formed';*/
				
			}
			
			// Select: count
			if( $args['get'] === 'count' ) {
				$sql_select[] = 'COUNT(DISTINCT artists.id) AS num_artists';
			}
			
			//
			// WHERE
			//
			$sql_where = [];
			$sql_values = [];
			
			// By name: check artist table and name-change table at the same time, grab IDs, continue
			if(strlen($args['name']) ) {
				
				// Set some variables
				$converted_name = html_entity_decode($args['name'], ENT_QUOTES, 'utf-8');
				$friendlied_name = friendly($args['name']);
				$sanitized_name = sanitize($args['name']);
				$pronunciation = $this->clean_pronunciation($converted_name);
				$name_search_type = $args['exact_name'] ? 'exact' : ( $args['fuzzy'] && mb_strlen( $converted_name ) > 2 ? 'fuzzy' : 'default' );
				
				// Exact search
				if($name_search_type === 'exact') {
					$sql_name = 'SELECT * FROM ( (SELECT id, "" AS display_name, "" AS display_romaji FROM artists WHERE name=? OR romaji=?) UNION ALL (SELECT artist_id AS id, name AS display_name, romaji AS display_romaji FROM artists_names WHERE name=? OR romaji=?) ) names';
					$values_name = [ $sanitized_name, $sanitized_name, $sanitized_name, $sanitized_name ];
				}
				
				// Fuzzy search
				else if($name_search_type === 'fuzzy') {
					$sql_name = '
						SELECT * FROM ( 
							( 
								SELECT id, "" AS display_name, "" AS display_romaji 
								FROM artists 
								WHERE friendly=? OR name=? OR romaji=? OR pronunciation=? OR name LIKE CONCAT("%", ?, "%") OR romaji LIKE CONCAT("%", ?, "%") 
							)
							UNION ALL 
							(
								SELECT artist_id AS id, name AS display_name, romaji AS display_romaji 
								FROM artists_names 
								WHERE friendly=? OR name=? OR romaji=? OR pronunciation=? OR name LIKE CONCAT("%", ?, "%") OR romaji LIKE CONCAT("%", ?, "%")
							) 
						) names';
					$values_name = [ 
						$friendlied_name, $sanitized_name, $sanitized_name, $pronunciation, $sanitized_name, $sanitized_name,
						$friendlied_name, $sanitized_name, $sanitized_name, $pronunciation, $sanitized_name, $sanitized_name
					];
				}
				
				// Generic search
				else {
					$sql_name = '
						SELECT * FROM ( 
							( 
								SELECT id, "" AS display_name, "" AS display_romaji 
								FROM artists 
								WHERE friendly=? OR name=? OR romaji=? OR pronunciation=?
							)
							UNION ALL
							(
								SELECT artist_id AS id, name AS display_name, romaji AS display_romaji 
								FROM artists_names 
								WHERE friendly=? OR name=? OR romaji=? OR pronunciation=?
							)
						) names';
					$values_name = [ 
						$friendlied_name, $sanitized_name, $sanitized_name, $pronunciation,
						$friendlied_name, $sanitized_name, $sanitized_name, $pronunciation
					];
				}
				
				// Run query to get IDs of matching artists
				$stmt_name = $this->pdo->prepare($sql_name);
				$stmt_name->execute($values_name);
				$rslt_name = $stmt_name->fetchAll();
				
				// Pass results as IDs
				if(is_array($rslt_name) && !empty($rslt_name)) {
					
					// Unset name arg, change to ID
					unset($args['name']);
					$args['ids'] = [];
					
					// Add IDs to arg
					foreach($rslt_name as $name) {
						$args['ids'][] = $name['id'];
						$num_duplicates[$name['id']]++;
						
						// If display name (i.e. name-change result), save for later
						if(strlen($name['display_name'])) {
							$display_names[$name['id']][] = [ 'display_name' => $name['display_name'], 'display_romaji' => $name['display_romaji'] ];
						}
					}
				}
				
				// If no results returned, unset sql_select so query stops
				else {
					unset($sql_select);
				}
				
			}
			
			// [PRE-SELECT] Tag
			if(is_array($args["tags"]) && !empty($args["tags"])) {
				foreach($args["tags"] as $i => $tag) {
					if(strlen($tag) > 0) {
						$sql_pre = "SELECT artists_tags.artist_id AS id FROM tags_artists LEFT JOIN artists_tags ON artists_tags.tag_id=tags_artists.id WHERE ((artists_tags.score>0 AND artists_tags.mod_score>-1) OR artists_tags.mod_score=1) AND tags_artists.friendly=? GROUP BY artists_tags.artist_id";
						$stmt_pre = $this->pdo->prepare($sql_pre);
						$stmt_pre->execute([ friendly($tag) ]);
						$rslt_pre = $stmt_pre->fetchAll();
						
						if(is_array($rslt_pre) && !empty($rslt_pre)) {
							foreach($rslt_pre as $key => $rslt) {
								$rslt_pre[$key] = $rslt["id"];
							}
							
							if($i === 0) {
								$tmp_ids = $rslt_pre;
							}
							else {
								foreach($tmp_ids as $key => $id) {
									if(!in_array($id, $rslt_pre)) {
										unset($tmp_ids[$key]);
									}
								}
							}
						}
					}
				}
				
				if(is_array($tmp_ids) && !empty($tmp_ids)) {
					foreach($tmp_ids as $id) {
						$args["id"][] = $id;
					}
				}
				else {
					unset($args["tags"], $args["get"], $sql_select);
				}
			}
			
			if(!is_array($args["id"]) && strlen($args["id"]) > 0) {
				if(is_numeric($args["id"])) {
					$sql_where[] = "artists.id=?";
					$sql_values[] = $args["id"];
				}
			}
			if(is_array($args["id"]) && !empty($args["id"])) {
				$sql_where[] = "artists.id=".implode(" OR artists.id=", array_fill(0, count($args["id"]), "?"));
				$sql_values = is_array($sql_values) ? array_merge($sql_values, array_values($args["id"])) : array_values($args["id"]);
			}
			if(is_array($args["ids"]) && !empty($args["ids"])) {
				$sql_where[] = "artists.id=".implode(" OR artists.id=", array_fill(0, count($args["ids"]), "?"));
				$sql_values = is_array($sql_values) ? array_merge($sql_values, array_values($args["ids"])) : array_values($args["ids"]);
			}
			if($args["letter"]) {
				$args["letter"] = sanitize($args["letter"]);
				$args["letter"] = (strlen($args["letter"]) === 1 ? $args["letter"] : "-");
				
				if(preg_match("/"."[A-z]"."/", $args["letter"])) {
					$sql_where[] = "artists.friendly LIKE CONCAT(?, '%')";
					$sql_values[] = $args["letter"];
				}
				else {
					$sql_where[] = "artists.friendly REGEXP '^[^A-z]'";
				}
			}
			if($args["friendly"]) {
				if($args["get"] === "prev") {
					$sql_where[] = "artists.friendly < ? AND affiliation < '3'";
					$sql_order[] = "artists.friendly DESC";
					$sql_limit = "LIMIT 1";
				}
				elseif($args["get"] === "next") {
					$sql_where[] = "artists.friendly > ? AND affiliation < '3'";
					$sql_order[] = "artists.friendly ASC";
					$sql_limit = "LIMIT 1";
				}
				else {
					$sql_where[] = "artists.friendly=?";
				}
				
				$sql_values[] = friendly($args["friendly"]);
			}
			
			// Where: active=?
			if( is_numeric($args['active']) ) {
				
				$sql_where[] = 'artists.active=?';
				$sql_values[] = $args['active'];
				
				if( $args['active'] == 1 ) {
					
					$this_year = date('Y');
					$last_year = $this_year - 1;
					
					$year_join = 'artists_years.year='.$this_year;
					$year_join = date('n') < 6 ? '('.$year_join.' OR artists_years.year='.$last_year.')' : $year_join;
					
					$sql_join[] = 'LEFT JOIN artists_years ON artists_years.artist_id=artists.id AND '.$year_join;
					$sql_where[] = 'artists_years.year IS NOT NULL';
					
				}
				
			}
			
			// Where: year
			if( is_numeric($args['year']) ) {
				
				$sql_from = 'artists_years';
				$sql_join[] = 'LEFT JOIN artists ON artists.id=artists_years.artist_id';
				$sql_where[] = 'artists_years.year=?';
				$sql_values[] = $args['year'];
				
			}
			
			// Where: is_vkei
			if( $args['vkei_only'] || $args['is_vkei'] ) {
				$sql_where[] = 'artists.is_vkei>?';
				$sql_values[] = -1;
			}
			
			// Where: years
			if( is_array($args['years']) && !empty($args['years']) ) {
				
				$args['years'] = array_filter($args['years'], function($x) {
					return is_numeric($x) && strlen($x) === 4;
				});
				
				if( is_array($args['years']) && !empty($args['years']) ) {
					
					$sql_from = 'artists_years';
					$sql_join[] = 'LEFT JOIN artists ON artists.id=artists_years.artist_id';
					$sql_where[] = 'artists_years.year IN ('.substr( str_repeat('?,', count($args['years'])), 0, -1 ).')';
					
					foreach( $args['years'] as $year) {
						$sql_values[] = $year;
					}
					
				}
				
			}
			
			// Exclude
			if(is_array($args['exclude']) && !empty($args['exclude'])) {
				$sql_where[] = 'artists.id NOT IN ('.substr(str_repeat('?, ', count($args['exclude'])), 0, -2).')';
				foreach($args['exclude'] as $exclude_id) {
					$sql_values[] = $exclude_id;
				}
			}
			if(is_numeric($args["label_id"])) {
				$sql_where[] = "label_history LIKE CONCAT('%{', ?, '}%')";
				$sql_values[] = $args["label_id"];
			}
			if(is_numeric($args['type'])) {
				$sql_where[] = 'type=?';
				$sql_values[] = $args['type'];
			}
			if(is_numeric($args["affiliation"])) {
				$sql_where[] = "artists.affiliation <= ?";
				$sql_values[] = $args["affiliation"];
			}
			
			// Where: area
			if(!is_array($args['area']) && strlen($args['area'])) {
				if($args['area'] === 'overseas') {
					$sql_where[] = 'artists_tags.tag_id=? AND ((artists_tags.score>0 AND artists_tags.mod_score>-1) OR artists_tags.mod_score=1)';
					$sql_values[] = 18;
					$sql_from = 'artists_tags';
					$sql_join[] = 'LEFT JOIN artists ON artists.id=artists_tags.artist_id';
				}
				else {
					$sql_parent_area = 'SELECT id FROM areas WHERE friendly=? LIMIT 1';
					$stmt_parent_area = $this->pdo->prepare($sql_parent_area);
					$stmt_parent_area->execute([ $args['area'] ]);
					$rslt_parent_area = $stmt_parent_area->fetchColumn();
					
					if(is_numeric($rslt_parent_area)) {
						$area_ids[] = $rslt_parent_area;
						$tmp_area_ids[] = $rslt_parent_area;
						
						// Given a set of area IDs, find all child areas
						// then store their IDs into a temporary array
						// + add them onto a permanent array, and keep
						// looping down until all children areas are found.
						// Later we'll take those IDs and get related artists.
						while(!$all_area_children_found) {
							if(is_array($tmp_area_ids)) {
								$sql_child_area = 'SELECT id FROM areas WHERE ';
								
								foreach($tmp_area_ids as $key => $id) {
									$sql_child_area .= '(parent_id=? AND id!=parent_id) OR ';
								}
								
								$sql_child_area = substr($sql_child_area, 0, -4);
								$stmt_child_area = $this->pdo->prepare($sql_child_area);
								$stmt_child_area->execute($tmp_area_ids);
								$rslt_child_area = $stmt_child_area->fetchAll();
								
								$tmp_area_ids = [];
								
								if(is_array($rslt_child_area) && !empty($rslt_child_area)) {
									foreach($rslt_child_area as $child_area) {
										$area_ids[] = $child_area['id'];
										$tmp_area_ids[] = $child_area['id'];
									}
								}
								else {
									$all_area_children_found = true;
								}
							}
							else {
								$all_area_children_found = true;
							}
						}
						
						// Take resulting area IDs and apply to artist query
						if(is_array($area_ids) && !empty($area_ids)) {
							$sql_from = 'areas_artists';
							$sql_join[] = 'LEFT JOIN artists ON artists.id=areas_artists.artist_id';
							$sql_where[] = substr(str_repeat('(areas_artists.area_id=?) OR ', count($area_ids)), 0, -4);
							foreach($area_ids as $area_id) {
								$sql_values[] = $area_id;
							}
						}
					}
				}
			}
			
			//
			// JOINS
			//
			
			// Join years active
			if( !$args['year'] && ( $args['get'] === 'all' || $args['get'] === 'basics' ) ) {
				$sql_join[] = 'LEFT JOIN artists_years ON artists_years.artist_id=artists.id';
			}
			
			// Get tags
			if($args['get'] === 'artist_list' || $args['get'] === 'basics') {
				$sql_join[] = 'LEFT JOIN artists_tags ON artists_tags.artist_id=artists.id AND ((artists_tags.mod_score>-1 AND artists_tags.score>0) OR artists_tags.mod_score>0)';
				$sql_join[] = 'LEFT JOIN tags_artists ON tags_artists.id=artists_tags.tag_id';
				$sql_group[] = 'artists.id';
			}
			
			//
			// ORDER
			//
			$sql_order = $args['order'] ? [ $args['order'] ] : $sql_order;
			
			// DEFAULTS
			$sql_select = $sql_select ?: [];
			$sql_from = $sql_from ?: 'artists';
			$sql_join = is_array($sql_join) ? implode(' ', $sql_join) : null;
			$sql_where = $sql_where ?: [];
			$sql_values = $sql_values ?: [];
			$sql_order = $sql_order ?: ["artists.friendly ASC"];
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : ($sql_limit ?: null);
			$sql_group = is_array($sql_group) ? array_unique($sql_group) : null;
			
			// QUERY
			if(is_numeric($args["id"]) && $args["get"] !== "all" && is_array($this->indexed_artists) && !empty($this->indexed_artists[$args["id"]])) {
				return $this->indexed_artists[$args["id"]];
			}
			else {
				if(!empty($sql_select)) {
					
					
					$sql_artist = "SELECT ".implode(", ", $sql_select)." FROM ".$sql_from.' '.$sql_join.' '.(!empty($sql_where) ? "WHERE (".implode(") AND (", $sql_where).")" : null).($sql_group ? ' GROUP BY '.implode(', ', $sql_group) : null)." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
					$stmt = $this->pdo->prepare($sql_artist);
						
						/*if($_SESSION['username'] === 'inartistic') {
							
							echo $sql_artist;
							
							echo '<pre>'.print_r($sql_values, true).'</pre>';
							
						}*/
					
						
					if($stmt) {
						
						$stmt->execute($sql_values);
						$artists = $stmt->fetchAll();
						$num_artists = count($artists);
					
						if(is_array($artists)) {
							
							// Get tags
							if( $args['get'] === 'artist_list' ) {
								
								include_once('../php/class-tag.php');
								$access_tag = new tag($this->pdo);
								
								for( $i=0; $i<$num_artists; $i++ ) {
									
									$artists[$i]['tags'] = $access_tag->access_tag([ 'item_type' => 'artist', 'item_id' => $artists[$i]['id'], 'get' => 'basics', 'flat' => true ]);
									
								}
								
							}
							
							//
							// This whole thing doesn't work because we need the formation included in the original query or else we can't sort by it, duh
							//
							// Get formation date
							/*if( $args['get'] === 'artist_list' && $_SESSION['username'] === 'inartistic') {
								
								// Save list of artist ids
								for($i=0; $i<$num_artists; $i++) {
									$artist_ids[] = $artists[$i]['id'];
								}
								
								// Get formation date
								$sql_formation = '
									SELECT
										aa.artist_id,
										IF( MAX(aa.date_occurred)>"1", MAX(aa.date_occurred), IF( MAX(aa.year_occurred)>"1", CONCAT(MAX(aa.year_occurred),"-00-00"), "0000-00-00" ) ) AS date_formed
									FROM (
										(
											SELECT
												artists_years.artist_id,
												"" AS date_occurred,
												MIN(artists_years.year) AS year_occurred
											FROM
												artists_years
											WHERE
												artists_years.artist_id IN ('.implode(',',$artist_ids).')
											GROUP BY
												artists_years.artist_id
										) 
										UNION
										(
											SELECT
												artists_bio.artist_id,
												MIN(date_occurred) AS date_occurred,
												"" AS year_occurred
											FROM
												artists_bio
											WHERE
												artists_bio.artist_id IN ('.implode(',',$artist_ids).')
												AND
												artists_bio.type LIKE CONCAT("%","(10)","%")
											GROUP BY
												artists_bio.artist_id
										) 
									) aa
									GROUP BY aa.artist_id
								';
								$stmt_formation = $this->pdo->prepare($sql_formation);
								$stmt_formation->execute();
								$rslt_formation = $stmt_formation->fetchAll();
								
								if( is_array($rslt_formation) && !empty($rslt_formation) ) {
									foreach( $rslt_formation as $formation ) {
										
										$artist_key = array_search( $formation['artist_id'], $artist_ids );
										$artists[$artist_key]['date_formed'] = $formation['date_formed'];
										
									}
								}
								
								echo '<br /><br />**************************<pre>'.print_r($rslt_formation, true).'</pre>===========================<br /><br />';
								
							}*/
							
							// If getting all artist info or basics, grab musician data, then compile into lineup string
							if($args["get"] === "all" || $args["get"] === "basics" || $args['get'] === 'profile') {
								$access_musician = new access_musician($this->pdo);
								
								for($i=0; $i<$num_artists; $i++) {
									$lineup = [];
									$musicians = $access_musician->access_musician([ 'artist_id' => $artists[$i]['id'], 'get' => ($args['get'] === 'all' ? 'all' : 'list') ]);
									
									if( is_array($musicians) ) {
										
										$num_musicians = count($musicians);
										$musicians = array_values($musicians);

										for($n=0; $n<$num_musicians; $n++) {
											if($musicians[$n]['to_end'] && $musicians[$n]['position'] != 7 && $musicians[$n]['position_name'] != 'roadie') {
												$lineup[] = 
													($musicians[$n]['position'] ? 
													['O', 'V', 'G', 'B', 'D', 'K', 'O', 'S'][$musicians[$n]['position']] : 
													(substr($musicians[$n]['position_romaji'], 0, 1) ?: (substr($musicians[$n]['position_name'], 0, 1) ?: 'O'))).
													'. '.($musicians[$n]['as_quick_name'] ?: $musicians[$n]['quick_name']);
											}
										}

										$artists[$i]['musicians'] = $musicians;
										$artists[$i]['lineup'] = !empty($lineup) ? implode(' / ', $lineup) : null;
									
									}
									else {
										$artists[$i]['musicians'] = [];
									}
									
								}
							}
							
							// If getting all data, grab labels, etc.
							if($args['get'] === 'all') {
								for($i=0; $i<$num_artists; $i++) {
									$artists[$i]["labels"] = $this->format_label_history($artists[$i]["label_history"]);
									$artists[$i]["prev_artist"] = $this->access_artist(["friendly" => $artists[$i]["friendly"], "get" => "prev"]);
									$artists[$i]["next_artist"] = $this->access_artist(["friendly" => $artists[$i]["friendly"], "get" => "next"]);
									$artists[$i]["history"] = $this->get_history($artists[$i]["id"]);
									$artists[$i]['images'] = $this->access_image->access_image([ 'artist_id' => $artists[$i]['id'], 'get' => 'most', 'associative' => true ]);
									$artists[$i]['lives'] = $this->access_live->access_live([ 'artist_id' => $artists[$i]['id'], 'get' => 'name', 'keys' => 'date', 'order' => 'lives.date_occurred ASC' ]);
									$artists[$i]['num_lives'] = $this->access_live->access_live([ 'artist_id' => $artists[$i]['id'], 'get' => 'count', 'keys' => 'date', 'order' => 'lives.date_occurred ASC' ]);
									
									$sql_areas = 'SELECT areas.* FROM areas_artists LEFT JOIN areas ON areas.id=areas_artists.area_id WHERE areas_artists.artist_id=? ORDER BY areas.friendly ASC';
									$stmt_areas = $this->pdo->prepare($sql_areas);
									if($stmt_areas->execute([ $artists[$i]['id'] ])) {
										$artists[$i]['areas'] = $stmt_areas->fetchAll();
									}
								}
							}
							
							// If getting all data, grab URLs
							if($args['get'] === 'all' || $args['get'] === 'profile') {
								for($i=0; $i<$num_artists; $i++) {
									$sql_urls = 'SELECT * FROM artists_urls WHERE artist_id=?';
									$stmt_urls = $this->pdo->prepare($sql_urls);
									$stmt_urls->execute([ $artists[$i]['id'] ]);
									$rslt_urls = $stmt_urls->fetchAll();
									
									// For resulting URLs, grab musician info if necessary
									if(is_array($rslt_urls) && !empty($rslt_urls)) {
										foreach($rslt_urls as $url_key => $url) {
											if(is_numeric($url['musician_id'])) {
												
												// Grab musician info if musician exists
												if(is_array($artists[$i]['musicians']) && is_array($artists[$i]['musicians'][$url['musician_id']])) {
													$musician = $artists[$i]['musicians'][$url['musician_id']];
													
													$rslt_urls[$url_key]['musician'] = [
														'name' => $musician['name'],
														'romaji' => $musician['romaji'],
														'id' => $musician['id'],
														'as_name' => $musician['as_name'],
														'as_romaji' => $musician['as_romaji'],
														'friendly' => $musician['friendly'],
														'position' => $musician['position']
													];
													
													unset($musician);
												}
												
											}
										}
									}
									
									// If no URLs returned, but artist does have old official_links field, use that instead
									elseif( strlen($artists[$i]['official_links']) ) {
										$urls = explode("\n", $artists[$i]['official_links']);
										$urls = array_filter($urls);
										
										if(is_array($urls) && !empty($urls)) {
											foreach($urls as $url) {
												$rslt_urls[] = [ 'content' => $url ];
											}
										}
									}
									
									// Attach URLs to artist
									$artists[$i]['urls'] = $rslt_urls;
									unset($rslt_urls);
									
								}
							}
							
							// If have an array of display names, that means we did a search for name changes, so let's duplicate those artists with their display names
							if($args['get'] === 'name') {
								if(is_array($display_names) && !empty($display_names)) {
									for($i=0; $i<$num_artists; $i++) {
										
										$this_artist_id = $artists[$i]['id'];
										
										// If display name array has key that matches artist's id,
										if($display_names[$this_artist_id][0]) {
											
											// If number of ID occurrences are greater than number of display names, that means we have an organic artist result + a changed name result, so make a clean clone first
											if($num_duplicates[$this_artist_id] === count($display_names) + 1) {
												
												// Splice vanilla artist after current array (and increase artist count so loop continues)
												array_splice($artists, $i, 0, [ $artists[$i] ]);
												$num_artists++;
												
												// Unset num duplicates so we don't worry about this again
												unset($num_duplicates[$this_artist_id]);
											}
											
											// If there's another display name after this, clone this array so we can use it in the next loop
											if(count($display_names[$this_artist_id]) > 1) {
												
												// Make new array with artist info + display names + clone flag
												$clone_artist = $artists[$i];
												
												// Splice new array after current array (and increase artist count so loop continues)
												array_splice($artists, $i + 1, 0, [ $clone_artist ]);
												$num_artists++;
												
											}
											
											// Merge this artist with the display name info
											$artists[$i] = array_merge($artists[$i], $display_names[$this_artist_id][0]);
											$artists[$i]['is_alternate_name'] = true;
											
											// Remove display name that was just added from display name array, then reset keys so next check works
											unset($display_names[$this_artist_id][0]);
											if(count($display_names[$this_artist_id])) {
												$display_names[$this_artist_id] = array_values($display_names[$this_artist_id]);
											}
											else {
												unset($display_names[$this_artist_id]);
											}
											
										}
									}
								}
							}
							
							// If getting list, determine if name hints necessary
							for($i=0; $i<$num_artists; $i++) {
								if(friendly($artists[$i]["quick_name"]) != $artists[$i]["friendly"]) {
									$artists[$i]["needs_hint"] = true;
								}
							}
							
							// Index artists, unless grabbing a large amount of info
							if(is_numeric($args["id"]) && $args["get"] !== "all" && $args["get"] !== "id") {
								$this->indexed_artists[$args["id"]] = reset($artists);
							}
							
							// Reformat as associative array, if requested
							if($args['associative']) {
								for($i=0; $i<$num_artists; $i++) {
									$artists_assoc[$artists[$i]['id']] = $artists[$i];
								}
								$artists = $artists_assoc;
								unset($artists_assoc);
							}
							
							// If only one artist expected, return only first elem of artists array
							if(!empty($args["friendly"]) || is_numeric($args["id"])) {
								$artists = reset($artists);
							}
							
							// If only returning count, get first entry
							if( $args['get'] === 'count' ) {
								$artists = reset( reset( $artists ) );
							}
							
							return $artists;
						}
					}
				}
			}
		}
		
		
		
		// ======================================================
		// Calculate the years that an artist was active
		// ======================================================
		function calculate_years_active($artist_id) {
			
			if( is_numeric($artist_id) ) {
				
				$sql_artist = 'SELECT active FROM artists WHERE id=? LIMIT 1';
				$stmt_artist = $this->pdo->prepare($sql_artist);
				$stmt_artist->execute([ $artist_id ]);
				$artist_status = $stmt_artist->fetchColumn();
				
				// Use status as proxy to see if artist exists
				if( is_numeric($artist_status) ) {
					
					$sql_start_end = '
						SELECT 
							artist_history.type,
							SUBSTRING(artist_history.date_occurred, 1, 4) AS year
						FROM (
							SELECT artists_bio.id, artists_bio.date_occurred, artists_bio.type FROM artists_bio WHERE artist_id=?
						) artist_history
						WHERE
							artist_history.date_occurred > ?
							AND
							(
								artist_history.type LIKE CONCAT("%", ?, "%") 
								OR
								artist_history.type LIKE CONCAT("%", ?, "%")
							)
						ORDER BY
							artist_history.date_occurred ASC,
							artist_history.id ASC
					';
					$stmt_start_end = $this->pdo->prepare($sql_start_end);
					$stmt_start_end->execute([ $artist_id, '0000-12-31', '(10)', '(5)' ]);
					$artist_start_end = $stmt_start_end->fetchAll();
					
					// Set up empty arrays for the eras and eventually the years
					$eras = [];
					$years_active = [];
					
					if( is_array($artist_start_end) && !empty($artist_start_end) ) {
						
						$current_era_number = 0;
						
						foreach( $artist_start_end as $start_end_event ) {
							
							$current_year = $start_end_event['year'];
							
							// Ignore events on '0000-00-00'
							if( $current_year > 0 ) {
								
								$is_start = strpos( $start_end_event['type'], '(10)' ) !== false;
								$is_end = !$is_start;
								
								// Start a new era
								if( $is_start ) {
									$current_era_number++;
									$eras[ $current_era_number ]['start'] = $current_year;
								}
								
								if( $is_end ) {
									$eras[ $current_era_number ]['end'] = $current_year;
								}
								
							}
							
						}
						
					}
					
					// If we have any eras set up, go back and try to fill in the dates
					if( is_array($eras) && !empty($eras) ) {
						
						foreach( $eras as $era_key => $era ) {
							
							// If the era has only a start or an end but not both, we need to do some further checks
							if( !$era['start'] || !$era['end'] ) {
								
								$eras_needing_further_checks[] = $era_key;
								
							}
							
						}
						
					}
					
					// If no eras are set up by now (e.g. bio doesn't have start or end), let's go ahead and get additional data and just set their active years as anything that's occurred
					else {
						
						$additional_events = $this->get_activity_events($artist_id);
						
						if( is_array($additional_events) && !empty($additional_events) ) {
							foreach($additional_events as $event_year) {
								
								$years[] = $event_year;
								
							}
						}
						
					}
					
					// If there are any eras needing further checks (meaning there *were* some start and end dates in bio, but one or more eras has only one boundary), let's use additional data to get the other boundary
					if( is_array($eras_needing_further_checks) && !empty($eras_needing_further_checks) ) {
						
						$last_era_key = end( $eras_needing_further_checks );
						
						foreach( $eras_needing_further_checks as $era_key ) {
							
							$era = $eras[ $era_key ];
							
							// If this is the last era, and it has a start date but not an end date, and the artist is active, we'll just set the end of this era to the current year irl
							if( $era['start'] && $artist_status === 1 ) {
								
								$eras[ $era_key ]['end'] = date('Y');
								
							}
							
							// Otherwise, we'll have to make a guess at the activity period through additional data
							else {
								
								// See if there are previous and next eras
								$previous_era_key = $era_key - 1;
								$next_era_key = $era_key + 1;
								
								// Get additional events
								$comparison_date = ( $era['start'] ?: $era['end'] ).'-01-01';
								$comparison_symbol = $era['start'] ? '>' : '<';
								$additional_events = $this->get_activity_events($artist_id, $comparison_symbol, $comparison_date);
								
								// Given the additional events, let's try to get the other boundaries
								if( is_array($additional_events) && !empty($additional_events) ) {
									
									// If there's a next era, make sure we don't look at dates in that one
									if( $eras[$next_era_key] && $eras[$next_era_key]['start'] ) {
										
										$additional_events = array_filter( $additional_events, function($var) { return $var < $eras[$next_era_key]['start']; } );
										
									}
									
									// ...And the opposite if there was a previous era
									if( $eras[$previous_era_key] && $eras[$previous_era_key]['end'] ) {
										
										$additional_events = array_filter( $additional_events, function($var) { return $var > $eras[$previous_era_key]['end']; } );
										
									}
									
									// If we already have start boundary, let's get the last event as the end
									if( $era['start'] ) {
										$eras[$era_key]['end'] = end($additional_events);
									}
									
									// And otherwise get the earliest event as the start
									else {
										$eras[$era_key]['start'] = $additional_events[0];
									}
									
									unset($additional_events);
									
								}
								
							}
							
						}
						
					}
					
					// Ok, so now if we have some eras, we'll loop through and populate the list of years
					if( is_array($eras) && !empty($eras) ) {
						
						foreach( $eras as $era ) {
							
							// If both boundaries supplied, just add everything between them
							if( $era['start'] && $era['end'] ) {
								
								for( $year = $era['start']; $year < $era['end'] + 1; $year++ ) {
									
									$years[] = $year;
									
								}
								
							}
							
							// If we still only have one boundary event after getting additional data, that boundary will be the only year in that era
							elseif( $era['start'] || $era['end'] ) {
								
								$years[] = $era['start'] ?: $era['end'];
								
							}
							
						}
						
					}
					
					// If we have years after all that, return them
					if( is_array($years) && !empty($years) ) {
						return $years;
					}
					
				}
				else {
					$output['result'] = 'The artist couldn\'t be found.';
				}
				
			}
			else {
				$output['result'] = 'No artist specified.';
			}
			
		}
		
		
		
		// ======================================================
		// Helper function to help calculate years active
		// ======================================================
		function get_activity_events( $artist_id, $comparison_symbol = null, $comparison_date = null ) {
			
			$null_date = '0000-12-31';
			$bio_note_type = '(16)';
			
			if( is_numeric($artist_id) ) {
				
				// Just here so I can close the group
				if(1) {
					$sql_additional = '
						SELECT
							SUBSTRING(additional.date_occurred, 1, 4) AS year
						FROM
							(
								(
									SELECT
										artist_bio.date_occurred
									FROM (
										SELECT date_occurred, type FROM artists_bio WHERE artist_id=?
									) artist_bio
									WHERE
										artist_bio.type!=? AND artist_bio.date_occurred > ? '.( $comparison_date ? 'AND artist_bio.date_occurred '.$comparison_symbol.' ?' : null).'
								)
								UNION
								(
									SELECT
										artist_lives.date_occurred
									FROM (
										SELECT lives.date_occurred FROM lives_artists LEFT JOIN lives ON lives.id=lives_artists.live_id WHERE artist_id=?
									) artist_lives
									WHERE
										artist_lives.date_occurred > ? '.( $comparison_date ? 'AND artist_lives.date_occurred '.$comparison_symbol.' ?' : null).'
								)
								UNION
								(
									SELECT
										artist_releases.date_occurred
									FROM (
										SELECT date_occurred FROM releases WHERE artist_id=?
									) artist_releases
									WHERE
										artist_releases.date_occurred > ? '.( $comparison_date ? 'AND artist_releases.date_occurred '.$comparison_symbol.' ?' : null).'
								)
							) additional
						ORDER BY
							additional.date_occurred ASC
					';
				}
				
				// If we have a comparison date, make sure to include that in the query
				if( $comparison_date ) {
					
					$values_additional = [ $artist_id, $bio_note_type, $null_date, $comparison_date, $artist_id, $null_date, $comparison_date, $artist_id, $null_date, $comparison_date ];
					
				}
				
				else {
					
					$values_additional = [ $artist_id, $bio_note_type, $null_date, $artist_id, $null_date, $artist_id, $null_date ];
					
				}
				
				// Get additional events (sql is set up earlier)
				$stmt_additional = $this->pdo->prepare($sql_additional);
				$stmt_additional->execute( $values_additional );
				$additional_events = $stmt_additional->fetchAll(PDO::FETCH_COLUMN, 0);
				
				return $additional_events;
				
			}
			
		}
		
		
		
		// ======================================================
		// Update the years that an artist was active
		// ======================================================
		function update_years_active($artist_id, $years) {
			
			// If after all that we have some years, we'll update the database accordingly
			if( is_numeric($artist_id) && is_array($years) && !empty($years) ) {

				// Make sure we have a clean array of just years
				$updated_years = array_filter($years, function($x) {
					return strlen($x) === 4 && is_numeric($x);
				});
				
				$updated_years = array_values( array_unique($years) );

				// Get artist's current dates
				$sql_current_years = 'SELECT year FROM artists_years WHERE artist_id=?';
				$stmt_current_years = $this->pdo->prepare( $sql_current_years );
				$stmt_current_years->execute([ $artist_id ]);
				$current_years = $stmt_current_years->fetchAll(PDO::FETCH_COLUMN, 0);

				if( is_array($current_years) && !empty($current_years) ) {

					foreach( $current_years as $current_year ) {

						// If year already in DB, unset it from the list of ones we're about to add
						if( in_array( $current_year, $updated_years ) ) {

							$updated_key = array_search( $current_year, $updated_years );
							unset( $updated_years[ $updated_key ] );

						}

						// If year in DB that isn't in updated list, we need to delete it
						else {

							$years_to_delete[] = $current_year;

						}

					}

				}

				$years_to_add = array_values( $updated_years );

				// Add years
				if( is_array($years_to_add) && !empty($years_to_add) ) {

					foreach( $years_to_add as $year_to_add ) {
						$values_add[] = $artist_id;
						$values_add[] = $year_to_add;
					}

					$sql_add = 'INSERT INTO artists_years (artist_id, year) VALUES '.substr( str_repeat( '(?, ?), ', count($years_to_add) ), 0, -2 );
					$stmt_add = $this->pdo->prepare($sql_add);
					$stmt_add->execute( $values_add );

				}

				// Delete years
				if( is_array($years_to_delete) && !empty($years_to_delete) ) {

					$values_delete = $years_to_delete;
					array_unshift( $values_delete, $artist_id );

					$sql_delete = 'DELETE FROM artists_years WHERE artist_id=? AND year IN ('.substr( str_repeat( '?,', count($years_to_delete) ), 0, -1 ).')';

					$stmt_delete = $this->pdo->prepare($sql_delete);
					$stmt_delete->execute( $values_delete );

				}

			}
			
		}
		
		
		
		// ======================================================
		// Helper function to help calculate “vkei-ness”
		// ======================================================
		function calculate_vkei_ness($artist_id) {
			
			if( is_numeric($artist_id) ) {
				
				// Check tags
				$sql_tags = 'SELECT artists_tags.score, artists_tags.mod_score FROM artists_tags WHERE artist_id=? AND tag_id=?';
				$stmt_tags = $this->pdo->prepare($sql_tags);
				$stmt_tags->execute([ $artist_id, 16 ]);
				$rslt_tags = $stmt_tags->fetch();
				
				$tags = [
					'non_vkei_score' => !empty($rslt_tags) && is_numeric($rslt_tags['score']) ? $rslt_tags['score'] : 0,
					'non_vkei_mod_score' => !empty($rslt_tags) && is_numeric($rslt_tags['mod_score']) ? $rslt_tags['mod_score'] : 0,
				];
				
				// Check members
				$sql_musicians = '
					SELECT
						COUNT(artists.id) AS num_connections,
						COALESCE(SUM(artists.is_vkei),0) AS is_vkei_sum
					FROM
						artists_musicians
					LEFT JOIN
						artists_musicians xx ON xx.musician_id=artists_musicians.musician_id AND xx.artist_id!=artists_musicians.artist_id
					LEFT JOIN
						artists ON artists.id=xx.artist_id
					WHERE
						artists_musicians.artist_id=?
					GROUP BY
						xx.musician_id
				';
				$stmt_musicians = $this->pdo->prepare($sql_musicians);
				$stmt_musicians->execute([ $artist_id ]);
				$rslt_musicians = $stmt_musicians->fetchAll();
				
				if( is_array($rslt_musicians) && !empty($rslt_musicians) ) {
					foreach( $rslt_musicians as $musician ) {
						if( $musician['is_vkei_sum'] ) {
							$num_vkei_musicians++;
						}
					}
				}
				
				$musicians = [
					'num_musicians' => count($rslt_musicians),
					'num_vkei_musicians' => $num_vkei_musicians ?: 0,
				];
				
				$musicians['percent_vkei'] = round( ( $musicians['num_musicians'] ? $musicians['num_vkei_musicians'] / $musicians['num_musicians'] : 0 ) * 100 );
				
				// Check lives
				$sql_lives = '
					SELECT
						COUNT(1) AS num_lives,
						SUM(yy.has_vkei_artist>0) AS num_vkei_lives
					FROM (
						SELECT
							SUM(artists.is_vkei) AS has_vkei_artist
						FROM
							lives_artists
						LEFT JOIN
							lives_artists xx ON xx.live_id=lives_artists.live_id AND xx.artist_id!=lives_artists.artist_id
						LEFT JOIN
							artists ON artists.id=xx.artist_id
						WHERE
							lives_artists.artist_id=?
							AND
							artists.id IS NOT NULL
						GROUP BY
							lives_artists.live_id
					) yy
				';
				$stmt_lives = $this->pdo->prepare($sql_lives);
				$stmt_lives->execute([ $artist_id ]);
				$rslt_lives = $stmt_lives->fetch();
				
				$lives = [
					'num_lives' => $rslt_lives['num_lives'] ?: 0,
					'num_vkei_lives' => $rslt_lives['num_vkei_lives'] ?: 0
				];
				
				$lives['percent_vkei'] = round( ( $lives['num_lives'] ? $lives['num_vkei_lives'] / $lives['num_lives'] : 0 ) * 100 );
				
				// Let's start to set up a meta array with an arbitrary-ish total score and confidence level
				$meta['is_confident'] = true;
				
				// If we have data for musicians and lives, we can be confident-ish
				if( $musicians['num_musicians'] && $lives['num_lives'] ) {
					$meta['total_percent'] = ( $musicians['percent_vkei'] + $lives['percent_vkei'] ) / 2;
				}
				
				// If we only have data for musicians, confident if over three
				elseif( $musicians['num_musicians'] >= 4 ) {
					$meta['total_percent'] = $musicians['percent_vkei'];
				}
				
				// If we only have data for lives, confident if over five
				elseif( $lives['num_lives'] >= 5 ) {
					$meta['total_percent'] = $lives['precent_vkei'];
				}
				
				// Otherwise, we're not really confident
				else {
					$meta['is_confident'] = false;
				}
				
				// Turn the score into a word
				if( $meta['is_confident'] ) {
					switch(true) {
						case $meta['total_percent'] > 85: $meta['likelihood'] = 'high'; break;
						case $meta['total_percent'] > 65: $meta['likelihood'] = 'medium'; break;
						default: $meta['likelihood'] = 'low';
					}
				}
				else {
					$meta['likelihood'] = 'unknown';
				}
				
				return [ 'meta' => $meta, 'musicians' => $musicians, 'lives' => $lives, 'tags' => $tags ];
				
			}
			
		}
		
	}