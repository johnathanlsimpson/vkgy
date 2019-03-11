<?php
	include_once("../php/include.php");
	$access_blog = new access_blog($pdo);
	
	// User info
	$username = is_array($_GET) && !empty($_GET) ? sanitize(array_keys($_GET)[0]) : null;
	$sql_user = "SELECT COUNT(releases_collections.release_id) AS num_releases, users.username, users.is_vip, users.rank FROM users LEFT JOIN releases_collections ON releases_collections.user_id=users.id WHERE users.username=? LIMIT 1";
	$stmt_user = $pdo->prepare($sql_user);
	$stmt_user->execute([$username]);
	$rslt_user = $stmt_user->fetch();
	
	// Artist of day
	$sql_aod = "SELECT artists.friendly, COALESCE(artists.romaji, artists.name) AS quick_name FROM queued_aod LEFT JOIN artists ON artists.id=queued_aod.artist_id ORDER BY queued_aod.date_occurred DESC LIMIT 1";
	$stmt_aod = $pdo->prepare($sql_aod);
	$stmt_aod->execute();
	$artist_of_day = $stmt_aod->fetch();
	
	// Flyer of day
	$sql_fod = "SELECT COALESCE(artists.romaji, artists.name) AS quick_name FROM queued_fod LEFT JOIN images ON images.id=queued_fod.image_id LEFT JOIN artists ON images.artist_id=CONCAT('(', artists.id, ')') WHERE queued_fod.id='1'";
	$stmt_image_of_day = $pdo->prepare($sql_fod);
	$stmt_image_of_day->execute();
	$rslt_image_of_day = $stmt_image_of_day->fetchColumn();
	
	// Last-edited artist
	$sql_artist = "SELECT COALESCE(romaji, name) AS quick_name FROM artists ORDER BY id DESC LIMIT 1";
	$stmt_artist = $pdo->prepare($sql_artist);
	$stmt_artist->execute();
	$rslt_artist = $stmt_artist->fetchColumn();
	
	// Blog
	$entries = $access_blog->access_blog(["page" => "latest", "get" => "list", "limit" => 1]);
	$rslt_entries = $entries[0]["title"];
	
	// Set image variables
	$image_width = 600;
	$image_height = 150;
	$font = "../style/font-notosans.otf";
	$font_size = 10;
	
	// Create base image
	$image = imagecreatetruecolor($image_width, $image_height);
	imagealphablending($image, true);
	
	// Set image colors, fill
	$color["bg"] = imagecolorallocate($image, 230,230,230);
	$color["text"] = imagecolorallocate($image, 67,61,61);
	$color["light"] = imagecolorallocate($image, 128,128,128);
	$color["less_attention"] = imagecolorallocate($image, 111,17,49);
	imagefill($image,0,0,$color["bg"]);
	
	// Add user avatar and logo
	$avatar_img = '../usericons/avatar-'.$rslt_user["username"].'.png';
	$avatar_img = file_exists($avatar_img) ? $avatar_img : '../usericons/avatar-anonymous.png';
	$avatar_img = imagecreatefrompng($avatar_img);
	imagecopyresampled($image, $avatar_img, 5, (150 - 60 - 5), 0, 0, 60, 60, 200, 200);
	
	$cage_img = imagecreatefromjpeg('../style/cage.jpg');
	imagecopyresampled($image, $cage_img, 13.5, 10, 0, 0, 42, 65, 84, 130);
	
	// Add text
	$lines[] = [
		"text" => "vk.gy".$internal_image_path,
		"color" => "less_attention",
		"size" => 12,
		"margin" => 5,
		"preserve_y" => true
	];
	$lines[] = [
		"text" => " | visual kei library",
		"color" => "light",
		"size" => 12,
		"margin" => 5,
		"x" => 48
	];
	
	$lines[] = [
		"text" => 'New: '.html_entity_decode($rslt_entries, ENT_QUOTES, "UTF-8"),
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
		"color" => "light",
		"size" => 10,
		"preserve_y" => true
	];
	$lines[] = [
		"text" => html_entity_decode($rslt_image_of_day, ENT_QUOTES, "UTF-8"),
		"color" => "light",
		"size" => 10,
		"x" => 150,
		"margin" => 10,
		"preserve_y" => true
	];
	$lines[] = [
		"text" => html_entity_decode($rslt_artist, ENT_QUOTES, "UTF-8"),
		"color" => "light",
		"size" => 10,
		"x" => 300,
		"margin" => 20,
	];
	
	
	$lines[] = [
		"text" => $username ? "MY VKGY USERNAME" : null,
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
		"color" => "less_attention",
		"margin" => 5,
		"size" => 10,
		"preserve_y" => true,
	];
	$lines[] = [
		"text" => $username ? $rslt_user["num_releases"]." releases" : null,
		"color" => "less_attention",
		"size" => 10,
		"x" => 150,
		"preserve_y" => true
	];
	$lines[] = [
		"text" => "https://vk.gy/sig.jpg?yourUsername",
		"color" => "light",
		"size" => 10,
		"x" => 300
	];
	
	$x_margin = 10;
	$y = 10;
	
	// Loop through each text, calculate position, and add to image
	foreach($lines as $line_key => $line) {
		$line["height"] = imageftbbox($line["size"], 0, $font, $line["text"]);
		$line["offset"] = $line["height"][7] * -1;
		$line["height"] = abs($line["height"][1]) + abs($line["height"][7]);
		$line["y"] = $y + $line["offset"];
		
		imagefttext($image, $line["size"], 0, 60 + ($line["x"] ?: 10), $line["y"], $color[$line["color"]], $font, $line["text"]);
		
		if(!$line["preserve_y"]) {
			$y = $y + $line["height"] + $line["margin"];
		}
		
		$lines[$line_key] = $line;
	}

	// Display image
	header("Content-Type: image/jpeg");
	imagejpeg($image, null, 100);
?>