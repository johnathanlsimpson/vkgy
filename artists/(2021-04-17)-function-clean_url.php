<?php

include_once('../php/include.php');

function clean_url( $url ) {
	
	// Make sure has at least one dot
	if( strpos($url, '.') === false ) {
		$url = null;
	}
	
	if( $url ) {
		
		// Remove spaces
		$url = trim($url);
		
		// Make sure has protocol
		$url = preg_replace('/'.'^(?!http)'.'/m', 'http://', $url);
		
		// Remove archive.org
		$url = preg_replace('/'.'^(?:https?:\/\/)?web\.archive\.org\/web\/\d+\/'.'/m', '', $url);
		
	}
	
	return $url;
	
}