<?php
	include_once("../php/include.php");
	
	if($_SESSION["is_moderator"]) {
		$file = $_GET["file"];
		$file = preg_match("/"."\d+\.jpg"."/", $file) ? $file : null;
		$file = $file ? "../images/image_files_queued/".$file : null;
		$file = file_exists($file) ? $file : null;
		
		if($file) {
			$type = "image/jpeg";
			header("Content-Type:".$type);
			readfile($file);
		}
	}
?>