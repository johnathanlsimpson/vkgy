<?php
	breadcrumbs([
		"Images" => "/images/"
	]);
	
	subnav([
		'Images' => '/images/',
	]);
	
	if($_SESSION["is_boss"]) {
		subnav([
			"Upload queue" => "/images/add/",
			"Edit queue" => "/images/edit/"
		], 'interact', true);
	}
	
	$pageTitle = "Images";
	$page_header = lang('Image list', '画像一覧', ['container' => 'div']);
?>

<?php
	if($_SESSION["is_boss"] && !empty($_GET["action"])) {
		if($_GET["action"] === "add") {
			breadcrumbs([
				"Upload queue" => "/images/add/"
			]);
			
			$pageTitle = "Upload queue";
			
			include("../images/page-add.php");
		}
		
		if($_GET["action"] === "edit") {
			breadcrumbs([
				"Edit queue" => "/images/edit/"
			]);
			
			$pageTitle = "Edit queue";
			
			include("../images/page-edit.php");
		}
	}
	else {
		include('../images/page-index.php');
	}
?>