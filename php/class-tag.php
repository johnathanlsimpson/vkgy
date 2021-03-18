<?php

include_once('../php/include.php');
include_once('../php/class-vote.php');

class tag {
	
	public const allowed_item_types = [
		'artist',
		'musician',
		'release',
	];
	
	private const item_type_tables = [
		'blog' => 'blog',
	];
	
	public const tag_types = [
		'artist'   => [ 'scenes', 'sounds like', 'other tags', 'moderator tags', 'disputed' ],
		'musician' => [ 'moderation', 'disputed' ],
		'release'  => [ 'styles', 'other', 'moderation', 'disputed' ]
	];
	
	public const allowed_directions = [
		'upvote',
		'downvote',
		'pin',
		'hide',
	];

	// ======================================================
	// Connect
	// ======================================================
	function __construct($pdo) {
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		
		$this->pdo = $pdo;
		$this->access_vote = new vote($this->pdo);
	}
	
	// ======================================================
	// Core function
	// ======================================================
	public function access_tag($args = []) {
		
		$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
		
		// PRE -------------------------------------------------
		$args['hide_tags'] = isset($args['hide_tags']) ? $args['hide_tags'] : ( is_numeric($args['item_id']) && $args['get'] != 'all' ? true : false );
		$items_table       = self::item_type_tables[ $args['item_type'] ] ?: $args['item_type'].'s';
		$tags_table        = 'tags_'.$items_table;      // e.g. tags_artists
		$items_tags_table  = $items_table.'_tags';      // e.g. artists_tags
		$item_id_column    = $args['item_type'].'_id';  // e.g. artist_id
		
		// SELECT ----------------------------------------------
		if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
			$sql_select[] = 'tags.id';
			$sql_select[] = 'tags.name';
			$sql_select[] = 'tags.romaji';
			$sql_select[] = 'tags.friendly';
			$sql_select[] = 'tags.type';
			$sql_select[] = 'tags.requires_permission';
			$sql_select[] = 'tags.is_votable';
		}
		
		if( is_numeric($args['item_id']) ) {
			$sql_select[] = 'items_tags.id AS items_tags_id';
			$sql_select[] = 'items_tags.date_occurred';
			$sql_select[] = 'items_tags.score';
			$sql_select[] = 'items_tags.mod_score';
			$sql_select[] = 'IF( (items_tags.score > 0 AND items_tags.mod_score > -1) OR (items_tags.mod_score = 1), 1, 0 ) AS is_tagged';
		}
		
		// If not checking for specific item, get num items tagged
		else {
			$sql_select[] = 'COUNT(items_tags.tag_id) AS num_tagged';
		}
		
		// FROM ------------------------------------------------
		$sql_from = $tags_table.' AS tags';
		
		// JOIN ------------------------------------------------
		if( is_numeric($args['item_id']) ) {
			$sql_join[] = 'LEFT JOIN '.$items_tags_table.' items_tags ON items_tags.tag_id=tags.id AND items_tags.'.$item_id_column.'=?';
			$sql_values[] = $args['item_id'];
		}
		
		// If not checking for specific item, get num items tagged
		else {
			$sql_join[] = 'LEFT JOIN '.$items_tags_table.' items_tags ON items_tags.tag_id=tags.id';
		}
		
		// WHERE -----------------------------------------------
		if( $args['hide_tags'] ) {
			$sql_where[] = 'items_tags.date_occurred IS NOT NULL';
			$sql_where[] = 'items_tags.mod_score>?';
			$sql_values[] = -1;
		}
		
		// GROUP -----------------------------------------------
		if( !is_numeric($args['item_id']) ) {
			$sql_group[] = 'tags.id';
		}
		
		// ORDER -----------------------------------------------
		$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'tags.type ASC, tags.friendly ASC' ];
		
		// BUILD QUERY -----------------------------------------
		$sql_tags = '
			SELECT '.implode(', ', $sql_select).'
			FROM '.$sql_from.' '.
			(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
			(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
			(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' 
			ORDER BY '.implode(', ', $sql_order).'
		';
		$stmt_tags = $this->pdo->prepare($sql_tags);
		
		// EXECUTE QUERY ---------------------------------------
		if($stmt_tags->execute($sql_values)) {
			
			// Get result
			$rslt_tags = $stmt_tags->fetchAll();
			
			// Get additional data
			if(is_array($rslt_tags) && !empty($rslt_tags)) {
				
				// Restructure by id, save id for later queries
				foreach($rslt_tags as $rslt_tag) {
					
					$tags[ $rslt_tag['id'] ] = $rslt_tag;
					$tag_ids[] = $rslt_tag['id'];
					
					// If getting tags by item id, also save join's id for later queries
					if( is_numeric($args['item_id']) && is_numeric($rslt_tag['items_tags_id']) ) {
						
						// Join id as key, tag id as value, so we can easily add the join's score to the tag array
						$items_tags_ids[ $rslt_tag['items_tags_id'] ] = $rslt_tag['id'];
						
					}
					
				}
				
				// EXTRAS --------------------------------------------
				
				// Get scores by passing items_tags_id to access_vote
				if( is_numeric($args['item_id']) ) {
					
					if( in_array($items_tags_table, $this->access_vote::allowed_item_types) && is_array($items_tags_ids) && !empty($items_tags_ids) ) {
						
						$votes = $this->access_vote->access_vote([ 'item_type' => $items_tags_table, 'item_ids' => array_keys($items_tags_ids), 'get' => 'basics' ]);
						
					}
					
					if( is_array($votes) && !empty($votes) ) {
						foreach($votes as $vote) {
							
							// Get ID of e.g. artists_tags join
							$items_tags_id = $vote['item_id'];
							
							// Then using that ID, get the ID of the actual associated tag
							$tag_id = $items_tags_ids[ $items_tags_id ];
							
							// If we want tags with low scores to be filtered out, do that here
							if( $args['hide_tags'] && !$score && !$tags[$tag_id]['mod_score'] ) {
								
								unset( $tags[$tag_id] );
								
							}
							
							// Otherwise, add score to tag
							else {
								
								$tags[$tag_id] = array_merge( $tags[$tag_id], $vote );
								
								// If score is negative, and mod score hasn't overruled, make sure is_tagged is false
								if( $vote['score'] < 1 && $tags[$tag_id]['mod_score'] != 1 ) {
									$tags[$tag_id]['is_tagged'] = 0;
								}
								
							}
							
						}
						
					}
				}
				
				// Restructure by type and name
				if( !$args['flat'] ) {
					
					foreach($tags as $tag) {
						
						$tag_type = self::tag_types[ $args['item_type'] ][ $tag['type'] ];
						
						// Separate by is_tagged status if necessary
						if( $args['separate'] ) {
							
							if( $tag['is_tagged'] ) {
								$tmp_tags['tagged'][ $tag_type ][] = $tag;
							}
							else {
								$tmp_tags['untagged'][ $tag_type ][] = $tag;
							}
							
						}
						
						// Otherwise just separate by type
						else {
							$tmp_tags[ $tag_type ][] = $tag;
						}
						
					}
					
					$tags = $tmp_tags;
					
				}
				
			}
			
		}
		
		// RETURN ----------------------------------------------
		$tags = is_array($tags) ? $tags : [];
		
		if( $args['flat'] ) {
			$tags = array_values($tags);
		}
		
		return $tags;
		
	}
	
	// ======================================================
	// Pin/hide tag
	// ======================================================
	public function pin_or_hide($args = []) {
		
		// Set up vars
		$action     = $args['action'] === 'add' ? 'add' : 'remove';
		$direction  = in_array($args['direction'], self::allowed_directions) ? $args['direction'] : null;
		$item_type  = sanitize($args['item_type']);
		$item_id    = is_numeric($args['item_id']) ? $args['item_id'] : null;
		$mod_score  = $action === 'remove' ? 0 : ( $direction === 'pin' ? 1 : -1 );
		$tags_table = 'tags_'.str_replace('_tags', '', $item_type);
		
		// Only moderators can pin/hide
		if( $_SESSION['can_approve_data'] ) {
			
			// Make sure necessary info provided
			if( $action && $direction && $item_type && is_numeric($item_id) ) {
				
				// Make sure item exists
				$sql_check = 'SELECT items_tags.id, tags.is_votable FROM '.$item_type.' items_tags LEFT JOIN '.$tags_table.' tags ON tags.id=items_tags.tag_id WHERE items_tags.id=?';
				$stmt_check = $this->pdo->prepare($sql_check);
				$stmt_check->execute([ $item_id ]);
				$rslt_check = $stmt_check->fetch();
				
				if( $rslt_check && is_numeric($rslt_check['id']) ) {
					
					// Pin/hide
					$sql_update = 'UPDATE '.$item_type.' items_tags SET items_tags.mod_score=? WHERE items_tags.id=? LIMIT 1';
					$stmt_update = $this->pdo->prepare($sql_update);
					
					if( $stmt_update->execute([ $mod_score, $item_id ]) ) {
						
						// If adding pin/hide, and tag is votable, make user's vote match
						if( $action === 'add' && $rslt_check['is_votable'] ) {
							
							$vote_result = $this->access_vote->vote([
								'item_type' => $item_type,
								'item_id' => $item_id,
								'action' => 'add',
								'direction' => $args['direction'] === 'hide' ? 'downvote' : 'upvote',
								'user_id' => $_SESSION['user_id'],
							]);
							
							if( $vote_result['status'] === 'success' ) {
								$output['status'] = 'success';
							}
							else {
								$output['result'] = 'Couldn\'t make vote match pinned status.';
							}
							
						}
						
						// If removing pin/hide, make sure to pass success variable
						else {
							$output['status'] = 'success';
						}
						
					}
					else {
						$output['result'] = 'Couldn\'t pin/hide.';
					}
					
				}
				else {
					$output['result'] = 'Couldn\'t find item.';
				}
				
			}
			else {
				$output['result'] = 'Missing necessary information.';
			}
			
		}
		else {
			$output['result'] = 'Must be able to approve data to pin or hide tags.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		
		return $output;
		
	}
	
	// ======================================================
	// Add/remove tag
	// ======================================================
	public function update($args = []) {
		
		// Set up vars
		$action    = $args['action'] === 'add' ? 'add' : 'remove';
		$item_type = in_array($args['item_type'], self::allowed_item_types) ? $args['item_type'] : null;
		$item_id   = is_numeric($args['item_id']) ? $args['item_id'] : null;
		$tag_id    = is_numeric($args['tag_id']) ? $args['tag_id'] : null;
		$user_id   = $_SESSION['user_id'];
		
		// Set up tables
		$items_table       = self::item_type_tables[ $args['item_type'] ] ?: $args['item_type'].'s'; // e.g. artists
		$tags_table        = 'tags_'.$items_table;     // e.g. tags_artists
		$items_tags_table  = $items_table.'_tags';     // e.g. artists_tags
		$item_id_column    = $args['item_type'].'_id'; // e.g. artist_id
		
		// Only registered users can tag
		if( $_SESSION['is_signed_in'] ) {
			
			// Get tag attributes
			$sql_check = '
				SELECT tags.type, tags.requires_permission, IF(items_tags.date_occurred, 1, 0) AS is_tagged, tags.is_votable, items_tags.id AS items_tags_id, items_tags.mod_score
				FROM '.$tags_table.' tags
				LEFT JOIN '.$items_tags_table.' items_tags ON items_tags.tag_id=tags.id AND items_tags.'.$item_id_column.'=?
				WHERE tags.id=?
				LIMIT 1';
			$stmt_check = $this->pdo->prepare($sql_check);
			$stmt_check->execute([ $item_id, $tag_id ]);
			$rslt_check = $stmt_check->fetch();
			
			// Make sure tag exists
			if( is_array($rslt_check) && !empty($rslt_check) ) {
				
				// Get tag type name
				$tag_type = self::tag_types[ $args['item_type'] ][ $rslt_check['type'] ];
				
				// Make sure user has permission
				if( !$rslt_check['requires_permission'] || $_SESSION[ $rslt_check['requires_permission'] ] ) {
					
					// Add tag
					if( $action == 'add' ) {
						
						// If item already tagged, save id, and check if we need to change mod score
						if( $rslt_check['is_tagged'] ) {
							
							// Save id
							$items_tags_id = $rslt_check['items_tags_id'];
							
							// If mod_score < 0, but mod is adding it back, then undo mod score
							if( $rslt_check['mod_score'] < 0 && $_SESSION['can_approve_data'] ) {
								
								$sql_mod_score = 'UPDATE '.$items_tags_table.' items_tags SET items_tags.mod_score=? WHERE items_tags.id=? LIMIT 1';
								$stmt_mod_score = $this->pdo->prepare($sql_mod_score);
								
								if( $stmt_mod_score->execute([ 0, $items_tags_id ]) ) {
								}
								else {
									$output['result'] = 'Couldn\'t undo mod score.';
								}
								
							}
							
						}
						
						// If not already tagged, insert tag into items_tags
						else {
							
							$sql_add = 'INSERT INTO '.$items_tags_table.' ('.$item_id_column.', tag_id, user_id) VALUES (?, ?, ?)';
							$stmt_add = $this->pdo->prepare($sql_add);
							$values_add = [ $item_id, $tag_id, $user_id ];
							
							if($stmt_add->execute($values_add)) {
								$output['status'] = 'success';
								$output['result'] = 'Tagged.';
								$items_tags_id = $this->pdo->lastInsertId();
							}
							else {
								$output['result'] = 'Couldn\'t tag item.';
							}
							
						}
						
						// If this is a votable tag, add an upvote
						if( is_numeric($items_tags_id) && $rslt_check['is_votable'] ) {
							
							$vote_result = $this->access_vote->vote([
								'item_type' => $items_tags_table,
								'item_id' => $items_tags_id,
								'action' => 'add',
								'direction' => 'upvote',
								'user_id' => $user_id,
							]);
							
							if( $vote_result['status'] === 'success' ) {
								$output['status'] = 'success';
								$output['result'] = 'Voted.'.print_r($rslt_check, true);
							}
							else {
								$output['result'] = 'Couldn\'t vote.';
							}
							
						}
						
						// If tag can only be used by mods, then it can only be pinned or unpinned
						if( is_numeric($items_tags_id) && $rslt_check['requires_permission'] === 'can_approve_data' && $_SESSION['can_approve_data'] ) {
							
							$pin_result = $this->pin_or_hide([
								'item_type' => $items_tags_table,
								'item_id' => $items_tags_id,
								'direction' => 'pin',
								'action' => 'add',
							]);
							
							if( $pin_result['status'] === 'success' ) {
								$output['status'] = 'success';
								$output['result'] = 'Pinned.';
							}
							else {
								$output['result'] = 'Couldn\'t pin.';
							}
							
						}
						
					}
					
					// Remove tag
					if( $action == 'remove' ) {
						
						$sql_remove = 'DELETE FROM '.$items_tags_table.' WHERE '.$item_id_column.'=? AND tag_id=?';
						$stmt_remove = $this->pdo->prepare($sql_remove);
						
						if($stmt_remove->execute([ $item_id, $tag_id ])) {
							$output['status'] = 'success';
						}
						else {
							$output['result'] = 'Couldn\'t delete.';
						}
						
					}
					
				}
				else {
					$output['result'] = 'You don\'t have permission to use that tag.';
				}
				
			}
			else {
				$output['result'] = 'Tag doesn\'t exist.';
			}
			
		}
		else {
			$output['result'] = 'Please sign in to tag items.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		
		return $output;
		
	}
	
}