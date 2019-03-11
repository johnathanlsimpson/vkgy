<?php
	include_once("../php/include.php");
	
	if(!empty($_GET["letter"])) {
		$letter = friendly($_GET["letter"]);
		$letter = preg_match("/"."^[a-z]$"."/", $letter) ? $letter : "-";
		
		$access_musician = new access_musician($pdo);
		
		$musician_list = $access_musician->access_musician(["letter" => $letter, "get" => "list"]);
	}
	
	echo json_encode(is_array($musician_list) && !empty($musician_list) ? array_values($musician_list) : []);
?>