<?php
	$pageTitle = "Musicians";
	
	breadcrumbs([
		"Musicians" => "/musicians/"
	]);
	
	if($_SESSION["admin"]) {
		subnav([
			"Add" => "/musicians/add/"
		]);
	}
?>