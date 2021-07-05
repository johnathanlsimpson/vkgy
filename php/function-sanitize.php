<?php

include_once('../php/class-sanitizer.php');
$sanitizer = $sanitizer ?: new sanitizer();

// For now, let's include this wrapper for the class since so many functions use it
function sanitize( $input, $modifier=null ) {

	global $sanitizer;
	
	$output = $sanitizer->sanitize( $input, $modifier );
	
	return $output;
	
}

/*// Standardize common symbol variants
function standardize_symbols( $input )  {
	
	// Make kana full width
	$input = mb_convert_kana($input, "aKV", "UTF-8");
	
	// Define standard symbols and the variants to standardize
	$symbols = [
		
		// Tilde
		'~' => [
			// Replaces
			'˜', '∼', '～', '〜', '⁓'
		],
		
		// Hyphen
		'-' => [
			// Replaces
			'‐', '‑', '‒', '–', '―', '−', '⸺', '⸻', '﹘', '﹣', '－',
		],
		
		// Trailing dots
		'・・・' => [
			// Replaces
			'…', '⋯',
		],
		
		// Middle dot
		'・' => [
			// Replaces
			'·', '•',
		],
		
	];
	
	// With each standard symbol, replace all its variants
	foreach($symbols as $replacement => $searches) {
		$input = str_replace( $searches, $replacement, $input );
	}
	
	return $input;
	
}

// Clean up multiple spaces, hidden spaces, etc
function standardize_spaces( $input ) {
	
	// Standardize spaces
	$input = preg_replace('/(\h+)/u', ' ', $input);
	
	// Trim whitespace
	$input = trim($input, " \t\0\x0B﻿");
	
	return $input;
	
}

// Perform standardization then convert to entities for storage
function sanitize( $input, $modifier=null ) {
	
	// Don't try to sanitize arrays
	if( !is_array( $input ) ) {
		
		// Standardize symbols
		$input = standardize_symbols( $input );
		
		// Clean spaces
		$input = standardize_spaces( $input );
		
		// Set up some special characters that will be stored as entities in DB
		// Probably need to reconsider how we're handling escapes here, 'cause it's getting messy
		$search = [ "?", "\\", "<", ">", "\"", "'", ];
		$replace = [ "&#63;", "&#92;", "&#60;", "&#62;", "&#34;", "&#39;", ];
		
		// If output will be used within JSON (or Alpine specifically), escape additional chars
		if( $modifier === 'for_json' || $modifier === 'alpine' ) {

			$search[] = '{';
			$replace[] = '&#123;';

			$search[] = '}';
			$replace[] = '&#125;';

			$search[] = '&#39;';
			$replace[] = '&#92;&#39;';

		}
		
		// Now do the search/replace
		if($modifier != "allowhtml") {
			$input = str_replace($search, $replace, $input);
		}
		
		// Convert to entities
		$input = mb_convert_encoding( $input, 'HTML-ENTITIES', 'UTF-8' );
		
		
		// There was an edgecase where spaces were being converted, so let's fix that
		$input = str_replace( '&nbsp;', ' ', $input );
		
		// Added 2021-06-30: let's try returning null if string was empty--might cause problems
		$input = strlen( $input ) ? $input : null;
		
	}
	
	return $input;
	
}*/