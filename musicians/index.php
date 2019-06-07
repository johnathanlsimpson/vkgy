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
		
		if(is_array($musician) && !empty($musician)) {
			breadcrumbs([$musician["quick_name"] => "/musicians/".$musician["friendly"]."/"]);
			
			for($i = 0; $i < count($musician["history"]); $i++) {
				if(!empty($musician["history"][$i]["friendly"])) {
					subnav([
						"Edit musician" => "/artists/".$musician["history"][$i]["friendly"]."/edit/"
					]);
				}
			}
			
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