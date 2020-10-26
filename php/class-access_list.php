<?php
	include_once('../php/include.php');
	
	class access_list {
		public $allowed_item_types;
		
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
			}
			
			$this->pdo = $pdo;
			$this->access_user = new access_user($pdo);
			$this->access_release = new access_release($pdo);
			$this->access_video = new access_video($pdo);
			$this->markdown_parser = new parse_markdown($pdo);
			
			// List item types
			$this->allowed_item_types = [
				'release' => 0,
				'video' => 1,
				'artist' => 2,
			];
			
		}
		
		
		
		// ======================================================
		// Core function
		// ======================================================
		function access_list($args = []) {
			$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
			
			// SELECT ----------------------------------------------
			if($args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'name') {
				$sql_select[] = 'lists.id';
				$sql_select[] = 'lists.name';
				$sql_select[] = 'lists.friendly';
				$sql_select[] = 'lists.date_occurred';
				$sql_select[] = 'lists.user_id';
			}
			if( $args['get'] === 'basics' ) {
				$sql_select[] = 'COUNT(lists_items.id) AS num_items';
			}
			
			// FROM ------------------------------------------------
			if( strlen($args['item_type']) && is_numeric($args['item_id']) ) {
				$sql_from = 'lists_items';
			}
			else {
				$sql_from = 'lists';
			}
			
			// JOINS -----------------------------------------------
			if( $args['get'] === 'basics' ) {
				$sql_join[] = 'LEFT JOIN lists_items ON lists_items.list_id=lists.id';
			}
			if( strlen($args['item_type']) && is_numeric($args['item_id']) ) {
				$sql_join[] = 'LEFT JOIN lists ON lists.id=lists_items.list_id';
			}
			
			// WHERE -----------------------------------------------
			if( strlen($args['item_type']) && is_numeric($args['item_id']) ) {
				$sql_where[] = 'lists_items.item_type=?';
				$sql_where[] = 'lists_items.item_id=?';
				$sql_values[] = $this->allowed_item_types[$args['item_type']];
				$sql_values[] = $args['item_id'];
			}
			if(is_numeric($args['user_id'])) {
				$sql_where[] = 'lists.user_id=?';
				$sql_values[] = $args['user_id'];
			}
			if( is_numeric($args['id']) ) {
				$sql_where[] = 'lists.id=?';
				$sql_values[] = $args['id'];
			}
			
			// GROUP -----------------------------------------------
			if( $args['get'] === 'basics' ) {
				$sql_group = 'lists.id';
			}
			
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'lists.date_occurred DESC' ];
			
			// LIMIT -----------------------------------------------
			$sql_limit = $args['limit'] ?: 100;
			
			// BUILD QUERY -----------------------------------------
			$sql_lists = '
				SELECT '.implode(', ', $sql_select).'
				FROM '.$sql_from.'
				'.(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).'
				'.(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).'
				'.($sql_group ? 'GROUP BY '.$sql_group : null).'
				ORDER BY '.implode(', ', $sql_order).'
				'.(strlen($sql_limit) ? 'LIMIT '.$sql_limit : null).'
			';
			$stmt_lists = $this->pdo->prepare($sql_lists);
			
			// EXECUTE QUERY ---------------------------------------
			if($stmt_lists->execute($sql_values)) {
				$rslt_lists = $stmt_lists->fetchAll();
				
				if(is_array($rslt_lists) && !empty($rslt_lists)) {
					$num_lists = count($rslt_lists);
					
					// FORMAT DATA -------------------------------------
					/*for($i=0; $i<$num_lists; $i++) {
						
						// Save all returned IDs so we can get artists
						$list_ids[] = $rslt_lists[$i]['id'];
						
						// Separate text lineup and add to artists array
						$lineup = explode("\n", $rslt_lists[$i]['lineup']);
						if(is_array($lineup) && !empty($lineup)) {
							foreach($lineup as $lineup_value) {
								if(strlen($lineup_value)) {
									$rslt_lists[$i]['artists'][]['name'] = $lineup_value;
								}
							}
						}
						
						// Make lists array associative
						$output_lists[$rslt_lists[$i]['id']] = $rslt_lists[$i];
						
					}*/
					
					// GET USERS -----------------------------------------
					if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
						
						for($i=0; $i<$num_lists; $i++) {
							
							$rslt_lists[$i]['user'] = $this->access_user->access_user([ 'id' => $rslt_lists[$i]['user_id'], 'get' => 'name' ]);
							
						}
						
					}
							
					$s = 'SELECT * FROM lists_items';
					$t = $this->pdo->prepare($s);
					$t->execute();
					
					// GET ITEMS -----------------------------------------
					if( $args['get'] === 'all' ) {
						
						// Loop through lists and save ids so we can list items
						for($i=0; $i<$num_lists; $i++) {
							
							// This is some dumb hacky shit to make it easier to put the query together
							$list_ids[ $rslt_lists[$i]['id'] ] = '?';
							
							// Get lists' items, adding in dummy url so we can use markdown parser to get module
							$sql_item_ids = '
								SELECT
									lists_items.list_id,
									lists_items.item_id,
									lists_items.item_type,
									IF(
										item_type="0", CONCAT("https://vk.gy/releases/dummy-artist/", item_id, "/dummy-title/"), IF(
										item_type="1", CONCAT("https://vk.gy/videos/", item_id, "/"), IF(
										item_type="2", CONCAT("https://vk.gy/artists/", artists.friendly, "/"), ""
									))) AS url
								FROM lists_items 
								LEFT JOIN releases ON releases.id=lists_items.item_id AND lists_items.item_type='.$this->allowed_item_types['release'].'
								LEFT JOIN videos ON videos.id=lists_items.item_id AND lists_items.item_type='.$this->allowed_item_types['video'].'
								LEFT JOIN artists ON artists.id=lists_items.item_id AND lists_items.item_type='.$this->allowed_item_types['artist'].'
								WHERE list_id IN ('.implode(', ', $list_ids).')';
							
							$stmt_item_ids = $this->pdo->prepare($sql_item_ids);
							$stmt_item_ids->execute( array_keys($list_ids) );
							$rslt_item_ids = $stmt_item_ids->fetchAll();
							
							// Take each url, pretend it's from a chunk of text, and let markdown parser get the html module
							foreach( $rslt_item_ids as $rslt_item ) {
								$rslt_item['content'] = $this->markdown_parser->parse_markdown( $rslt_item['url'] );
								$rslt_lists[$i]['items'][] = $rslt_item;
							}
							
						}
						
					}
					
					// REMOVE EMPTIES, RE-KEY --------------------------
					/*$output_lists = array_values($output_lists);
					$num_lists = count($output_lists);
					
					if($args['get'] != 'count') {
						for($i=0; $i<$num_lists; $i++) {
							
							// Remove if date or listhouse name is empty (and if expecting artists, remove if artists empty)
							if($args['get'] === 'name') {
								if(!$output_lists[$i]['date_occurred'] || !$output_lists[$i]['listhouse_name']) {
									unset($output_lists[$i]);
								}
							}
							elseif($args['get'] === 'basics' || $args['get'] === 'all') {
								if(!$output_lists[$i]['date_occurred'] || !$output_lists[$i]['listhouse_name'] || !is_array($output_lists[$i]['artists']) || empty($output_lists[$i]['artists'])) {
									unset($output_lists[$i]);
								}
							}
							
							// Change keys if necessary
							if($output_lists[$i] && $args['keys']) {
								if($args['keys'] === 'associative') {
									$tmp_output_lists[$output_lists[$i]['id']] = $output_lists[$i];
								}
								elseif($args['keys'] === 'date') {
									list($y, $m, $d) = explode('-', $output_lists[$i]['date_occurred']);
									$tmp_output_lists[$y][$m][$d][] = $output_lists[$i];
								}
							}
							
						}
					}
					
					$output_lists = is_array($tmp_output_lists) && !empty($tmp_output_lists) ? $tmp_output_lists : $output_lists;*/
					
					if( is_numeric($args['id']) && $args['get'] === 'all' ) {
						$rslt_lists = $rslt_lists[0];
					}
					
					$output_lists = $rslt_lists;
					
				}
				
				// FORMAT OUTPUT -------------------------------------
				$output_lists = is_array($output_lists) ? $output_lists : [];
				
				return $output_lists;
			}
		}
	}
?>