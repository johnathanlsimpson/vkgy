<?php

include_once('../php/include.php');
include_once('../php/class-issue.php');

class magazine {
	
	public static $attribute_types = [
		'format',
		'page size',
		'num pages',
		'frequencies',
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
		
		$this->access_label = new access_label($pdo);
		
	}
	
	
	
	// ======================================================
	// Get attributes
	// ======================================================
	public function get_attributes() {
		
		$sql_attributes = 'SELECT type, name, romaji, friendly, id, is_default, type FROM magazines_attributes ORDER BY type ASC, friendly ASC';
		$stmt_attributes = $this->pdo->prepare($sql_attributes);
		$stmt_attributes->execute();
		$attributes = $stmt_attributes->fetchAll(PDO::FETCH_GROUP);
		
		return $attributes;
		
	}
	
	
	
	// ======================================================
	// Core function
	// ======================================================
	public function access_magazine($args = []) {
		
		$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
		
		// SELECT ----------------------------------------------
		if( $args['get'] === 'all' ) {
			$sql_select[] = 'magazines.*';
			$sql_select[] = 'REPLACE(magazines.date_occurred, "-00", "") AS date_occurred';
			$sql_select[] = 'magazine_type.name AS type_name';
			$sql_select[] = 'magazine_type.romaji AS type_romaji';
			$sql_select[] = 'magazine_size.name AS size_name';
			$sql_select[] = 'magazine_size.romaji AS size_romaji';
		}
		
		if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
			$sql_select[] = 'CONCAT_WS("/", "", "magazines", magazines.friendly, "") AS url';
		}
		
		if( $args['get'] === 'basics' ) {
			$sql_select[] = 'magazines.id';
			$sql_select[] = 'magazines.name';
			$sql_select[] = 'magazines.romaji';
			$sql_select[] = 'magazines.friendly';
			$sql_select[] = 'REPLACE(magazines.date_occurred, "-00", "") AS date_occurred';
			$sql_select[] = 'magazines.volume_name_pattern';
			$sql_select[] = 'magazines.volume_romaji_pattern';
			$sql_select[] = 'magazines.num_volume_digits';
			$sql_select[] = 'magazines.default_price';
		}
		
		if( $args['get'] === 'count' ) {
			$sql_select[] = 'COUNT(1) AS num_magazines';
		}
		
		// FROM ------------------------------------------------
		
		// Get by label
		if( is_numeric($args['label_id']) ) {
			$sql_from = 'magazines_labels';
		}
		
		// Default
		else {
			$sql_from = 'magazines';
		}
		
		// JOIN ------------------------------------------------
		
		// Type and size
		if( $args['get'] === 'all' ) {
			$sql_join[] = 'LEFT JOIN magazines_attributes AS magazine_type ON magazine_type.id=magazines.type';
			$sql_join[] = 'LEFT JOIN magazines_attributes AS magazine_size ON magazine_size.id=magazines.size';
		}
		
		// Get by label
		if( is_numeric($args['label_id']) ) {
			$sql_join[] = 'LEFT JOIN magazines ON magazines.id=magazines_labels.magazine_id';
		}
		
		// WHERE -----------------------------------------------
		
		// ID
		if( is_numeric( $args['id'] ) ) {
			$sql_where[] = 'magazines.id=?';
			$sql_values[] = $args['id'];
		}
		
		// Friendly
		if( strlen( $args['friendly'] ) ) {
			$sql_where[] = 'magazines.friendly=?';
			$sql_values[] = friendly($args['friendly']);
		}
		
		// Label
		if( is_numeric($args['label_id']) ) {
			$sql_where[] = 'magazines_labels.label_id=?';
			$sql_values[] = sanitize($args['label_id']);
		}
		
		// GROUP -----------------------------------------------
		
		// Count magazines
		if( $args['get'] === 'count' ) {
			$sql_group[] = 'magazines.id';
		}
		
		// ORDER -----------------------------------------------
		
		// Custom order
		if( $args['order'] ) {
			$sql_order = is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ];
		}
		
		// Default order
		else {
			$sql_order = [ 'magazines.friendly ASC' ];
		}
		
		// BUILD QUERY -----------------------------------------
		$sql_magazines = '
			SELECT '.implode(', ', $sql_select).'
			FROM '.$sql_from.' '.
			(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
			(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
			(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' 
			ORDER BY '.implode(', ', $sql_order).'
		';
		$stmt_magazines = $this->pdo->prepare($sql_magazines);
		
		// EXECUTE QUERY ----------------------------------------
		if($stmt_magazines->execute($sql_values)) {
			
			// Get result
			$magazines = $stmt_magazines->fetchAll();
			$num_magazines = count($magazines);
			
			// Get additional data
			if( is_array($magazines) && !empty($magazines) ) {
				foreach( $magazines as $magazine ) {
					
					// EXTRAS -------------------------------------------
					if( $args['get'] === 'all' ) {
						for($i=0; $i<$num_magazines; $i++) {
							
							// Get parent
							if( is_numeric($magazines[$i]['parent_magazine_id']) ) {
								$magazines[$i]['parent_magazine'] = $this->access_magazine([ 'id' => $magazines[$i]['parent_magazine_id'], 'get' => 'basics' ]);
							}
							
							// Get issues
							$access_issue = $access_issue ?: new issue($this->pdo);
							$magazines[$i]['issues'] = $access_issue->access_issue([ 'magazine_id' => $magazines[$i]['id'], 'get' => 'basics' ]);
							
							// Get labels
							$magazines[$i]['labels'] = $this->access_label->access_label([ 'magazine_id' => $magazines[$i]['id'], 'get' => 'name' ]);
							
						}
					}
					
				}
			}
			
		}
		
		// RETURN ----------------------------------------------
		
		// Make sure array is returned
		$magazines = is_array($magazines) ? $magazines : [];
		
		// Return single column if limited to 1 result
		$magazines = strlen($args['id']) || $args['limit'] == 1 ? reset($magazines) : $magazines;
		
		return $magazines;
		
	}
	
	
	
	// =======================================================
	// Check if magazine name is already used
	// =======================================================
	private function friendly_is_taken( $friendly ) {
		
		// Make sure name is available
		$sql_friendly = 'SELECT 1 FROM magazines WHERE friendly=? LIMIT 1';
		$stmt_friendly = $this->pdo->prepare($sql_friendly);
		$stmt_friendly->execute([ $friendly ]);
		return $stmt_friendly->fetchColumn() ? true : false;
		
	}
	
	
	
	// =======================================================
	// Update magazine
	// =======================================================
	public function update_magazine( $magazine ) {
		
		// Whitelist of columns allowed in update
		$allowed_columns = [ 'id', 'name', 'romaji', 'friendly', 'volume_name_pattern', 'volume_romaji_pattern', 'num_volume_digits', 'parent_magazine_id', 'default_price', 'notes', 'type', 'frequency', 'date_occurred', 'size' ];
		
		if( is_array($magazine) && !empty($magazine) ) {
			
			// Clean normal vars
			foreach( $magazine as $key => $value ) {
				$value = is_array($value) ? $value : sanitize($value);
				$magazine[$key] = is_array($value) || strlen($value) ? $value : null;
			}
			
			// Clean numbers
			foreach( [ 'id', 'parent_magazine_id', 'type', 'size', 'frequency' ] as $key ) {
				$magazine[$key] = is_numeric($magazine[$key]) ? $magazine[$key] : null;
			}
			
			// Clean other vars
			$magazine['friendly'] = strlen($magazine['friendly']) ? $magazine['friendly'] : friendly( $magazine['romaji'] ?: $magazine['name'] );
			$magazine['num_volume_digits'] = $magazine['num_volume_digits'] ?: 0;
			
			// Date might have underscores from inputmask
			$magazine['date_occurred'] = str_replace( [ '-__', '__' ], '', $magazine['date_occurred'] );
			
			// Make sure date is correct format and add -00 if necessary
			if( preg_match('/'.'\d{4}(?:-\d{2})?(?:-\d{2})?'.'/', $magazine['date_occurred']) ) {
				$magazine['date_occurred'] .= str_repeat( '-00', ( 10 - strlen( $magazine['date_occurred'] ) ) / 3 );
			}
			else {
				$magazine['date_occurred'] = null;
			}
			
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
		
	}
	
	
	
	// =======================================================
	// Update attribute
	// =======================================================
	public function update_attribute( $attribute ) {
		
		// Whitelist of columns allowed in update
		$allowed_columns = [ 'id', 'name', 'romaji', 'friendly', 'type', 'is_default' ];
		
		if( is_array($attribute) && !empty($attribute) ) {
			
			// Clean normal vars
			foreach( $attribute as $key => $value ) {
				$value = sanitize($value);
				$attribute[$key] = strlen($value) ? $value : null;
			}
			
			// Clean other vars
			$attribute['id']         = is_numeric($attribute['id']) ? $attribute['id'] : null;
			$attribute['is_default'] = $attribute['is_default'] ? 1 : 0;
			$attribute['type']       = is_numeric($attribute['type']) ? $attribute['type'] : null;
			$attribute['friendly']   = friendly( $attribute['friendly'] ?: ( $attribute['romaji'] ?: $attribute['name'] ) );
			
			// Set flag
			$is_edit = is_numeric($attribute['id']);
			
			// Remove disallowed columns before setting up keys/values
			$attribute = array_filter( $attribute, function ($column) use ($allowed_columns) { return in_array($column, $allowed_columns); }, ARRAY_FILTER_USE_KEY );
			
			// Set up keys and values
			$keys_update = array_keys($attribute);
			$values_update = array_values($attribute);
			
			// Unset ID from array of values that will be inserted
			$index_of_id_in_array = array_search('id', $keys_update);
			unset( $keys_update[$index_of_id_in_array], $values_update[$index_of_id_in_array] );
			$keys_update = array_values($keys_update);
			$values_update = array_values($values_update);
			
			// Make sure name is specified
			if( strlen($attribute['name']) && is_numeric($attribute['type']) ) {
				
				// If updating existing
				if( $is_edit ) {
					
					// Add ID to values
					$values_update[] = $attribute['id'];
					
					// Set query
					$sql_update = 'UPDATE magazines_attributes SET '.implode('=?,', $keys_update).'=? WHERE id=? LIMIT 1';
					
				}
				
				// If adding new
				else {
					
					// Set query
					$sql_update = 'INSERT INTO magazines_attributes ('.implode( ',', $keys_update ).') VALUES ('.substr( str_repeat( '?,', count($keys_update) ), 0, -1 ).')';
					
				}
				
				// If nothing went wrong while generating the query, run it
				if( $sql_update ) {
					
					$stmt_update = $this->pdo->prepare($sql_update);
					if( $stmt_update->execute( $values_update ) ) {
						
						// Set output
						$output['status'] = 'success';
						
						// Only need message if added attribute
						if( !$is_edit ) {
							$output['result'] = 'Updated attribute.';
						}
						
					}
					else {
						$output['result'] = 'Couldn\'t update &ldquo;'.( $attribute['romaji'] ?: $attribute['name'] ).'&rdquo;';
					}
					
				}
				else {
					$output['result'] = $output['result'] ?: 'Couldn\'t generate attribute query.';
				}
				
			}
			else {
				if( $is_edit ) {
					$output['result'] = 'The attribute needs a name and type.';
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
	// Delete attribute
	// =======================================================
	public function delete_attribute( $attribute_id ) {
		
		if( $_SESSION['can_delete_data'] ) {
			
			if( is_numeric($attribute_id) ) {
				
				$sql_delete = 'DELETE FROM magazines_attributes WHERE id=? LIMIT 1';
				$stmt_delete = $this->pdo->prepare($sql_delete);
				
				if( $stmt_delete->execute([ $attribute_id ]) ) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = 'Couldn\'t delete attribute.';
				}
				
			}
			else {
				$output['result'] = 'That attribute doesn\'t exist.';
			}
			
		}
		else {
			$output['result'] = 'Sorry, you don\'t have permission to delete attributes.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
	
	
	// =======================================================
	// Update magazine labels
	// =======================================================
	public function update_magazine_labels( $magazine_id, $label_ids = [] ) {
		
		// Make sure have magazine ID
		if( is_numeric($magazine_id) ) {
			
			// Clean label IDs
			$label_ids = is_array($label_ids) ? array_filter( $label_ids, 'is_numeric' ) : [];
			$num_label_ids = count($label_ids);
			
			// If we still have labels provided, make sure they're attached--relies on magazine-label unique key
			if( is_array($label_ids) && !empty($label_ids) ) {
				
				// Create values array
				foreach( $label_ids as $label_id ) {
					$values_update[] = $magazine_id;
					$values_update[] = $label_id;
				}
				
				$sql_update = 'INSERT IGNORE INTO magazines_labels (magazine_id, label_id) VALUES '.substr( str_repeat( '(?,?),', $num_label_ids ), 0, -1 );
				$stmt_update = $this->pdo->prepare($sql_update);
				$stmt_update->execute($values_update);
				
			}
			
			// Set up values for deletion
			$values_delete = [ $magazine_id ] + $label_ids;
			
			// Delete any labels previously linked to magazine which aren't in new list
			$sql_delete = 'DELETE FROM magazines_labels WHERE magazine_id=? '.( $num_label_ids ? 'AND label_id NOT IN ('.substr( str_repeat( '?,', $num_label_ids ), 0, -1 ).')' : null );
			$stmt_delete = $this->pdo->prepare($sql_delete);
			$stmt_delete->execute();
			
		}
		else {
			$output['result'] = 'Magazine ID must be specified.';
		}
		
		return $output;
		
	}
	
}