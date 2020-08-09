<?php

include_once('../php/include.php');
include_once('../translations/function-generate_translations.php');

// Allowed languages
$allowed_languages = [ 'ja' => '日本語', 'es' => 'español' ];

// Get translations for this particular folder
function init_tr($folder = null) {
	
	// Get global vars for allowed languages and current translations stored in memory
	global $allowed_languages;
	global $translations;
	
	// Make sure translations var is array
	$translations = is_array($translations) ? $translations : [];
	
	// Get current language, and then add Japanese too it if necessary
	$language = sanitize($_COOKIE['language']) ?: (sanitize($_SESSION['language']) ?: 'en');
	$languages = $language === 'ja' ? [ 'ja' ] : [ $language, 'ja' ];
	
	// For each language which we'll be using in the interface, grab translations
	foreach($languages as $language_key) {
		
		// Make sure each language is allowed
		if( strlen($language_key) && in_array($language_key, array_keys($allowed_languages)) ) {
			
			// Set working folder and translation file to look for in folder
			$folder = friendly($folder) ?: end(explode('/', dirname(__FILE__, 1)));
			$translation_file = '../'.$folder.'/lang.'.$language_key;
			
			// If translation file exists, we'll grab its contents
			if( strlen($folder) && file_exists($translation_file) ) {
				
				// Open translation file, decompress, and turn contents into array
				$file_contents = file_get_contents($translation_file);
				$file_contents = $file_contents ? gzuncompress($file_contents) : null;
				$file_contents = $file_contents ? unserialize($file_contents) : null;
				
				// If successfully grabbed translation strings, add then to global translations var (broken down by folder and language)
				if( is_array($file_contents) && !empty($file_contents) ) {
					foreach($file_contents as $string => $translation) {
						$translations[ $string ][ $language_key ] = $translation;
					}
				}
				
			}
			
		}
		
	}
	
}

// Get translation for requested string
function tr($string, $args = null) {
	
	// Sanitize string
	$string = sanitize($string);
	
	// Get global vars for allowed languages and current translations stored in memory
	global $allowed_languages;
	global $translations;
	
	// Get current language
	$language = sanitize($_COOKIE['language']) ?: (sanitize($_SESSION['language']) ?: 'en');
	
	// Set working folder and translation file to look for in folder
	$folder = friendly($args['folder']) ?: end(explode('/', dirname(__FILE__, 1)));
	
	// If string provided and requested language allowed
	if( strlen($string) && strlen($language) && in_array($language, array_keys($allowed_languages)) ) {
		
		// Check global translations var for string in this particular folder
		if( 
			is_array($translations) && !empty($translations) &&
			is_array($translations[ $string ]) && !empty($translations[ $string ])
		) {
			
			// If string exists, return translation if exists, otherwise return original input
			if(strlen($translations[ $string ][ $language ])) {
				$output = $translations[ $string ][ $language ];
			}
			else {
				$output = $string;
			}
			
		}
		
		// If string wasn't set at all (i.e. isn't in database), add it
		else {
			
			global $pdo;
			
			if($pdo) {
				
				// Double check that string isn't in DB
				$sql_check = 'SELECT 1 FROM translations WHERE content=? AND folder=?';
				$stmt_check = $pdo->prepare($sql_check);
				$stmt_check->execute([ $string, $folder ]);
				$rslt_check = $stmt_check->fetchColumn();
				
				// If string not in DB, add it, then regen language file for this folder
				if(!$rslt_check) {
					
					$sql_string = 'INSERT INTO translations (content, folder) VALUES (?, ?)';
					$stmt_string = $pdo->prepare($sql_string);
					if($stmt_string->execute([ $string, $folder ])) {
						
						generate_translation_file($folder, $language, $pdo);
						
					}
					
				}
				
			}
			
			$output = $string;
			
		}
		
	}
	
	// Replace any variables
	if(is_array($args['replace']) && !empty($args['replace'])) {
		foreach($args['replace'] as $key => $value) {
			$output = str_replace('{'.$key.'}', $value, $output);
		}
	}
	
	// If lang set to true, get Japanese translation as well and display with lang()
	if($args['lang']) {
		
		// Check global translations var for Japanese string
		if( 
			is_array($translations) && !empty($translations) &&
			is_array($translations[ $string ]) && !empty($translations[ $string ])
		) {
			
			// If string exists, return translation if exists, otherwise return original input
			if(strlen($translations[ $string ][ 'ja' ])) {
				$output_ja = $translations[ $string ][ 'ja' ];
				
				// Replace any variables
				if(is_array($args['replace']) && !empty($args['replace'])) {
					foreach($args['replace'] as $key => $value) {
						$output_ja = str_replace('{'.$key.'}', $value, $output_ja);
					}
				}
				
			}
			
		}
		
		// If found Japanese string, return output as lang()
		if(strlen($output_ja)) {
			
			$output = lang($output, $output_ja, $args['lang_args']);
		}
		
	}
	
	// Return
	return $output;
	
}