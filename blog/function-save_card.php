<?php

include_once('../php/include.php');

$blog_id = sanitize($_POST['blog_id']);
$image_data = $_POST['image_data'];

if( is_numeric($blog_id) && strlen($image_data) ) {
	
	// Clean image data and convert to image
	$image_data = str_replace('data:image/webp;base64,', '', $image_data);
	$image_data = str_replace(' ', '+', $image_data);
	$image = base64_decode($image_data);
	
	// Save image
	$filename = '../images/blog_images/'.$blog_id.'.webp';
	if( file_put_contents($filename, $image) ) {
		$output['status'] = 'success';
		$output['result'] = 'Saved image.';
	}
	else {
		$output['result'] = 'Couldn\'t save image.';
	}
	
}
else {
	$output['result'] = 'Either ID or image is missing.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);