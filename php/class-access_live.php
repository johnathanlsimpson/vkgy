<?php
	include_once("../php/include.php");
	
	class access_live {
		public $pdo;
		public $live_types;
		
		
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
			}
			
			$this->pdo = $pdo;
			
			// Live types
			$this->live_types = [
				'taiban'   => 0,
				'oneman'   => 1,
				'twoman'   => 2,
				'threeman' => 3,
				'fourman'  => 4,
				'session'  => 5,
				'instore'  => 6,
				'outstore' => 7,
			];
			
		}
		
		
		
		// ======================================================
		// Delete certain lives
		// ======================================================
		function batch_delete_live_links($args = []) {
			
			// Delete all lives by artist
			if(is_numeric($args['artist_id'])) {
				$sql_where[] = 'lives_artists.artist_id=?';
				$sql_values[] = $args['artist_id'];
			}
			
			// Ignore certain lives
			if(is_array($args['lives_to_ignore']) && !empty($args['lives_to_ignore'])) {
				$sql_where[] = 'lives_artists.live_id NOT IN ('.substr(str_repeat('?, ', count($args['lives_to_ignore'])), 0, -2).')';
				
				foreach($args['lives_to_ignore'] as $live_id) {
					$sql_values[] = $live_id;
				}
			}
			
			// Get lives that match specifications
			$sql_get_lives = 'SELECT live_id FROM lives_artists WHERE '.implode(' AND ', $sql_where);
			$stmt_get_lives = $this->pdo->prepare($sql_get_lives);
			$stmt_get_lives->execute( $sql_values );
			$selected_lives = $stmt_get_lives->fetchAll();
			
			// Delete selected live links
			if(is_array($selected_lives) && !empty($selected_lives)) {
				
				// For now, let's limit the scope
				if(is_numeric($args['artist_id'])) {
					
					$values_delete_links[] = $args['artist_id'];
					foreach($selected_lives as $selected_live) {
						$values_delete_links[] = $selected_live['live_id'];
					}
					
					$sql_delete_links = 'DELETE FROM lives_artists WHERE artist_id=? AND ('.substr(str_repeat('live_id=? OR ', count($selected_lives)), 0, -4).')';
					$stmt_delete_links = $this->pdo->prepare($sql_delete_links);
					if($stmt_delete_links->execute( $values_delete_links )) {
						$live_links_deleted = true;
					}
					
				}
			}
			
			// If lives links deleted, let's remove lives with no artists left
			if($live_links_deleted && is_array($selected_lives) && !empty($selected_lives)) {
				
				foreach($selected_lives as $selected_live) {
					$values_get_orphans[] = $selected_live['live_id'];
				}
				
				$sql_get_orphans = 'SELECT lives.id FROM lives LEFT JOIN lives_artists ON lives_artists.live_id=lives.id WHERE lives.lineup IS NULL AND lives_artists.artist_id IS NULL AND ('.substr( str_repeat('lives.id=? OR ', count($selected_lives)), 0, -4 ).') GROUP BY lives.id';
				$stmt_get_orphans = $this->pdo->prepare($sql_get_orphans);
				$stmt_get_orphans->execute( $values_get_orphans );
				$orphaned_lives = $stmt_get_orphans->fetchAll();
				
				// Delete any found orphans
				if(is_array($orphaned_lives) && !empty($orphaned_lives)) {
					
					foreach($orphaned_lives as $orphaned_live) {
						$values_delete_orphans[] = $orphaned_live['id'];
					}
					
					$sql_delete_orphans = 'DELETE FROM lives WHERE '.substr( str_repeat('id=? OR ', count($values_delete_orphans)), 0, -4);
					$stmt_delete_orphans = $this->pdo->prepare($sql_delete_orphans);
					$stmt_delete_orphans->execute( $values_delete_orphans );
				}
				
			}
		}
		
		
		
		// ======================================================
		// Build 'concert' object
		// ======================================================
		function access_live($args = []) {
			$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
			
			// SELECT ----------------------------------------------
			if($args['get'] === 'basics' || $args['get'] === 'all' || $args['get'] === 'name') {
				$sql_select[] = 'areas.id AS area_id';
				$sql_select[] = 'areas.name AS area_name';
				$sql_select[] = 'areas.romaji AS area_romaji';
				$sql_select[] = 'areas.friendly AS area_friendly';
				$sql_select[] = 'lives_livehouses.id AS livehouse_id';
				$sql_select[] = 'lives_livehouses.name AS livehouse_name';
				$sql_select[] = 'lives_livehouses.romaji AS livehouse_romaji';
				$sql_select[] = 'lives_livehouses.friendly AS livehouse_friendly';
			}
			if($args['get'] === 'name') {
				$sql_select[] = 'lives.id';
				$sql_select[] = 'lives.date_occurred';
			}
			if($args['get'] === 'basics') {
				$sql_select[] = 'lives.id';
				$sql_select[] = 'lives.date_occurred';
				$sql_select[] = 'lives.lineup';
				$sql_select[] = 'lives.name';
				$sql_select[] = 'lives.romaji';
				$sql_select[] = 'lives.type';
			}
			if($args['get'] === 'all') {
				$sql_select[] = 'lives.*';
			}
			if($args['get'] === 'count') {
				$sql_select[] = 'COUNT(*) AS num_lives';
			}
			
			// FROM ------------------------------------------------
			$sql_from = 'lives';
			if($args['get'] === 'basics' || $args['get'] === 'all' || $args['get'] === 'name') {
				$sql_join[] = 'LEFT JOIN lives_livehouses ON lives_livehouses.id=lives.livehouse_id';
				$sql_join[] = 'LEFT JOIN areas ON areas.id=lives_livehouses.area_id';
			}
			
			// WHERE -----------------------------------------------
			if(is_numeric($args['id'])) {
				$sql_where[] = 'lives.id=?';
				$sql_values[] = $args['id'];
			}
			if(is_numeric($args['livehouse_id'])) {
				$sql_where[] = 'lives.livehouse_id=?';
				$sql_values[] = $args['livehouse_id'];
			}
			if(is_numeric($args['artist_id'])) {
				$sql_from = '(SELECT live_id FROM lives_artists WHERE artist_id=?) inner_join';
				array_unshift($sql_join, 'LEFT JOIN lives ON lives.id=inner_join.live_id');
				$sql_values[] = $args['artist_id'];
			}
			if(is_numeric($args['area_id'])) {
				$sql_areas = 'SELECT id FROM areas WHERE id=? OR parent_id=?';
				$stmt_areas = $this->pdo->prepare($sql_areas);
				$stmt_areas->execute([ $args['area_id'], $args['area_id'] ]);
				$rslt_areas = $stmt_areas->fetchAll();
				
				if(is_array($rslt_areas) && !empty($rslt_areas)) {
					foreach($rslt_areas as $rslt_area_key => $rslt_area) {
						$rslt_areas[$rslt_area_key] = $rslt_area['id'];
					}
					
					$sql_from = '(SELECT id AS livehouse_id FROM lives_livehouses WHERE ('.substr(str_repeat('area_id=? OR ', count($rslt_areas)), 0, -4).')) inner_join';
					array_unshift($sql_join, 'LEFT JOIN lives ON lives.livehouse_id=inner_join.livehouse_id');
					$sql_values = array_merge($sql_values, $rslt_areas);
				}
			}
			if($args['date_occurred']) {
				if(strlen($args['date_occurred']) === 10) {
					$sql_where[] = 'lives.date_occurred=?';
					$sql_values[] = $args['date_occurred'];
				}
				if(strlen($args['date_occurred']) === 7) {
					$sql_where[] = 'lives.date_occurred>=?';
					$sql_where[] = 'lives.date_occurred<=?';
					$sql_values[] = $args['date_occurred'].'-01';
					$sql_values[] = $args['date_occurred'].'-31';
				}
				if(strlen($args['date_occurred']) === 4) {
					$sql_where[] = 'lives.date_occurred>=?';
					$sql_where[] = 'lives.date_occurred<=?';
					$sql_values[] = $args['date_occurred'].'-01-01';
					$sql_values[] = $args['date_occurred'].'-12-31';
				}
			}
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'lives.date_occurred DESC' ];
			
			// LIMIT -----------------------------------------------
			$sql_limit = $args['limit'] ?: ($args['get'] === 'count' || strlen($args['artist_id']) ? null : '100');
			
			// BUILD QUERY -----------------------------------------
			$sql_lives = '
				SELECT '.implode(', ', $sql_select).'
				FROM '.$sql_from.'
				'.(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).'
				'.(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).'
				ORDER BY '.implode(', ', $sql_order).'
				'.(strlen($sql_limit) ? 'LIMIT '.$sql_limit : null).'
			';
			$stmt_lives = $this->pdo->prepare($sql_lives);
			
			// EXECUTE QUERY ---------------------------------------
			if($stmt_lives->execute($sql_values)) {
				$rslt_lives = $stmt_lives->fetchAll();
				
				if(is_array($rslt_lives) && !empty($rslt_lives)) {
					$num_lives = count($rslt_lives);
					
					// FORMAT DATA -------------------------------------
					for($i=0; $i<$num_lives; $i++) {
						
						// Save all returned IDs so we can get artists
						$live_ids[] = $rslt_lives[$i]['id'];
						
						// Separate text lineup and add to artists array
						$lineup = explode("\n", $rslt_lives[$i]['lineup']);
						if(is_array($lineup) && !empty($lineup)) {
							foreach($lineup as $lineup_value) {
								if(strlen($lineup_value)) {
									$rslt_lives[$i]['artists'][]['name'] = $lineup_value;
								}
							}
						}
						
						// Make lives array associative
						$output_lives[$rslt_lives[$i]['id']] = $rslt_lives[$i];
						
					}
					
					// GET ARTISTS -------------------------------------
					if($args['get'] === 'basics' || $args['get'] === 'all') {
						$live_ids = is_array($live_ids) ? array_unique($live_ids) : [];
						$sql_artists = 'SELECT artists.id, artists.name, artists.romaji, artists.friendly, lives_artists.live_id, lives_artists.is_sponsor FROM lives_artists LEFT JOIN artists ON artists.id=lives_artists.artist_id WHERE ('.substr(str_repeat('live_id=? OR ', count($live_ids)), 0, -4).')';
						$stmt_artists = $this->pdo->prepare($sql_artists);
						$stmt_artists->execute($live_ids);
						$rslt_artists = $stmt_artists->fetchAll();
						
						if(is_array($rslt_artists) && !empty($rslt_artists)) {
							$num_artists = count($rslt_artists);
							
							// Place artists back onto lives
							for($i=0; $i<$num_artists; $i++) {
								if(strlen($rslt_artists[$i]['name'])) {
									$output_lives[ $rslt_artists[$i]['live_id'] ]['artists'][] = [ 
										'id' => $rslt_artists[$i]['id'],
										'is_sponsor' => $rslt_artists[$i]['is_sponsor'],
										'name' => $rslt_artists[$i]['name'], 
										'romaji' => $rslt_artists[$i]['romaji'], 
										'friendly' => $rslt_artists[$i]['friendly'] 
									];
								}
							}
						}
					}
					
					// REMOVE EMPTIES, RE-KEY --------------------------
					$output_lives = array_values($output_lives);
					$num_lives = count($output_lives);
					
					if($args['get'] != 'count') {
						for($i=0; $i<$num_lives; $i++) {
							
							// Remove if date or livehouse name is empty (and if expecting artists, remove if artists empty)
							if($args['get'] === 'name') {
								if(!$output_lives[$i]['date_occurred'] || !$output_lives[$i]['livehouse_name']) {
									unset($output_lives[$i]);
								}
							}
							elseif($args['get'] === 'basics' || $args['get'] === 'all') {
								if(!$output_lives[$i]['date_occurred'] || !$output_lives[$i]['livehouse_name'] || !is_array($output_lives[$i]['artists']) || empty($output_lives[$i]['artists'])) {
									unset($output_lives[$i]);
								}
							}
							
							// Change keys if necessary
							if($output_lives[$i] && $args['keys']) {
								if($args['keys'] === 'associative') {
									$tmp_output_lives[$output_lives[$i]['id']] = $output_lives[$i];
								}
								elseif($args['keys'] === 'date') {
									list($y, $m, $d) = explode('-', $output_lives[$i]['date_occurred']);
									$tmp_output_lives[$y][$m][$d][] = $output_lives[$i];
								}
							}
							
						}
					}
					
					$output_lives = is_array($tmp_output_lives) && !empty($tmp_output_lives) ? $tmp_output_lives : $output_lives;
				}
				
				// FORMAT OUTPUT -------------------------------------
				$output_lives = is_array($output_lives) ? $output_lives : [];
				
				if($args['get'] === 'count' && is_array($output_lives) && !empty($output_lives)) {
					$output_lives = reset($output_lives)['num_lives'];
				}
				
				return $output_lives;
			}
		}
	}
?>