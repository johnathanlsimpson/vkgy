<?php
	include_once("../php/include.php");
	
	if(is_numeric($_POST["artist_id"])) {
		$sql_songs = "SELECT name, romaji FROM releases_tracklists WHERE artist_id=? GROUP BY COALESCE(romaji, name) ORDER BY COALESCE(romaji, name) ASC";
		$stmt_songs = $pdo->prepare($sql_songs);
		$stmt_songs->execute([$_POST["artist_id"]]);
		$songs = $stmt_songs->fetchAll();
		
		function clean_song_title($input) {
			$input = preg_replace("/"."\s+"."/u", " ", $input);
			$input = preg_replace("/"."^[\s\n\t]*(.+?)[\s\n\t]*$"."/", "$1", $input);
			$input = preg_replace("/"."^\s*(.+?)\s*$"."/u", "$1", $input);
			$input = str_replace("&#92;", "\\", $input);
			$input = str_replace(["\\(", "\\)"], ["\\\\&#40;", "\\\\&#41;"], $input);
			$input = sanitize($input);
			$input = str_replace(["&#65374;", "&#8764;", "&#8765;", "&#12316;"], "~", $input);
			$input = str_replace(["&#65378;", "&#65379;", "&#65339;", "&#65341;", "&#65288;", "&#65289;"], ["&#12300;", "&#12301;", "[", "]", "(", ")"], $input);
			$input = preg_replace("/"."(.*?)\"(.+?)\"(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
			$input = preg_replace("/"."(.*?)&#34;(.+?)&#34;(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
			$input = preg_replace("/"."(\(.+\))"."/", "", $input);
			$input = preg_replace("/"."^ (.*)"."/", "$1", $input);
			$input = preg_replace("/"."(.*?) $"."/", "$1", $input);
			$input = preg_replace("/"."(.*?) \\$"."/", "$1", $input);
			return $input;
		}
		
		if(is_array($songs) && !empty($songs)) {
			foreach($songs as $key => $song) {
				$song["name"] = clean_song_title($song["name"]);
				$song["romaji"] = clean_song_title($song["romaji"]);
				$songs[$key] = $song;
				
				$tmp_song = $song["romaji"] ? $song["romaji"]." (".$song["name"].")" : $song["name"];
				$tmp_songs[$tmp_song] = $key;
			}
			
			if(is_array($tmp_songs) && !empty($tmp_songs)) {
				foreach($tmp_songs as $tmp_song => $key) {
					if($tmp_song) {
						$output[] = [
							"name" => "".$tmp_song
						];
					}
				}
			}
			echo json_encode($output);
		}
	}
?>