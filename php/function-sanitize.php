<?php

// Standardize input
function standardize($input) {
	
	// Make kana full width
	$input = mb_convert_kana($input, "aKV", "UTF-8");
	
	// Define standard symbols and the variants to standardize
	$symbols = [
		
		// Tildes
		'~' => [
			'˜', '∼', '～', '〜', '⁓'
		],
		
		// Hyphens
		'-' => [
			'‐', '‑', '‒', '–', '―', '−', '⸺', '⸻', '﹘', '﹣', '－',
		],
		
		// Ambiguous periods
		'・・・' => [
			'…',
		],
		
	];
	
	// Standardize symbols
	foreach($symbols as $replacement => $searches) {
		$input = str_replace( $searches, $replacement, $input );
	}
	
	// Standardize spaces
	$input = preg_replace('/(\h+)/u', ' ', $input);
	
	// Trim whitespace
	$input = trim($input, " \t\0\x0B");
	
	return $input;
	
}

// Transform text to entities
function sanitize($input, $modifier = NULL) {
	
	// Standardize
	$input = standardize($input);
	
	$search = [ "?", "\\", "<", ">", "\"", "'", '﻿' ];
	$replace = [ "&#63;", "&#92;", "&#60;", "&#62;", "&#34;", "&#39;", '' ];
	
	if( $modifier === 'alpine' ) {
		
		$search[] = '{';
		$replace[] = '&#123;';
		
		$search[] = '}';
		$replace[] = '&#125;';
		
		$search[] = '&#39;';
		$replace[] = '&#92;&#39;';
		
	}
	
	if($modifier != "allowhtml") {
		$input = str_replace($search, $replace, $input);
	}
	
	$input = mb_convert_encoding($input, "HTML-ENTITIES", "UTF-8");
	
	if($modifier != "allowslash") {
		if(@mysqli_ping()) {
			$input = mysqli_real_escape_string($GLOBALS["mysqli"], $input);
		}
	}
	
	return($input);
	
}