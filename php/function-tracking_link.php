<?php

// Returns a link built to track clicks to outbound affiliate links
function tracking_link( $platform, $input = null, $location_in_page = null ) {
	
	// Set up some defaults
	$allowed_platforms = [
		'amazon',
		'cdjapan',
		'rarezhut',
	];
	
	// Input may be string (e.g. product number) or array (e.g. preferred product number / backup product name)
	if( is_array($input) && !empty($input) ) {
		
		$favored_term = $input[0];
		$default_term = $input[1];
		
	}
	
	// Otherwise may be a string
	elseif( strlen($input) ) {
		
		$default_term = $input;
		
	}
	
	// Make sure we have correct inputs set
	if( strlen($platform) && in_array( $platform, $allowed_platforms ) ) {
		//if( strlen($default_term) ) {
			
			// Clean some vars
			$default_term     = substr( $default_term, 0, 100 );
			$favored_term   = strlen($favored_term) ? substr( $favored_term, 0, 100 ) : null;
			$current_page     = $_SERVER['REQUEST_URI'];
			
			// Make array of data for url
			$url_data = [
				'p' => $platform,
				'd' => $default_term,
				'f' => $favored_term,
				'c' => $current_page,
				'l' => $location_in_page
			];
			
			// Output into template
			$output = '/track/?'.http_build_query($url_data);
			
		//}
	}
	
	return $output;
	
}