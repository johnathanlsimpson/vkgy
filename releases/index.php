<?php
	$access_release = new access_release($pdo);
	$access_artist = new access_artist($pdo);
	$access_user = new access_user($pdo);
	
	breadcrumbs([
		'Releases' => '/releases/',
	]);
	
	$page_header = lang('Releases', 'リリース', ['container' => 'div']);

if( is_numeric($_GET['id']) ) {
	
	$sql_removed = 'SELECT artists_tags.id FROM releases LEFT JOIN artists_tags ON artists_tags.artist_id=releases.artist_id AND artists_tags.tag_id=? WHERE releases.id=? LIMIT 1';
	$values_removed = [ 21, $_GET['id'] ];
	
}
elseif( $_GET['artist'] ) {
	
	$sql_removed = 'SELECT artists_tags.id FROM artists LEFT JOIN artists_tags ON artists_tags.artist_id=artists.id AND artists_tags.tag_id=? WHERE artists.friendly=? LIMIT 1';
	$values_removed = [ 21, sanitize($_GET['artist']) ];
	
}

if( $sql_removed ) {
	
	$stmt_removed = $pdo->prepare($sql_removed);
	$stmt_removed->execute( $values_removed );
	$artist_is_removed = $stmt_removed->fetchColumn();
	
	// Set up permissions
	$artist_is_viewable = $artist_is_removed && $_SESSION['is_vip'] || !$artist_is_removed ? true : false;
	
}
else {
	
	$artist_is_viewable = true;
	
}




	
	// ID template
	if( is_numeric($_GET["id"]) && $artist_is_viewable ) {
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
		
		// Get tags
		// ============================================
		include_once('../php/class-tag.php');
		
		$item_type = 'release';
		$item_id = $release['id'];
		
		$access_tag = new tag($pdo);
		$tags = $access_tag->access_tag([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'all', 'separate' => true ]);
		
		// Loop through tags and set some flags
		if( is_array($tags) && !empty($tags) && is_array($tags['tagged']) ) {
			foreach($tags['tagged'] as $tag_type => $tagged_tags) {
				foreach($tagged_tags as $tag) {
					
					// Set flags
					if($tag['friendly'] === 'exclusive') {
						$release_is_exclusive = true;
					}
					else if($tag['friendly'] === 'removed') {
						$release_is_removed = true;
					}
					
				}
			}
		}
		
		if(is_array($release) && !empty($release)) {
			
			
			include_once("../releases/page-id.php");
		}
		else {
			$error = "Sorry, the requested release could not be found.";
			include_once("../releases/page-index.php");
		}
	}
	
	elseif( !empty($_GET["artist"]) && $artist_is_viewable ) {
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