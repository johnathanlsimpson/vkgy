<?php

include_once('../php/include.php');

class access_points {
	public $pdo;
	public $point_types;
	public $point_values;
	
	// ======================================================
	// Connect
	// ======================================================
	function __construct($pdo) {
		
		// Set connection
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		
		$this->pdo = $pdo;
		
		// Set point values
		$this->set_point_values();
		
		// Init badges
		$this->access_badge = new access_badge($pdo);
	}
	
	// ======================================================
	// Core function
	// ======================================================
	function access_points($args = []) {
		
		// Select
		switch($args['get']) {
			case 'basics':
				$select[] = 'COUNT(1) AS num_points';
				$select[] = 'SUM(users_points.point_value) AS points_value';
				$select[] = 'users_points.point_type';
				$select[] = 'MAX(users_points.date_occurred) AS date_occurred';
				break;
			case 'ranking':
				$select[] = 'SUM(users_points.point_value) AS points_value';
				$select[] = 'users_points.user_id';
				$select[] = 'users.username';
				break;
			case 'level':
				$select[] = 'SUM(users_points.point_value) AS points_value';
				$select[] = 'users_points.user_id';
				break;
		}
		
		// From
		switch(true) {
			case $from:
				break;
			default:
				$from = 'users_points';
		}
		
		// Join
		switch(true) {
			case $args['get'] === 'ranking':
				$join[] = 'LEFT JOIN users ON users.id=users_points.user_id';
				break;
		}
		
		// Where
		switch(true) {
			case is_numeric($args['user_id']):
				$where[] = 'users_points.user_id=?';
				$values[] = $args['user_id'];
				break;
				
			case is_array($args['user_ids']) && !empty($args['user_ids']):
				$where[] = substr( str_repeat( 'users_points.user_id=? OR ', count($args['user_ids']) ), 0, -4 );
				foreach($args['user_ids'] as $user_id) { $values[] = $user_id; }
				break;
				
			case preg_match('/'.'^\d{4}-\d{2}-\d{2}$'.'/', $args['start_date']) && preg_match('/'.'^\d{4}-\d{2}-\d{2}$'.'/', $args['end_date']):
				$where[] = 'users_points.date_occurred>=?';
				$where[] = 'users_points.date_occurred<=?';
				$values[] = $args['start_date'];
				$values[] = $args['end_date'];
				break;
		}
		
		// Group
		switch(true) {
			case is_numeric($args['user_id']):
				$group_by[] = 'users_points.point_type';
				break;
				
			default:
				$group_by[] = 'users_points.user_id';
				break;
		}
		
		// Order
		switch(true) {
			case strlen($args['order_by']):
				$order_by[] = sanitize( $args['order_by'] );
				break;
			case $args['get'] === 'ranking':
				$order_by[] = 'points_value DESC';
				break;
		}
		
		// Query
		$sql_access =
			'SELECT '.implode(', ', $select).' '.
			'FROM '.$from.' '.
			($join ? ' '.implode(', ', $join) : null).' '.
			($where ? 'WHERE '.implode(' AND ', $where) : null).' '.
			($group_by ?      'GROUP BY '.implode(', ', $group_by) : null).' '.
			($order_by ?      'ORDER BY '.implode(', ', $order_by) : null).' '.
			($args['limit'] ? 'LIMIT '.$args['limit'] : null);
		$stmt_access = $this->pdo->prepare($sql_access);
		$stmt_access->execute( $values );
		$points = $stmt_access->fetchAll();
		$num_points = count($points);
		
		// Additional data: level
		if($args['get'] === 'level') {
			for($i=0; $i<$num_points; $i++) {
				foreach($this->point_levels as $level => $minimum) {
					if($points[$i]['points_value'] >= $minimum) {
						$points[$i]['level'] = $level;
					}
				}
			}
		}
		
		// Additional data: level
		if($args['get'] === 'ranking') {
			for($i=0; $i<$num_points; $i++) {
				$user_ids[] = $points[$i]['user_id'];
			}
			
			$user_levels = $this->access_points([ 'user_ids' => $user_ids, 'get' => 'level', 'associative' => true ]);
			
			for($i=0; $i<$num_points; $i++) {
				$points[$i]['level'] = $user_levels[$points[$i]['user_id']]['level'];
			}
		}
		
		// Additional data: meta values
		if(is_numeric($args['user_id'])) {
			
			// Additional data: total num points
			$meta_array = [ 'points_value' => 0, 'point_type' => 'meta' ];
			for($i=0; $i<$num_points; $i++) {
				$meta_array['point_value'] += $points[$i]['points_value'];
				$meta_array['date_occurred'] = $meta_array['date_occurred'] > $points[$i]['date_occurred'] ? $meta_array['date_occurred'] : $points[$i]['date_occurred'];
			}
			
			// Additional data: get user level
			foreach($this->point_levels as $level => $minimum) {
				if($meta_array['point_value'] >= $minimum) {
					$meta_array['level'] = $level;
				}
			}
			
			// Additional data: get next level req
			if( $meta_array['level'] < end(array_keys($this->point_levels)) ) {
				$meta_array['next_level_at'] = $this->point_levels[ $meta_array['level'] + 1 ];
				$meta_array['next_level_progress'] = ( $meta_array['point_value'] / $meta_array['next_level_at'] ) * 100;
			}
			
		}
		
		// Format data: get point type name
		if($args['get'] === 'basics') {
			for($i=0; $i<$num_points; $i++) {
				$points[$i]['point_type'] = $this->point_types[$points[$i]['point_type']];
			}
		}
		
		// Format data: make associative if necessary
		if($args['associative']) {
			for($i=0; $i<$num_points; $i++) {
				
				if($args['get'] === 'basics') {
					$tmp_points[$points[$i]['point_type']] = $points[$i];
				}
				elseif($args['get'] === 'level') {
					$tmp_points[$points[$i]['user_id']] = $points[$i];
				}
				
			}
			
			$points = $tmp_points;
		}
		
		// Format data: add meta array
		if(is_numeric($args['user_id'])) {
			if($args['associative']) {
				$points['meta'] = $meta_array;
			}
			else {
				array_unshift($points, $meta_array);
				$num_points++;
			}
		}
		
		return $points;
	}
	
	// ======================================================
	// Set point values
	// ======================================================
	function set_point_values() {
		
		// Point levels
		$this->point_levels = [
			 1 => 0,
			 2 => 10,
			 3 => 20,
			 4 => 50,
			 5 => 100,
			 6 => 200,
			 7 => 300,
			 8 => 500,
			 9 => 1000,
			10 => 2000,
			11 => 3000,
			12 => 4000,
			13 => 5000,
			14 => 7500,
			15 => 10000,
			16 => 15000,
			17 => 20000,
			18 => 30000,
			19 => 40000,
			20 => 50000,
		];
		
		// All possible point types
		foreach([
			
			// Adding
			'added-artist'    => 1,
			'added-blog'      => 10,
			'added-comment'   => 1,
			'added-label'     => 1,
			'added-livehouse' => 1,
			'added-image'     => 1,
			'added-musician'  => 1,
			'added-release'   => 1,
			'added-video'     => 1,
			
			// Editing
			'edited-artist'   => 1,
			'edited-avatar'   => 1,
			'edited-blog'     => 1,
			'edited-label'    => 1,
			'edited-live'     => 1,
			'edited-musician' => 1,
			'edited-profile'  => 1,
			'edited-release'  => 1,
			
			// Rating
			'rated-artist'    => 1,
			'rated-release'   => 1,
			
			// Tagging
			'tagged-artist'   => 1,
			'tagged-release'  => 1,
			
			// Time-based
			'newest-release'          =>  1,
			'oldest-release'          =>  1,
			'editor-since'            => 10,
			'fan-since'               => 10,
			'member-since'            => 10,
			'vip-since'               => 20,
			
			// Likes
			'liked-comment'           =>  1,
			'comment-liked'           =>  1,
			
			// Miscellaneous
			'collected-release'       =>  1,
			'marked-for-sale'         =>  1,
			'wanted-release'          =>  1,
			'sold-release'            =>  1,
			
		] as $point_type => $point_value) {
			$this->point_types[] = $point_type;
			$this->point_values[$point_type] = $point_value;
		}
		
	}
	
	// ======================================================
	// Award points
	// ======================================================
	function award_points($args = []) {
		
		// Set defaults
		$args = array_merge([
			'user_id' => is_numeric($args['user_id']) ? $args['user_id'] : ($_SESSION['is_signed_in'] ? $_SESSION['user_id'] : null),
			'item_id' => 0,
			'point_type' => null,
			'date_occurred' => date('Y-m-d H:i:s'),
			'allow_multiple' => true,
		], $args);
		
		$args['check_date'] = $args['allow_multiple'] ? '9999-12-31' : substr($args['date_occurred'], 0, 10).' 00:00:00';
		
		// Clean arguments
		foreach($args as $arg_key => $arg) {
			$args[$arg_key] = sanitize($arg);
		}
		
		// If user and type specified, continue
		if(
			is_numeric($args['user_id']) && 
			strlen($args['point_type']) && 
			in_array($args['point_type'], $this->point_types)
		) {
			
			// Set point type and num points
			$num_points = $this->point_values[$args['point_type']];
			$point_type = array_search($args['point_type'], $this->point_types);
			
			// Check if points already rewarded for this action on this day
			$sql_check = 'SELECT 1 FROM users_points WHERE user_id=? AND point_type=? AND item_id=? AND date_occurred>=?';
			$stmt_check = $this->pdo->prepare($sql_check);
			$stmt_check->execute([ $args['user_id'], $point_type, $args['item_id'], $args['check_date'] ]);
			$rslt_check = $stmt_check->fetchColumn();
			
			if($rslt_check) {
			}
			
			// Insert points
			else {
				
				$sql_add = 'INSERT INTO users_points (user_id, point_type, point_value, item_id, date_occurred) VALUES (?, ?, ?, ?, ?)';
				$stmt_add = $this->pdo->prepare($sql_add);
				if($stmt_add->execute([ $args['user_id'], $point_type, $num_points, $args['item_id'], $args['date_occurred'] ])) {
					
					// Send data to badge checker, in case point unlocked new badge
					$this->access_badge->check_badge([
						'user_id' => $args['user_id'],
						'point_type' => $point_type,
						'point_type_name' => $args['point_type'],
					]);
					
				}
				
			}
			
		}
	}
	
}