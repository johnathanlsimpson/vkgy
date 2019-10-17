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
		// Add artist website
		// ======================================================
		function add_website($artist_id, $website_urls) {
			
			// Artist ID must be provided
			if(is_numeric($artist_id)) {
				
				// If string (one URL) given, convert to array
				if(!is_array($website_urls) && strlen($website_urls)) {
					$website_urls = [ $website_urls ];
				}
				
				// If new URLs provided
				if(is_array($website_urls) && !empty($website_urls)) {
					
					// Grab previous URLs
					$sql_extant = 'SELECT official_links FROM artists WHERE id=? LIMIT 1';
					$stmt_extant = $this->pdo->prepare($sql_extant);
					$stmt_extant->execute([ $artist_id ]);
					$rslt_extant = $stmt_extant->fetchColumn;
					$rslt_extant = explode("\n", $rslt_extant);
					$rslt_extant = array_filter($rslt_extant);
					
					// Loop through new URLs, check if in extant list
					// Want to add URL filtering functions here too
					$num_new_websites = count($website_urls);
					for($i=0; $i<$num_new_websites; $i++) {
						if(in_array($website_urls, $rslt_extant)) {
							unset($website_urls[$i]);
						}
					}
					
					// Combine extant URLs and new URLs, then update DB
					if(is_array($website_urls) && !empty($website_urls)) {
						$website_urls = implode("\n", $website_urls);
						
						$sql_add = 'UPDATE artists SET official_links=CONCAT(official_links, "\n", ?) WHERE id=? LIMIT 1';
						$stmt_add = $this->pdo->prepare($sql_add);
						if($stmt_add->execute([ $website_urls, $artist_id ])) {
							return true;
						}
					}
					
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
					$sql_artist_tags = 'SELECT a.artist_id FROM artists_tags LEFT JOIN artists_tags a ON a.tag_id=artists_tags.tag_id WHERE '.substr(str_repeat('artists_tags.artist_id=? OR ', count($artist_ids)), 0, -4);
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
		// Artist biography types
		// ======================================================
		public $artist_bio_types = [
			0  => "cancellation", // live or release (etc) cancellation
			1  => "activity",     // activity change
			2  => "live",         // live event or tour
			3  => "release",      // release
			4  => "name",         // band name change
			5  => "disbandment",  // disbandment or activity end
			6  => "media",        // media, fanclub, etc
			7  => "other",        // ?:?
			8  => "label",        // label change or start
			9  => "lineup",       // notes current lineup (not for lineup change)
			10 => "formation",    // formation-related
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
				
				if(is_array($bio_lines)) {
					foreach($bio_lines as $line) {
						
						$line_type = [];
						
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
								"formation"    => "\bforms\b|\bformed\b|\bformation\b",
								"label"        => "\blabels?\b|\bgraduates?\b|\bsublabel\b",
								"live"         => "\blive\b|\btour\b|\bevent\b|\boneman\b|\btwoman\b|\bthreeman\b|\bfourman\b",
								"name"         => "\bchanges?\b.*\bnames?\b",
								"activity"     => "(?:\bpauses?\b|\bactivit(?:y|ies)\b|\bfreezes?\b)",
								"disbandment"  => "\bdisbands\b",
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
						
						// Name pronunciation
						if(is_array($line_type) && (in_array(10, $line_type) || in_array(4, $line_type))) {
							$pronunciation_pattern = '(?:\(\d+\)|\/|\]) \(([&#; A-z0-9]+)\)[ \.]';
							$katakana_pattern = '^[ぁ-んァ-ン 　・]+$';
							
							if(preg_match('/'.$pronunciation_pattern.'/', $line, $pronunciation_match)) {
								if(strlen($pronunciation_match[1])) {
									$pronunciation_string = html_entity_decode($pronunciation_match[1], ENT_QUOTES, 'UTF-8');
									
									if(preg_match('/'.$katakana_pattern.'/', $pronunciation_string)) {
										$pronunciation = sanitize($pronunciation_string);
									}
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
							$pattern_schedule_ref_in_live = 'at(?: ([\w-\. ]+?)(?:\(([\w-\. &#;]+)\))?){1,2}\.?$';
							
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
											$parsed_live = $this->live_parser->parse_raw_input($possible_livehouse, $date, $artist_id);
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
							
							$parsed_live = $this->live_parser->parse_raw_input($line, $date, $artist_id);
							
							if(is_array($parsed_live) && !empty($parsed_live)) {
								$line = ($parsed_live['livehouse']['area_romaji'] ?: $parsed_live['livehouse']['area_name']).' '.($parsed_live['livehouse']['romaji'] ?: $parsed_live['livehouse']['name']);
								
								// If user supplied other bands on that live date, show them so they can confirm that those bands were found in DB
								// Btw, skip first element of parsed lineup since it will always be the artist being edited
								if(is_array($parsed_live['lineup'][1]) || is_array($parsed_live['additional_lineup'])) {
									$note = 'Also linked: ';
									
									if(is_array($parsed_live['lineup']) && !empty($parsed_live['lineup'])) {
										foreach($parsed_live['lineup'] as $parsed_lineup) {
											if(is_array($parsed_lineup)) {
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
							"user_id" => $_SESSION["userID"],
							'area_id' => $area_id,
							"parsed_live" => $parsed_live,
							"note" => $note ? '<div class="any--weaken symbol__help">'.$note.'</div>' : null,
							'pronunciation' => $pronunciation
						];
						
						unset($parsed_live, $note, $area_id, $area_name, $area_romaji, $pronunciation);
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
			
			usort($history, function($a, $b) {
				return $a["date_occurred"] <=> $b["date_occurred"];
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
			}
			
			if($args['get'] === 'artist_list') {
				$sql_select[] = 'artists.description';
				$sql_select[] = 'artists.pronunciation';
				$sql_select[] = 'artists.active';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.name) AS tag_names';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.romaji) AS tag_romajis';
				$sql_select[] = 'GROUP_CONCAT(tags_artists.friendly) AS tag_friendlys';
			}
			
			// WHERE
			$sql_where = [];
			$sql_values = [];
			
			// [PRE-SELECT] Tag
			if(is_array($args["tags"]) && !empty($args["tags"])) {
				foreach($args["tags"] as $i => $tag) {
					if(strlen($tag) > 0) {
						$sql_pre = "SELECT artists_tags.artist_id AS id FROM tags_artists LEFT JOIN artists_tags ON artists_tags.tag_id=tags_artists.id WHERE tags_artists.friendly=? GROUP BY artists_tags.artist_id";
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
					unset($args["tags"], $args["get"]);
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
			if(is_numeric($args['active'])) {
				$sql_where[] = 'artists.active=?';
				$sql_values[] = $args['active'];
			}
			if($args["name"]) {
				$tmp_name = html_entity_decode($args["name"], ENT_QUOTES, "utf-8");
				
				if($args["fuzzy"] && mb_strlen($tmp_name) > 2) {
					$sql_where[] = "artists.friendly = ? OR artists.name=? OR artists.romaji=? OR (artists.name LIKE CONCAT('%', ?, '%') OR artists.romaji LIKE CONCAT('%', ?, '%'))";
					array_push($sql_values, friendly($args["name"]), sanitize($args["name"]), sanitize($args["name"]), sanitize($args["name"]), sanitize($args["name"]));
				}
				elseif($args['exact_name']) {
					$sql_where[] = "artists.name=? OR artists.romaji=?";
					array_push($sql_values, sanitize($args["name"]), sanitize($args["name"]));
				}
				else {
					$sql_where[] = "artists.friendly=? OR artists.name=? OR artists.romaji=?";
					array_push($sql_values, friendly($args["name"]), sanitize($args["name"]), sanitize($args["name"]));
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
			if(strlen($args['area'])) {
				if($args['area'] === 'overseas') {
					$sql_where[] = 'artists_tags.tag_id=?';
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
			
			if($args['get'] === 'artist_list') {
				$sql_join[] = 'LEFT JOIN artists_tags ON artists_tags.artist_id=artists.id';
				$sql_join[] = 'LEFT JOIN tags_artists ON tags_artists.id=artists_tags.tag_id';
				$sql_group[] = 'artists.id';
			}
			
			// DEFAULTS
			$sql_select = $sql_select ?: [];
			$sql_from = $sql_from ?: 'artists';
			$sql_join = is_array($sql_join) ? implode(' ', $sql_join) : null;
			$sql_where = $sql_where ?: [];
			$sql_values = $sql_values ?: [];
			$sql_order = $sql_order ?: ["artists.friendly ASC"];
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;
			
			// QUERY
			if(is_numeric($args["id"]) && $args["get"] !== "all" && is_array($this->indexed_artists) && !empty($this->indexed_artists[$args["id"]])) {
				return $this->indexed_artists[$args["id"]];
			}
			else {
				if(!empty($sql_select)) {
					
					$sql_artist = "SELECT ".implode(", ", $sql_select)." FROM ".$sql_from.' '.$sql_join.' '.(!empty($sql_where) ? "WHERE (".implode(") AND (", $sql_where).")" : null).($sql_group ? ' GROUP BY '.implode(', ', $sql_group) : null)." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
					$stmt = $this->pdo->prepare($sql_artist);
					
					if($stmt) {
						$stmt->execute($sql_values);
						$artists = $stmt->fetchAll();
						$num_artists = count($artists);
						
						if(is_array($artists)) {
							
							// If getting all artist info or basics, grab musician data, then compile into lineup string
							if($args["get"] === "all" || $args["get"] === "basics") {
								$access_musician = new access_musician($this->pdo);
								
								for($i=0; $i<$num_artists; $i++) {
									$lineup = [];
									$musicians = $access_musician->access_musician([ 'artist_id' => $artists[$i]['id'], 'get' => 'all' ]);
									
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
							
							return $artists;
						}
					}
				}
			}
		}
	}