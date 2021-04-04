<?php

function calculate_face_box($args) {
	
	//$image_url      = $args['image_url'];
	$image_height   = $args['image_height'];
	$image_width    = $args['image_width'];
	$face           = $args['face'];
	$desired_width  = $args['desired_width'] ?: 100;
	$desired_height = $args['desired_height'] ?: $desired_width * 1.5;
	
	// Save initial face coords
	$box_left   = $face['start_x'];
	$box_width  = $face['end_x'] - $face['start_x'];
	$box_top    = $face['start_y'];
	$box_height = $face['end_y'] - $face['start_y'];
	
	// Get vertical midpoint of box, then extend height of box to 1.5x of width
	$box_middle_y = $box_top + ($box_height / 2);
	$box_height   = $box_width * ($desired_height / $desired_width);
	$box_top      = $box_middle_y - ($box_height / 2);
	$box_top      = $box_top > -1 ? $box_top : 0;
	
	// Convert points into percents so we can adjust size
	$box_left_percent   = $box_left / $image_width;
	$box_width_percent  = $box_width / $image_width;
	$box_top_percent    = $box_top / $image_height;
	$box_height_percent = $box_height / $image_height;
	
	// Let's adjust sizing so final box width will equal 100px
	$new_box_width    = $desired_width;
	$size_ratio       = $new_box_width / $box_width;
	$new_image_width  = $image_width * $size_ratio;
	$new_image_height = $image_height * $size_ratio;
	
	// Then get new coords
	$new_box_left   = $new_image_width * $box_left_percent;
	$new_box_width  = $new_image_width * $box_width_percent;
	$new_box_top    = $new_image_height * $box_top_percent;
	$new_box_height = $new_image_height * $box_height_percent;
	
	$return = [
		'box_left' => $new_box_left,
		'box_width' => $new_box_width,
		'box_top' => $new_box_top,
		'box_height' => $new_box_height,
		'image_width' => $new_image_width,
		'image_height' => $new_image_height,
	];
	
	// Round values
	foreach($return as $key => $value) {
		$return[$key] = round($value);
	}
	
	// Make a css string w/ variables for convenience	
	$face_styles  = '--background-position:-'.$return['box_left'].'px -'.$return['box_top'].'px;';
	$face_styles .= '--background-size:'.$return['image_width'].'px '.$return['image_height'].'px;';
	
	$return['css'] = $face_styles;
	
	return $return;
	
}