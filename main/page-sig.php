<?php
	include_once("../php/include.php");
	$access_blog = new access_blog($pdo);
	
	// User info
	$username = is_array($_GET) && !empty($_GET) ? array_keys($_GET)[0] : null;
	$sql_user = "SELECT COUNT(releases_collections.release_id) AS num_releases, users.username, users.is_vip, users.rank FROM users LEFT JOIN releases_collections ON releases_collections.user_id=users.id WHERE users.username=? LIMIT 1";
	$stmt_user = $pdo->prepare($sql_user);
	$stmt_user->execute([$username]);
	$rslt_user = $stmt_user->fetch();
	
	
	$sql_aod = "SELECT artists.friendly, COALESCE(artists.romaji, artists.name) AS quick_name FROM artists_of_day LEFT JOIN artists ON artists.id=artists_of_day.artist_id ORDER BY artists_of_day.date_occurred DESC LIMIT 1";
	$stmt_aod = $pdo->prepare($sql_aod);
	$stmt_aod->execute();
	$artist_of_day = $stmt_aod->fetch();

	$sql_fod = "SELECT COALESCE(artists.romaji, artists.name) AS quick_name FROM images_of_day LEFT JOIN images ON images.id=images_of_day.image_id LEFT JOIN artists ON images.artist_id=CONCAT('(', artists.id, ')') WHERE images_of_day.id='1'";
	$stmt_image_of_day = $pdo->prepare($sql_fod);
	$stmt_image_of_day->execute();
	$rslt_image_of_day = $stmt_image_of_day->fetchColumn();
	
	$sql_artist = "SELECT COALESCE(romaji, name) AS quick_name FROM artists ORDER BY edit_history DESC LIMIT 1";
	$stmt_artist = $pdo->prepare($sql_artist);
	$stmt_artist->execute();
	$rslt_artist = $stmt_artist->fetchColumn();
	
	$entries = $access_blog->access_blog(["page" => "latest", "get" => "list", "limit" => 1]);
	$rslt_entries = $entries[0]["title"];
	
	$image_width = 600;
	$image_height = 150;
	$font = "../style/font-notosans.otf";
	$font_size = 10;
	
	$image = imagecreatetruecolor($image_width, $image_height);
	imagealphablending($image, true);
	
	$color["bg"] = imagecolorallocate($image, 230,230,230);
	$color["text"] = imagecolorallocate($image, 67,61,61);
	$color["light"] = imagecolorallocate($image, 128,128,128);
	$color["less_attention"] = imagecolorallocate($image, 111,17,49);
	imagefill($image,0,0,$color["bg"]);

	$png = imagecreatefromjpeg('../style/cage.jpg');
	//$bg_image_scaled = imagescale($png, (84), (130));
	imagecopymerge($image, $png, (600 - 84 - 10), 10, 0, 0, (84), (130), 100);
	
	
	$lines[] = [
		"text" => "vk.gy".$internal_image_path,
		"color" => "less_attention",
		"size" => 12,
		"margin" => 5,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => " | latest news",
		"color" => "light",
		"size" => 12,
		"margin" => 5,
		"x" => 48
	];
	
	/*$lines[] = [
		"text" => "LATEST NEWS",
		"color" => "light",
		"size" => 8,
		"margin" => 5
	];*/
	
	$lines[] = [
		"text" => html_entity_decode($rslt_entries, ENT_QUOTES, "UTF-8"),
		"color" => "text",
		"size" => 10,
		"margin" => 20,
	];
	
	$lines[] = [
		"text" => "ARTIST OF DAY",
		"color" => "light",
		"size" => 8,
		"margin" => 5,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => "FLYER OF DAY",
		"color" => "light",
		"size" => 8,
		"margin" => 5,
		"x" => 150,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => "RECENTLY UPDATED",
		"color" => "light",
		"size" => 8,
		"margin" => 5,
		"x" => 300,
	];
	
	
	$lines[] = [
		"text" => html_entity_decode($artist_of_day["quick_name"], ENT_QUOTES, "UTF-8"),
		"color" => "text",
		"size" => 10,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => html_entity_decode($rslt_image_of_day, ENT_QUOTES, "UTF-8"),
		"color" => "text",
		"size" => 10,
		"x" => 150,
		"margin" => 10,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => html_entity_decode($rslt_artist, ENT_QUOTES, "UTF-8"),
		"color" => "text",
		"size" => 10,
		"x" => 300,
		"margin" => 20,
	];
	
	
	$lines[] = [
		"text" => $username ? "MY USERNAME" : null,
		"color" => "light",
		"size" => 8,
		"margin" => 5,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => $username ? "MY VK COLLECTION" : null,
		"color" => "light",
		"margin" => 5,
		"size" => 8,
		"x" => 150,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => "GET  YOURS  AT",
		"color" => "light",
		"margin" => 5,
		"size" => 8,
		"x" => 300
	];
	
	$lines["username"] = [
		"text" => $rslt_user["username"],
		"color" => "light",
		"margin" => 5,
		"size" => 10,
		"preserve_y" => true,
	];
	
	$lines[] = [
		"text" => $username ? $rslt_user["num_releases"]." releases" : null,
		"color" => "light",
		"size" => 10,
		"x" => 150,
		"preserve_y" => true
	];
	
	$lines[] = [
		"text" => "https://vk.gy/sig.jpg?user",
		"color" => "light",
		"size" => 10,
		"x" => 300
	];
	
	$x_margin = 10;
	$y = 10;
	
	foreach($lines as $line_key => $line) {
		$line["height"] = imageftbbox($line["size"], 0, $font, $line["text"]);
		$line["offset"] = $line["height"][7] * -1;
		$line["height"] = abs($line["height"][1]) + abs($line["height"][7]);
		$line["y"] = $y + $line["offset"];
		
		imagefttext($image, $line["size"], 0, $line["x"] ?: 10, $line["y"], $color[$line["color"]], $font, $line["text"]);
		
		if(!$line["preserve_y"]) {
			$y = $y + $line["height"] + $line["margin"];
		}
		
		$lines[$line_key] = $line;
	}
	
	/*if(is_array($rslt_user) && !empty($rslt_user) && $rslt_user["is_vip"]) {
		$x = imageftbbox($lines["username"]["size"], 0, $font, $lines["username"]["text"]);
		$x = abs($x[0]) + abs($x[2]);
		$x = $x_margin + $x + 5;
		
		$vip_margin = 3;
		$vip_text = "VIP";
		$vip_dimensions = imageftbbox($lines["username"]["size"], 0, $font, $vip_text);
		$vip_width = abs($vip_dimensions[0]) + abs($vip_dimensions[2]);
		
		imagerectangle($image, $x, $lines["username"]["y"] - $lines["username"]["offset"], $x + $vip_margin + $vip_width + $vip_margin - 1, $lines["username"]["y"] - $lines["username"]["offset"] + $lines["username"]["height"], $color["less_attention"]);
		
		imagefttext($image, $lines["username"]["size"], 0, $x + $vip_margin, $lines["username"]["y"], $color["less_attention"], $font, $vip_text);
	}*/
	
	/*imagefttext($image, 12, 0, 10, 30, $color["less_attention"], $font, "vk.gy visual kei database");
	imagefttext($image, 8, 0, 10, 40, $color["light"], $font, "2018-04-04 BY INARTISTIC");
	imagefttext($image, 12, 0, 10, 60, $color["text"], $font, "llll-Ligro- vocalist gone, album cancelled, will disband");
	
	imagefttext($image, 8, 0, 10, 85, $color["light"], $font, "ARTIST OF THE DAY");
	imagefttext($image, 12, 0, 10, 100, $color["text"], $font, $artist_of_day);
	
	imagefttext($image, 8, 0, 300, 85, $color["light"], $font, "FLYER OF THE DAY");
	imagefttext($image, 12, 0, 300, 100, $color["text"], $font, $rslt_image_of_day);*/
	
	//$text_height = imageftbbox(16, 0, $font, $text)[2];
	
	//imagefttext($image, $font_size, 0, 20, 20, $color["text"], $font, $text);
	
	/*foreach($text as $t) {
		if($t["align"] === "right") {
			$temp_x = $image_width - $x - imageftbbox($t["size"], 0, $font, $t["line"])[2] - (49 + 5);
		}
		
		imagefttext($image, $t["size"], 0, $temp_x ?: $x, $y + $t["offset"], $t["color"] ? $color[$t["color"]] : $color["text"], $font, $t["line"]);
		
		$y = $y + $t["height"];
		
		unset($temp_x);
	}*/

	
	header("Content-Type: image/jpeg");
	imagejpeg($image, null, 100);
?>