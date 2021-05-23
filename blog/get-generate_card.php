<?php

include_once('../php/include.php');
include_once('../blog/function-generate_card.php');

$blog_id = $_POST['blog_id'];

if( is_numeric($blog_id) ) {
	
	if( generate_card( $blog_id ) ) {
		$output['status'] = 'success';
	}
	else {
		$output['result'] = 'Couldn\'t generate image.';
	}
	
}
else {
	$output['result'] = 'Missing ID.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);