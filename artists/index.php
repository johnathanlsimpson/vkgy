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
	
	//
	// Choose template and get base data
	//
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
			elseif($_GET['action'] === 'edits') {
				
				if(is_numeric($_GET['id'])) {
					
					if($_SESSION['is_vip']) {
						$edit_id = sanitize($_GET['id']);
					}
					else {
						$error = 'Sorry, only VIP members can view full edit.';
					}
					
				}
				
				$show_edits_page = true;
				
			}
			else {
				if($_GET['section'] === 'videos') {
					$show_videos = true;
				}
				else {
					$show_artist_page = true;
				}
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
	
	
	
	//
	// Transform data & load page: edit
	//
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

	//
	// Specific edits
	//
	if($show_edits_page) {
		
		$page_title = 'Edit history: '.$artist['quick_name'].($artist['romaji'] ? ' ('.$artist['name'].')' : null);
		
		breadcrumbs([
			'Edits' => '/artists/'.$artist['friendly'].'/edits/'
		]);
		
		// Get specific edit
		if(is_numeric($edit_id)) {
			breadcrumbs([
				$edit_id => '/artists/'.$artist['friendly'].'/edits/'.$edit_id.'/'
			]);
			
			$sql_edit_history = 'SELECT edits_artists.*, users.username FROM edits_artists LEFT JOIN users ON users.id=edits_artists.user_id WHERE edits_artists.id=? LIMIT 1';
			$stmt_edit_history = $pdo->prepare($sql_edit_history);
			$stmt_edit_history->execute([ $edit_id ]);
			$edit = $stmt_edit_history->fetch();
			$num_edits = is_array($edit) ? count($edit) : 0;
			
			if($num_edits > 0) {
				$edit['content'] = json_decode($edit['content'], true);
			}
			else {
				$error = 'Sorry, the specified edit couldn\'t be found. Showing all edits instead.';
				unset($edit_id);
			}
		}
		
		// Get all edits
		if(!is_numeric($edit_id)) {
			$sql_edit_history = 'SELECT edits_artists.*, users.username FROM edits_artists LEFT JOIN users ON users.id=edits_artists.user_id WHERE edits_artists.artist_id=? ORDER BY date_occurred DESC';
			$stmt_edit_history = $pdo->prepare($sql_edit_history);
			$stmt_edit_history->execute([ $artist['id'] ]);
			$edits = $stmt_edit_history->fetchAll();
			$num_edits = is_array($edits) ? count($edits) : 0;
			
			if($num_edits > 0) {
				foreach($edits as $edit_key => $edit) {
					// Decode JSON, and since we're showing all edits, only show fields that were edited
					$content = $edits[$edit_key]['content'];
					$content = json_decode($content, true) ?: $content;
					$content = is_array($content) ? implode(', ', array_keys($content) ) : $content;
					$edits[$edit_key]['content'] = $content;
				}
			}
			else {
				$error = 'Sorry, no edits could be found.';
			}
		}
		
		include('head.php');
		include('page-edits.php');
		
	}
	
	//
	// Transform data & load page: profile or videos
	//
	if($show_videos || $show_artist_page) {
		
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
		
		// Pull out default image from images array
		if(!empty($artist['images']) && is_numeric($artist['image_id'])) {
			$artist['image'] = $artist['images'][$artist['image_id']];
			unset($artist['images'][$artist['image_id']]);
			$artist['images'] = array_values($artist['images']);
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
				
				if($tag['friendly'] === 'non-visual') {
					$artist_is_non_visual = true;
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
		
		// Links
		include('function-format_artist_links.php');
		$artist['official_links'] = format_artist_links($artist['official_links']);
		
		// Remove empty arrays
		foreach(['musicians', 'history', 'lives', 'images', 'videos', 'labels', 'official_links', 'edit_history'] as $key) {
			if(is_array($artist[$key]) && !empty($artist[$key])) {
			}
			else {
				unset($artist[$key]);
			}
		}
		
		// Back/forward navigation
		if(is_array($rslt_next) && !empty($rslt_next)) {
			if(count($rslt_next) === 2) {
				$rslt_next[] = [ 'romaji' => $artist['romaji'], 'name' => $artist['name'], 'type' => $rslt_next[0]['type'] === 'previous' ? 'next' : 'previous' ];
			}
			foreach($rslt_next as $directional_artist) {
				subnav([
					[
						'text' => lang( (  $directional_artist['romaji'] ?:   $directional_artist['name']),   $directional_artist['name'], 'hidden' ),
						'url' => strlen(  $directional_artist['friendly']) ? '/artists/'.  $directional_artist['friendly'].'/' : null,
						'position' =>   $directional_artist['type'] === 'rand' ? 'center' : (  $directional_artist['type'] === 'previous' ? 'left' : 'right'),
					]
				], 'directional');
			}
		}
		
		// Set up permissions
		$artist_is_removed;
		$artist_is_stub = $artist['musicians'] || $artist['history'] ? false : true;
		$artist_is_viewable = $artist_is_removed && $_SESSION['is_vip'] || !$artist_is_removed ? true : false;
	}
	
	//
	// Transform data & load page: videos
	//
	if($show_videos) {
		
		// Set page variables
		$page_title = $artist['quick_name'].' videos | '.$artist['name'].sanitize('の動画');
		$page_description = 'Official videos by '.$artist['quick_name'].'. '.$artist['name'].sanitize('より公式の動画。');
		$page_image = 'https://vk.gy/artists/'.$artist['friendly'].'/main.large.jpg';
		
		breadcrumbs([
			$artist['quick_name'] => '/artists/'.$artist['friendly'].'/',
			'Videos' => '/artists/'.$artist['friendly'].'/videos/',
		]);
		
		// Get videos
		if($_GET['section'] === 'videos') {
			$artist['videos'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'get' => 'all' ]);
		}
		
		// Include template
		include('page-videos.php');
	}
	
	//
	// Transform data & load page: profile
	//
	if($show_artist_page) {
		
		// Set page variables
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
		
		// Default video
		$artist['video'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'is_approved' => true, 'get' => 'basics', 'limit' => 1 ]);
		
		include("../artists/page-artist.php");
	}
	
	//
	// Transform data & load page: add artist
	//
	if($show_add_page) {
		$pageTitle = "Add artists";

		breadcrumbs([
			"Add artists" => "/artists/add/"
		]);

		include("../artists/page-add.php");
	}
	
	//
	// Transform data & load page: artist list
	//
	if(!$show_add_page && !$show_artist_page && !$show_videos && !$show_edit_page && !$show_edits_page) {
		$artist_list = $access_artist->access_artist(["letter" => $_GET["letter"], "get" => "artist_list"]);
		$num_artists = is_array($artist_list) ? count($artist_list) : 0;
		
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