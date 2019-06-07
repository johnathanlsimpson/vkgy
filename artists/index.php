<?php	
	include_once("../artists/function-sort_musicians.php");
	include_once("../php/class-access_user.php");
	
	breadcrumbs([
		"Artists" => "/artists/"
	]);
	
	$access_artist = new access_artist($pdo);
	$access_user = new access_user($pdo);
	$markdown_parser = new parse_markdown($pdo);
	
	$pageTitle = "Artist list | アーティスト一覧";
	$page_description = "Visual kei artist list. Profiles, biographies, member histories. ビジュアル系アーティスト一覧。プロフィール、活動、リリース情報、など。";
	
	$page_header = lang('Artists', 'アーティスト', [ 'container' => 'div' ]);
	
	subnav([
		'Add artist' => '/artists/add/',
	], 'interact', true);
	
	// Choose page
	if(!empty($_GET["artist"])) {
		$artist = $access_artist->access_artist(["friendly" => friendly($_GET["artist"]), "get" => "all"]);
		if(is_array($artist) && !empty($artist)) {
			if($_GET["action"] === "edit") {
				if($_SESSION["admin"]) {
					$show_edit_page = true;
				}
				else {
					$error = 'Sorry, only admin users may access the edit artist page. Showing artist list instead.';
				}
			}
			else {
				$show_artist_page = true;
			}
		}
		else {
			$error = 'Sorry, <span class="any__note">'.friendly($_GET["artist"]).'</span> isn\'t in the database. Showing artist list instead.';
		}
	}
	if($_GET["action"] === "add") {
		if($_SESSION["admin"]) {
			$show_add_page = true;
		}
		else {
			$error = 'Sorry, only admin users may add artists. Showing artist list instead.';
		}
	}
	$_GET["letter"] = preg_match('/'.'^[A-z0\-]$'.'/', $_GET["letter"]) ? $_GET["letter"] : 'a';
	
	// Show page: Edit
	if($show_edit_page) {
		$pageTitle = "Edit ".$artist["quick_name"];
		
		breadcrumbs([
			$artist["quick_name"] => "/artists/".$artist["friendly"]."/",
			"Edit" => "/artists/".$artist["friendly"]."/edit/"
		]);
		
		// Remove uneditable lines from history
		if(is_array($artist["history"])) {
			foreach($artist["history"] as $history_key => $history_line) {
				if(in_array("is_uneditable", $history_line["type"])) {
					unset($artist["history"][$history_key]);
				}
			}
		}
		
		include("../artists/page-edit.php");
	}
	
	// Show page: Profile
	elseif($show_artist_page) {
		$pageTitle = $artist["quick_name"]." profile | ".$artist["name"]."&#12503;&#12525;&#12501;&#12451;&#12540;&#12523;";
		$page_description = $artist["quick_name"]." profile, biography, members' history. 「".$artist["name"]."」のプロフィール、活動、リリース情報、など。".($artist["lineup"] ? " (".$artist["lineup"].")" : null);
		$page_image = "https://vk.gy/artists/".$artist["friendly"]."/main.large.jpg";
		
		breadcrumbs([
			$artist["quick_name"] => "/artists/".$artist["friendly"]."/",
			"Profile" => "/artists/".$artist["friendly"]."/"
		]);
		
		subnav([
			"Edit artist" => "/artists/".$artist["friendly"]."/edit/"
		], true);
		
		// Get musicians' band history
		$artist["musicians"] = sort_musicians($artist["musicians"]);
		
		$sql_view = "INSERT INTO artists_views (artist_id, date_occurred, view_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE view_count = view_count + 1";
		$stmt_view = $pdo->prepare($sql_view);
		$stmt_view->execute([$artist["id"], date("Y-m-d")]);
		
		// Parse history
		if(is_array($artist["history"])) {
			foreach($artist["history"] as $history_line) {
				if(!in_array("is_uneditable", $history_line["type"])) {
					$history_line["content"] = $markdown_parser->parse_markdown($history_line["content"]);
				}
				
				if(!in_array(14, $history_line['type'])) {
					$tmp_history[substr($history_line["date_occurred"], 0, 4)][$history_line["date_occurred"]][] = $history_line;
				}
				else {
					$tmp_schedule[substr($history_line["date_occurred"], 0, 4)][$history_line["date_occurred"]][] = $history_line;
					
					$num_lives++;
				}
			}
			
			if(is_array($tmp_schedule) && !empty($tmp_schedule)) {
				foreach($tmp_schedule as $tmp_schedule_year => $tmp_schedule_dates) {
					foreach($tmp_schedule_dates as $tmp_schedule_date => $tmp_schedule_lives) {
						if(is_array($tmp_history[$tmp_schedule_year][$tmp_schedule_date])) {
							foreach($tmp_schedule_lives as $tmp_schedule_live) {
								array_push($tmp_history[$tmp_schedule_year][$tmp_schedule_date], $tmp_schedule_live);
							}
						}
					}
				}
			}
			
			$artist["history"] = $tmp_history;
			$artist['schedule'] = $tmp_schedule;
		}
		
		// Unname history keys
		if(is_array($artist["history"]) && !empty($artist["history"])) {
			$artist["history"] = array_values($artist["history"]);
			for($i = 0; $i < count($artist["history"]); $i++) {
				$artist["history"][$i] = array_values($artist["history"][$i]);
			}
		}
		
		// Get history types
		for($i = 0; $i < count($artist["history"]); $i++) {
			for($n = 0; $n < count($artist["history"][$i]); $n++) {
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					$types = "";
					
					foreach($artist["history"][$i][$n][$m]["type"] as $type) {
						$types .= " ".$access_artist->artist_bio_types[$type]." ";
					}
					
					$artist["history"][$i][$n][$m]["type"] = $types;
				}
			}
		}
		
		// Reorder history
		for($i = 0; $i < count($artist["history"]); $i++) {
			for($n = 0; $n < count($artist["history"][$i]); $n++) {
				$keys_to_move = [];
				
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					if(strstr($artist["history"][$i][$n][$m]["type"], " member ") !== false) {
						$keys_to_move[] = $m;
					}
				}
				
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					if(strstr($artist["history"][$i][$n][$m]["type"], " schedule ") !== false) {
						$keys_to_move[] = $m;
					}
				}
				
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					if(strstr($artist["history"][$i][$n][$m]["type"], " release ") !== false) {
						$keys_to_move[] = $m;
					}
				}
				
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					if(strstr($artist["history"][$i][$n][$m]["type"], " disbandment ") !== false) {
						$keys_to_move[] = $m;
						$artist["date_ended"] = $artist["history"][$i][$n][$m]["date_occurred"] > $artist["date_ended"] ? $artist["history"][$i][$n][$m]["date_occurred"] : $artist["date_ended"];
					}
					if(strstr($artist["history"][$i][$n][$m]["type"], " formation ") !== false) {
						$artist["date_occurred"] = $artist["history"][$i][$n][$m]["date_occurred"] > $artist["date_occurred"] ? $artist["history"][$i][$n][$m]["date_occurred"] : $artist["date_occurred"];
					}
				}
				
				for($m = 0; $m < count($artist["history"][$i][$n]); $m++) {
					if(strstr($artist["history"][$i][$n][$m]["type"], " lineup ") !== false) {
						$keys_to_move[] = $m;
					}
				}
				
				if(!empty($keys_to_move)) {
					foreach($keys_to_move as $key) {
						$artist["history"][$i][$n][] = $artist["history"][$i][$n][$key];
						unset($artist["history"][$i][$n][$key]);
					}
					$artist["history"][$i][$n] = array_values($artist["history"][$i][$n]);
				}
			}
		}
		
		// Format edit history
		$sql_edit_history = 'SELECT edits_artists.*, users.username FROM edits_artists LEFT JOIN users ON users.id=edits_artists.user_id WHERE edits_artists.artist_id=? ORDER BY date_occurred DESC';
		$stmt_edit_history = $pdo->prepare($sql_edit_history);
		$stmt_edit_history->execute([ $artist['id'] ]);
		$rslt_edit_history = $stmt_edit_history->fetchAll();
		$num_edit_history = count($rslt_edit_history);
		
		if(is_array($rslt_edit_history)) {
			$artist['edit_history'] = [];
			
			for($i=0; $i<$num_edit_history; $i++) {
				$e = $rslt_edit_history[$i];
				
				$artist['edit_history'][$e['date_occurred']]['username'] = $e['username'];
				$artist['edit_history'][$e['date_occurred']]['date_occurred'] = $e['date_occurred'];
				$artist['edit_history'][$e['date_occurred']]['content'][] = $e['content'];
			}
			
			$artist['edit_history'] = array_values($artist['edit_history']);
		}
		
		// Get comments
		$access_comment = new access_comment($pdo);
		$artist["comments"] = $access_comment->access_comment(["id" => $artist["id"], 'user_id' => $_SESSION['userID'], "type" => "artist", "get" => "all"]);
		
		// Get prev/next artist
		$sql_next = "
			(SELECT name, romaji, friendly, 'previous' AS type FROM artists WHERE friendly<? ORDER BY friendly DESC LIMIT 1)
			UNION
			(SELECT name, romaji, friendly, 'rand' AS type FROM artists ORDER BY RAND() LIMIT 1)
			UNION
			(SELECT name, romaji, friendly, 'next' AS type FROM artists WHERE friendly>? ORDER BY friendly ASC LIMIT 1)
		";
		$stmt_next = $pdo->prepare($sql_next);
		$stmt_next->execute([ $artist["friendly"], $artist["friendly"] ]);
		$rslt_next = $stmt_next->fetchAll();
		
		// Tags
		$sql_tags = "SELECT * FROM tags_artists ORDER BY friendly ASC";
		$stmt_tags = $pdo->prepare($sql_tags);
		$stmt_tags->execute();
		$rslt_tags = $stmt_tags->fetchAll();
		
		$sql_curr_tags = "SELECT tags_artists.*, COUNT(artists_tags.id) AS num_times_tagged FROM artists_tags LEFT JOIN tags_artists ON tags_artists.id=artists_tags.tag_id WHERE artists_tags.artist_id=? GROUP BY artists_tags.tag_id";
		$stmt_curr_tags = $pdo->prepare($sql_curr_tags);
		$stmt_curr_tags->execute([ $artist["id"] ]);
		$rslt_curr_tags = $stmt_curr_tags->fetchAll();
		
		if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
			foreach($rslt_curr_tags as $tag) {
				$needs_admin_tags = $needs_admin_tags ?: ($tag["is_admin_tag"] ?: false);
				$rslt_curr_tag_ids[] = $tag["id"];
			}
		}
		
		if($_SESSION["loggedIn"]) {
			$sql_user_tags = "SELECT tag_id FROM artists_tags WHERE artist_id=? AND user_id=?";
			$stmt_user_tags = $pdo->prepare($sql_user_tags);
			$stmt_user_tags->execute([ $artist["id"], $_SESSION["userID"] ]);
			$rslt_user_tags = $stmt_user_tags->fetchAll();
			
			if(is_array($rslt_user_tags) && !empty($rslt_user_tags)) {
				foreach($rslt_user_tags as $key => $tag) {
					$rslt_user_tags[$key] = $tag["tag_id"];
				}
			}
		}
		
		include("../artists/page-artist.php");
	}
	
	elseif($show_add_page) {
		$pageTitle = "Add artists";

		breadcrumbs([
			"Add artists" => "/artists/add/"
		]);

		include("../artists/page-add.php");
	}
	
	else {
		$artist_list = $access_artist->access_artist(["letter" => $_GET["letter"], "get" => "artist_list"]);
		$num_artists = count($artist_list);
		
		$full_artist_list = $access_artist->access_artist(["get" => "list"]);
		for($i = 0; $i < count($full_artist_list); $i++) {
			$full_artist_list[$i] = [
				$full_artist_list[$i]["friendly"],
				"",
				str_replace(["&#92;", "&#34;"], ["\\", "\""], $full_artist_list[$i]["quick_name"].($full_artist_list[$i]["romaji"] ? " (".$full_artist_list[$i]["name"].")" : "")).($full_artist_list[$i]["friendly"] != friendly($full_artist_list[$i]["quick_name"]) ? " (".$full_artist_list[$i]["friendly"].")" : null)
			];
		}
		array_unshift($full_artist_list, [0, "", "(omnibus / various artists)"]);
		
		include("../artists/page-letter.php");
	}
?>