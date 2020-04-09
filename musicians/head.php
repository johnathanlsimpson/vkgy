<?php
	$pageTitle = "Musicians";
	
	breadcrumbs([
		"Musicians" => "/musicians/"
	]);
	
	if($_SESSION["can_add_data"]) {
		subnav([
			"Add" => "/musicians/add/"
		]);
	}
?>