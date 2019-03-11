<?php
	include_once("../php/include.php");
	
	if(!empty($_GET["letter"])) {
		$letter = friendly($_GET["letter"]);
		$letter = preg_match("/"."^[a-z]$"."/", $letter) ? $letter : "-";
		
		$access_artist = new access_artist($pdo);
		
		$artist_list = $access_artist->access_artist(["letter" => $letter, "get" => "artist_list"]);
	}
	
	echo json_encode(is_array($artist_list) && !empty($artist_list) ? $artist_list : []);
?>