<?php

include_once('../php/include.php');
include_once('../php/class-parse_markdown.php');
include_once('../php/class-magazine.php');

class issue {
	
	public $allowed_groups = [
		'is_cover',
		'is_large',
		'is_normal',
		'is_flyer',
	];
	
	
	
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
	// Core function
	// ======================================================
	public function access_issue($args = []) {
		
		$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
		
		// SELECT ----------------------------------------------
		if( $args['get'] === 'all' ) {
			$sql_select[] = 'issues.*';
		}
		
		if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
			$sql_select[] = 'magazines.id AS magazine_id';
			$sql_select[] = 'magazines.name';
			$sql_select[] = 'magazines.romaji';
			$sql_select[] = 'magazines.friendly';
			$sql_select[] = 'CONCAT_WS("/", "", "magazines", magazines.friendly, issues.id, issues.friendly, "" ) AS url';
			$sql_select[] = 'CONCAT_WS("/", "", "magazines", magazines.friendly, "") AS magazine_url';
			$sql_select[] = 'issues.friendly AS issue_friendly';
		}
		
		if( $args['get'] === 'basics' ) {
			$sql_select[] = 'issues.id';
			$sql_select[] = 'issues.volume_name';
			$sql_select[] = 'issues.volume_romaji';
			$sql_select[] = 'issues.volume_is_custom';
			$sql_select[] = 'issues.date_represented';
		}
		
		if( $args['get'] === 'count' ) {
			$sql_select[] = 'COUNT(1) AS num_issues';
		}
		
		// FROM ------------------------------------------------
		
		$sql_from = 'issues';
		
		// JOIN ------------------------------------------------
		
		// Get basic magazine info
		if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
			$sql_join[] = 'LEFT JOIN magazines ON magazines.id=issues.magazine_id';
		}
		
		// WHERE -----------------------------------------------
		
		// ID
		if( is_numeric( $args['id'] ) ) {
			$sql_where[] = 'issues.id=?';
			$sql_values[] = $args['id'];
		}
		
		// Magazine ID
		if( is_numeric( $args['magazine_id'] ) ) {
			$sql_where[] = 'issues.magazine_id=?';
			$sql_values[] = $args['magazine_id'];
		}
		
		// Date represented
		if( preg_match( '/'.'^\d{4}\-\d{2}$'.'/', $args['date_represented'] ) ) {
			$sql_where[] = 'issues.date_represented=?';
			$sql_values[] = $args['date_represented'].'-00';
		}
		
		// GROUP -----------------------------------------------
		if( $args['get'] === 'count' ) {
			$sql_group[] = 'issues.magazine_id';
		}
		
		// ORDER -----------------------------------------------
		
		// Custom order
		if( $args['order'] ) {
			$sql_order = is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ];
		}
		
		// Default order
		else {
			$sql_order = [ 'magazines.friendly ASC, issues.volume_order ASC, issues.date_represented ASC, issues.date_occurred ASC' ];
		}
		
		// BUILD QUERY -----------------------------------------
		$sql_issues = '
			SELECT '.implode(', ', $sql_select).'
			FROM '.$sql_from.' '.
			(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
			(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
			(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' 
			ORDER BY '.implode(', ', $sql_order).'
		';
		
		$stmt_issues = $this->pdo->prepare($sql_issues);
		
		// EXECUTE QUERY ---------------------------------------
		if($stmt_issues->execute($sql_values)) {
			
			// Get result
			$issues = $stmt_issues->fetchAll();
			$num_issues = is_array($issues) ? count($issues) : 0;
			
			// EXTRAS ---------------------------------------------
			$access_artist = new access_artist($this->pdo);
			$access_image = new access_image($this->pdo);
			
			// If getting All
			if( $num_issues && $args['get'] === 'all' ) {
				
				// Get artists
				for($i=0; $i<$num_issues; $i++) {
					
					// Query artists linked to issue
					$sql_artists = 'SELECT * FROM issues_artists WHERE issue_id=?';
					$stmt_artists = $this->pdo->prepare($sql_artists);
					$stmt_artists->execute([ $issues[$i]['id'] ]);
					$issue_artists = $stmt_artists->fetchAll();
					
					// Get artist data and merge with is_cover data, then attach to issue
					if( is_array($issue_artists) && !empty($issue_artists) ) {
						foreach($issue_artists as $issue_artist) {
							
							// Get artist info
							$artist = $access_artist->access_artist([ 'id' => $issue_artist['artist_id'], 'get' => 'name' ]);
							
							// Then attach back to issue in array based on group type (is_cover)
							foreach( $this->allowed_groups as $group_name ) {
								
								// Let's make an arbitrary key so we can force the artists groups in a certain order later
								$group_key = [ 'is_cover' => 0, 'is_large' => 1, 'is_normal' => 2, 'is_flyer' => 3 ][ $group_name ];
								
								if( $issue_artist[ $group_name ] ) {
									$issues[$i]['artists'][ $group_key.$group_name ][] = $artist;
									$issues[$i]['artists_text'][ $group_key.$group_name ] .= '('.$artist['id'].')/'.$artist['friendly'].'/ ';
								}
								
							}
							
						}
					}
					
					// Now if we were able to get some artists for this issue, let's make sure the groups are ordered in the correct way
					if( is_array($issues[$i]['artists']) && !empty($issues[$i]['artists']) ) {
						
						ksort($issues[$i]['artists']);
						ksort($issues[$i]['artists_text']);
						
						// Now loop back through and remove that number from the beginning of the names--this is dumb but can't think of a better way to do it atm
						foreach( $issues[$i]['artists'] as $group_name => $group ) {
							$issues[$i]['artists'][ substr($group_name,1) ] = $group;
							unset($issues[$i]['artists'][$group_name]);
						}
						foreach( $issues[$i]['artists_text'] as $group_name => $group ) {
							$issues[$i]['artists_text'][ substr($group_name,1) ] = $group;
							unset($issues[$i]['artists_text'][$group_name]);
						}
						
					}
					
				}
				
				// Get all images
				for($i=0; $i<$num_issues; $i++) {
					$issues[$i]['images'] = $access_image->access_image([ 'issue_id' => $issues[$i]['id'], 'get' => 'name' ]);
					$issues[$i]['image'] = is_array($issues[$i]['images']) && !empty($issues[$i]['images']) ? $issues[$i]['images'][0] : null;
				}
				
			}
			
			// If getting Basics
			if( $num_issues && $args['get'] === 'basics' ) {
				
				// Get default image
				for($i=0; $i<$num_issues; $i++) {
					$issues[$i]['image'] = $access_image->access_image([ 'issue_id' => $issues[$i]['id'], 'default' => true, 'get' => 'name' ]);
					$issues[$i]['image'] = is_array($issues[$i]['image']) && !empty($issues[$i]['image']) ? $issues[$i]['image'][0] : null;
				}
				
			}
			
		}
		
		// RETURN ----------------------------------------------
		
		// Make sure array is returned
		$issues = is_array($issues) ? $issues : [];
		
		// Return single column if limited to 1 result
		$issues = strlen($args['id']) || $args['limit'] == 1 ? reset($issues) : $issues;
		
		return $issues;
		
	}
	
	
	
	// ======================================================
	// Update issue
	// ======================================================
	public function update_issue( $issue ) {
		
		// Whitelist of columns allowed in update
		$allowed_columns = [ 'id', 'magazine_id', 'date_represented', 'date_occurred', 'volume_name', 'volume_romaji', 'volume_is_custom', 'volume_order', 'image_id', 'friendly', 'product_number', 'jan_code', 'notes', 'price' ];
		
		if( is_array($issue) && !empty($issue) ) {
			
			// Clean normal vars
			foreach( $issue as $key => $value ) {
				$value = is_array($value) ? $value : sanitize($value);
				$issue[$key] = is_array($value) || strlen($value) ? $value : null;
			}
			
			// Clean numbers
			foreach( [ 'id', 'magazine_id' ] as $key ) {
				$issue[$key] = is_numeric($issue[$key]) ? $issue[$key] : null;
			}
			
			// Clean dates
			$issue['date_represented'] = preg_match('/'.'\d{4}-\d{2}'.'/',        $issue['date_represented']) ? $issue['date_represented'].'-00' : null;
			$issue['date_occurred']    = preg_match('/'.'\d{4}(?:-\d{2}){2}'.'/', $issue['date_occurred'])    ? $issue['date_occurred'] : null;
			
			// Clean other specific vars
			$issue['volume_is_custom'] = $issue['volume_is_custom'] ? 1 : 0;
			
			// Now get info for the magazine this issue belongs to
			$access_magazine = $access_magazine ?: new magazine($this->pdo);
			$magazine = $access_magazine->access_magazine([ 'id' => $issue['magazine_id'], 'get' => 'basics' ]);
			
			// Magazine must exist
			if( is_array($magazine) && !empty($magazine) ) {
				
				// Clean volume name (unless marked custom)
				if( !$issue['volume_is_custom'] ) {
					
					// If minimum number of digits is specified by the magazine, make sure the volume name matches that num (and that there's no romaji)
					if( $magazine['num_volume_digits'] ) {
						$issue['volume_name'] = is_numeric($issue['volume_name']) ? sprintf('%0'.$magazine['num_volume_digits'].'d', $issue['volume_name']) : null;
						$issue['volume_romaji'] = null;
					}
					
					// Insert volume name into full volume patterns specified by magazine
					$issue['volume_name']   = str_replace( '{volume}', $issue['volume_name'], $magazine['volume_name_pattern'] );
					$issue['volume_romaji'] = $magazine['volume_romaji_pattern'] || $issue['volume_romaji'] ? str_replace( '{volume}', ($issue['volume_romaji'] ?: $issue['volume_name']), ($magazine['volume_romaji_pattern'] ?: $magazine['volume_name_pattern']) ) : null;
					
				}
				
				// Need a separate volume order so issues can be sorted properly (soukangou before 2, 10 after 3, etc)
				$issue['volume_order'] = is_numeric($issue['volume_order']) ? $issue['volume_order'] : ( strtolower($issue['volume_romaji']) == 'soukangou' ? 1 : preg_replace('/\D/', '', html_entity_decode($issue['volume_name'])) );
				
				// Clean friendly (now that full volume name is set)
				$issue['friendly'] = friendly( $issue['friendly'] ) ?: friendly( $issue['volume_romaji'] ?: $issue['volume_name'] );
				
				// Artists will need to be added in a separate pass; save for later
				foreach( $this->allowed_groups as $group_name ) {
					$issue_artists[ $group_name ] = $issue[ $group_name ];
				}
				
				// Set flag
				$is_edit = is_numeric($issue['id']);
				
				// Remove disallowed columns before setting up keys/values
				$issue = array_filter( $issue, function ($column) use ($allowed_columns) { return in_array($column, $allowed_columns); }, ARRAY_FILTER_USE_KEY );
				
				// Set up keys and values
				$keys_update = array_keys($issue);
				$values_update = array_values($issue);
				
				// Make sure we have volume name before moving ahead
				if( strlen($issue['volume_name']) ) {
					
					// If updating
					if( $is_edit ) {
						
						// Add ID to values
						$values_update[] = $issue['id'];
						
						// Set query
						$sql_update = 'UPDATE issues SET '.implode('=?,', $keys_update).'=? WHERE id=? LIMIT 1';
						
					}
					
					// If adding new
					else {
						
						// Unset ID from array of values that will be inserted
						$index_of_id_in_array = array_search('id', $keys_update);
						unset( $keys_update[$index_of_id_in_array], $values_update[$index_of_id_in_array] );
						
						// Set query
						$sql_update = 'INSERT INTO issues ('.implode( ',', $keys_update ).') VALUES ('.substr( str_repeat( '?,', count($keys_update) ), 0, -1 ).')';
						
					}
					
					// If nothing went wrong while generating the query, run it
					if( $sql_update ) {
						
						// Make sure values start with 0 to avoid error
						$values_update = array_values($values_update);
						
						$stmt_update = $this->pdo->prepare($sql_update);
						if( $stmt_update->execute( $values_update ) ) {
							
							// If newly added, get ID
							if( !$is_edit ) {
								$issue['id'] = $this->pdo->lastInsertID();
							}
							
							// Output results
							$output['url']      = '/magazines/'.$magazine['friendly'].'/'.$issue['id'].'/'.$issue['friendly'].'/';
							$output['edit_url'] = $output['url'].'edit/';
							$output['status']   = 'success';
							$output['id']       = is_numeric($issue['id']) ? $issue['id'] : $this->pdo->lastInsertID();
							
							// Update artists
							$this->update_issue_artists( $issue['id'], $issue_artists );
							
						}
						else {
							$output['result'] = 'Couldn\'t '.($is_edit ? 'update' : 'add').' &ldquo;';
						}
						
					}
					else {
						$output['result'] = 'Couldn\'t generate query.';
					}
					
				}
				else {
					$output['result'] = 'Volume is empty or in wrong format (mark as &ldquo;custom volume&rdquo; if necessary).';
				}
				
			}
			else {
				$output['result'] = 'The issue isn\'t attached to an extant magazine.';
			}
			
		}
		else {
			$output['result'] = 'Data empty.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
	
	
	// =======================================================
	// Delete issue
	// =======================================================
	public function delete_issue( $issue_id ) {
		
		if( $_SESSION['can_delete_data'] ) {
			
			if( is_numeric($issue_id) ) {
				
				$sql_delete = 'DELETE FROM issues WHERE id=? LIMIT 1';
				$stmt_delete = $this->pdo->prepare($sql_delete);
				
				if( $stmt_delete->execute([ $issue_id ]) ) {
					$output['status'] = 'success';
					$output['result'] = 'Issue deleted.';
				}
				else {
					$output['result'] = 'Couldn\'t delete issue.';
				}
				
			}
			else {
				$output['result'] = 'That issue doesn\'t exist.';
			}
			
		}
		else {
			$output['result'] = 'Sorry, you don\'t have permission to delete issues.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
	
	
	// =======================================================
	// Update issue artists
	// =======================================================
	public function update_issue_artists( $issue_id, $artist_groups = [] ) {
		
		$markdown_parser = new parse_markdown($this->pdo);
		
		// Make sure have magazine ID
		if( is_numeric($issue_id) ) {
			
			// If any artists specified, we need to clean them and insert into DB
			if( is_array($artist_groups) && !empty($artist_groups) ) {
				
				// Artists will be split into groups based on what kind of feature they have within the issue, so we need to clean those
				foreach( $artist_groups as $artist_group => $group_content ) {
					if( in_array( $artist_group, $this->allowed_groups ) ) {
						
						// Content is presented as Markdown text
						$group_references = $markdown_parser->get_reference_data($group_content);
						
						// If we got some references, loop through them, clean up the artist ones, and save them for later
						if( is_array($group_references) && !empty($group_references) ) {
							foreach( $group_references as $reference_in_group ) {
								
								// Make sure we're only paying attention to artists referenced in the text
								if( $reference_in_group['type'] == 'artist' ) {
									
									$artist_id = $reference_in_group['id'];
									
									if( is_numeric($artist_id) ) {
										
										// Save each artist id into an array of artists, setting a flag that represents the group they're in
										$artists[ $artist_id ]['id'] = $artist_id;
										$artists[ $artist_id ][ $artist_group ] = 1;
										
									}
									
								}
								
							}
						}
						
					}
				}
				
				// If we produced an array of artists and flags for the groups they're in, go back through and make sure any groups they're *not* in are stated as such
				if( is_array($artists) && !empty($artists) ) {
					
					// Loop through each artist and set a flag for that artist for each group possible
					foreach( $artists as $artist_id => $artist ) {
						
						// Create a temporary artist object so we can make sure the flags are in the same order as the allowed_groups up top
						// This is super brittle but otherwise the wrong flags will be set
						$temp_artist = [ $artist_id ];
						
						// Now set each flag in the right order
						foreach( $this->allowed_groups as $group_name ) {
							$temp_artist[ $group_name ] = $artist[ $group_name ] ? 1 : 0;
						}
						
						// And then replace original artist with temp artist
						$artists[ $artist_id ] = $temp_artist;
						
					}
					
				}
				
			}
			
			// Make a master list of artist IDs just to help with query building
			$artist_ids = is_array($artists) && !empty($artists) ? array_keys($artists) : [];
			$num_artist_ids = $artist_ids ? count($artist_ids) : null;
			
			// If we still have artists provided, make sure they're in DB (relies on issue-artist unique key)
			if( $num_artist_ids ) {
				
				// Create values array
				foreach( $artists as $artist ) {
					$values_update[] = $issue_id;
					$values_update = array_merge( $values_update, array_values($artist) );
				}
				
				$sql_update = '
					INSERT INTO
					issues_artists
					(issue_id, artist_id, '.implode(', ', $this->allowed_groups).')
					VALUES '.
					substr( str_repeat( '(?,?,'. substr(str_repeat('?,',count($this->allowed_groups)),0,-1) .'),', $num_artist_ids ), 0, -1 ).'
					ON DUPLICATE KEY UPDATE ';
				foreach( $this->allowed_groups as $index => $group_column ) {
					$sql_update .= ($index ? ', ' : null).$group_column.'=VALUES('.$group_column.')';
				}
				
				$stmt_update = $this->pdo->prepare($sql_update);
				$stmt_update->execute($values_update);
				
			}
			
			// Set up values for deletion
			$values_delete = array_merge( [ $issue_id ], $artist_ids );
			
			// Delete any artists previously linked to magazine which aren't in new list
			$sql_delete = 'DELETE FROM issues_artists WHERE issue_id=? '.( $num_artist_ids ? 'AND artist_id NOT IN ('.substr( str_repeat( '?,', $num_artist_ids ), 0, -1 ).')' : null );
			$stmt_delete = $this->pdo->prepare($sql_delete);
			$stmt_delete->execute($values_delete);
			
		}
		else {
			$output['result'] = 'Issue ID must be specified.';
		}
		
		return $output;
		
	}
	
}