<?php

include_once('../php/include.php');

class vote {
	
	public const allowed_item_types = [
		'artists_tags',
		'musicians_tags',
		'releases_tags',
		'development',
	];
	
	public const allowed_actions = [
		'add',
		'remove',
	];
	
	public const allowed_directions = [
		'upvote',
		'downvote',
	];

	// ======================================================
	// Connect
	// ======================================================
	function __construct($pdo) {
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		
		$this->pdo = $pdo;
	}
	
	// ======================================================
	// Get votes
	// ======================================================
	public function access_vote($args = []) {
		
		$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
		
		// PRE -------------------------------------------------
		if( is_numeric($args['item_id']) ) {
			$args['item_ids'][] = $args['item_id'];
			unset($args['item_id']);
		}
		
		// PRE -------------------------------------------------
		$votes_table = 'votes_'.$args['item_type']; // e.g. votes_artists_tags
		
		// SELECT ----------------------------------------------
		if( $args['get'] === 'basics' ) {
			$sql_select[] = 'votes.item_id';
			$sql_select[] = 'SUM(votes.score) AS score';
			$sql_select[] = 'COUNT(votes.score) AS num_votes';
		}
		if( $args['get'] === 'basics' && $_SESSION['is_signed_in'] ) {
			$sql_select[] = 'IF(user_votes.score, user_votes.score, 0) AS user_score';
		}
		if( $args['get'] === 'all' ) {
			$sql_select[] = 'votes.item_id';
			$sql_select[] = 'votes.score';
			$sql_select[] = 'votes.user_id';
			$sql_select[] = 'votes.date_occurred';
		}
		
		// FROM ------------------------------------------------
		$sql_from = $votes_table.' AS votes';
		
		// JOIN ------------------------------------------------
		if( $args['get'] === 'basics' && $_SESSION['is_signed_in'] ) {
			$sql_join[] = 'LEFT JOIN '.$votes_table.' user_votes ON user_votes.item_id=votes.item_id AND user_votes.user_id=?';
			$sql_values[] = $_SESSION['user_id'];
		}
		
		// WHERE -----------------------------------------------
		if( is_array($args['item_ids']) && !empty($args['item_ids']) ) {
			$sql_where[] = 'votes.item_id IN ('. substr( str_repeat( '?, ', count($args['item_ids']) ), 0, -2) .')';
			$sql_values = array_merge($sql_values, $args['item_ids']);
		}
		
		// GROUP BY --------------------------------------------
		if( is_array($args['item_ids']) && !empty($args['item_ids']) ) {
			$sql_group[] = 'votes.item_id';
		}
		
		// ORDER -----------------------------------------------
		$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'votes.date_occurred DESC' ];
		
		// BUILD QUERY -----------------------------------------
		$sql_votes = '
			SELECT '.implode(', ', $sql_select).'
			FROM '.$sql_from.' '.
			(is_array($sql_join)  && !empty($sql_join) ? implode(' ', $sql_join) : null).' '.
			(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
			(is_array($sql_group) && !empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).'
			ORDER BY '.implode(', ', $sql_order).'
		';
		$stmt_votes = $this->pdo->prepare($sql_votes);
		
		// EXECUTE QUERY ---------------------------------------
		if($stmt_votes->execute($sql_values)) {
			
			// Get result
			$votes = $stmt_votes->fetchAll();
			
		}
		
		// RETURN ----------------------------------------------
		if( !is_array($votes) ) {
			$votes = [];
		}
		
		return $votes;
		
	}
	
	// ======================================================
	// Vote
	// ======================================================
	public function vote($args = []) {
		
		if( $_SESSION['is_signed_in'] ) {
			
			if( is_numeric($args['item_id']) ) {
				
				if( in_array($args['item_type'], self::allowed_item_types) && in_array($args['action'], self::allowed_actions) &&  in_array($args['direction'], self::allowed_directions) ) {
					
					// Each item type has its own votes table
					$votes_table = 'votes_'.$args['item_type'];
					$item_table = $args['item_type'];
					
					// Check existing votes for item
					$sql_score = 'SELECT SUM(score) AS score, COUNT(1) AS num_votes FROM '.$votes_table.' WHERE item_id=? AND user_id!=? GROUP BY item_id';
					$stmt_score = $this->pdo->prepare($sql_score);
					$stmt_score->execute([ $args['item_id'], $args['user_id'] ]);
					$rslt_score = $stmt_score->fetch();
					
					// Save some stuff for later
					$num_votes = ( $rslt_score['num_votes'] ?: 0 ) + 1;
					$extant_score = is_numeric($rslt_score['score']) ? $rslt_score['score'] : 0;
					
					// Score is based on direction and action
					$score = ( $args['action'] === 'add' ? ( $args['direction'] === 'upvote' ? 1 : -1 ) : 0 );
					$total_score = $extant_score + $score;
					
					// Check item exists
					$sql_check_item = 'SELECT 1 FROM '.$item_table.' WHERE id=? LIMIT 1';
					$stmt_check_item = $this->pdo->prepare($sql_check_item);
					$stmt_check_item->execute([ $args['item_id'] ]);
					$rslt_check_item = $stmt_check_item->fetchColumn();
					
					// If item exists, do vote
					if($rslt_check_item) {
						
						$sql_update = 'INSERT INTO '.$votes_table.' (item_id, user_id, score) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE score=?, date_occurred=?';
						$stmt_update = $this->pdo->prepare($sql_update);
						
						if( $stmt_update->execute([ $args['item_id'], $args['user_id'], $score, $score, date('Y-m-d H:i:s') ]) ) {
							
							$output['score'] = $total_score;
							$output['num_votes'] = $num_votes;
							
							// If voting on a tag, update the tag's score column for quick reference
							if( strpos($args['item_type'], 'tag') !== false ) {
								
								// Update items_tags user_score for quick reference
								$sql_items_tags = 'UPDATE '.$args['item_type'].' items_tags SET items_tags.score=? WHERE items_tags.id=? LIMIT 1';
								$stmt_items_tags = $this->pdo->prepare($sql_items_tags);
								
								if( $stmt_items_tags->execute([ $total_score, $args['item_id'] ]) ) {
									$output['status'] = 'success';
								}
								else {
									$output['result'] = 'Couldn\'t update score.';
								}
								
							}
							
							// If not voting on tag, be sure to pass success since vote went through
							else {
								$output['status'] = 'success';
							}
							
						}
						else {
							$output['result'] = 'Couldn\'t update vote.';
						}
						
					}
					else {
						$output['result'] = 'Item doesn\'t exist.';
					}
					
				}
				else {
					$output['result'] = 'Action not allowed.';
				}
				
			}
			else {
				$output['result'] = 'No item specified.';
			}
			
		}
		else {
			$output['result'] = 'Please sign in to vote.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		
		return $output;
	}
	
}