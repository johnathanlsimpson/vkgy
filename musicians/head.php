<?php
	$pageTitle = "Musicians";
	
	breadcrumbs([
		"Musicians" => "/musicians/"
	]);
	
	if($_SESSION["is_editor"]) {
		subnav([
			"Add" => "/musicians/add/"
		]);
	}
?>