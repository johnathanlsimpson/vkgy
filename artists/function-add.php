<?php
	include_once("../php/include.php");
	
	function auto_link_musicians($pdo, $artist_id, $artist_name, $artist_romaji = null) {
		if(is_numeric($artist_id) && !empty($artist_name)) {
			$timestamp = date("Y-m-d H:i:s")." (".$_SESSION["userID"].")";
			$access_musician = new access_musician($pdo);
			
			$sql_musicians = "SELECT * FROM musicians WHERE history REGEXP CONCAT('(^|\n|, )', ?, '($|\n|,| [{\\\[(])')";
			$stmt_musicians = $pdo->prepare($sql_musicians);
			$stmt_musicians->execute([ ($artist_romaji ?: $artist_name) ]);
			$rslt_musicians = $stmt_musicians->fetchAll();
			
			if(is_array($rslt_musicians) && !empty($rslt_musicians)) {
				$tmp_result = [];
				
				for($i=0; $i<count($rslt_musicians); $i++) {
					$history = $rslt_musicians[$i]["history"];
					$artist_references = $access_musician->get_artist_references_from_band_history($history);
					
					$history = str_replace("\r\n", "\n", $history);
					$history = str_replace(["[*]\n", "[*]"], "", $history);
					$history = explode("\n", $history);
					
					if(is_array($history) && !empty($history) && is_array($artist_references) && !empty($artist_references)) {
						foreach($artist_references as $line_key => $ref_period) {
							foreach($ref_period as $ref) {
								if(!is_numeric($ref["id"])) {
									if($ref["name"] === $artist_name) {
										$history[$line_key] = preg_replace("/"."(^|, )(".preg_quote($artist_romaji ?: $artist_name).")( \(".preg_quote($artist_name)."\))?(,|$| \()"."/u", "$1(".$artist_id.")/".($artist_romaji ?: $artist_name)."/"."$4", $history[$line_key]);
										$matched = true;
									}
								}
							}
						}
					}
					
					$history = implode("\n", $history);
					
					if($matched) {
						$sql_musician = "UPDATE musicians SET history=? WHERE id=? LIMIT 1";
						$stmt_musician = $pdo->prepare($sql_musician);
						
						if($stmt_musician->execute([ $history, $rslt_musicians[$i]["id"] ])) {
							$sql_link = "INSERT INTO artists_musicians (artist_id, musician_id, position, to_end, unique_id) VALUES (?, ?, ?, ?, ?)";
							$stmt_link = $pdo->prepare($sql_link);
							
							$val_link = [
								$artist_id, 
								$rslt_musicians[$i]["id"], 
								($rslt_musicians[$i]["usual_position"] ?: 6), 
								1, 
								$artist_id."-".$rslt_musicians[$i]["id"]
							];
							
							if($stmt_link->execute($val_link)) {
								$output[] = '<a class="symbol__musician" href="/musicians/'.$rslt_musicians[$i]["id"].'/'.$rslt_musicians[$i]["friendly"].'/">'.($rslt_musicians[$i]["romaji"] ?: $rslt_musicians[$i]["name"]).'</a>';
							}
						}
					}
					
					unset($matched);
				}
				
				if(is_array($tmp_result) && !empty($tmp_result)) {
					unset($tmp_result);
				}
			}
		}
		
		return is_array($output) && !empty($output) ? $output : null;
	}
	
	if($_SESSION["admin"] && !empty($_POST)) {
		if(!empty($_POST["name"])) {
			foreach($_POST["name"] as $key => $name) {
				$name         = sanitize($name);
				$romaji       = sanitize($_POST["romaji"][$key]) ?: null;
				$friendly     = friendly($romaji ?: $name);
				
				if(!empty($name)) {
					$sql_check = "SELECT id, name, romaji, friendly FROM artists WHERE name=? OR friendly=? LIMIT 1";
					$stmt_check = $pdo->prepare($sql_check);
					$stmt_check->execute([$name, $friendly]);
					$row_check = $stmt_check->fetch();
					
					if(!empty($row_check)) {
						$linked_musicians = auto_link_musicians($pdo, $row_check["id"], $row_check["name"], $row_check["romaji"]);
						
						if(is_array($linked_musicians) && !empty($linked_musicians)) {
							$output["result"][] = '<a class="artist" href="/artists/'.$row_check["friendly"].'/">'.($row_check["romaji"] ?: $row_check["name"]).'</a> already exists. '.implode(", ", $linked_musicians).' linked to artist.';
							$output["status"] = "success";
						}
						else {
							$output["result"][] = '<a class="artist" href="/artists/'.$row_check["friendly"].'/">'.($row_check["romaji"] ?: $row_check["name"]).'</a> already exists. No musicians linked to artist.';
						}
					}
					else {
						$kana_name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
						if(preg_match('/'.'^[ぁ-んァ-ン]+$'.'/', $kana_name)) {
							$pronunciation = $name;
						}
						else {
							$pronunciation = null;
						}
						
						$sql_add = "INSERT INTO artists (name, romaji, friendly, pronunciation) VALUES (?, ?, ?, ?)";
						$stmt_add = $pdo->prepare($sql_add);
						if($stmt_add->execute([ $name, $romaji, $friendly, $pronunciation ])) {
							$artist_id = $pdo->lastInsertId();
							$linked_musicians = auto_link_musicians($pdo, $artist_id, $name, $romaji);
							
							$sql_edit_history = 'INSERT INTO edits_artists (artist_id, user_id, content) VALUES (?, ?, ?)';
							$stmt_edit_history = $pdo->prepare($sql_edit_history);
							if($stmt_edit_history->execute([ $artist_id, $_SESSION['userID'], 'created' ])) {
							}
							
							if(is_array($linked_musicians) && !empty($linked_musicians)) {
								$output["result"][] = '<a class="artist" href="/artists/'.$friendly.'/">'.($romaji ?: $name).'</a> successfully added. '.implode(", ", $linked_musicians).' linked to artist.';
								$output["status"] = "success";
							}
							else {
								$output["result"][] = '<a class="artist" href="/artists/'.$friendly.'/">'.($romaji ?: $name).'</a> successfully added. No musicians linked to artist.';
								$output["status"] = "success";
							}
						}
						else {
							$output["result"][] = ($romaji ?: $name)." could not be added";
						}
					}
				}
			}
		}
	}
	
	$output["result"] = is_array($output["result"]) ? implode("<br />", $output["result"]) : null;
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>