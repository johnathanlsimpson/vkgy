<?php
	include_once("../php/include.php");
	
	if($_SESSION["admin"] && !empty($_POST)) {
		if(is_array($_POST["name"]) && !empty($_POST["name"])) {
			foreach($_POST["name"] as $key => $name) {
				if(strlen($name) > 0) {
					$id = is_numeric($_POST["id"][$key]) ? $_POST["id"][$key] : null;
					$name = trim(sanitize($name));
					$romaji = sanitize(trim($_POST["romaji"][$key])) ?: null;
					$capacity = preg_replace('/'.'[^\d]'.'/', '', $_POST["capacity"][$key]);
					$capacity = is_numeric($capacity) ? $capacity : null;
					$area_id = is_numeric($_POST["area_id"][$key]) ? $_POST["area_id"][$key] : 0;
					$renamed_to = is_numeric($_POST["renamed_to"][$key]) ? $_POST["renamed_to"][$key] : null;
					$merge_with = is_numeric($_POST["merge_with"][$key]) ? $_POST["merge_with"][$key] : null;
					$parent_id = is_numeric($_POST["parent_id"][$key]) ? $_POST["parent_id"][$key] : null;
					$nicknames = sanitize($_POST["nicknames"][$key]);
					
					$sql_area = "SELECT name, romaji FROM areas WHERE id=?";
					$stmt_area = $pdo->prepare($sql_area);
					$stmt_area->execute([ $area_id ]);
					$rslt_area = $stmt_area->fetch();
					
					$friendly = $_POST["friendly"][$key];
					$friendly = strlen($friendly) > 0 ? $friendly : ($rlst_area["romaji"] ?: $rslt_area["name"]).' '.($romaji ?: $name);
					$friendly = friendly($friendly);
					
					$values_update = [ $name, $romaji, $friendly, $capacity, $area_id, $parent_id, $renamed_to ];
					
					unset($sql_update);
					
					if(is_numeric($id) && strlen($name)) {
						$sql_update = "UPDATE lives_livehouses SET name=?, romaji=?, friendly=?, capacity=?, area_id=?, parent_id=?, renamed_to=? WHERE id=? LIMIT 1";
						$values_update[] = $id;
					}
					elseif(strlen($name)) {
						$sql_check_name = 'SELECT 1 FROM lives_livehouses WHERE '.(strlen($_POST["friendly"][$key]) ? 'friendly' : 'name').'=? LIMIT 1';
						$stmt_check_name = $pdo->prepare($sql_check_name);
						$stmt_check_name->execute([ $name ]);
						$rslt_check_name = $stmt_check_name->fetchColumn();
						
						if(!$rslt_check_name) {
							$sql_update = "INSERT INTO lives_livehouses (name, romaji, friendly, capacity, area_id, parent_id, renamed_to) VALUES (?, ?, ?, ?, ?, ?, ?)";
						}
						else {
							$output["status"] = "error";
							$output["result"][] = 'A livehouse named <span class="any__note">'.$name.'</span> already exists.';
						}
					}
					
					if($sql_update) {
						$stmt_update = $pdo->prepare($sql_update);
						
						if($stmt_update->execute($values_update)) {
							if(!is_numeric($id)) {
								$id = $pdo->lastInsertId();
								
								// Award point
								$access_points = new access_points($pdo);
								$access_points->award_points([ 'point_type' => 'added-livehouse' ]);
							}
							
							if(strlen($nicknames) > 0) {
								$nicknames = explode(",", $nicknames);
								
								if(is_array($nicknames) && !empty($nicknames)) {
									$sql_check_nick = "SELECT livehouse_id FROM lives_nicknames WHERE nickname=? OR nickname=? LIMIT 1";
									
									foreach($nicknames as $nickname) {
										$nickname_no_spaces = str_replace(" ", "", $nickname);
										
										$stmt_check_nick = $pdo->prepare($sql_check_nick);
										$stmt_check_nick->execute([ $nickname, $nickname_no_spaces ]);
										$rslt_check_nick = $stmt_check_nick->fetchColumn();
										
										if(is_numeric($rslt_check_nick)) {
											if($rslt_check_nick != $id) {
												$taken_nicknames[] = $nickname;
											}
										}
										else {
											$sql_add_nick = "INSERT INTO lives_nicknames (livehouse_id, nickname) VALUES (?, ?)";
											$stmt_add_nick = $pdo->prepare($sql_add_nick);
											
											if($stmt_add_nick->execute([ $id, $nickname ])) {
												$successful_nicknames[] = $nickname;
											}
											else {
												$failed_nicknames[] = $nickname;
											}
										}
									}
								}
							}
							
							if(is_numeric($merge_with)) {
								$sql_current_livehouse = "SELECT * FROM lives_livehouses WHERE id=? LIMIT 1";
								$stmt_current_livehouse = $pdo->prepare($sql_current_livehouse);
								$stmt_current_livehouse->execute([ $id ]);
								$rslt_current_livehouse = $stmt_current_livehouse->fetch();
								
								$sql_second_livehouse = "SELECT * FROM lives_livehouses WHERE id=? LIMIT 1";
								$stmt_second_livehouse = $pdo->prepare($sql_second_livehouse);
								$stmt_second_livehouse->execute([ $merge_with ]);
								$rslt_second_livehouse = $stmt_second_livehouse->fetch();
								
								if(is_array($rslt_current_livehouse) && !empty($rslt_current_livehouse) && is_array($rslt_second_livehouse) && !empty($rslt_second_livehouse)) {
									foreach(["id", "name", "romaji", "friendly"] as $key) {
										unset($rslt_current_livehouse[$key]);
									}
									
									foreach($rslt_current_livehouse as $key => $value) {
										if(!$rslt_current_livehouse[$key]) {
											if(isset($rslt_second_livehouse[$key]) && strlen($rslt_second_livehouse[$key])) {
												$rslt_current_livehouse[$key] = $rslt_second_livehouse[$key];
											}
										}
									}
									
									if(is_array($rslt_current_livehouse) && !empty($rslt_current_livehouse)) {
										
										$sql_update_current_livehouse = "UPDATE lives_livehouses SET ".implode("=?, ", array_keys($rslt_current_livehouse))."=? WHERE id=? LIMIT 1";
										$values_update_current_livehouse = array_values(array_merge($rslt_current_livehouse, [$id]));
										$stmt_update_current_livehouse = $pdo->prepare($sql_update_current_livehouse);
										if($stmt_update_current_livehouse->execute($values_update_current_livehouse)) {
											
											$sql_update_lives = "UPDATE lives SET livehouse_id=? WHERE livehouse_id=?";
											$stmt_update_lives = $pdo->prepare($sql_update_lives);
											if($stmt_update_lives->execute([ $id, $rslt_second_livehouse["id"] ])) {
												
												$sql_delete_second_livehouse = "DELETE FROM lives_livehouses WHERE id=? LIMIT 1";
												$stmt_delete_second_livehouse = $pdo->prepare($sql_delete_second_livehouse);
												if($stmt_delete_second_livehouse->execute([ $rslt_second_livehouse["id"] ])) {
													
												}
											}
										}
									}
								}
							}
							
							$output["result"][] =
								'<a class="symbol__company" >'.
								($rslt_area["romaji"] ?: $rslt_area["name"]).($rslt_area["name"] ? ' ' : null).($romaji ?: $name).
								'</a> updated. '.
								(
									is_array($successful_nicknames) && !empty($successful_nicknames)
									? 'Nickname'.(count($successful_nicknames) > 1 ? 's' : null).' &ldquo;'.implode('&rdquo;, &ldquo;', $successful_nicknames).'&rdquo; added. '
									: null
								).
								(
									is_array($failed_nicknames) && !empty($failed_nicknames)
									? 'Nickname'.(count($failed_nicknames) > 1 ? 's' : null).' &ldquo;'.implode('&rdquo;, &ldquo;', $failed_nicknames).'&rdquo; could not be added. '
									: null
								).
								(
									is_array($taken_nicknames) && !empty($taken_nicknames)
									? 'Nickname'.(count($taken_nicknames) > 1 ? 's' : null).' &ldquo;'.implode('&rdquo;, &ldquo;', $taken_nicknames).'&rdquo; already being used. '
									: null
								);
							$output["status"] = "success";
							
							unset($successful_nicknames, $failed_nicknames, $taken_nicknames);
						}
						else {
							$output["status"] = "error";
							$output["result"][] = $name.' could not be updated.';
						}
					}
				}
			}
		}
	}
	
	$output["result"] = is_array($output["result"]) ? implode("<br />", $output["result"]) : ($output["result"] ?: "No changes made.");
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>