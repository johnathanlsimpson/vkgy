<?php

// Setup
include_once('../php/include.php');
include_once('../php/class-access_social_media.php');
$markdown_parser = new parse_markdown($pdo);
$access_social_media = new access_social_media($pdo);
$content = sanitize($_POST['content']);

// Separate first paragraph (presumed summary) to get featured artist
$first_paragraph = explode("\n\n", $content)[0];
$summary_references = $markdown_parser->get_reference_data($first_paragraph);

// Loop through references and get first artist mentioned in summary, so we can pass as 'main' artist
if(is_array($summary_references) && !empty($summary_references)) {
	foreach($summary_references as $summary_reference) {
		if($summary_reference['type'] === 'artist') {
			$output['artist'] = $summary_reference;
		}
	}
}

// Parse content
if(!empty($content)) {
	$content = sanitize($content);
	$content = $markdown_parser->validate_markdown($content);
	$content = $markdown_parser->parse_markdown($content);

	$output["result"] = $content;
	$output["status"] = "success";
}

$output["status"] = $output["status"] ?: "error";

echo json_encode($output);