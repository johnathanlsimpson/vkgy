<?php
	/*function clean_values(&$value, $key) {
		
		if( !is_array($value) ) {
			
			$value = sanitize($value);

			// Sanitize seems to be replacing spaces with &nbsp; *sometimes*, which we don't really want
			// but rather than modify sanitize which is used across the site, let's start here
			$value = str_replace('&nbsp;', ' ', $value);

			foreach([
				"&#92;("     => "\\(",
				"&#92;)"     => "\\)",
				"\'"         => "&#39;", // apostrophe
				"&#92;&#39;" => "&#39;", // apostrophe
				"'"          => "&#39;", // apostrophe
				"&#65374;"   => "~", // tilde
				"&#65378;"   => "&#12300;", // bracket
				"&#65379;"   => "&#12301;", // bracket
				"&#12288;"   => " ", // wide space
			] as $search => $replace) {
				$value = str_replace($search, $replace, $value);
			}

			$value = trim($value);
			$value = preg_replace('/'.'\h+'.'/', ' ', $value); // remove whitespace
			$value = mb_strlen($value, 'utf-8') > 0 ? $value : null; // make null
			
		}
		
	}*/
	
	// This is in songs class now
	// Compare romaji to Japanese and correct "translations" of symbols
	/*function match_japanese($japanese, $romaji) {
		
		// Standardize (but undo entity transform)
		$japanese = html_entity_decode(sanitize($japanese), ENT_QUOTES, 'utf-8');
		$romaji = html_entity_decode(sanitize($romaji), ENT_QUOTES, 'utf-8');
		
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
			
			// Unset if unnecessary
			if($romaji === $japanese) {
				$romaji = null;
			}
		}
		
		return $romaji;
	}*/
?>