<?php

include_once('../php/include.php');
include_once('../images/function-detect_faces.php');
include_once('../images/function-get_face_box.php');

$image_url = $_POST['image_url'];
$faces = $_POST['faces'];

if( strlen($image_url) /*&& strpos($image_url, 'https://vk.gy') === 0 && file_exists($image_url)*/ ) {
	
	// Get image info
	list($image_width, $image_height) = getimagesize($image_url);
	
	// If faces not already found, use API
	if( !strlen($faces) ) {
		$faces = detect_faces($image_url);
		$output['got_faces'] = 1;
	}
	else {
		$output['got_faces'] = 0;
	}
	
	// Decode faces
	$faces = json_decode($faces, true);
	
	// Turn API response into boxes
	foreach($faces as $i => $face) {
		
		// Get proportional box coordinates
		$desired_box_width = 116;
		$face_box = get_face_box([ 'image_url' => $image_url, 'image_height' => $image_height, 'image_width' => $image_width, 'face' => $face, 'desired_width' => $desired_box_width ]);
		
		// Get a random-ish key just to differentiate fields in the html
		$key = substr( md5($image_url), 0, 5 ).$i;
		
		$face_css  = 'background-position: -'.$face_box['box_left'].'px -'.$face_box['box_top'].'px;';
		$face_css .= 'background-size: '.$face_box['image_width'].'px '.$face_box['image_height'].'px;';
		$face_css .= 'height: '.$face_box['box_height'].'px;';
		$face_css .= 'width: '.$face_box['box_width'].'px;';
		
		$returned_html[] = '<div style="margin-top:1rem;display:inline-block;margin-right:1rem;width:116px;">';
		$returned_html[] = '<div style="background-image:url('.$image_url.');background-repeat:no-repeat;display:inline-block;'.$face_css.'"></div>';
		$returned_html[] = '<select class="input" data-populate-on-click="true" data-source="musicians" data-face=\''.json_encode($face).'\' name="image_musician_id['.$key.']" placeholder="musicians"></select>';
		$returned_html[] = '</div>';
		
	}
	
	$returned_html[] = '<div class="input__group" style="margin-left:-0.5rem;"><label class="input__label">other musicians</label><select class="input" data-populate-on-click="true" data-source="musicians" name="image_musician_id[]" placeholder="musicians"></select></div>';
	
	/*$returned_html[] = '<a class="image__add-face symbol__plus">add face</a>';
	$returned_html[] = '<a class="add-face__wrapper" style="box-shadow:0 0 10px 0 pink;position:fixed;top:5;left:0;right:0;bottom:0;margin:var(--gutter);background:hsl(var(--background));z-index:2;">';
	$returned_html[] = '<img class="add-face__image" src="'.$image_url.'" style="max-width:100%;max-height:100%;object-fit:contain;" ismap />';
	$returned_html[] = '</a>';*/
	
	$output['status'] = 'success';
	$output['result'] = implode('', $returned_html);
	$output['face_boundaries'] = json_encode($faces);
	
}
else {
	
	$output['result'] = 'Couldn\'t send data.'.$image_url.(strpos($image_url, 'https://vk.gy') === 0 ? 'y' : 'n').(file_exists($image_url) ? 'y' : 'n');
	
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);