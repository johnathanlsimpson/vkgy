<?php

include_once('../php/include.php');

// Get translation for requested string
function tr($string, $args = null) {
	
	global $translate;
	
	if($translate) {
		
		return $translate->tr($string, $args);
		
	}
	
}