<?php
	
	breadcrumbs([
		"Labels" => "/labels/"
	]);
	
	$access_label = new access_label($pdo);
	
	$page_header = lang('Labels', 'レーベル', ['container' => 'div']);
	
	subnav([
		lang('Label list', 'レーベル一覧', ['secondary_class' => 'any--hidden']) => '/labels/',
	]);
	
	subnav([
		'Add label' => '/labels/add/',
	], 'interact', true);
	
	
	if(!empty($_GET["label"])) {
		$label = $access_label->access_label(["friendly" => friendly($_GET["label"]), "get" => "all"]);
		
		if(is_array($label)) {
			if($_GET["action"] === "edit") {
				if($_SESSION["admin"]) {
					$pageTitle = "Edit ".$label["quick_name"];
					
					breadcrumbs([
						$label["quick_name"] => "/labels/".$label["friendly"]."/",
						"Edit" => "/labels/".$label["friendly"]."/edit/"
					]);
					
					include("../labels/page-edit.php");
				}
				else {
					unset($label);
				}
			}
			else {
				$pageTitle = $label["quick_name"]." label profile | ".$label["name"]."&#12524;&#12540;&#12505;&#12523;";
				
				breadcrumbs([
					$label["quick_name"] => "/labels/".$label["friendly"]."/"
				]);
				
				subnav([
					"Edit label" => "/labels/".$label["friendly"]."/edit/"
				], 'interact', true);
				
				include("../labels/page-label.php");
			}
		}
	}
	
	
	
	if(!is_array($label) || empty($label)) {
		if($_GET["action"] === "add" && $_SESSION["admin"]) {
			$pageTitle = "Add label";
			
			breadcrumbs([
				"Add label" => "/labels/add/"
			]);
			
			include("../labels/page-add.php");
		}
		else {
			$labels = $access_label->access_label(["get" => "list"]);
			
			if(is_array($labels)) {
				$pageTitle = "Label list | &#12524;&#12540;&#12505;&#12523;&#32034;&#24341;";
				
				include("../labels/page-index.php");
			}
		}
	}
?>