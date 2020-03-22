<?php
	$access_user = new access_user($pdo);
	$access_comment = new access_comment($pdo);
	$markdown_parser = new parse_markdown($pdo);
	
	breadcrumbs([
		"Support vkgy" => "/support/",
		"VIP forum" => "/vip/"
	]);
	
	subnav([
		"Become VIP" => "https://patreon.com/vkgy",
		"VIP forum" => "/vip/",
	]);
	
	subnav([
		"Add entry" => "/vip/add/",
	], 'interact', true);
	
	$pageTitle = "VIP section";
	$page_header = 'VIP section';
	
	background("../support/patreon-back.png");
	
	if($_SESSION["is_signed_in"] && is_numeric($_SESSION["user_id"])) {
		$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $_SESSION["user_id"] ]);
		$is_vip = $stmt_check->fetchColumn();
	}
	
	$sql_members = "SELECT username FROM users WHERE is_vip=1";
	$stmt_members = $pdo->prepare($sql_members);
	$stmt_members->execute();
	$rslt_members = $stmt_members->fetchAll();
	
	$sql_images_preview = "SELECT CONCAT('/images/', id, IFNULL(CONCAT('-', friendly), ''), '.', extension) AS url FROM images WHERE is_exclusive=? ORDER BY date_added DESC LIMIT 10";
	$stmt_images_preview = $pdo->prepare($sql_images_preview);
	$stmt_images_preview->execute([ 1 ]);
	$rslt_images_preview = $stmt_images_preview->fetchAll();
	
	$sql_entries = "SELECT vip.*, COUNT(comments.id) AS comment_count, users.username FROM vip LEFT JOIN users ON users.id=vip.user_id LEFT JOIN comments ON comments.item_id=vip.id AND comments.item_type=? ".(!$two_months ? "WHERE vip.date_occurred > '".date("Y-m-d", $last_month)."' AND" : "WHERE")." vip.friendly != ? GROUP BY vip.id ORDER BY vip.date_occurred DESC";
	$stmt_entries = $pdo->prepare($sql_entries);
	$stmt_entries->execute([ 2, "development" ]);
	$rslt_entries = $stmt_entries->fetchAll();
	
	if(!empty($_GET["friendly"])) {
		$_GET["friendly"] = friendly($_GET["friendly"]);
		
		$sql_entry = "SELECT * FROM vip WHERE friendly=? LIMIT 1";
		$stmt_entry = $pdo->prepare($sql_entry);
		$stmt_entry->execute([$_GET["friendly"]]);
		$entry = $stmt_entry->fetch();
		
		if(is_array($entry) && !empty($entry)) {
			$entry["comments"] = $access_comment->access_comment(["id" => $entry["id"], 'get_user_likes' => true, "type" => "vip", "get" => "all"]);
		}
		else {
			unset($entry);
		}
	}
	
	// Edit
	if($_GET["action"] === "update" && is_array($entry) && !empty($entry)) {
		breadcrumbs([
			$entry["title"] => "/vip/".$entry["friendly"]."/",
			"Edit" => "/vip/".$entry["friendly"]."/edit/"
		]);
		
		$pageTitle = "Edit VIP entry: ".$entry["title"];
		
		include("page-update.php");
	}
	// Add
	elseif($_GET["action"] === "update") {
		breadcrumbs([
			"Add" => "/vip/add/"
		]);
		
		$pageTitle = "Add VIP entry";
		
		include("../vip/page-update.php");
	}
	// Images
	elseif($_GET["friendly"] === "images") {
		breadcrumbs([
			"VIP images" => "/vip/images/"
		]);
		
		$sql_images = "SELECT *, CONCAT('/images/', id, IFNULL(CONCAT('-', friendly), ''), '.', extension) AS url FROM images WHERE is_exclusive=? ORDER BY date_added DESC LIMIT 50";
		$stmt_images = $pdo->prepare($sql_images);
		$stmt_images->execute([ 1 ]);
		$rslt_images = $stmt_images->fetchAll();
		
		$access_artist = new access_artist($pdo);
		$access_user = new access_user($pdo);
		
		for($i=0; $i<count($rslt_images); $i++) {
			$artists = str_replace("(", "", $rslt_images[$i]["artist_id"]);
			$artists = explode(")", $artists);
			$artists = array_filter($artists, "is_numeric");
			
			foreach($artists as $artist_key => $artist_id) {
				if(is_numeric($artist_id)) {
					$artists[$artist_key] = $access_artist->access_artist(["id" => $artist_id, "get" => "name"]);
				}
			}
			
			$rslt_images[$i]["username"] = $access_user->access_user(["id" => $rslt_images[$i]["user_id"], "get" => "name"])["username"];
			$rslt_images[$i]["artists"] = $artists;
		}
		
		$pageTitle = "VIP images";
		
		include("../vip/page-images.php");
	}
	// Entry
	elseif(is_array($entry) && !empty($entry)) {
		breadcrumbs([
			$entry["title"] => "/vip/".$entry["friendly"]."/"
		]);
		
		subnav([
			"Edit entry" => "/vip/".$entry["friendly"]."/edit/"
		], 'interact', true);
		
		$pageTitle = $entry["title"];
		
		include("../vip/page-entry.php");
	}
	// Index
	else {
		style("../vip/style-page-index.css");
		
		include("../vip/page-index.php");
	}
?>