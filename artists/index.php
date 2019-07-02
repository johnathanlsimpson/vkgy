<?php	
	include_once("../artists/function-sort_musicians.php");
	include_once("../php/class-access_user.php");
	include_once('../php/class-access_video.php');
	
	breadcrumbs([
		"Artists" => "/artists/"
	]);
	
	$access_artist = new access_artist($pdo);
	$access_user = new access_user($pdo);
	$access_video = new access_video($pdo);
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
		
		$access_live = new access_live($pdo);
		$artist['lives'] = $access_live->access_live([ 'artist_id' => $artist['id'], 'get' => 'name' ]);
		
		foreach($artist['lives'] as $live_key => $live) {
			$artist['lives'][$live_key] = [
				'date_occurred' => $live['date_occurred'],
				'content' => ($live['area_romaji'] ?: $live['area_name']).' '.($live['livehouse_romaji'] ?: $live['livehouse_name']),
				'type' => [ 14 ],
			];
		}
		
		if(is_array($artist['history']) && is_array($artist['lives'])) {
			$artist['history'] = array_merge($artist['history'], $artist['lives']);
		}
		
		usort($artist['history'], function($a, $b) {
			return $a['date_occurred'] <=> $b['date_occurred'];
		});
		
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
				
				if($tag['friendly'] === 'exclusive') {
					$artist_is_exclusive = true;
				}
				
				if($tag['friendly'] === 'removed') {
					$artist_is_removed = true;
				}
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
		
		// Default video
		$artist['video'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'is_approved' => true, 'get' => 'basics', 'limit' => 1 ]);
		
		// All videos
		if($_GET['section'] === 'videos') {
			$artist['videos'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'get' => 'all' ]);
		}
		
		// Links
		include('function-format_artist_links.php');
		$artist['official_links'] = format_artist_links($artist['official_links']);
		
		// History
		include('function-sort_history.php');
		$artist['history'] = parse_history_types($artist['history'], $access_artist);
		$artist['history'] = insert_lives_into_history($artist['history'], $artist['lives']);
		$artist['history'] = parse_history_markdown($artist['history'], $markdown_parser);
		$artist['history'] = link_activity_area($artist['history'], $artist['areas']);
		$artist['date_occurred'] = get_formation_dates($artist['history']);
		$artist['history'] = inline_lists($artist['history']);
		$artist['history'] = flag_questions($artist['history']);
		$artist['history'] = structure_by_date($artist['history']);
		foreach($artist['history'] as $y => $history_year) {
			foreach($history_year as $m => $history_month) {
				foreach($history_month as $d => $history_day) {
					$artist['history'][$y][$m][$d] = format_releases($history_day);
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