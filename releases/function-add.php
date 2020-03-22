<?php
	include_once("../php/include.php");
	include_once("../php/class-parse_markdown.php");
	include_once("../php/class-auto_blogger.php");
	include_once("../releases/function-clean_values.php");
	$auto_blogger = new auto_blogger($pdo);
	$markdown_parser = new parse_markdown($pdo);
	
	unset($_POST["submit"]);
	
	if(empty($_POST)) {
		$output["result"] = "Submission empty.";
	}
	else {
		if(!$_SESSION["is_signed_in"]) {
			$output["result"] = "Sorry, you must be signed in.";
		}
		else {
			if(!is_numeric($_POST["artist_id"])) {
				$output["result"] = "Please fill in the artist field.";
			}
			else {
				if(empty($_POST["name"])) {
					$output["result"] = "Please fill in the release's name field.";
				}
				else {
					$release = $_POST;
					unset($release["post"], $release['medium'], $release['format'], $release['venue_limitation'], $release['press_limitation_name']);
					$is_edit = is_numeric($release["id"]) ? true : false;
					
					
					
					
					
					// Format certain fields
					$release['romaji']        = match_japanese($release['name'], $release['romaji']);
					$release["user_id"]       = $_SESSION["user_id"];
					$release["friendly"]      = friendly($release["friendly"] ?: ($release["romaji"] ?: $release["name"])." ".($release["press_romaji"] ?: $release["press_name"])." ".($release["type_romaji"] ?: $release["type_name"]));
					$release["date_occurred"] = str_replace(["y", "m", "d"], "0", $release["date_occurred"]) ?: "0000-00-00";
					$release["notes"]         = sanitize($markdown_parser->validate_markdown($release["notes"])) ?: null;
					
					// Format price
					if(strlen($release['price'])) {
						$tmp_price = $release['price'];
						$tmp_price = sanitize($tmp_price);
						
						if(strpos($tmp_price, '&#65509;') !== false) {
							$tmp_price  = str_replace('&#65509;', '', $tmp_price);
							$tmp_price  = trim($tmp_price);
							$tmp_price .= ' yen';
						}
						
						if(strpos($tmp_price, '&euro;') !== false) {
							$tmp_price  = str_replace('&euro;', '', $tmp_price);
							$tmp_price  = trim($tmp_price);
							$tmp_price .= ' EUR';
						}
						
						$tmp_price = preg_replace('/'.'(&#[A-z0-9]+?;)'.'/', '', $tmp_price);
						
						if(strpos($tmp_price, 'EUR') !== false) {
							$tmp_price = str_replace(',', '.', $tmp_price);
						}
						
						$tmp_price = trim($tmp_price);
						$tmp_price = preg_replace('/'.'\s+'.'/', ' ', $tmp_price);
						$tmp_price = str_ireplace(['free', 'not for sale'], '0 yen', $tmp_price);
						$tmp_price = str_replace(['usd', 'eur', 'euro'], [' USD', 'EUR', 'EUR'], $tmp_price);
						
						if(preg_match('/'.'^[\d,]+$'.'/', $tmp_price)) {
							$tmp_price .= ' yen';
						}
						
						preg_match('/'.'([\d,]+)'.'/', $tmp_price, $price_match);
						if(is_array($price_match) && strlen($price_match[1])) {
							$tmp_price = str_replace($price_match[0], number_format(str_replace(',', '', $price_match[1])), $tmp_price);
						}
						
						$release['price'] = $tmp_price;
					}
					
					
					
					// Companies
					foreach(["label", "publisher", "distributor", "marketer", "manufacturer", "organizer"] as $company_type) {
						$company_type .= "_id";
						$release[$company_type] = (!empty($release[$company_type][0]) ? "(".implode(")(", $release[$company_type]).")" : null);
					}
					
					
					
					// Build tracklist
					foreach(["disc_name", "disc_romaji", "section_name", "section_romaji", "name", "romaji", "artist_id", "artist_display_name", "artist_display_romaji"] as $key) {
						for($i = 0; $i < count(reset($release["tracklist"])); $i++) {
							$tmp_tracklist[$i][$key] = $release["tracklist"][$key][$i];
						}
					}
					
					
					
					// Loop through tracklist, fill in missing info
					if(is_array($tmp_tracklist)) {
						foreach($tmp_tracklist as $line) {
							if(!empty($line["disc_name"])) {
								$disc_name = $line["disc_name"];
								$disc_romaji = $line["disc_romaji"] ?: null;
								$section_name = null;
								$section_romaji = null;
								$track_num = 0;
								$disc_num++;
								$section_num = 0;
								continue;
							}
							
							if(!empty($line["section_name"])) {
								$section_name = $line["section_name"];
								$section_romaji = $line["section_romaji"] ?: null;
								$section_num++;
								continue;
							}
							
							if(!empty($line["name"]) || $line["name"] === "0") {
								$track_num++;
								
								// Clean values
								$line['disc_romaji'] = match_japanese($line['disc_name'], $line['disc_romaji']);
								$line['section_romaji'] = match_japanese($line['section_name'], $line['section_romaji']);
								$line['romaji'] = match_japanese($line['name'], $line['romaji']);
								
								$tracklist[] = [
									"track_num" => $track_num ?: null,
									"disc_name" => $disc_name ?: null,
									"disc_romaji" => $disc_romaji ?: null,
									"disc_num" => is_numeric($disc_num) ? $disc_num : null,
									"section_name" => $section_name ?: null,
									"section_romaji" => $section_romaji ?: null,
									"section_num" => is_numeric($section_num) ? $section_num : null,
									"name" => $line["name"],
									"romaji" => $line["romaji"] ?: null,
									"artist_id" => is_numeric($line["artist_id"]) ? $line["artist_id"] : $release["artist_id"],
									"artist_display_name" => $line["artist_display_name"] ?: null,
									"artist_display_romaji" => $line["artist_display_romaji"] ?: null
								];
							}
						}
					}
					
					
					// Clean tracklist
					if(is_array($tracklist) && !empty($tracklist)) {
						$release["tracklist"] = $tracklist;
						
						array_walk_recursive($release, "clean_values");
						
						// Get remaining fields
						foreach($release as $key => $value) {
							if(!in_array($key, ["id", "tracklist"]) && strpos($key, "image_") !== 0) {
								$sql_keys[] = $key;
								$sql_values[] = $value;
							}
						}
						
						// Set up queries
						if($is_edit) {
							$sql_release = "UPDATE releases SET ".implode("=?, ", $sql_keys)."=? WHERE id=? LIMIT 1";
							array_push($sql_values, $release["id"]);
						}
						else {
							$sql_release = "INSERT INTO releases (".implode(", ", $sql_keys).") VALUES(".implode(", ", array_fill(0, count($sql_values), "?")).")";
						}
						$stmt = $pdo->prepare($sql_release);
						
						// Run main query
						if($stmt) {
							if($stmt->execute($sql_values)) {
								$release["id"] = is_numeric($release["id"]) ? $release["id"] : $pdo->lastInsertId();
								
								// Update edits table
								$sql_edit_history = 'INSERT INTO edits_releases (release_id, user_id, content) VALUES (?, ?, ?)';
								$stmt_edit_history = $pdo->prepare($sql_edit_history);
								if($stmt_edit_history->execute([
									$release['id'], 
									$_SESSION['user_id'], 
									($is_edit ? null : 'created') 
								])) {
								}
								
								// Update medium/format/venue/pressing type
								// For venue, set a default of 'available everywhere'
								if(!is_array($_POST['venue_limitation']) || empty($_POST['venue_limitation'])) {
									$_POST['venue_limitation'][] = 34;
								}
								if(!is_numeric($_POST['press_limitation_name'])) {
									$_POST['press_limitation_name'] = 42;
								}
								
								// Since all are 'release attributes', combine arrays, then update releases_releases_attributes
								$release_attributes = [];
								foreach(['medium', 'format', 'venue_limitation', 'press_limitation_name'] as $key) {
									if(is_array($_POST[$key]) && !empty($_POST[$key])) {
										$release_attributes = array_merge($release_attributes, $_POST[$key]);
									}
									elseif(!is_array($_POST[$key]) && is_numeric($_POST[$key])) {
										$release_attributes[] = $_POST[$key];
									}
								}
								
								// Loop through release attributes and clean
								if(is_array($release_attributes) && !empty($release_attributes)) {
									foreach($release_attributes as $release_attribute_key => $release_attribute) {
										if(!is_numeric($release_attribute)) {
											unset($release_attributes[$release_attribute_key]);
										}
									}
								}
								
								// Check current release/attribute connections
								$values_del_attributes = [];
								$sql_extant_attributes = 'SELECT * FROM releases_releases_attributes WHERE release_id=?';
								$stmt_extant_attributes = $pdo->prepare($sql_extant_attributes);
								$stmt_extant_attributes->execute([ $release['id'] ]);
								$rslt_extant_attributes = $stmt_extant_attributes->fetchAll();
								
								foreach($rslt_extant_attributes as $extant_attribute_key => $extant_attribute) {
									
									// If already set, remove from query
									if(in_array($extant_attribute['attribute_id'], $release_attributes)) {
										$duplicate_key = array_search($extant_attribute['attribute_id'], $release_attributes);
										unset($release_attributes[$duplicate_key]);
									}
									
									// If was set but now isn't, remove from DB
									else {
										$values_del_attributes[] = $extant_attribute['id'];
									}
								}
								
								// Remove old attributes
								if(is_array($values_del_attributes) && !empty($values_del_attributes)) {
									$sql_del_attributes = 'DELETE FROM releases_releases_attributes WHERE '.substr(str_repeat('id=? OR ', count($values_del_attributes)), 0, -4);
									$stmt_del_attributes = $pdo->prepare($sql_del_attributes);
									$stmt_del_attributes->execute($values_del_attributes);
								}
								
								// Add new attributes
								if(is_array($release_attributes) && !empty($release_attributes)) {
									$sql_new_attributes = 'INSERT INTO releases_releases_attributes (attribute_id, release_id) VALUES '.substr(str_repeat('(?, ?), ', count($release_attributes)), 0, -2);
									$values_new_attributes = [];
									
									foreach($release_attributes as $release_attribute) {
										$values_new_attributes[] = $release_attribute;
										$values_new_attributes[] = $release['id'];
									}
									
									$stmt_new_attributes = $pdo->prepare($sql_new_attributes);
									$stmt_new_attributes->execute($values_new_attributes);
								}
								
								$sql_extant_tracks = "SELECT id FROM releases_tracklists WHERE release_id=?";
								$stmt = $pdo->prepare($sql_extant_tracks);
								$stmt->execute([$release["id"]]);
								
								foreach($stmt->fetchAll() as $key => $extant_id) {
									if(!empty($release["tracklist"][$key])) {
										$sql_new_tracks[] = "UPDATE releases_tracklists SET ".implode("=?, ", array_keys($release["tracklist"][$key]))."=? WHERE id=? LIMIT 1";
										$sql_new_track_values[] = array_values(array_merge($release["tracklist"][$key], $extant_id));
										
										unset($release["tracklist"][$key]);
									}
									else {
										$sql_new_tracks[] = "DELETE FROM releases_tracklists WHERE id=? LIMIT 1";
										$sql_new_track_values[] = [$extant_id["id"]];
									}
								}
								
								if(is_array($release["tracklist"]) && !empty($release["tracklist"])) {
									foreach($release["tracklist"] as $key => $track) {
										$sql_new_tracks[] = "INSERT INTO releases_tracklists (".implode(", ", array_keys($release["tracklist"][$key])).", release_id) VALUES (".implode(", ", array_fill(0, count($release["tracklist"][$key]), "?")).", ?)";
										$sql_new_track_values[] = array_values(array_merge($release["tracklist"][$key], [$release["id"]]));
									}
								}
								
								if(is_array($sql_new_tracks) && !empty($sql_new_tracks) && is_array($sql_new_track_values) && count($sql_new_tracks) === count($sql_new_track_values)) {
									foreach($sql_new_tracks as $key => $sql) {
										$stmt = $pdo->prepare($sql);
										if($stmt->execute($sql_new_track_values[$key])) {
											$output["status"] = "success";
											
											$access_artist = new access_artist($pdo);
											$artist = $access_artist->access_artist(["id" => $release["artist_id"], "get" => "name"]);
											
											$output["url"] = "/releases/".$artist["friendly"]."/".$release["id"]."/".$release["friendly"]."/";
											$output["quick_name"] = ($release["romaji"] ?: $release["name"])." ".($release["press_romaji"] ?: $release["press_name"])." ".($release["type_romaji"] ?: $release["type_name"]);
											$output["artist_url"] = "/releases/".$artist["friendly"]."/";
											$output["artist_quick_name"] = $artist["quick_name"];
											$output["id"] = $release["id"];
											$output["artist_id"] = $artist["id"];
										}
									}
								}
								else {
									$output["status"] = "error";
									$output["result"] = "There was an error preparing the tracklist query.";
								}
								
								// Send to auto poster, if newly-added release
								if(!$is_edit) {
									$auto_post_url = $auto_blogger->auto_post('release', array_merge($release, ['attributes' => $release_attributes]));
									
									if($auto_post_url) {
										$output["status"] = "success";
										$output["result"] =
											'The blog has been updated with this information. Feel free to edit the post.'.
											'<br /><br/ >'.
											'<a class="a--outlined a--padded symbol__edit" href="'.$auto_post_url.'edit/">Edit blog entry</a>'.
											'<a class="a--padded symbol__news" href="'.$auto_post_url.'">View blog entry</a>';
									}
								}
								
								// Award point
								$access_points = new access_points($pdo);
								if($is_edit) {
									$access_points->award_points([ 'point_type' => 'edited-release', 'allow_multiple' => false, 'item_id' => $release['id'] ]);
								}
								else {
									$access_points->award_points([ 'point_type' => 'added-release' ]);
								}
							}
							else {
								$output["status"] = "error";
								$output["result"] = "Sorry, the release could not be added.";
							}
						}
						else {
							$output["result"] = "There was an error preparing the statement.";
						}
					}
					else {
						$output["status"] = "error";
						$output["result"] = "Each release must have at least one track (or a descriptor, such as &ldquo;contents unknown&rdquo;).";
					}
				}
			}
		}
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>