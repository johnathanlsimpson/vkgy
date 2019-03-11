<?php
	include_once("../php/include.php");
	$access_release = new access_release($pdo);
	//$debug_on = true;
	
	// Verify user agent
	if(stripos($_SERVER["HTTP_USER_AGENT"], "mp3tag") !== false) {
		$is_mp3tag = true;
	}

	// Verify VIP
	if($_SESSION["loggedIn"]) {
		$sql_verify = "SELECT tag_hash FROM users WHERE username=? AND is_vip=? LIMIT 1";
		$stmt_verify = $pdo->prepare($sql_verify);
		$stmt_verify->execute([ sanitize($_SESSION["username"]), "1" ]);
		$rslt_verify = $stmt_verify->fetchColumn();
		
		$is_vip = !empty($rslt_verify) ? true : false;
		
		if(!empty($rslt_verify)) {
			$_GET["hash"] = $rslt_verify;
		}
	}
	elseif(strlen($_GET["username"]) > 0 && strlen($_GET["hash"]) > 0) {
		$sql_verify = "SELECT 1 FROM users WHERE username=? AND tag_hash=? LIMIT 1";
		$stmt_verify = $pdo->prepare($sql_verify);
		$stmt_verify->execute([ sanitize($_GET["username"]), sanitize($_GET["hash"]) ]);
		$rslt_verify = $stmt_verify->fetchColumn();
		
		$is_vip = $rslt_verify ? true : false;
	}
	
	if(!$is_mp3tag && !$debug_on && $_SESSION["loggedIn"]) {
		if(is_numeric($_GET["release_id"])) {
			$sql_url = "SELECT releases.id, releases.friendly, artists.friendly AS artist_friendly FROM releases LEFT JOIN artists ON artists.id=releases.artist_id WHERE releases.id=? LIMIT 1";
			$stmt_url = $pdo->prepare($sql_url);
			$stmt_url->execute([ sanitize($_GET["release_id"]) ]);
			$rslt_url = $stmt_url->fetch();
			
			$url = "https://vk.gy/releases/".$rslt_url["artist_friendly"]."/".$rslt_url["id"]."/".$rslt_url["friendly"]."/";
			
			header("Location: ".$url);
			die();
		}
		else {
			$txt_file = "../releases/script-tag-source.txt";
			$txt_file = file_get_contents($txt_file);
			$txt_file = str_replace(["{username}", "{hash}", "\n"], [$_SESSION["username"], $_GET["hash"], "\r\n"], $txt_file);
			
			header("Content-Disposition: attachment; filename=\"vkgy.src\"");
			header("Content-Type: application/force-download");
			header("Content-Length: " . strlen($txt_file));
			header("Connection: close");
			print_r($txt_file);
		}
	}
	
	elseif($is_mp3tag || ($debug_on && $_SESSION["admin"] > 1)) {
		// Format input: artist_album
		if(strlen($_GET["artist_album"]) > 0) {
			$input = strtolower(urldecode($_GET["artist"]));
			$input = explode("|", $_GET["artist_album"]);
			
			if(is_array($input) && count($input) === 2) {
				$_GET["artist"] = $input[0];
				$_GET["album"] = $input[1];
				
				unset($input);
			}
			else {
				$_GET["artist"] = $_GET["artist_album"];
			}
		}
		
		// Format input: release ID
		if(!is_numeric($_GET["release_id"])) {
			unset($_GET["release_id"]);
		}
		
		// Get list: artist, artist_album
		if(strlen($_GET["artist"]) > 0) {
			$rslt_releases = $access_release->access_release([ "artist_display_name" => sanitize($_GET["artist"]), "get" => "list" ]);
			
			if(is_array($rslt_releases) && !empty($rslt_releases)) {
				foreach($rslt_releases as $release) {
					if(
						empty($_GET["album"])
						||
						strpos(strtolower($release["friendly"]), sanitize($_GET["album"])) !== false
						||
						(strlen(friendly($_GET["album"])) > 1 && strpos($release["friendly"], friendly($_GET["album"])) !== false)
						||
						strpos(strtolower($release["name"]), sanitize($_GET["album"])) !== false
						||
						strpos(strtolower($release["romaji"]), sanitize($_GET["album"])) !== false
					) {
						echo
							"|".
							html_entity_decode($release["artist"]["quick_name"]).
							"|".
							"/releases/page-tag-source.php?release_id=".$release["id"]."&username=".$_GET["username"]."&hash=".$_GET["hash"].
							"|".
							html_entity_decode($release["quick_name"]).
							"\n";
					}
				}
			}
			echo " "; // Trailing line break needed due to Mp3tag bug
		}
		
		// Get list: album
		elseif(strlen($_GET["album"]) > 0) {
			$rslt_releases = $access_release->access_release([ "release_name" => $_GET["album"], "get" => "list" ]);
			
			if(is_array($rslt_releases) && !empty($rslt_releases)) {
				foreach($rslt_releases as $release) {
					echo
						"|".
						html_entity_decode($release["artist"]["quick_name"]).
						"|".
						"/releases/page-tag-source.php?release_id=".$release["id"].
						"|".
						html_entity_decode($release["quick_name"]).
						"\n";
				}
			}
			echo " "; // Trailing line break needed due to Mp3tag bug
		}
		
		// Get details: album ID
		elseif(is_numeric($_GET["release_id"])) {
			$rslt_release = $access_release->access_release([ "release_id" => $_GET["release_id"], "get" => "basics", "tracklist" => "flat" ]);
			
			// If multi-artist
			if($rslt_release["artist"]["id"] === 0) {
				$is_multi_artist = true;
			}
			if(!$is_multi_artist) {
				foreach($rslt_release["tracklist"] as $track) {
					if($track["artist"]["id"] !== $rslt_release["artist"]["id"]) {
						$is_multi_artist = true;
						break;
					}
				}
			}
			
			// If multi-disc
			foreach($rslt_release["tracklist"] as $track) {
				if(strlen($track["disc_name"]) > 0) {
					$is_multi_disc = true;
					break;
				}
			}
			
			// If needs album sort
			if(strlen($rslt_release["romaji"]) > 0) {
				$needs_album_sort = true;
			}
			if(!$needs_album_sort) {
				foreach($rslt_release["tracklist"] as $track) {
					if(strlen($track["disc_romaji"]) > 0) {
						$needs_album_sort = true;
						break;
					}
				}
			}
			
			// Album title
			$rslt_release["romaji"] =
				($rslt_release["romaji"] || $rslt_release["press_romaji"] || $rslt_release["type_romaji"] ?
					($rslt_release["romaji"] ?: $rslt_release["name"]).
					($rslt_release["press_name"] ? " ".($rslt_release["press_romaji"] ?: $rslt_release["press_name"]) : null).
					($rslt_release["type_name"] ? " ".($rslt_release["type_romaji"] ?: $rslt_release["type_name"]) : null)
					:
					null
				);
			
			$rslt_release["name"] =
				$rslt_release["name"].
				($rslt_release["press_name"] ? " ".$rslt_release["press_name"] : null).
				($rslt_release["type_name"] ? " ".$rslt_release["type_name"] : null);
			
			if($is_multi_disc) {
				foreach($rslt_release["tracklist"] as $track) {
					$album[] = str_replace("|", "\\|", $rslt_release["name"].($track["disc_name"] !== "CD" ? " ".$track["disc_name"] : null));
					$album_sort[] = str_replace("|", "\\|", (($rslt_release["romaji"] ?: $rslt_release["name"]).($track["disc_name"] !== "CD" ? " ".($track["disc_romaji"] ?: $track["disc_name"]) : null)));
				}
				
				$rslt_release["name"] = implode("|", $album);
				$rslt_release["romaji"] = $needs_album_sort ? implode("|", $album_sort) : $rslt_release["romaji"];
			}
			
			// Artist 
			if($is_multi_artist) {
				foreach($rslt_release["tracklist"] as $track) {
					$artist[] = str_replace("|", "\\|", $track["artist"]["name"]);
					$artist_sort[] = str_replace("|", "\\|", ($track["artist"]["romaji"] ?: $track["artist"]["name"]));
				}
				
				$rslt_release["artist"]["name"] = implode("|", $artist);
				$rslt_release["artist"]["romaji"] = implode("|", $artist_sort);
			}
			
			// Cover
			if($rslt_release["cover"]) {
				$rslt_release["cover"] = "http://vk.gy".$rslt_release["cover"].($is_vip ? "?username=".$_GET["username"]."&hash=".$_GET["hash"] : null);
			}
			
			// Tracks
			foreach($rslt_release["tracklist"] as $track) {
				if(is_array($track["notes"]) && !empty($track["notes"])) {
					foreach($track["notes"] as $note) {
						$track["name"] = trim(substr_replace($track["name"], "", $note["name_offset"], $note["name_length"]));
						$track["romaji"] = trim(substr_replace($track["romaji"], "", $note["romaji_offset"], $note["romaji_length"]));
					}
				}
				
				$track["romaji"] = $track["romaji"] ?: $track["name"];
				
				$rslt_release["tracks_name"][] = str_replace("|", "\\|", $track["name"]);
				$rslt_release["tracks_romaji"][] = str_replace("|", "\\|", $track["romaji"]);
			}
			$rslt_release["tracks_name"] = implode("|", $rslt_release["tracks_name"]);
			$rslt_release["tracks_romaji"] = implode("|", $rslt_release["tracks_romaji"]);
			
			// Format output
			$output_release = [
				$rslt_release["artist"]["name"],
				($rslt_release["artist"]["romaji"] ?: $rslt_release["artist"]["name"]).(substr($rslt_release["artist"]["romaji"], -1) === "|" ? "|" : null), // Trailing pipe is needed due to Mp3tag bug
				$rslt_release["name"],
				$rslt_release["romaji"] ?: $rslt_release["name"],
				$is_multi_artist ? "omnibus" : null,
				"https://vk.gy/releases/".$rslt_release["artist"]["friendly"]."/".$rslt_release["id"]."/".$rslt_release["friendly"]."/",
				$rslt_release["cover"],
				substr($rslt_release["date_occurred"], 0, 4),
				"(".str_replace("-", ".", $rslt_release["date_occurred"]).")",
				"ビジュアル系",
				$rslt_release["tracks_name"],
				$rslt_release["tracks_romaji"].(substr($rslt_release["tracks_romaji"], -1) === "|" ? "|" : null) // Trailing pipe is needed due to Mp3tag bug
			];
			
			// Send output
			foreach($output_release as $line) {
				echo " ".html_entity_decode($line)."\n";
			}
		}
	}
?>