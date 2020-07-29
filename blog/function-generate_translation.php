<?php

include_once('../php/include.php');

$blog_id = sanitize($_POST['id']);
$title = sanitize($_POST['title']);
$friendly = friendly($_POST['friendly']);
$language = friendly($_POST['language']);
$allowed_languages = [ 'ja' ];

// Check ID/language/title
if(is_numeric($blog_id)) {
	if( $language && in_array($language, $allowed_languages) ) {
		if( strlen($title) && strlen($friendly) ) {
			
			// Update friendly to include language
			$friendly .= '-'.$language;
			
			// Check translation doesn't already exist
			$sql_check = 'SELECT 1 FROM blog_translations WHERE blog_id=? AND language=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $blog_id, $language ]);
			$rslt_check = $stmt_check->fetchColumn();
			
			// If no translation extant, create one
			if(!$rslt_check) {
				
				$sql_trans = 'INSERT INTO blog_translations (blog_id, language, friendly, title) VALUES (?, ?, ?, ?)';
				$stmt_trans = $pdo->prepare($sql_trans);
				
				if($stmt_trans->execute([ $blog_id, $language, $friendly, $title ])) {
					$output['id'] = $pdo->lastInsertId();
					$output['url'] = '/blog/'.$friendly.'/';
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Sorry, couldn\'t create translation.';
				}
				
			}
			else {
				$output['result'] = 'Sorry, that translation already exists.';
			}
			
		}
		else {
			$output['result'] = 'Please set a title for article before generating a translation.';
		}
	}
	else {
		$output['result'] = 'Sorry, that language isn\'t allowed yet.';
	}
}
else {
	$output['result'] = 'Please save article before generating a translation.';
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);