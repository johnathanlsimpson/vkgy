<?php
	breadcrumbs([
		"Musicians" => "/musicians/"
	]);
	
	subnav([
		lang('Musician list', 'ミュージシャン一覧', ['secondary_class' => 'any--hidden']) => '/musicians/'
	]);
	
	subnav([
		lang('Add musician', 'ミュージシャン追加', ['secondary_class' => 'any--hidden']) => '/musicians/add/',
	], 'interact', true);
	
	$page_header = lang('Musicians', 'ミュージシャン', ['container' => 'div']);
	
	$access_musician = new access_musician($pdo);
	
	if(is_numeric($_GET["musician"])) {
		$musician_id = sanitize($_GET["musician"]);
		$musician = $access_musician->access_musician(["id" => $musician_id, "get" => "all"]);
		
		// Get tags
		$item_type = 'musician';
		$item_id = $musician['id'];
		include_once('../tags/function-get_tags.php');
		$tags = get_tags($pdo, $item_type, $item_id);
		
		// Loop through tags and do some stuff
		if(is_array($tags) && !empty($tags)) {
			
			$all_tags = $tags['all_tags'];
			$current_tags = $tags['current_tags'];
			$user_upvotes = $tags['user_upvotes'] ?: [];
			$user_downvotes = $tags['user_downvotes'] ?: [];
			$tag_types = $tags['tag_types'];
			
			// Loop through current tags and set some flags for artist
			if(is_array($current_tags['admin']) && !empty($current_tags['admin'])) {
				foreach($current_tags['admin'] as $numeric_key => $tag) {
					
					// Set flags
					if($tag['friendly'] === 'exclusive') {
						$musician_is_exclusive = true;
					}
					if($tag['friendly'] === 'removed') {
						$musician_is_removed = true;
					}
					
				}
			}
			
		}
		
		if(is_array($musician) && !empty($musician)) {
			breadcrumbs([$musician["quick_name"] => "/musicians/".$musician["friendly"]."/"]);
			
			for($i = 0; $i < count($musician["history"]); $i++) {
				if(!empty($musician["history"][$i]["friendly"])) {
					subnav([
						"Edit musician" => "/artists/".$musician["history"][$i]["friendly"]."/edit/"
					]);
				}
			}
			
			// Get musician's images
			$sql_images = 'SELECT * FROM images_musicians LEFT JOIN images ON images.id=images_musicians.image_id WHERE images_musicians.musician_id=?';
			$stmt_images = $pdo->prepare($sql_images);
			$stmt_images->execute([ $musician['id'] ]);
			$images = $stmt_images->fetchAll();
			
			$pageTitle = $musician["quick_name"]." musician profile | ".$musician["name"]."&#12503;&#12525;&#12501;&#12451;&#12540;&#12523;";
			
			include("../musicians/page-musician.php");
		}
		else {
			$error = "Sorry, that musician doesn't exist. Showing musicians list instead.";
		}
	}
	
	if(!is_array($musician) || empty($musician)) {
		if($_GET["action"] === "add") {
			breadcrumbs(["Add musician" => "/musicians/add/"]);
			
			$pageTitle = "Add musician";
			
			include("../musicians/page-add.php");
		}
		else {
			$pageTitle = "Musician list | &#20491;&#20154;&#32034;&#24341;";
			include("../musicians/page-index.php");
		}
	}
?>