<?php
	include_once("../php/include.php");
	$markdown_parser = new parse_markdown($pdo);
	
	$content = $_POST["content"];
	
	if(!empty($content)) {
		$content = sanitize($content);
		$content = $markdown_parser->validate_markdown($content);
		$content = $markdown_parser->parse_markdown($content);
		
		$output["result"] = $content;
		$output["status"] = "success";
	}
	
	$output["status"] = $output["status"] ?: "error";
	
	echo json_encode($output);
?>