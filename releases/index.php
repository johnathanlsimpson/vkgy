<?php
	$access_release = new access_release($pdo);
	$access_artist = new access_artist($pdo);
	
	breadcrumbs([
		'Releases' => '/releases/',
	]);
	
	$page_header = lang('Releases', 'リリース', ['container' => 'div']);
	
	if(is_numeric($_GET["id"])) {
		$release = $access_release->access_release(["release_id" => $_GET["id"], "get" => "all"]);
		
		subnav([
			'Add release' => '/releases/add/'.$release['artist']['friendly'].'/',
		], 'interact', true);
		
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
		
		if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
			foreach($rslt_curr_tags as $tag) {
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