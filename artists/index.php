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
			
			if($_GET['section'] === 'videos') {
				$show_videos = true;
			}
			elseif($_GET['section'] === 'tags') {
				$show_tags = true;
			}
			elseif($_GET['section'] === 'images') {
				$show_images = true;
			}
			elseif($_GET["action"] === "edit") {
				if($_SESSION["can_add_data"]) {
					$show_edit_page = true;
				}
				else {
					$error = 'Sorry, only editors may edit artists. Showing artist list instead.';
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
		if($_SESSION["can_add_data"]) {
			$show_add_page = true;
		}
		else {
			$error = 'Sorry, only editors can add artists. Showing artist list instead.';
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
		
		// Get areas for musicians' hometown
		$sql_areas = 'SELECT areas.id, areas.name, areas.romaji, areas.friendly FROM areas ORDER BY areas.friendly ASC';
		$stmt_areas = $pdo->prepare($sql_areas);
		$stmt_areas->execute();
		$areas = $stmt_areas->fetchAll();
		
		include("../artists/page-edit.php");
	}
	
	//
	// Transform data & load page: tags
	//
	if( $show_tags ) {
		
		include('../artists/page-tags.php');
		
	}
	
	//
	// Transform data & load page: images
	//
	if( $show_images ) {
		
		if( $_GET['action'] === 'edit' ) {
			include('../artists/page-edit-images.php');
		}
		else {
			include('../artists/page-images.php');
		}
		
	}
	
	//
	// Transform data & load page: profile or videos
	//
	if($show_videos || $show_artist_page) {
		
		// Format edit history
		$sql_edit_history = 'SELECT edits_artists.* FROM edits_artists LEFT JOIN users ON users.id=edits_artists.user_id WHERE edits_artists.artist_id=? ORDER BY date_occurred DESC';
		$stmt_edit_history = $pdo->prepare($sql_edit_history);
		$stmt_edit_history->execute([ $artist['id'] ]);
		$rslt_edit_history = $stmt_edit_history->fetchAll();
		$num_edit_history = count($rslt_edit_history);
		
		if(is_array($rslt_edit_history)) {
			$artist['edit_history'] = [];
			
			for($i=0; $i<$num_edit_history; $i++) {
				$e = $rslt_edit_history[$i];
				
				$artist['edit_history'][$e['date_occurred']]['user'] = $access_user->access_user([ 'id' => $e['user_id'], 'get' => 'name' ]);
				$artist['edit_history'][$e['date_occurred']]['date_occurred'] = $e['date_occurred'];
				$artist['edit_history'][$e['date_occurred']]['content'][] = $e['content'];
			}
			
			$artist['edit_history'] = array_values($artist['edit_history']);
		}
		
		// Get comments
		$access_comment = new access_comment($pdo);
		$artist["comments"] = $access_comment->access_comment(["id" => $artist["id"], 'get_user_likes' => true, "type" => "artist", "get" => "all"]);
		
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
		
		// Clean up links for display
		if(is_array($artist['urls']) && !empty($artist['urls'])) {
			include('function-format_artist_urls.php');
			$artist['urls'] = format_artist_urls($artist['urls']);
		}
		
		// Remove empty arrays
		foreach(['musicians', 'history', 'lives', 'images', 'videos', 'labels', 'edit_history'] as $key) {
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
		
	}

		
	$sql_removed = 'SELECT 1 FROM artists_tags WHERE artist_id=? AND tag_id=? LIMIT 1';
	$stmt_removed = $pdo->prepare($sql_removed);
	$stmt_removed->execute([ $artist['id'], 21 ]);
	$artist_is_removed = $stmt_removed->fetchColumn();
		
	// Set up permissions
	$artist_is_removed;
	$artist_is_stub = $artist['musicians'] || $artist['history'] ? false : true;
	$artist_is_viewable = $artist_is_removed && $_SESSION['is_vip'] || !$artist_is_removed ? true : false;

	if( !$artist_is_viewable ) {
		unset( $show_videos, $show_tags, $show_images, $show_artist_page );
	}
	
	//
	// Transform data & load page: videos
	//
	if($show_videos) {
		
		// Set page variables
		$page_title = $artist['quick_name'].' videos | '.$artist['name'].sanitize('の動画');
		$page_description = 'All music videos (MV) by visual kei band '.$artist['quick_name'].'. ビジュアル系バンド「'.$artist['name'].'」'.sanitize('動画・MV');
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
		$page_description = 'About visual kei band '.$artist["quick_name"].': profile, biography, history. ビジュアル系バンド「'.$artist["name"].'」プロフィール・活動'.($artist["lineup"] ? " (".$artist["lineup"].")" : null);
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
		
		// Record view
		include('../php/class-views.php');
		$views = new views($pdo);
		$views->add('artist', $artist['id']);
		
		// Default video
		$artist['video'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'is_approved' => true, 'get' => 'basics', 'limit' => 1 ])[0];
		
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
	if( !$show_add_page && !$show_artist_page && !$show_videos && !$show_edit_page && !$show_tags && !$show_images ) {
		
		$full_artist_list = $access_artist->access_artist(["get" => "list"]);
		for($i = 0; $i < count($full_artist_list); $i++) {
			$full_artist_list[$i] = [
				$full_artist_list[$i]["friendly"],
				"",
				str_replace(["&#92;", "&#34;"], ["\\", "\""], $full_artist_list[$i]["quick_name"].($full_artist_list[$i]["romaji"] ? " (".$full_artist_list[$i]["name"].")" : "")).($full_artist_list[$i]["friendly"] != friendly($full_artist_list[$i]["quick_name"]) ? " (".$full_artist_list[$i]["friendly"].")" : null)
			];
		}
		array_unshift($full_artist_list, [0, "", "(omnibus / various artists)"]);
		
		include('../artists/page-index.php');
	}
?>