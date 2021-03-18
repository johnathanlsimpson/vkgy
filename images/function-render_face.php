<?php

include_once('../php/include.php');
include_once('../php/function-render_component.php');
include_once('../images/function-calculate_face_box.php');
include_once('../images/template-face.php');

function render_face($args) {
	
	$face               = $args['face'];
	$image_url          = $args['image_url'];
	$relative_image_url = preg_replace('/'.'^.*?\/images\/(.+)$'.'/', '../images/image_files/$1', $image_url);
	$musician_id        = is_numeric($args['musician_id']) ? $args['musician_id'] : null;
	$musician_name      = strlen($args['musician_name']) ? $args['musician_name'] : null;
	$artist_id          = $args['artist_id'];
	
	global $face_template;
	
	if( is_array($face) && !empty($face) ) {
		
		if( strlen($relative_image_url) /*&& strpos($image_url, 'https://vk.gy') === 0*/ && file_exists($relative_image_url) ) {
			
			// Get image info
			list($image_width, $image_height) = getimagesize($relative_image_url);
			
			// Make sure image is valid
			if( $image_height && $image_width ) {
				
				// Get proportional box coordinates
				$desired_box_width = 116;
				$face_box = calculate_face_box([ /*'image_url' => $image_url,*/ 'image_height' => $image_height, 'image_width' => $image_width, 'face' => $face, 'desired_width' => $desired_box_width ]);
				
				return render_component($face_template, [
					'image_url'           => $image_url,
					'background_position' => '-'.$face_box['box_left'].'px -'.$face_box['box_top'].'px',
					'background_size'     => $face_box['image_width'].'px '.$face_box['image_height'].'px',
					'height'              => $face_box['box_height'].'px',
					'width'               => $face_box['box_width'].'px',
					'face_coordinates'    => str_replace( ['{','}'], ['&lcub;','&rcub;'], json_encode($face) ),
					'musician_id'         => $musician_id,
					'musician_name'       => $musician_name,
					'artist_id'           => $artist_id,
				'source_attr_suffix' => is_numeric($artist_id) ? '_'.$artist_id : null,
				]);
				
			}
			
		}
		
	}
	
}