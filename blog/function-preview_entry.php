<?php

// Setup
include_once('../php/include.php');
include_once('../php/class-access_social_media.php');
$markdown_parser = new parse_markdown($pdo);
$access_social_media = new access_social_media($pdo);
$content = sanitize($_POST['content']);

// Separate first paragraph (presumed summary) to get featured artist
$content = explode("\n\n", $content);
$first_paragraph = array_shift($content);
$content = implode("\n\n", $content);
$summary_references = $markdown_parser->get_reference_data($first_paragraph);

// Loop through references and get first artist mentioned in summary, so we can pass as 'main' artist
if(is_array($summary_references) && !empty($summary_references)) {
	foreach($summary_references as $summary_reference) {
		if($summary_reference['type'] === 'artist') {
			$output['artist'] = $summary_reference;
		}
	}
}

// If sources included, transform into list, then get Markdown
$sources = sanitize($_POST['sources']);
$sources = preg_replace('/'.'([^\\\])\n'.'/m', "$1 \\\n", $sources);
$sources = $markdown_parser->validate_markdown($sources);
$sources = $markdown_parser->parse_markdown($sources);
$sources = $sources ? '<h5 style="margin:2rem 0 0.5rem 0;">Sources</h5><div class="text text--compact text--outlined" style="margin:0;">'.$sources.'</div>' : null;

// If supplemental included, transform into list, then get Markdown
$supplemental = sanitize($_POST['supplemental']);
$supplemental = $markdown_parser->validate_markdown($supplemental);
$supplemental = $markdown_parser->parse_markdown($supplemental);
$supplemental = $supplemental ? '<h5 style="margin:2rem 0 0.5rem 0;">Supplemental</h5><div class="text text--compact text--outlined" style="margin:0;">'.$supplemental.'</div>' : null;

// Parse content
if(!empty($first_paragraph)) {
	$content  = sanitize($content);
	$content  = $markdown_parser->validate_markdown($content);
	$content  = $markdown_parser->parse_markdown($content);
	$content .= $sources;
	$content .= $supplemental;
	
	$summary = sanitize($first_paragraph);
	$summary = $markdown_parser->validate_markdown($summary);
	$summary = $markdown_parser->parse_markdown($summary);
	
	$output['summary'] = $summary;
	$output["result"] = $content;
	$output["status"] = "success";
}

$output["status"] = $output["status"] ?: "error";

echo json_encode($output);