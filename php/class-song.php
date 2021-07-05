<?php

include_once('../php/include.php');

class song {
	
	public static $song_types = [
		'original song',
		'original SE',
		'variant',
		'cover',
	];
	
	public static $variant_types = [
		'rerecording',
		'remastering',
		'remix',
		'recomposition',
		'instrumental',
		'other',
	];
	
	
	
	// =======================================================
	// Connect
	// =======================================================
	function __construct($pdo) {
		
		// Set up connection
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		
		$this->pdo       = $pdo;
		$this->sanitizer = new sanitizer();
		
	}
	
	
	
	// ======================================================
	// Change incorrect “songs” to comments
	// ======================================================
	public function change_track_to_comment( $song_id ) {
		
		if( is_numeric($song_id) ) {
			
			// Get all tracks linked to this song
			$sql_tracks = 'SELECT id, name, romaji FROM releases_tracklists WHERE song_id=?';
			$stmt_tracks = $this->pdo->prepare($sql_tracks);
			$stmt_tracks->execute([ $song_id ]);
			$tracks = $stmt_tracks->fetchAll();
			
			// Prepare to update tracks, but we'll actually do it in the next loop
			$sql_track = 'UPDATE releases_tracklists SET name=?, romaji=?, song_id=? WHERE id=? LIMIT 1';
			$stmt_track = $this->pdo->prepare($sql_track);
			
			// Loop through each track and see what we need to do
			if( is_array($tracks) && !empty($tracks) ) {
				foreach( $tracks as $track ) {
					
					// If name/romaji aren't encased in parentheses, change that
					foreach([ 'name', 'romaji' ] as $key) {
						
						// Add to beginning
						if( strlen( $track[$key] ) && substr( $track[$key], 0, 1 ) != '(' ) {
							$track[$key] = '('.$track[$key];
						}
						
						// Add to end
						if( strlen( $track[$key] ) && substr( $track[$key], -1 ) != ')' ) {
							$track[$key] .= ')';
						}
						
						$stmt_track->execute([ $track['name'], $track['romaji'], null, $track['id'] ]);
						
					}
					
				}
			}
			
			// Now remove the song from the DB
			$sql_song = 'DELETE FROM songs WHERE id=? LIMIT 1';
			$stmt_song = $this->pdo->prepare($sql_song);
			$stmt_song->execute([ $song_id ]);
			
		}
		
	}
	
	
	
	// ======================================================
	// Change track names
	// ======================================================
	public function change_track_name( $song_id, $name, $romaji=null ) {
		
		if( is_numeric($song_id) && strlen($name) && strlen($romaji) ) {
			
			$name = sanitize($name);
			$romaji = sanitize($romaji) ?: null;
			
			// Prepare to update tracks, but we'll actually do it in the next loop
			$sql_tracks = 'UPDATE releases_tracklists SET name=?, romaji=? WHERE song_id=? LIMIT 1';
			$stmt_tracks = $this->pdo->prepare($sql_tracks);
			$stmt_tracks->execute([ $name, $romaji, $song_id ]);
			
		}
		
	}
	
	
	
	// ======================================================
	// Guess song
	// ======================================================
	public function guess_song( $args=[] ) {
		
		// Set up defaults
		$name = $args['name'];
		$romaji = sanitize($args['romaji']);
		$flat = $this->flatten_song_title( $name );
		$artist_id = $args['artist_id'];
		$search_type = in_array( $args['type'], [ 'fuzzy', 'stric' ] ) ? $args['type'] : 'strict';
		$date_occurred = $args['date_occurred'];
		
		// Make sure we have name and artist info
		if( mb_strlen($name) && is_numeric($artist_id) ) {
			
			// If we're doing a strict search, then abandon search if name has comments at start or middle
			// e.g. Let's not match '(comment)', '(MC) → Orion once again', or 'Orion (→) Pains of aspiration'
			if( $search_type != 'fuzzy' ) {
				if( mb_substr( $name, 0, 1 ) === '(' || preg_match( '/'.'[^\\\]\)[^\(]'.'/u', $name ) ) {
					unset( $name );
				}
			}
			
			// If we still have a name (passted strictness test or got results from fuzzy search), move ahead
			if( strlen($name) ) {
				
				// For fuzzy search, get all artist's songs, then loop through them and pick one
				if( $search_type === 'fuzzy' ) {
					
					// Get matches
					$matches = $this->access_song([ 'artist_id' => $artist_id, 'get' => 'basics' ]);
					
					// Order matches by length of flat name (more of flat name matches = more likely to be right)
					usort($matches, function($a, $b) {
						return mb_strlen( html_entity_decode( html_entity_decode( $b['flat'], ENT_QUOTES, 'UTF-8' ) ) ) - mb_strlen( html_entity_decode( html_entity_decode( $a['flat'], ENT_QUOTES, 'UTF-8' ) ) );
					});
					
					// Then loop through each song and assume the first one fully within the reference name is the correct one
					foreach($matches as $match) {
						if( strpos( $flat, $match['flat'] ) !== false ) {
							$song = $match;
							break;
						}
					}
					
				}
				
				// For strict search, both artist and name must be right
				else {
					
					// Get matches
					$matches = $this->access_song([ 'artist_id' => $artist_id, 'flat' => $flat, 'get' => 'basics' ]);
					
					// This is so that we don't return empty handed if a song exists but has a date after the reference date
					// (because a song could be generated from a newer release and then added to an older release later)
					$backup_match = reset( $matches );
					
					// Optionally, if we have multiple songs and a date to cross-reference, knock out any songs after that date
					if( strlen($date_occurred) && count($matches) > 1 ) {
						
						// Filter out songs released after the reference date
						$matches = array_filter( $matches, function($x) use($date_occurred) { return $x['date_occurred'] <= $date_occurred; } );
						
					}
					
					// Return first remaining match (or the backup one if necessary)
					$song = reset( $matches ) ?: $backup_match;
					
				}
				
				// Unset somet things just in case
				unset( $matches, $backup_match );
				
				// If we're doing strict search, we may create or update song based on results
				if( $search_type === 'strict' ) {
					
					// If we found a song via strict search, let's check if we need to update the song based on the passed-in data
					if( is_array( $song ) && !empty( $song ) ) {
						
						// If passed-in date is before song's date, let's update the song
						if( strlen($date_occurred) && $date_occurred > '0000-00-00' && ( !strlen($song['date_occurred']) || $song['date_occurred'] === '0000-00-00' || $date_occurred < $song['date_occurred'] ) ) {
							$args['id'] = $song['id'];
							$song_output = $this->update_song( $args );
						}
						
						// If passed-in date no good, but the passed-in romaji is different, let's update the romaji
						elseif( strlen($romaji) && $romaji != $song['romaji'] ) {
							
							// Unset passed-in date since we know it's bad
							unset($args['date_occurred']);
							$args['id'] = $song['id'];
							$song_output = $this->update_song( $args );
							
						}
						
						// Return the song
						return $song;
						
					}
					
					// If we didn't find a song via strict search, create it
					else {
						
						// Try to create song
						$song_output = $this->update_song( $args );
						if( $song_output['status'] === 'success' && is_numeric($song_output['id']) ) {
							
							// Return array with song ID
							return [ 'id' => $song_output['id'] ];
							
						}
						
					}
					
				}
				
				// If we got a song from fuzzy search, return that
				elseif( is_array($song) && !empty($song) ) {
					
					return $song;
					
				}
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Merge dupe/misspelling into correct song
	// ======================================================
	public function merge_song( $dupe_id, $original_id ) {
		
		if( is_numeric( $dupe_id ) && is_numeric( $original_id ) ) {
			
			// Update any variants, covers, tracks, or videos connected to the dupe
			foreach([
				'UPDATE songs SET variant_of=? WHERE variant_of=?',
				'UPDATE songs SET cover_of=? WHERE cover_of=?',
				'UPDATE releases_tracklists SET song_id=? WHERE song_id=?',
				'UPDATE videos SET song_id=? WHERE song_id=?'
			] as $sql_update) {
				$stmt_update = $this->pdo->prepare($sql_update);
				$stmt_update->execute([ $original_id, $dupe_id ]);
			}
			
			// Then remove dupe
			$this->delete_song( $dupe_id );
			
		}
		
	}
	
	
	
	// ======================================================
	// Find other potential matches in tracklists and link
	// ======================================================
	public function link_videos_to_song( $song_id, $artist_id, $name ) {
		
		// Make sure song length is at least n characters, otherwise it'll get messy
		$min_length = 2;
		
		// Make sure we have everything and length is alright
		if( is_numeric($song_id) && is_numeric($artist_id) && strlen($name) && mb_strlen( html_entity_decode( $name, ENT_QUOTES, 'UTF-8' ) ) >= $min_length ) {
			
			$flat = $this->flatten_song_title( $name );
			
			// Get all artist's videos that don't have a song yet
			$sql_videos = 'SELECT id, name FROM videos WHERE artist_id=? AND song_id IS NULL AND is_custom=?';
			$stmt_videos = $this->pdo->prepare($sql_videos);
			$stmt_videos->execute([ $artist_id, 0 ]);
			$videos = $stmt_videos->fetchAll();
			
			// Loop through videos and get flat name
			if( is_array($videos) && !empty($videos) ) {
				foreach( $videos as $video ) {
					
					// If flat name of input song is within the flat name of the video, assume the video is of this song
					$video_flat = $this->flatten_song_title( $video['name'] );
					if( strpos( $video_flat, $flat ) !== false ) {
						
						$matched_ids[] = $video['id'];
						
					}
					
				}
			}
			
			// If we had any matched ids, update them to this song
			if( is_array($matched_ids) && !empty($matched_ids) ) {
				
				$values_update = array_merge( [ $song_id, null ], $matched_ids );
				$sql_update = 'UPDATE videos SET song_id=?, name=? WHERE id IN('.substr( str_repeat( '?,', count($matched_ids) ), 0, -1 ).')';
				$stmt_update = $this->pdo->prepare($sql_update);
				$stmt_update->execute($values_update);
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Find other potential matches in tracklists and link
	// ======================================================
	public function link_tracks_to_song( $song_id, $artist_id, $name, $date_occurred = null ) {
		
		// Need to sort this out
		$access_release = new access_release($this->pdo);
		
		// Make sure song ID is provided at least
		if( is_numeric($song_id) && is_numeric($artist_id) && strlen($name) ) {
			
			$flat = $this->flatten_song_title( $name );
			
			// Find all tracks by same artist that aren't already connected to a song
			$sql_tracks = '
				SELECT
					tracks.id,
					tracks.name,
					tracks.romaji,
					tracks.notes_name,
					tracks.notes_romaji,
					releases.date_occurred
				FROM
					(
						SELECT
							releases_tracklists.id,
							releases_tracklists.release_id,
							releases_tracklists.name,
							releases_tracklists.romaji,
							releases_tracklists.notes_name,
							releases_tracklists.notes_romaji
						FROM
							releases_tracklists
						WHERE
							releases_tracklists.artist_id=?
							AND
							releases_tracklists.song_id IS NULL
					) tracks
				LEFT JOIN
					releases ON releases.id=tracks.release_id AND releases.date_occurred>?
			';
			$stmt_tracks = $this->pdo->prepare($sql_tracks);
			$stmt_tracks->execute([ $artist_id, '0000-00-00' ]);
			$tracks = $stmt_tracks->fetchAll();
			
			// If we found tracks from artist, loop through them and find matches
			if( is_array($tracks) && !empty($tracks) ) {
				foreach( $tracks as $track ) {
					
					$track['flat'] = $this->flatten_song_title( $track['name'] );
					
					// If track matches name of input, assume it's same song
					if( $track['name'] == $name || $track['flat'] == $flat ) {
						
						// Add to list of matched tracks
						$matched_tracks[] = $track;
						
						// Save lowest date_occurred--we'll assume that's when the song first appeared
						if( $track['date_occurred'] && $track['date_occurred'] > '0000-00-00' ) {
							if( !$date_occurred || $date_occurred == '0000-00-00' || $track['date_occurred'] < $date_occurred ) {
								$date_occurred = $track['date_occurred'];
							}
						}
						
					}
					
				}
			}
			
			// If we found any matches, let's connect them
			if( is_array($matched_tracks) && !empty($matched_tracks) ) {
				
				$sql_link = 'UPDATE releases_tracklists SET song_id=?, name=?, romaji=?, notes_name=?, notes_romaji=? WHERE id=?';
				$stmt_link = $this->pdo->prepare($sql_link);
				
				// Loop through matched tracks and update song ID, names, and notes
				foreach( $matched_tracks as $matched_track ) {
					
					// Find notes in track, remove them from track name, move them to notes_name
					$matched_track = array_merge( $matched_track, $access_release->split_notes_from_track($matched_track) );
					
					// Perform the query
					$values_link = [
						$song_id,
						$matched_track['name'],
						$matched_track['romaji'],
						$matched_track['notes_name'],
						$matched_track['notes_romaji'],
						$matched_track['id'],
					];
					$stmt_link->execute( $values_link );
					
				}
				
			}
			
			// If we have a new release date, update it
			if( strlen($date_occurred) ) {
				
				$sql_song = 'UPDATE songs SET date_occurred=? WHERE id=? AND (date_occurred IS NULL OR date_occurred=? OR date_occurred>?) LIMIT 1';
				$stmt_song = $this->pdo->prepare($sql_song);
				$stmt_song->execute([ $date_occurred, $song_id, '0000-00-00', $date_occurred ]);
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Update song
	// ======================================================
	public function update_song( $input = [] ) {
		
		// Allowed columns in query
		$allowed_columns = [
			'artist_id',
			'name',
			'romaji',
			'friendly',
			'flat',
			'hint',
			'type',
			'variant_of',
			'variant_type',
			'cover_of',
			'notes',
			'length',
			'date_occurred',
		];
		
		$allowed_flags = [
			'problem',
			'correct_song_id',
			'convert_tracks_to_notes',
			'convert_tracks_name',
			'original_name'
		];
		
		// Make sure we're given some info
		if( is_array($input) && !empty($input) ) {
			
			// Make sure we an artist ID
			if( is_numeric( $input['artist_id'] ) ) {
				
				// Make sure we have a name
				if( strlen( $input['name'] ) ) {
					
					// Save ID
					$id = is_numeric( $input['id'] ) ? $input['id'] : null;
					$is_edit = is_numeric( $id );
					
					// Clean type
					$input['type'] = is_numeric( $input['type'] ) && in_array( $input['type'], array_keys(self::$song_types) ) ? $input['type'] : 0;
					
					// Convert length to seconds
					if( preg_match('/'.'\d+\:\d+'.'/', $input['length']) ) {
						$input['length'] = explode(':', $input['length']);
						$input['length'] = ( $input['length'][0] * 60 ) + $input['length'][1];
					}
					$input['length'] = is_numeric( $input['length'] ) ? $input['length'] : null;
					
					// For covers and variants, song type must be correct, and cover/variant id must point to a valid song (not itself)
					$input['cover_of']     = $input['type'] == array_search('cover', self::$song_types)   && is_numeric( $input['cover_of'] )   && $input['cover_of']   != $input['id'] ? $input['cover_of']   : null;
					$input['variant_of']   = $input['type'] == array_search('variant', self::$song_types) && is_numeric( $input['variant_of'] ) && $input['variant_of'] != $input['id'] ? $input['variant_of'] : null;
					
					// For variant type, variant id must be valid, and type must be allowed
					$input['variant_type'] = is_numeric( $input['variant_of'] ) && is_numeric( $input['variant_type'] ) && in_array( $input['variant_type'], array_keys(self::$variant_types) ) ? $input['variant_type'] : null;
					
					// Now we need to loop back and unset the song type if variant or cover were messed up
					if(
						( $input['type'] == array_search('variant', self::$song_types) && !is_numeric( $input['variant_of'] ) )
						||
						( $input['type'] == array_search('cover', self::$song_types) && !is_numeric( $input['cover_of'] ) )
					) {
						$input['type'] = array_search('original song', self::$song_types);
					}
					
					// For name and romaji, clean it up, remove any notes--let's not unescape parentheses b/c even though they're part of title it seems better to unescape at runtime rather than have to re-escape before running queries
					foreach([ 'name', 'romaji' ] as $key) {
						$input[ $key ] = $this->clean_song_title( $input[ $key ] );
						$input[ $key ] = $this->remove_notes( $input[ $key ] );
					}
					
					// Get a semi-unique name ignoring symbols
					$input['flat'] = strlen( $input['flat'] ) ? $input['flat'] : $this->flatten_song_title( $input['name'] );
					$input['flat'] = strlen( $input['flat'] ) ? $input['flat'] : '-';
					
					// Set friendly
					$input['friendly'] = friendly( $input['friendly'] ?: ( $input['romaji'] ?: $input['name'] ) );
					
					// Clean date (will also be used to look for untagged tracks)
					$input['date_occurred']  = preg_match('/'.'\d{4}(?:-\d{2}){0,2}'.'/', $input['date_occurred']) ? $input['date_occurred'] : '0000-00-00';
					$input['date_occurred'] .= str_repeat( '-00', ( 10 - strlen( $input['date_occurred'] ) ) / 3 );
					
					// Make romaji symbols match original
					$input['romaji'] = $this->sanitizer->match_romaji_to_japanese( $input['name'], $input['romaji'] );
					
					// Finally sanitize/set null a few things
					foreach([ 'name', 'romaji', 'notes', 'flat', 'hint' ] as $key) {
						$input[ $key ] = sanitize( $input[$key] );
					}
					
					// Remove unallowed values and build flags if necessary
					foreach( $input as $key => $value ) {
						
						// Remove disallowed
						if( !in_array( $key, $allowed_columns ) ) {
							
							// Set flags
							if( in_array( $key, $allowed_flags ) ) {
								$flags[ $key ] = $value;
							}
							
							unset( $input[$key] );
							
						}
						
					}
					
					// We must have a flat name or something's gone wrong
					if( strlen( $input['flat'] ) ) {
						
						// Query to edit existing song
						if( is_numeric( $id ) ) {
							
							$sql_song = 'UPDATE songs SET '.implode( '=?, ', array_keys($input) ).'=? WHERE id=?';
							$values_song = array_merge( $input, [ $id ] );
							
						}
						
						// Query to add new song
						else {
							
							$sql_song = 'INSERT INTO songs ('.implode( ',', array_keys($input) ).') VALUES ('.substr( str_repeat( '?,', count($input) ), 0, -1 ).')';
							$values_song = $input;
							
						}
						
						// Run query
						$stmt_song = $this->pdo->prepare($sql_song);
						if( $stmt_song->execute( array_values( $values_song ) ) ) {
							
							$id = is_numeric( $id ) ? $id : $this->pdo->lastInsertID();
							$output['status'] = 'success';
							$output['id'] = $id;
							
							// Additional stuff (if new song)
							if( !$is_edit ) {
								
								// If we added new song, give more specific response
								$output['result'] = 'Successfully added <a class="symbol__song" href="/songs/artist/'.$id.'/'.$input['friendly'].'/">'.($input['romaji'] ?: $input['name']).($input['hint'] ? '<span class="any__note">'.$input['hint'].'</span>' : null).'</a>.';
								
								// Find tracks of same song that aren't linked yet
								// Also guess the first performance date of the song at this time
								$this->link_tracks_to_song( $id, $input['artist_id'], $input['name'] );
								
								// Find videos of same song that aren't linked yet
								$this->link_videos_to_song( $id, $input['artist_id'], $input['name'] );
								
							}
							
							// Additional stuff (if editing song)
							if( $is_edit && $_SESSION['can_approve_data'] ) {
								
								// Merge duplicate
								if( $flags['problem'] == 1 && is_numeric( $flags['correct_song_id'] ) ) {
									
									$this->merge_song( $id, $flags['correct_song_id'] );
									$output['result'] = 'Song successfully merged.';
									
								}
								
								// Delete song and update tracks
								if( $flags['problem'] == 2 ) {
									
									// Optionally change track name
									if( strlen($flags['convert_tracks_name']) ) {
										$this->change_track_name( $id, $flags['convert_tracks_name'], $flags['convert_tracks_romaji'] );
									}
									
									// Optionally change tracks to comments
									if( $flags['convert_tracks_to_notes'] ) {
										$this->change_track_to_comment( $id );
									}
									
									// Delete song
									$this->delete_song( $id );
									
									// Change output
									$output['result'] = 'Song successfully deleted.';
									
								}
								
							}
							
							// As long as there was no problem, update any releases by the artist that have the exact
							// same name as the song we edited (or actually, the same as the original song)
							// Basically here in case we update song's romaji and want ot make sure single titles match
							if( !$flags['problem'] ) {
								$access_release = new access_release($this->pdo);
								$access_release->update_releases_romaji_to_match_song( $flags['original_name'], $input['name'], $input['romaji'], $input['artist_id'] );
							}
							
						}
						else {
							$output['result'] = 'Couldn\'t update song.';
						}
						
					}
					else {
						$output['result'] = 'Something went wrong&mdash;flat name cannot be empty.';
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
	
	
	
	// =======================================================
	// Delete song
	// =======================================================
	public function delete_song( $song_id ) {
		
		if( $_SESSION['can_approve_data'] ) {
			
			if( is_numeric( $song_id ) ) {
				
				$sql_delete = 'DELETE FROM songs WHERE id=? LIMIT 1';
				$stmt_delete = $this->pdo->prepare($sql_delete);
				
				if( $stmt_delete->execute([ $song_id ]) ) {
					$output['status'] = 'success';
					$output['result'] = 'Song deleted.';
				}
				else {
					$output['result'] = 'Couldn\'t delete song.';
				}
				
			}
			else {
				$output['result'] = 'That song doesn\'t exist.';
			}
			
		}
		else {
			$output['result'] = 'Sorry, you don\'t have permission to delete songs.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
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
		
		if( $args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'name' ) {
			$sql_select[] = 'songs.hint';
			$sql_select[] = 'CONCAT_WS("/", "", "songs", artists.friendly, songs.id, songs.friendly, "" ) AS url';
		}
		
		if( $args['get'] === 'basics' ) {
			$sql_select[] = 'songs.variant_of';
			$sql_select[] = 'songs.cover_of';
			$sql_select[] = 'SUBSTRING(songs.date_occurred,1,4) AS date_occurred';
		}
		
		if( $args['get'] === 'basics' || $args['get'] === 'name' ) {
			$sql_select[] = 'songs.id';
			$sql_select[] = 'songs.artist_id';
			$sql_select[] = 'songs.name';
			$sql_select[] = 'songs.romaji';
			$sql_select[] = 'songs.friendly';
			$sql_select[] = 'songs.flat';
		}
		
		if( $args['show_hints'] ) {
			$sql_select[] = 'songs.hint';
		}
		
		if( $args['get'] === 'count' ) {
			$sql_select[] = 'COUNT(1) AS num_songs';
		}
		
		// FROM ------------------------------------------------
		
		// Default
		$sql_from = 'songs';
		
		// JOIN ------------------------------------------------
		
		// Get artist in most cases
		$sql_join[] = 'LEFT JOIN artists ON artists.id=songs.artist_id';
		
		// WHERE -----------------------------------------------
		
		// ID
		if( is_numeric( $args['id'] ) ) {
			$sql_where[] = 'songs.id=?';
			$sql_values[] = $args['id'];
		}
		
		// Variant of
		if( is_numeric( $args['variant_of'] ) ) {
			$sql_where[] = 'songs.variant_of=?';
			$sql_values[] = $args['variant_of'];
		}
		
		// Cover of
		if( is_numeric( $args['cover_of'] ) ) {
			$sql_where[] = 'songs.cover_of=?';
			$sql_values[] = $args['cover_of'];
		}
		
		// Artist
		if( is_numeric( $args['artist_id'] ) ) {
			$sql_where[] = 'songs.artist_id=?';
			$sql_values[] = $args['artist_id'];
		}
		
		// Flat
		if( strlen( $args['flat'] ) ) {
			$sql_where[] = 'songs.flat=?';
			$sql_values[] = $args['flat'];
		}
		
		// Friendly
		if( strlen( $args['friendly'] ) ) {
			$sql_where[] = 'songs.friendly=?';
			$sql_values[] = $args['friendly'];
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
			$num_songs = is_array($songs) ? count($songs) : 0;
			
			// EXTRAS -------------------------------------------
			if( $args['get'] === 'all' ) {
				
				// Set up getters--need to redo all classes eventually to make this better (lol)
				$this->access_release = new access_release($this->pdo);
				$this->access_video = new access_video($this->pdo);
				
				for($i=0; $i<$num_songs; $i++) {
					
					// Get parent (variant)
					if( is_numeric($songs[$i]['variant_of']) ) {
						$songs[$i]['original'] = $this->access_song([ 'id' => $songs[$i]['variant_of'], 'get' => 'basics' ]);
					}
					
					// Get parent (cover)
					if( is_numeric($songs[$i]['cover_of']) ) {
						$songs[$i]['original'] = $this->access_song([ 'id' => $songs[$i]['cover_of'], 'get' => 'basics' ]);
					}
					
					// Get variants and covers
					$songs[$i]['variants'] = $this->access_song([ 'variant_of' => $songs[$i]['id'], 'get' => 'basics' ]);
					$songs[$i]['covers'] = $this->access_song([ 'cover_of' => $songs[$i]['id'], 'get' => 'basics' ]);
					
					// Get releases
					$songs[$i]['releases'] = $this->access_release->access_release([ 'song_id' => $songs[$i]['id'], 'get' => 'name', 'associative' => true ]);
					
					// Get video
					$songs[$i]['video'] = $this->access_video->access_video([ 'song_id' => $songs[$i]['id'], 'get' => 'basics', 'limit' => 1 ]);
					
				}
				
			}
			
			// EXTRAS -------------------------------------------
			if( $args['get'] === 'basics' || $args['get'] === 'all' ) {
				
				// Set up getters--need to redo all classes eventually to make this better (lol)
				$this->access_artist = new access_artist($this->pdo);
				
				for($i=0; $i<$num_songs; $i++) {
					
					// Get artist
					$songs[$i]['artist'] = $this->access_artist->access_artist([ 'id' => $songs[$i]['artist_id'], 'get' => 'name' ]);
					
					// Transform length into string
					$songs[$i]['length'] = is_numeric( $songs[$i]['length'] ) ? gmdate('i:s', $songs[$i]['length']) : null;
					
				}
				
			}
			
			// EXTRAS -------------------------------------------
			if( $args['associative'] ) {
				
				for($i=0; $i<$num_songs; $i++) {
					$tmp_songs[ $songs[$i]['id'] ] = $songs[$i];
				}
				
				$songs = $tmp_songs;
				unset($tmp_songs);
				
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
	// Standardize song title
	// =======================================================
	public function clean_song_title( $input ) {
		
		// See clean_song_title in render_json for stuff I removed but am pretty sure is taken care of in sanitize
		// See also clean_values in /releases/; some parts may or may not be needed
		
		// Undo any previous sanitize
		$input = html_entity_decode( $input, ENT_QUOTES, 'UTF-8' );
		
		// Remove non-escaped notes
		$input = $this->remove_notes($input);
		
		// Normal sanitize
		$input = sanitize($input);
		
		// Pretty quotes
		$input = preg_replace("/"."(.*?)\"(.+?)\"(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
		$input = preg_replace("/"."(.*?)&#34;(.+?)&#34;(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
		
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
		
		// Make sure notes are removed
		$input = $this->remove_notes( $input );
		
		// Replace characters except alphanumeric/kanji/kana
		$input = preg_replace('/'.'[^a-z0-9\p{Han}\p{Katakana}\p{Hiragana}\p{Hangul}]'.'/ui', '', $input);
		
		// Now sanitize (will also standardize remaining characters to proper widths)
		$input = sanitize($input);
		
		return $input;
		
	}
	
	
	
	// ======================================================
	// Remove notes from track
	// ======================================================
	public function remove_notes( $track_name ) {
		
		// Make sure we're not working with entities
		$track_name = html_entity_decode( $track_name, ENT_QUOTES, 'UTF-8' );
		
		$note_pattern = '(?<!\\\)\((.+?)(?<!\\\)\)';
		
		$track_name = preg_replace('/'.$note_pattern.'/', '', $track_name);
		
		$track_name = preg_replace('/'.'\s+'.'/', ' ', $track_name);
		
		$track_name = trim( $track_name );
		
		return $track_name;
		
	}
	
	
	
	// ======================================================
	// Unescape parentheses that are part of song title
	// ======================================================
	public function unescape_parentheses( $name ) {
		
		// Make sure we're not working with entities
		$name = html_entity_decode( $name, ENT_QUOTES, 'UTF-8' );
		
		$name = str_replace( [ '\\(', '\\)' ], [ '(', ')' ], $name );
		
		return $name;
		
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
	
}