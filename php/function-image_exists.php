<?php
include_once('../php/include.php');

function image_exists($input_string, $pdo, $return_dimensions = false) {
	
	// /images/123-dali-group.jpg
	if(preg_match('/'.'\/images\/(\d+)(?:-[\-\w]*)?(?:\.[a-z]+)?\.(jpg|jpeg|gif|png|webp)$'.'/', $input_string, $match)) {
		if(is_array($match)) {
			$check_file = strtolower('/images/image_files/'.$match[1].'.'.$match[2]);
		}
	}
	
	// /artists/dali/main.jpg
	elseif(preg_match('/'.'\/artists\/([\-\w]+)\/main(?:\.(?:small|medium|large|thumbnail))?.jpg'.'/', $input_string, $match)) {
		if(is_array($match)) {
			$sql_check_default = 'SELECT images.id, images.extension FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.friendly=? LIMIT 1';
			$stmt_check_default = $pdo->prepare($sql_check_default);
			$stmt_check_default->execute([ $match[1] ]);
			$image = $stmt_check_default->fetch();
			
			if(is_array($image) && !empty($image) && is_numeric($image['id'])) {
				$check_file = '/images/image_files/'.$image['id'].'.'.strtolower($image['extension']);
			}
		}
	}
	
	// /images/blog_images/123.webp
	elseif( preg_match('/'.'^\/images\/[0-9a-z\-\/\_]+\.(?:jpg|jpeg|gif|png|webp)$'.'/', $input_string) ) {
		$check_file = strtolower($input_string);
	}
	
	if(strlen($check_file)) {
		if(file_exists('..'.$check_file)) {
			if($return_dimensions) {
				list($width, $height) = getimagesize('..'.$check_file);
				
				return [ 'height' => $height, 'width' => $width, 'ratio' => $height / $width ];
			}
			else {
				return true;
			}
		}
	}
}