<?php

include_once('../php/include.php');

class sanitizer {
	
	// =======================================================
	// Main function--prepare for DB storage
	// =======================================================
	public function sanitize( $input, $modifier=null ) {
		
		// Don't try to sanitize arrays
		if( !is_array( $input ) ) {
			
			// Standardize symbols
			$input = $this->standardize_symbols( $input );
			
			// Clean spaces
			$input = $this->standardize_spaces( $input );
			
			// Reconsider how we're handling escapes here, 'cause it's getting messy
			
			// Set up some special characters that will be stored as entities in DB
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
		
	}
	
	
	
	// =======================================================
	// Make URL-friendly slug
	// =======================================================
	public function friendly( $input, $method='return' ) {
		
		// Only clean text if it's a string
		if( !is_array($input) ) {
			
			// Make sure we're working with raw text
			$input = html_entity_decode( $input, ENT_QUOTES, 'UTF-8' );
			
			// Collapse apostrophes
			$input = str_replace( "'", '', $input );
			
			// Make lowercase
			$input = strtolower( $input );
			
			// Replace common accented letters
			$input = str_replace( [ 'é' ], [ 'e' ], $input );
			
			// Replace non alphanumeric
			$input = preg_replace('/'.'[^A-z0-9\-]'.'/', '-', $input);
			
			// Replace multi-hyphens
			$input = preg_replace('/'.'-+'.'/', '-', $input);
			
			// Remove starting or ending hyphens
			$input = preg_replace('/'.'^-|-$'.'/', '', $input);
			
		}
		
		// Default to hyphen
		if( is_array($input) || strlen($input) < 1 ) {
			$input = '-';
		}
		
		// Legacy: echo if necessary
		if( $method === 'echo' ) {
			echo $input;
		}
		else {
			return $input;
		}
		
		/*if(!empty($input)) {
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
		
		return $input;*/
		
	}
	
	
	
	// =======================================================
	// Standardize common symbol variants
	// =======================================================
	public function standardize_symbols( $input )  {
		
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
			
			// Apostrophe
			"'" => [
				// Replaces
				'’',
			],
			
		];
		
		// With each standard symbol, replace all its variants
		foreach($symbols as $replacement => $searches) {
			$input = str_replace( $searches, $replacement, $input );
		}
		
		return $input;
		
	}
	
	
	
	// =======================================================
	// Standardize multiple spaces, hidden spaces, etc
	// =======================================================
	public function standardize_spaces( $input ) {
		
		// Standardize spaces
		$input = preg_replace('/(\h+)/u', ' ', $input);
		
		// Trim whitespace from ends
		$input = trim($input, " \t\0\x0B");
		
		// Remove zero-width spaces--putting this in trim breaks certain characters
		$input = preg_replace('/'.'﻿'.'/', '', $input);
		
		return $input;
		
	}
	
	
	
	// =======================================================
	// Correct “translations” of Japanese symbols
	// =======================================================
	public function match_romaji_to_japanese( $japanese, $romaji ) {
		
		// Standardize (but undo entity transform)
		$japanese = html_entity_decode(sanitize($japanese), ENT_QUOTES, 'utf-8');
		$romaji = html_entity_decode(sanitize($romaji), ENT_QUOTES, 'utf-8');
		
		// If both Japanese and romaji provided
		if(mb_strlen($japanese, 'utf-8') && mb_strlen($romaji, 'utf-8')) {
			
			// 。 vs .
			$num_circle_periods = substr_count($japanese, '。');
			if($num_circle_periods) {
				if($num_circle_periods === substr_count($romaji, '.')) {
					$romaji = str_replace('.', '。', $romaji);
				}
				else {
					if(preg_match_all('/'.'\. |$'.'/', $romaji) === $num_circle_periods) {
						$romaji = preg_replace('/'.'\.( )|\.($)'.'/', '。$1', $romaji);
					}
				}
			}
			
			// 「」 vs '', "", []
			foreach(['「」', '『』', '【】'] as $brackets) {
				$left_bracket = mb_substr($brackets, 0, 1, 'utf-8');
				$right_bracket = mb_substr($brackets, 1, 1, 'utf-8');
				
				$num_brackets = substr_count($japanese, $left_bracket) + substr_count($japanese, $right_bracket);
				
				if($num_brackets) {
					if($num_brackets !== substr_count($romaji, $left_bracket) + substr_count($romaji, $right_bracket)) {
						
						// For each type of incorrect substitution, replace with correct bracket until all substitutions gone
						foreach(['\'', '"', '[]', '“”', '‘’'] as $sub) {
							$num_subs = mb_strlen($sub, 'utf-8') === 1 ? substr_count($romaji, $sub) : substr_count($romaji, mb_substr($sub, 0, 1, 'utf-8')) + substr_count($romaji, mb_substr($sub, 1, 1, 'utf-8'));
							
							if($num_subs === $num_brackets) {
								
								// If replaced by [], do simple replace; otherwise replace odd ' with 「 and even ' with 」, etc.
								if(mb_strlen($sub, 'utf-8') === 2) {
									$romaji = str_replace(mb_substr($sub, 0, 1, 'utf-8'), mb_substr($brackets, 0, 1, 'utf-8'), $romaji);
									$romaji = str_replace(mb_substr($sub, 1, 1, 'utf-8'), mb_substr($brackets, 1, 1, 'utf-8'), $romaji);
								}
								else {
									while($num_subs) {
										if($num_subs % 2 === 0) {
											$romaji = preg_replace('/'.$sub.'/', mb_substr($brackets, 0, 1, 'utf-8'), $romaji, 1);
										}
										else {
											$romaji = preg_replace('/'.$sub.'/', mb_substr($brackets, 1, 1, 'utf-8'), $romaji, 1);
										}
										$num_subs = mb_strlen($sub, 'utf-8') === 1 ? substr_count($romaji, $sub) : substr_count($romaji, mb_substr($sub, 0, 1, 'utf-8')) + substr_count($romaji, mb_substr($sub, 1, 1, 'utf-8'));
									}
								}
							}
						}
					}
				}
			}
			
			// ・・・ vs ...
			if(substr_count($japanese, '・・・') === substr_count($romaji, '...')) {
				$romaji = str_replace('...', '・・・', $romaji);
			}
			
			// ・ vs .
			if(substr_count($japanese, '・') === substr_count($romaji, '.')) {
				$romaji = str_replace('.', '・', $romaji);
			}
			
			// ・ vs (space)
			if( substr_count($japanese, '・') && !substr_count($romaji, '・') && substr_count($japanese, '・') === substr_count($romaji, ' ') ) {
				$romaji = str_replace(' ', '・', $romaji);
			}
			
			// Unset if unnecessary
			if($romaji === $japanese) {
				$romaji = null;
			}
			
		}
		
		return $romaji;
		
	}
	
}