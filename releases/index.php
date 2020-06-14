<?php
	$access_release = new access_release($pdo);
	$access_artist = new access_artist($pdo);
	$access_user = new access_user($pdo);
	
	breadcrumbs([
		'Releases' => '/releases/',
	]);
	
	$page_header = lang('Releases', 'リリース', ['container' => 'div']);
	
	// ID template
	if(is_numeric($_GET["id"])) {
		$release = $access_release->access_release(["release_id" => $_GET["id"], "get" => "all"]);
		
		subnav([
			'Add release' => '/releases/add/'.$release['artist']['friendly'].'/',
		], 'interact', true);
		
		$active_page = '/releases/'.$release['artist']['friendly'].'/';
		
		// Check collection/wanted/selling status of item
		$sql_is_for_sale = 'SELECT 1 FROM releases_collections WHERE release_id=? AND is_for_sale=? LIMIT 1';
		$stmt_is_for_sale = $pdo->prepare($sql_is_for_sale);
		$stmt_is_for_sale->execute([ $release['id'], 1 ]);
		$is_for_sale = $stmt_is_for_sale->fetchColumn();
		
		$sql_collections = "SELECT user_id, users.username, releases_collections.is_for_sale FROM releases_collections LEFT JOIN users ON users.id=releases_collections.user_id WHERE releases_collections.release_id=? ORDER BY users.username ASC";
		$stmt_collections = $pdo->prepare($sql_collections);
		$stmt_collections->execute([ $release["id"] ]);
		$rslt_collections = $stmt_collections->fetchAll();
		
		$sql_wants = "SELECT user_id, users.username FROM releases_wants LEFT JOIN users ON users.id=releases_wants.user_id WHERE releases_wants.release_id=? ORDER BY users.username ASC";
		$stmt_wants = $pdo->prepare($sql_wants);
		$stmt_wants->execute([$release["id"]]);
		$rslt_wants = $stmt_wants->fetchAll();
		
		// For collections, get correct usernames and check if being sold by current user
		if(is_array($rslt_collections) && !empty($rslt_collections)) {
			foreach($rslt_collections as $collection_key => $collection) {
				
				// Get user info
				$collection['user'] = $access_user->access_user([ 'id' => $collection['user_id'], 'get' => 'name' ]);
				$rslt_collections[$collection_key]['user'] = $collection['user'];
				
				if($collection['is_for_sale'] && $collection['user']['id'] === $_SESSION['user_id']) {
					$release['is_for_sale'] = true;
				}
			}
		}
		
		// If on omnibus release while cycling through artist's disco, make note
		if(is_numeric($_GET['prev_next_artist']) && $release['artist']['id'] != $_GET['prev_next_artist']) {
			$traversal_artist = $access_artist->access_artist([ 'id' => sanitize($_GET['prev_next_artist']), 'get' => 'name' ]);
			$needs_traversal_notice = true;
		}
		
		// Tags
		$sql_tags = "SELECT * FROM tags_releases ORDER BY friendly ASC";
		$stmt_tags = $pdo->prepare($sql_tags);
		$stmt_tags->execute();
		$rslt_tags = $stmt_tags->fetchAll();
		
		$sql_curr_tags = "SELECT tags_releases.*, COUNT(releases_tags.id) AS num_times_tagged FROM releases_tags LEFT JOIN tags_releases ON tags_releases.id=releases_tags.tag_id WHERE releases_tags.release_id=? GROUP BY releases_tags.tag_id";
		$stmt_curr_tags = $pdo->prepare($sql_curr_tags);
		$stmt_curr_tags->execute([ $release["id"] ]);
		$rslt_curr_tags = $stmt_curr_tags->fetchAll();
		
		/*if($_SESSION['is_signed_in']) {
			$sql_user_tags = 'SELECT tag_id FROM releases_tags WHERE release_id=? AND user_id=?';
			$stmt_user_tags = $pdo->prepare($sql_user_tags);
			$stmt_user_tags->execute([ $release['id'], $_SESSION['user_id'] ]);
			$rslt_user_tags = $stmt_user_tags->fetchAll();
			$num_user_tags = count($rslt_user_tags);
			
			for($i=0; $i<$num_user_tags; $i++) {
				$user_tags[ $rslt_user_tags[$i]['tag_id'] ] = true;
			}
		}*/
		
		if(is_array($rslt_curr_tags['admin']) && !empty($rslt_curr_tags['admin'])) {
			foreach($rslt_curr_tags['admin'] as $tag) {
				$needs_admin_tags = $needs_admin_tags ?: ($tag["is_admin_tag"] ?: false);
				$rslt_curr_tag_ids[] = $tag["id"];
				
				if($tag['friendly'] === 'exclusive') {
					$release_is_exclusive = true;
				}
				
				if($tag['friendly'] === 'removed') {
					$release_is_removed = true;
				}
			}
		}
		
		if(is_array($release) && !empty($release)) {
			$page_description = $release["artist"]["quick_name"]." 「".$release["quick_name"]."」 release information, reviews, etc. ".$release["artist"]["name"]." 「".$release["name"]." ".$release["press_name"]." ".$release["type_name"]."」のリリース情報、レビュー、など。 | vk.gy (ブイケージ)";
			
			include_once("../releases/page-id.php");
		}
		else {
			$error = "Sorry, the requested release could not be found.";
			include_once("../releases/page-index.php");
		}
	}
	
	elseif(!empty($_GET["artist"])) {
		$_GET["artist"] = friendly($_GET["artist"]);
		
		if(!empty($_GET["artist"])) {
			$artist = $access_artist->access_artist([ "friendly" => $_GET["artist"], "get" => "name", "limit" => "1" ]);
			
			subnav([
				'Add release' => '/releases/add/'.$artist['friendly'].'/',
			], 'interact', true);
			
			if(is_array($artist) && !empty($artist)) {
				$releases = $access_release->access_release([ "artist_id" => $artist["id"], "get" => "basics" ]);
				
				if(is_array($releases) && !empty($releases)) {
					$page_description = $artist["quick_name"]." full discography and release information.\n「".$artist["name"]."」のディスコグラフィ、リリース情報、など。\n| vkgy (ブイケージ)";
					
					breadcrumbs([
						$artist["quick_name"] => "/releases/".$artist["friendly"]."/",
					]);
					
					include_once("../releases/page-artist.php");
				}
				else {
					$error = '<a href="/artists/'.$artist["friendly"].'/">'.$artist["quick_name"].'</a> doesn\'t have any releases in the database yet.';
					include_once("../releases/page-index.php");
				}
			}
			else {
				$error = 'Sorry, <span class="any__note">'.$_GET["artist"]."</span> can't be found in the database.";
				include_once("../releases/page-index.php");
			}
		}
	}
	else {
		include_once("../releases/page-index.php");
	}
?>