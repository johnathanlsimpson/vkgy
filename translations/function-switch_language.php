<?php

include_once('../php/include.php');

$language = friendly($_POST['language']);

if(strlen($language) && in_array($language, array_keys($translate->allowed_languages))) {
	
	$output['x'] = 'yo';
	
	$translate->set_language($language);
	$output['status'] = 'success';
	
}
$output['language'] = $language;
$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);