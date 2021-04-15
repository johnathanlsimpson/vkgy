<?php
	include_once("../php/include.php");
	include_once("../artists/function-sort_musicians.php");
	
	$access_artist = new access_artist($pdo);
	$access_musician = new access_musician($pdo);
	$artist_friendly = friendly($_GET["friendly"]);
	$artist = $access_artist->access_artist([ "friendly" => $artist_friendly, "get" => "list" ]);
	$artist["musicians"] = $access_musician->access_musician([ "artist_id" => $artist["id"], "get" => "all" ]);
	$artist["musicians"] = sort_musicians($artist["musicians"]);
	
	$font = "../style/font-notosansmono.otf";
	$font = "../style/font-notosans.otf";
	$height = 0;
	$x = 20;
	$y = 20;
	
	$text[] = [
		"line" => $artist["quick_name"],
		"margin" => 5,
		"size" => 25,
		"color" => "attention"
	];
	
	if($artist["romaji"]) {
		$text[] = [
			"line" => "(".$artist["name"].")",
			"size" => 10
		];
	}
	
	$text[] = [
		"line" => "",
		"margin" => 25,
		"size" => 10,
	];
	
	if(is_array($artist["musicians"]) && !empty($artist["musicians"])) {
		unset($artist["musicians"][3]);
		
		foreach($artist["musicians"] as $group_key => $musician_group) {
			foreach($musician_group as $musician_key => $musician) {
				$position = strtoupper($musician["position_name"]);
				$position = strstr($position, "SUPPORT ") !== false ? "Sp ".substr(str_replace("SUPPORT ", "", $position), 0, 1) : substr($position, 0, 1);
				$position = $position.". ";
				$position = $group_key === 2 ? "ex-".$position : $position;
				
				$text[] = [
					"line" => $position.($musician["romaji"] ? $musician["romaji"]." (".$musician["name"].")" : $musician["name"]),
					"size" => 10,
					"margin" => 5,
					"color" => $group_key === 1 ? "name" : "less_attention",
				];
				
				$extant_lineup = "";
				
				if(is_array($musician["history"]) && !empty($musician["history"])) {
					foreach($musician["history"] as $period_key => $period) {
						foreach($period as $h_key => $h) {
							$extant_lineup .= $h["romaji"] ? $h["romaji"]." (".$h["name"].")" : $h["name"];
							$extant_lineup .= " ";
							
							if(is_array($h["notes"]) && !empty($h["notes"])) {
								foreach($h["notes"] as $note) {
									$extant_lineup .= $note;
									$extant_lineup .= " ";
								}
							}
							
							if($h_key + 1 < count($period)) {
								$extant_lineup .= ", ";
							}
						}
						
						if($period_key + 1 < count($musician["history"])) {
							$extant_lineup .= "→ ";
						}
					}
				}
				
				$text[] = [
					"line" => $extant_lineup,
					"size" => 9,
					"margin" => 20,
				];
			}
		}
	}
	
	$text[] = [
		"align" => "right",
		"line" => "last updated ".substr($artist["edit_history"], 0, 10),
		"margin" => 5,
		"size" => 10,
	];
	
	$text[] = [
		"align" => "right",
		"color" => "name",
		"line" => "https://vk.gy/artists/".$artist["friendly"]."/",
		"margin" => 5,
		"size" => 10,
	];
	
	$text[] = [
		"align" => "right",
		"color" => "less_attention",
		"line" => "provided by vk.gy",
		"margin" => 0,
		"size" => 10,
	];
	
	function wrap($string, $font, $font_size, $max_width) {
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		$string = preg_split("//u", $string, -1);
		
		$current_width = 0;
		$last_space_location = 0;
		
		for($pointer = 0; $pointer < count($string); $pointer++) {
			$char = $string[$pointer];
			$prev_char = $string[$pointer - 1];
			
			$prev_width = imageftbbox($font_size, 0, $font, $prev_char)[2];
			$char_width = imageftbbox($font_size, 0, $font, $prev_char.$char)[2];
			$current_width = $current_width + $char_width - $prev_width;
			
			if($char === " ") {
				$last_space_location = $pointer;
			}
			
			if($current_width > $max_width) {
				if($last_space_location) {
					$string[$last_space_location] = "\n";
					$pointer = $last_space_location + 1;
					$current_width = 0;
				}
				else {
					$string[$pointer] = "\n".$char;
					$current_width = $char_width;
				}
			}
		}
		
		return implode("", $string);
	}
	
	foreach($text as $t_key => $t) {
		$t["line"] = wrap($t["line"], $font, $t["size"], (500 - $x - $x));
		
		$t["height"] = imageftbbox($t["size"], 0, $font, $t["line"]);
		$t["offset"] = -1 * $t["height"][5];
		$t["height"] = abs($t["height"][5]) + abs($t["height"][1]) + $t["margin"];
		
		$text[$t_key] = $t;
		
		$height = $height + $t["height"];
	}
	
	$xsize = 500;
	$ysize = $y + $height + $y;
	
	$image = imagecreatetruecolor($xsize, $ysize);
	imagealphablending($image, true);
	$blue = imagecolorallocate($image, 230,230,230);
	imagefill($image,0,0,$blue);
	$color["white"] = imagecolorallocate($image, 255,255,255);
	$color["text"] = imagecolorallocate($image, 67,61,61);
	$color["attention"] = imagecolorallocate($image, 22,105,58);
	$color["name"] = imagecolorallocate($image, 0,54,112);
	$color["less_attention"] = imagecolorallocate($image, 111,17,49);
	
	$png = imagecreatefrompng('../style/Untitled.png');
	imagecopymerge($image, $png, (500 - $x - 49 + 4), ($ysize - 69 - $y + 5), 0, 0, 49, 69, 100);
	
	//
	// Get artist image
	//
	/*include_once("../images/function-get_image.php");
	$internal_image_path = get_image([
		"artist" => $artist_friendly,
		"not_vip" => true,
		"image_path_only" => true
	], $pdo);*/
	include_once('../php/class-access_image.php');
	$access_image = new access_image($pdo);
	$internal_image_path = $access_image->get_image([ 'artist' => $artist_friendly, 'not_vip' => true, 'image_path_only' => true ]);
	if($internal_image_path) {
		if(strstr($internal_image_path, "png") !== false) {
			$artist_image = imagecreatefrompng($internal_image_path);
		}
		else {
			$artist_image = imagecreatefromjpeg($internal_image_path);
		}
		imagealphablending($artist_image, true);
	}
	
	//
	// Resize background image
	//
	if($internal_image_path) {
		$bg_image_size = getimagesize($internal_image_path);
		$bg_image_ratio = $bg_image_size[0] / $bg_image_size[1];
		
		$output_image_ratio = $xsize / $ysize;
		
		if($bg_image_ratio > $output_image_ratio) {
			$bg_image_scaled = imagescale($artist_image, ($ysize * $bg_image_ratio), $ysize);
			$bg_image_x = (($ysize * $bg_image_ratio) - $xsize) / 2;
			$bg_image_y = 0;
		}
		else {
			$bg_image_scaled = imagescale($artist_image, $xsize, -1);
			$bg_image_x = 0;
			$bg_image_y = $xsize * $bg_image_ratio;
		}
		
		imagealphablending($bg_image_scaled, true);
		
		imagecopymerge($image, $bg_image_scaled, 0, 0, $bg_image_x, $bg_image_y, $xsize, $ysize, (100 * 0.1));
	}
	
	//
	// Resize artist image
	//
	if($internal_image_path) {
		$artist_image_scaled = imagescale($artist_image, (50 * $bg_image_ratio), 50, IMG_BICUBIC);
		
		imagecopymerge($image, $artist_image_scaled, 20, ($ysize - 20 - 50), 0, 0, ((50 * $bg_image_ratio) - 1), (50 - 1), 100);
	}
	
	
	
	//$penis = "../images/image_files/5092.jpg";
	//$cunt = imagecreatefromjpeg($penis);
	//imagealphablending($cunt, true);
	//imagesavealpha($cunt, true);
	//imagefilter($cunt, IMG_FILTER_COLORIZE, 50,0,50,0);
	//$trans = imagecolorallocate($cunt, 255,255,255);
	//$cock = imagescale($cunt, 500, -1, IMG_BICUBIC);
	//imagecopymerge($image, $cock, 0, 0, 0, 0, 500, 200, 50);
	
	//$final_height = 20 + 20;
	//foreach($text as $t) {
		//$final_height = $final_height + $t["height"] + $t["margin"] + $t["offset"];
	//}
	
	foreach($text as $t) {
		if($t["align"] === "right") {
			$temp_x = $xsize - $x - imageftbbox($t["size"], 0, $font, $t["line"])[2] - (49 + 5);
		}
		
		imagefttext($image, $t["size"], 0, $temp_x ?: $x, $y + $t["offset"], $t["color"] ? $color[$t["color"]] : $color["text"], $font, $t["line"]);
		
		$y = $y + $t["height"];
		
		unset($temp_x);
	}
	//$note = $internal_image_path;
	//imagefttext($image, 20, 0, 20, 20, $color["name"], $font, $note);

	header("Content-Type: image/jpg");
	imagejpeg($image, null, 100);
	imagedestroy($image);


?>