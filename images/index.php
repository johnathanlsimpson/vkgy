<?php
	breadcrumbs([
		"Images" => "/images/"
	]);
	
	if($_SESSION["admin"] > 1) {
		subnav([
			"Upload queue" => "/images/add/",
			"Edit queue" => "/images/edit/"
		]);
	}
	
	$pageTitle = "Images";
?>

<div class="col c1">
	<div>
		<h1>
			Images
		</h1>
	</div>
</div>

<?php
	if($_SESSION["admin"] > 1 && !empty($_GET["action"])) {
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