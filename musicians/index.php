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
		// ============================================
		include_once('../php/class-tag.php');
		
		$item_type = 'musician';
		$item_id = $musician['id'];
		
		$access_tag = new tag($pdo);
		$tags = $access_tag->access_tag([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'all', 'separate' => true ]);
		
		// Loop through tags and set some flags
		if( is_array($tags) && !empty($tags) && is_array($tags['tagged']) ) {
			foreach($tags['tagged'] as $tag_type => $tagged_tags) {
				foreach($tagged_tags as $tag) {
					
					// Set flags
					if($tag['friendly'] === 'exclusive') {
						$musician_is_exclusive = true;
					}
					else if($tag['friendly'] === 'removed') {
						$musician_is_removed = true;
					}
					
				}
			}
		}
		
		// Navigation
		// ============================================
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