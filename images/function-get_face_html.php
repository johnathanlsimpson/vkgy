<?php

include_once('../php/include.php');
include_once('../php/function-render_component.php');
include_once('../images/function-calculate_face_box.php');
include_once('../images/function-render_face.php');
include_once('../images/template-face.php');

$image_url = $_POST['image_url'];
$faces = $_POST['faces'];
$artist_id = $_POST['artist_id'];

// Decode faces
$faces = json_decode($faces, true);

// Make sure faces not empty
if( $faces && is_array($faces) && !empty($faces) ) {
	
	// Turn API response into boxes
	foreach($faces as $i => $face) {
		$returned_html[] = render_face([ 'face' => $face, 'image_url' => $image_url, 'artist_id' => $artist_id ]);
	}
	
	$output['status'] = 'success';
	$output['result'] = implode('', $returned_html);
	$output['face_boundaries'] = json_encode($faces);
	
}
else {
	$output['result'] = 'No faces supplied.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);