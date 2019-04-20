<?php
	include_once("../php/include.php");
	$access_blog = new access_blog($pdo);
	
	// User info
	$username = is_array($_GET) && !empty($_GET) ? array_keys($_GET)[0] : null;
	$sql_user = "SELECT COUNT(releases_collections.release_id) AS num_releases, users.username, users.is_vip, users.rank FROM users LEFT JOIN releases_collections ON releases_collections.user_id=users.id WHERE users.username=? LIMIT 1";
	$stmt_user = $pdo->prepare($sql_user);
	$stmt_user->execute([$username]);
	$rslt_user = $stmt_user->fetch();
	
	$sql_aod = "SELECT artists.friendly, COALESCE(artists.romaji, artists.name) AS quick_name FROM queued_aod LEFT JOIN artists ON artists.id=queued_aod.artist_id ORDER BY queued_aod.date_occurred DESC LIMIT 1";
	$stmt_aod = $pdo->prepare($sql_aod);
	$stmt_aod->execute();
	$artist_of_day = $stmt_aod->fetch();
	
	$sql_fod = 'SELECT COALESCE(artists.romaji, artists.name) AS quick_name FROM queued_fod LEFT JOIN images_artists ON images_artists.image_id=queued_fod.image_id LEFT JOIN artists ON artists.id=images_artists.artist_id WHERE queued_fod.id=? LIMIT 1';
	$stmt_image_of_day = $pdo->prepare($sql_fod);
	$stmt_image_of_day->execute([ 1 ]);
	$rslt_image_of_day = $stmt_image_of_day->fetchColumn();
	
	$sql_artist = "SELECT COALESCE(artists.romaji, artists.name) AS quick_name FROM edits_artists LEFT JOIN artists ON artists.id=edits_artists.artist_id ORDER BY edits_artists.id DESC LIMIT 1";
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

	
	header("Content-Type: image/jpeg");
	imagejpeg($image, null, 100);
?>