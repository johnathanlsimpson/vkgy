<?php

include_once('../php/include.php');

class song {
	
	// =======================================================
	// Connect
	// =======================================================
	function __construct($pdo) {
		
		// Set up connection
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		$this->pdo = $pdo;
		
	}
	
	
	
	// ======================================================
	// Get song from track info
	// ======================================================
	public function get_song_from_track( $args = [] ) {
		
		// Make sure we're given some info
		if( is_array($args) && !empty($args) ) {
			
			// Make sure we an artist ID
			if( is_numeric( $args['artist_id'] ) ) {
				
				// Make sure we have a name
				if( strlen( $args['name'] ) ) {
					
					// Get a semi-unique name ignoring symbols
					$args['flat_name'] = $this->flatten_song_title( $this->clean_song_title( $args['name'] ) );
					
					// If release date was provided, let's only look for songs that existed by that date
					$args['date_occurred'] = $args['date_occurred'] ?: '0000-00-00';
					
					// Find songs by same artist w/ same flattened name
					$sql_song = 'SELECT id FROM songs WHERE artist_id=? AND flat_name=? AND date_occurred>=? ORDER BY date_occurred DESC LIMIT 1';
					$stmt_song = $this->pdo->prepare($sql_song);
					$stmt_song->execute([ $args['artist_id'], $args['flat_name'], $args['date_occurred'] ]);
					$song_id = $stmt_song->fetchColumn();
					
					// If we found a song ID, then pass it back
					if( is_numeric($song_id) ) {
						
						return $song_id;
						
					}
					
					// If didn't find a song ID, let's try to add it
					else {
						
						$song_output = $this->update_song( $args );
						if( $song_output['status'] === 'success' && is_numeric($song_output['song_id']) ) {
							return $song_output['song_id'];
						}
						
					}
					
				}
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Create song from track info
	// ======================================================
	public function update_song( $input = [] ) {
		
		// Allowed columns in query
		$allowed_columns = [ 'name', 'romaji', 'friendly', 'flat_name', 'artist_id', 'date_occurred', 'notes' ];
		
		// Make sure we're given some info
		if( is_array($input) && !empty($input) ) {
			
			// Make sure we an artist ID
			if( is_numeric( $input['artist_id'] ) ) {
				
				// Make sure we have a name
				if( strlen( $input['name'] ) ) {
					
					// Save ID
					$id = is_numeric( $input['id'] ) ? $input['id'] : null;
					
					// Clean name and romaji
					$input['name'] = $this->remove_notes( $this->clean_song_title( $input['name'] ) );
					$input['romaji'] = $this->remove_notes( $this->clean_song_title( $input['romaji'] ) );
					$input['friendly'] = friendly( $input['friendly'] ?: ( $input['romaji'] ?: $input['name'] ) );
					
					// Get a semi-unique name ignoring symbols
					$input['flat_name'] = $this->flatten_song_title( $input['name'] );
					
					// If release date was provided, let's only look for songs that existed by that date
					$input['date_occurred'] = preg_match('/'.'\d{4}-\d{2}-\d{2}'.'/', $input['date_occurred']) ? $input['date_occurred'] : '0000-00-00';
					
					// Clean other stuff
					foreach([ 'name', 'romaji', 'notes' ] as $key) {
						$input[ $key ] = strlen( $input[$key] ) ? sanitize( $input[$key] ) : null;
					}
					
					// Remove all but allowed values
					foreach( $input as $key => $value ) {
						if( !in_array( $key, $allowed_columns ) ) {
							unset( $input[$key] );
						}
					}
					
					// Edit existing song
					if( is_numeric( $id ) ) {
						
						$sql_song = 'UPDATE songs SET '.substr( implode( '=?, ', $input ), 0, -2 ).' WHERE id=?';
						$values_song = array_merge( $input, [ $id ] );
						
					}
					
					// Add new song
					else {
						
						$sql_song = 'INSERT INTO songs ('.implode( ',', array_keys($input) ).') VALUES ('.substr( str_repeat( '?,', count($input) ), 0, -1 ).')';
						$values_song = $input;
						
					}
					
					// Run query
					$stmt_song = $this->pdo->prepare($sql_song);
					if( $stmt_song->execute( array_values( $values_song ) ) ) {
						
						$id = is_numeric($id) ? $id : $this->pdo->lastInsertID();
						$output['status'] = 'success';
						$output['song_id'] = $id;
						
					}
					else {
						$output['result'] = 'Couldn\'t update song.';
					}
					
				}
				else {
					$output['result'] = 'Please provide a name.';
				}
				
			}
			else {
				$output['result'] = 'An artist is required.';
			}
			
		}
		else {
			$output['result'] = 'No data provided.';
		}
		
		return $output;
		
	}
	
	
	
	// ======================================================
	// Core function
	// ======================================================
	public function access_song($args = []) {
		
		$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
		
		// SELECT ----------------------------------------------
		if( $args['get'] === 'all' ) {
			$sql_select[] = 'songs.*';
		}
		
		if( $args['get'] === 'name' ) {
			$sql_select[] = 'songs.id';
			$sql_select[] = 'songs.name';
			$sql_select[] = 'songs.romaji';
			$sql_select[] = 'songs.friendly';
		}
		
		if( $args['get'] === 'count' ) {
			$sql_select[] = 'COUNT(1) AS num_songs';
		}
		
		// FROM ------------------------------------------------
		
		// Default
		$sql_from = 'songs';
		
		// JOIN ------------------------------------------------
		
		// WHERE -----------------------------------------------
		
		// ID
		if( is_numeric( $args['id'] ) ) {
			$sql_where[] = 'songs.id=?';
			$sql_values[] = $args['id'];
		}
		
		// Artist
		if( is_numeric( $args['artist_id'] ) ) {
			$sql_where[] = 'songs.artist_id=?';
			$sql_values[] = $args['artist_id'];
		}
		
		// GROUP -----------------------------------------------
		
		// Count songs
		if( $args['get'] === 'count' ) {
			$sql_group[] = 'songs.id';
		}
		
		// ORDER -----------------------------------------------
		
		// Custom order
		if( $args['order'] ) {
			$sql_order = is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ];
		}
		
		// Default order
		else {
			$sql_order = [ 'songs.friendly ASC' ];
		}
		
		// BUILD QUERY -----------------------------------------
		$sql_songs = '
			SELECT '.implode(', ', $sql_select).'
			FROM '.$sql_from.' '.
			(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
			(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
			(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' 
			ORDER BY '.implode(', ', $sql_order).'
		';
		$stmt_songs = $this->pdo->prepare($sql_songs);
		
		// EXECUTE QUERY ----------------------------------------
		if($stmt_songs->execute($sql_values)) {
			
			// Get result
			$songs = $stmt_songs->fetchAll();
			$num_songs = count($songs);
			
			// Get additional data
			if( is_array($songs) && !empty($songs) ) {
				foreach( $songs as $song ) {
					
					// EXTRAS -------------------------------------------
					if( $args['get'] === 'all' ) {
						for($i=0; $i<$num_songs; $i++) {
							
							
							
							// Get parent
							if( is_numeric($songs[$i]['variation_of']) ) {
								$songs[$i]['varation_of'] = $this->access_song([ 'id' => $magazines[$i]['variation_of'], 'get' => 'basics' ]);
							}
							
							// Get cover
							if( is_numeric($songs[$i]['cover_of']) ) {
								$songs[$i]['coverof'] = $this->access_song([ 'id' => $magazines[$i]['cover_of'], 'get' => 'basics' ]);
							}
							
							// Get artist
							$magazines[$i]['artist'] = $this->access_artist->access_artist([ 'id' => $magazines[$i]['artist_id'], 'get' => 'name' ]);
							
						}
					}
					
				}
			}
			
		}
		
		// RETURN ----------------------------------------------
		
		// Make sure array is returned
		$songs = is_array($songs) ? $songs : [];
		
		// Return single column if limited to 1 result
		$songs = strlen($args['id']) || $args['limit'] == 1 ? reset($songs) : $songs;
		
		return $songs;
		
	}
	
	
	
	// =======================================================
	// Update magazine
	// =======================================================
	/*public function update_magazine( $magazine ) {
		
		// Whitelist of columns allowed in update
		$allowed_columns = [ 'id', 'name', 'romaji', 'friendly', 'volume_name_pattern', 'volume_romaji_pattern', 'num_volume_digits', 'parent_magazine_id', 'default_price', 'notes', 'type', 'size' ];
		
		if( is_array($magazine) && !empty($magazine) ) {
			
			// Clean normal vars
			foreach( $magazine as $key => $value ) {
				$value = is_array($value) ? $value : sanitize($value);
				$magazine[$key] = is_array($value) || strlen($value) ? $value : null;
			}
			
			// Clean numbers
			foreach( [ 'id', 'parent_magazine_id', 'type', 'size' ] as $key ) {
				$magazine[$key] = is_numeric($magazine[$key]) ? $magazine[$key] : null;
			}
			
			// Clean other vars
			$magazine['friendly'] = strlen($magazine['friendly']) ? $magazine['friendly'] : friendly( $magazine['romaji'] ?: $magazine['name'] );
			$magazine['num_volume_digits'] = $magazine['num_volume_digits'] ?: 0;
			
			// Create full volume patterns, then unset before/after parts
			$magazine['volume_name_pattern']   = sanitize($magazine['before_number']) . '{volume}' . sanitize($magazine['after_number']);
			$magazine['volume_romaji_pattern'] = $magazine['before_number_romaji'] || $magazine['after_number_romaji'] ? sanitize($magazine['before_number_romaji'] ?: $magazine['before_number']).'{volume}'.sanitize($magazine['after_number_romaji'] ?: $magazine['after_number']) : null;
			
			// Labels will need to be added in a separate pass; save for later
			$magazine_labels = $magazine['labels'];
			
			// Set flag
			$is_edit = is_numeric($magazine['id']);
			
			// Remove disallowed columns before setting up keys/values
			$magazine = array_filter( $magazine, function ($column) use ($allowed_columns) { return in_array($column, $allowed_columns); }, ARRAY_FILTER_USE_KEY );
			
			// Set up keys and values
			$keys_update = array_keys($magazine);
			$values_update = array_values($magazine);
			
			// Unset ID from array of values that will be inserted
			$index_of_id_in_array = array_search('id', $keys_update);
			unset( $keys_update[$index_of_id_in_array], $values_update[$index_of_id_in_array] );
			$keys_update = array_values($keys_update);
			$values_update = array_values($values_update);
			
			// Make sure name is specified
			if( strlen($magazine['name']) ) {
				
				// If updating existing
				if( $is_edit ) {
					
					// Add ID to values
					$values_update[] = $magazine['id'];
					
					// Set query
					$sql_update = 'UPDATE magazines SET '.implode('=?,', $keys_update).'=? WHERE id=? LIMIT 1';
					
				}
				
				// If adding new
				else {
					
					// Make sure name (friendly name) is unique
					if( !$this->friendly_is_taken($magazine['friendly']) ) {
						
						// Set query
						$sql_update = 'INSERT INTO magazines ('.implode( ',', $keys_update ).') VALUES ('.substr( str_repeat( '?,', count($keys_update) ), 0, -1 ).')';
						
					}
					else {
						$output['result'] = 'The friendly name <span class="any__note">'.$magazine['friendly'].'</span> is already taken.';
					}
					
				}
				
				// If nothing went wrong while generating the query, run it
				if( $sql_update ) {
					
					$stmt_update = $this->pdo->prepare($sql_update);
					if( $stmt_update->execute( $values_update ) ) {
						
						// Set output
						$output['status'] = 'success';
						$output['result'] = ( $is_edit ? 'Updated' : 'Added' ).' <a class="symbol__magazine" href="/magazines/'.$magazine['friendly'].'/">'.( $magazine['romaji'] ? lang($magazine['romaji'], $magazine['name'], 'hidden') : $magazine['name'] ).'</a>.';
						
						// Update labels
						$this->update_magazine_labels( $magazine['id'], $magazine_labels );
						
					}
					else {
						$output['result'] = 'Couldn\'t update &ldquo;'.( $magazine['romaji'] ?: $magazine['name'] ).'&rdquo;';
					}
					
				}
				else {
					$output['result'] = $output['result'] ?: 'Couldn\'t generate magazine query.';
				}
				
			}
			else {
				if( $is_edit ) {
					$output['result'] = 'The magazine needs a name.';
				}
			}
			
		}
		else {
			$output['result'] = 'Data empty.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
	
	
	// =======================================================
	// Delete magazine
	// =======================================================
	public function delete_magazine( $magazine_id ) {
		
		if( $_SESSION['can_delete_data'] ) {
			
			if( is_numeric( $magazine_id ) ) {
				
				$sql_delete = 'DELETE FROM magazines WHERE id=? LIMIT 1';
				$stmt_delete = $this->pdo->prepare($sql_delete);
				
				if( $stmt_delete->execute([ $magazine_id ]) ) {
					$output['status'] = 'success';
					$output['result'] = 'Magazine deleted.';
				}
				else {
					$output['result'] = 'Couldn\'t delete magazine.';
				}
				
			}
			else {
				$output['result'] = 'That magazine doesn\'t exist.';
			}
			
		}
		else {
			$output['result'] = 'Sorry, you don\'t have permission to delete magazines.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}*/
	
	
	
	// =======================================================
	// Standardize song title
	// =======================================================
	public function clean_song_title( $input ) {
		
		// See clean_song_title in render_json for stuff I removed but am pretty sure is taken care of in sanitize
		
		// See also clean_values in /releases/; some parts may or may not be needed
		
		// Normal sanitize
		$input = sanitize($input);
		
		// Pretty quotes
		$input = preg_replace("/"."(.*?)\"(.+?)\"(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
		$input = preg_replace("/"."(.*?)&#34;(.+?)&#34;(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
		
		// Remove non-escaped notes
		$input = preg_replace("/"."(\(.+\))"."/", "", $input);
		
		// Trim spaces again
		$input = preg_replace("/"."^ (.*)"."/", "$1", $input);
		$input = preg_replace("/"."(.*?) $"."/", "$1", $input);
		$input = preg_replace("/"."(.*?) \\$"."/", "$1", $input);
		
		return $input;
		
	}
	
	
	
	// =======================================================
	// Flatten song title
	// =======================================================
	public function flatten_song_title( $input ) {
		
		// Might have to undo sanitize?
		$input = html_entity_decode( $input, ENT_QUOTES, 'UTF-8' );
		
		// Make all letters lowercase to make comparisons easier
		$input = strtolower( $input );
		
		// Replace characters except alphanumeric/kanji/kana
		$input = preg_replace('/'.'[^a-z0-9\p{Han}\p{Katakana}\p{Hiragana}]'.'/ui', '', $input);
		
		// Now sanitize (will also standardize remaining characters to proper widths)
		$input = sanitize($input);
		
		return $input;
		
	}
	
	
	
	// ======================================================
	// Remove notes from track
	// ======================================================
	function remove_notes( $track_name ) {
		
		$note_pattern = '(?<!\\\)\((.+?)(?<!\\\)\)';
		
		$track_name = preg_replace('/'.$note_pattern.'/', '', $track_name);
		
		$track_name = preg_replace('/'.'\s+'.'/', ' ', $track_name);
		
		$track_name = trim( $track_name );
		
		return $track_name;
		
	}
	
	
	
	// ======================================================
	// Identify notes in track and optionally remove
	// ======================================================
	// Let's take another crack at this sometime and replace the one in releases class
	/*function get_notes_from_track( $input = [] ) {
		
		if( is_array($input) && !empty($input) ) {
			
			if( strlen( $input['name'] ) ) {
				
				// Looks for parentheses not preceded by backslash
				$note_pattern = '(?<!\\)\((.+?)(?<!\\)\)';
				
				// Get notes
				preg_match_all('/'.$note_pattern.'/', $input['name'], $notes, PREG_OFFSET_CAPTURE);
				preg_match_all('/'.$note_pattern.'/', $input['romaji'], $romaji_notes, PREG_OFFSET_CAPTURE);
				
				// Japanese notes
				foreach($notes[0] as $note_key => $note) {
					
					// Save notes to output--assumes that notes are same in Japanese and romaji
					$output['notes'][$note_key] = [
						'name'          => $notes[1][$note_key][0],
						'name_offset'   => $notes[0][$note_key][1],
						'name_length'   => strlen($notes[0][$note_key][0]),
						//'romaji'        => $romaji_notes[1][$note_key][0],
						//'romaji_offset' => $romaji_notes[0][$note_key][1],
						//'romaji_length' => strlen($romaji_notes[0][$note_key][0]),
					];
					
					// Optionally remove note from song title
					$input['name'] = substr_replace( $input['name'], '', $notes[0][$note_key][1], strlen($notes[0][$note_key][0]) );
					
				}
				
				// Romaji notes
				foreach($romaji_notes[0] as $note_key => $note) {
					
					// Save notes to output--assumes that notes are same in Japanese and romaji
					$output['notes'][$note_key] = [
						'name'          => $notes[1][$note_key][0],
						'name_offset'   => $notes[0][$note_key][1],
						'name_length'   => strlen($notes[0][$note_key][0]),
						//'romaji'        => $romaji_notes[1][$note_key][0],
						//'romaji_offset' => $romaji_notes[0][$note_key][1],
						//'romaji_length' => strlen($romaji_notes[0][$note_key][0]),
					];
					
					// Optionally remove note from song title
					$input['name'] = substr_replace( $input['name'], '', $notes[0][$note_key][1], strlen($notes[0][$note_key][0]) );
					
				}
				
				
				
				
			}
			
		}
		
		$output = is_array($output) ? $output : [];
		$output = array_merge( $input, $output );
		return $output;
		
	}*/
	
	
	
	// =======================================================
	// Correct “translations” of Japanese symbols
	// =======================================================
	public function match_japanese( $japanese, $romaji ) {
		
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
			
			// Unset if unnecessary
			if($romaji === $japanese) {
				$romaji = null;
			}
			
		}
		
		return $romaji;
		
	}
	
}