<?php

// Given stylesheet, clean up and save into array for display on page
function style( $style_urls, $location = 'bottom' ) {
	
	// Set location in page
	$array_key = 'styles_'.( $location === 'top' ? 'top' : 'bottom' );
	
	// Make sure we have an array of stylesheets
	if(!is_array($style_urls)) {
		$style_urls = [$style_urls];
	}
	
	// Loop through stylesheets, clean and append
	foreach($style_urls as $style_url) {
		
		// Make sure url starts with /
		$style_url = strpos($style_url, '..') === 0 ? substr($style_url, 2) : $style_url;
		$style_url = substr($style_url, 0, 1) != '/' ? '/'.$style_url : $style_url;
		
		// Make sure file exists
		if( file_exists('..'.$style_url) ) {
			
			$GLOBALS[ $array_key ][] = $style_url;
			
		}
		
	}
	
}

// Display the stylesheets on the page
function display_styles( $location = 'bottom', $preload = true, $defer = true ) {
	
	// Get array of styles
	$array_key = 'styles_'.( $location === 'top' ? 'top' : 'bottom' );
	$styles = $GLOBALS[ $array_key ];
	$styles = is_array($styles) ? $styles : [];
	
	// Make sure we don't load one twice
	$styles = array_unique($styles);
	
	// Loop through styles and echo
	if( is_array($styles) && !empty($styles) ) {
		foreach($styles as $style_url) {
			
			// Get the last update time as a string to make sure we don't grab out-of-date cached version
			$date_string = date( 'YmdHis', filemtime('..'.$style_url) );
			$style_url .= '?'.$date_string;
			
			// Set up templates
			$link_template     = '<link href="{style_url}" rel="stylesheet" />';
			$preload_template  = '<link as="style" href="{style_url}" rel="preload" />';
			$defer_template    = '<link href="{style_url}" media="print" onload="this.media=\'all\';this.onload=null;" rel="stylesheet" />';
			$defer_template   .= '<noscript>'.$link_template.'</noscript>';
			
			// Preload stylesheet
			if( $preload ) {
				$output .= str_replace( '{style_url}', $style_url, $preload_template );
			}
			
			// Defer stylesheet
			if( $defer ) {
				$output .= str_replace( '{style_url}', $style_url, $defer_template );
			}
			
			// Normal stylesheet
			else {
				$output .= str_replace( '{style_url}', $style_url, $link_template );
			}
			
		}
	}
	
	// Return
	return $output;
	
}