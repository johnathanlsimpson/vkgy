<?php
include_once('../php/include.php');

function image_exists($input_string, $pdo) {
	if(substr($input_string, 0, 3) === '../') {
		$input_string = substr($input_string, 2);
	}

	if(preg_match('/'.'\/images\/(\d+)(?:-[\w-]*)\.(jpg|jpeg|gif|png)'.'/', $input_string, $match)) {
		if(is_array($match)) {
			$check_file = strtolower('/images/image_files/'.$match[1].'.'.$match[2]);
		}
	}
	elseif(preg_match('/'.'\/artists\/([\w-]+)\/main(?:\.(?:small|medium|large))?.jpg'.'/', $input_string, $match)) {
		if(is_array($match)) {
			$sql_check_default = 'SELECT images.id, images.extension FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.friendly=? LIMIT 1';
			$stmt_check_default = $pdo->prepare($sql_check_default);
			$stmt_check_default->execute([ $match[1] ]);
			$rslt_check_default = $stmt_check_default->fetch();
			
			if(is_array($image) && !empty($image)) {
				$check_file = '/images/image_files/'.$image['id'].'.'.strtolower($image['extension']);
			}
		}
	}
	else {
		$check_file = $input_string;
	}

	if(strlen($check_file)) {
		return file_exists('..'.$check_file);
	}
}