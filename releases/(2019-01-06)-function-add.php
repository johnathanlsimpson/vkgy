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
		if(!$_SESSION["loggedIn"]) {
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
					unset($release["post"]);
					$is_edit = is_numeric($release["id"]) ? true : false;
					
					
					
					
					
					// Format certain fields
					//$release["edit_history"]  = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")";
					$release["user_id"]       = $_SESSION["userID"];
					$release["friendly"]      = friendly($release["friendly"] ?: ($release["romaji"] ?: $release["name"])." ".($release["press_romaji"] ?: $release["press_name"])." ".($release["type_romaji"] ?: $release["type_name"]));
					$release["date_occurred"] = str_replace(["y", "m", "d"], "0", $release["date_occurred"]) ?: "0000-00-00";
					$release["notes"]         = sanitize($markdown_parser->validate_markdown($release["notes"])) ?: null;
					$release["medium"]        = is_array($release["medium"]) && !empty(array_filter($release["medium"])) ? "(".implode(")(", array_filter($release["medium"])).")" : null;
					$release["format"]        = is_array($release["format"]) && !empty(array_filter($release["format"])) ? "(".implode(")(", array_filter($release["format"])).")" : null;
					
					
					
					
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
							//if(!in_array($key, ["id", "edit_history", "tracklist"]) && strpos($key, "image_") !== 0) {
							if(!in_array($key, ["id", "tracklist"]) && strpos($key, "image_") !== 0) {
								$sql_keys[] = $key;
								$sql_values[] = $value;
							}
						}
						
						// Set up queries
						if($is_edit) {
							//$sql_release = "UPDATE releases SET ".implode("=?, ", $sql_keys)."=?, edit_history = CONCAT(?, '\n', edit_history) WHERE id=? LIMIT 1";
							//array_push($sql_values, $release["edit_history"], $release["id"]);
							$sql_release = "UPDATE releases SET ".implode("=?, ", $sql_keys)."=? WHERE id=? LIMIT 1";
							array_push($sql_values, $release["id"]);
						}
						else {
							//$sql_release = "INSERT INTO releases (".implode(", ", $sql_keys).", edit_history) VALUES(".implode(", ", array_fill(0, count($sql_values), "?")).", ?)";
							//array_push($sql_values, $release["edit_history"]);
							$sql_release = "INSERT INTO releases (".implode(", ", $sql_keys).") VALUES(".implode(", ", array_fill(0, count($sql_values), "?")).")";
						}
						$stmt = $pdo->prepare($sql_release);
						
						// Run query
						if($stmt) {
							if($stmt->execute($sql_values)) {
								$release["id"] = is_numeric($release["id"]) ? $release["id"] : $pdo->lastInsertId();
								
								$sql_edit_history = 'INSERT INTO edits_releases(release_id, user_id) VALUES (?, ?)';
								$stmt_edit_history = $pdo->prepare($sql_edit_history);
								$stmt_edit_history->execute([ $release['id'], $_SESSION['userID'] ]);
								
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
											$output["artist_url"] = "/artists/".$artist["friendly"]."/";
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
									$auto_post_url = $auto_blogger->auto_post('release', $release);
									
									if($auto_post_url) {
										$output["status"] = "success";
										$output["result"] =
											'A blog entry has been auto-generated, and will be shared to social media in 15 minutes. Any user may edit the entry; edits will be reflected in the social media posts.'.
											'<br /><br/ >'.
											'<a class="a--outlined a--padded symbol__edit" href="'.$auto_post_url.'edit/">Edit blog entry</a>'.
											'<a class="a--padded symbol__news" href="'.$auto_post_url.'">View blog entry</a>';
									}
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