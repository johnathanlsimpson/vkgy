<?php

include_once('../php/class-sanitizer.php');
$sanitizer = $sanitizer ?: new sanitizer();

// For now, let's include this wrapper for the class since so many functions use it
function friendly( $input, $method='return' ) {

	global $sanitizer;
	
	$output = $sanitizer->friendly( $input, $method );
	
	return $output;
	
}


/*function friendly($input, $method = "return") {
		if(!empty($input)) {
			$input = str_replace(array("&ldquo;", "&rdquo;"), "-", $input);
			$input = html_entity_decode($input, ENT_QUOTES, "UTF-8");
			$input = str_replace("'", "", $input);
			$input = preg_replace("#[^A-Za-z0-9-]#", "-", $input);
			$input = preg_replace("/-{2,}/", "-", $input);
			$input = preg_replace("/^-+|-+$/", "", $input);
			$input = strtolower($input);
			$input = (strlen($input) < 1 ? "-" : $input);

			if($method == "echo") {
				echo($input);
			}
			else {
				return($input);
			}
		}
	}*/