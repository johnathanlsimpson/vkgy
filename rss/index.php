<?php

include_once('../php/include.php');
include_once('../php/function-script.php');

$access_blog = new access_blog($pdo);
$markdown_parser = new parse_markdown($pdo);

ob_start();

// Development log
if( $_GET['template'] === 'development' ) {
	
	// Only get posts older than one hour
	$time = date('Y-m-d H:i:s', strtotime('-1 hour'));
	
	$sql_dev = 'SELECT development.*, CONCAT("https://vk.gy/", development.id, "/") AS url, users.username FROM development LEFT JOIN users ON users.id=development.user_id WHERE development.is_issue=? AND development.date_occurred<? ORDER BY development.date_occurred DESC LIMIT 10';
	$stmt_dev = $pdo->prepare($sql_dev);
	$stmt_dev->execute([ 0, $time ]);
	$entries = $stmt_dev->fetchAll();
	
	include('../rss/page-development.php');
	
}

// Blog
else {
	
	$entries = $access_blog->access_blog(["page" => "latest", "get" => "basics"]);
	
	if(is_array($entries) && !empty($entries)) {
		foreach($entries as $key => $entry) {
			$sql_image = "SELECT IF(images.id IS NOT NULL, CONCAT('https://vk.gy/images/', images.id, IF(images.friendly IS NOT NULL, CONCAT('-', images.friendly), ''), '.', images.extension), '') AS image FROM blog LEFT JOIN images ON images.id=blog.image_id WHERE blog.id=? AND blog.image_id IS NOT NULL LIMIT 1";
			$stmt_image = $pdo->prepare($sql_image);
			$stmt_image->execute([$entry["id"]]);
			
			$entries[$key]["image"] = $stmt_image->fetchColumn();
			$entries[$key]["title"] = html_entity_decode($entry["title"], null, "UTF-8");
			$entries[$key]["date_occurred"] = date(DATE_RSS, strtotime($entry["date_occurred"]));
			$entries[$key]["url"] = "https://vk.gy/blog/".$entry["friendly"]."/";
		}
	}
	
	include('../rss/page-blog.php');
	
}

$output = ob_get_clean();
$output = str_replace(["\t"], "", $output);
$output = '<?xml version="1.0" encoding="utf-8"?>'."\n".$output;

header("content-type: text/xml; charset=utf-8");
echo $output;