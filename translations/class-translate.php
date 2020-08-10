<?php

include_once('../php/include.php');

class translate {
	
	public $allowed_languages;
	public $translations;
	public $language;
	public $language_name;
	public $pdo;
	
	
	
	// ======================================================
	// Construct
	// ======================================================
	
	// Connect and set vars
	function __construct($pdo) {
		
		// Create PDO connection if not already provided
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		$this->pdo = $pdo;
		
		// Set allowed languages
		$this->allowed_languages = [
			'ja' => '日本語',
			'en' => 'English',
			'de' => 'Deutsch',
			'es' => 'español',
			'fr' => 'français',
			'ru' => 'Русский',
			'zh' => '中文',
		];
		
		// Set current language
		$this->set_language();
		
	}
	
	
	
	// ======================================================
	// Generate translation file
	// ======================================================

	// Update translations for that particular page
	public function generate_translation_file($folder, $language) {
		
		if(strlen($folder) && file_exists('../'.$folder)) {
			
			$sql_translations = '
				SELECT
					translations.*,
					translations_proposals.content AS translation
				FROM
					translations
				LEFT JOIN
					translations_proposals ON translations_proposals.id=translations.'.$language.'_id
				WHERE
					translations.folder=?
			';
			
			$stmt_translations = $this->pdo->prepare($sql_translations);
			$stmt_translations->execute([ $folder ]);
			$rslt_translations = $stmt_translations->fetchAll();
			
			$translations = [];
			
			if(is_array($rslt_translations) && !empty($rslt_translations)) {
				foreach($rslt_translations as $translation) {
					$translations[ $translation['content'] ] = $translation['translation'] ?: null;
				}
			}
			
			$translation_file = gzcompress( serialize( $translations ) );
			$filename = '../'.$folder.'/lang.'.$language;
			file_put_contents( $filename, $translation_file );
			
		}
		
	}
	
	
	
	// ======================================================
	// Initialize translations
	// ======================================================
	
	// Set current language
	public function set_language($language = null) {
		
		// If language not manually specified, get from session or cookie
		$language = $language ?: ($_SESSION['language'] ?: $_COOKIE['language']);
		
		// If requested language exists, set it
		if(strlen($language) && in_array($language, array_keys($this->allowed_languages))) {
			
			$this->language = $language;
			
		}
		
		// Fallback to English as default language
		else {
			
			$this->language = 'en';
			
		}
		
		// Save language name and update session/cookie
		$this->language_name = $this->allowed_languages[ $this->language ];
		$_SESSION['language'] = $language;
		setcookie('language', $language, time() + 60*60*24*30, '/', 'vk.gy', true, true);
		
	}
	
	// Get translation strings from file
	public function init_tr($folder = null) {
		
		// Make sure translations var is array
		$this->translations = is_array($this->translations) ? $this->translations : [];
		
		// Get current language, and then add Japanese too it if necessary
		$languages = $this->language === 'ja' ? [ 'ja' ] : [ $this->language, 'ja' ];
		
		// For each language which we'll be using in the interface, grab translations
		foreach($languages as $language_key) {
			
			// Make sure each language is allowed
			if( strlen($language_key) && in_array($language_key, array_keys($this->allowed_languages)) ) {
				
				// Set working folder and translation file to look for in folder
				//$folder = friendly($folder) ?: end(explode('/', dirname(__FILE__, 1)));
				$folder = friendly($folder);
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
							$this->translations[ strtolower($string) ][ $language_key ] = $translation;
						}
					}
					
				}
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Show translation
	// ======================================================
	
	// Get translation for requested string
	public function tr($string, $args = null) {
		
		// Sanitize string
		$string = sanitize($string);
		
		// Set working folder and translation file to look for in folder
		$folder = friendly($args['folder']) ?: (explode('/', $_SERVER['REQUEST_URI'])[1] ?: 'main');
		
		// If string provided and requested language allowed
		if( strlen($string) && strlen($this->language) ) {
			
			// Check global translations var for string in this particular folder
			if( 
				is_array($this->translations) && !empty($this->translations) &&
				is_array($this->translations[ strtolower($string) ]) && !empty($this->translations[ strtolower($string) ])
			) {
				
				// If string exists, return translation if exists, otherwise return original input
				if(strlen($this->translations[ strtolower($string) ][ $this->language ])) {
					$output = $this->translations[ strtolower($string) ][ $this->language ];
				}
				else {
					$output = $string;
				}
				
			}
			
			// If string wasn't set at all (i.e. isn't in database), add it
			else {
				
				if($this->pdo) {
					
					// Double check that string isn't in DB
					$sql_check = 'SELECT 1 FROM translations WHERE content=? AND folder=?';
					$stmt_check = $this->pdo->prepare($sql_check);
					$stmt_check->execute([ $string, $folder ]);
					$rslt_check = $stmt_check->fetchColumn();
					
					// If string not in DB, add it, then regen language file for this folder
					if(!$rslt_check) {
						
						$sql_string = 'INSERT INTO translations (content, folder) VALUES (?, ?)';
						$stmt_string = $this->pdo->prepare($sql_string);
						if($stmt_string->execute([ $string, $folder ])) {
							
							// If fallback Japanese was provided (i.e. for initial pass at making site translatable), insert as proposal
							if(strlen($args['ja'])) {
								
								// Get ID of newly inserted string
								$string_id = $this->pdo->lastInsertId();
								
								// Insert Japanese as proposal
								$sql_proposal = 'INSERT INTO translations_proposals (language, content, en_id, user_id) VALUES (?, ?, ?, ?)';
								$stmt_proposal = $this->pdo->prepare($sql_proposal);
								$stmt_proposal->execute([ 'ja', sanitize($args['ja']), $string_id, 1 ]);
								
								// Get ID of proposal
								$proposal_id = $this->pdo->lastInsertId();
								
								// Insert vote for Japanese proposal
								$sql_vote = 'INSERT INTO translations_votes (proposal_id, vote, user_id) VALUES (?, ?, ?)';
								$stmt_vote = $this->pdo->prepare($sql_vote);
								$stmt_vote->execute([ $proposal_id, 1, 1 ]);
								
								// Update translation to set proposal as accepted
								$sql_accept = 'UPDATE translations SET ja_id=? WHERE id=? LIMIT 1';
								$stmt_accept = $this->pdo->prepare($sql_accept);
								$stmt_accept->execute([ $proposal_id, $string_id ]);
								
								$this->generate_translation_file($folder, 'ja');
								$this->init_tr($folder);
								
							}
							
							//if($this->language != 'en') {
								//$this->generate_translation_file($folder, $this->language);
							//}
							
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
				is_array($this->translations) && !empty($this->translations) &&
				is_array($this->translations[ strtolower($string) ]) && !empty($this->translations[ strtolower($string) ])
			) {
				
				// If string exists, return translation if exists, otherwise return original input
				if(strlen($this->translations[ strtolower($string) ][ 'ja' ])) {
					$output_ja = $this->translations[ strtolower($string) ][ 'ja' ];
					
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
	
}