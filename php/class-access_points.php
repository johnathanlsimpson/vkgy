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
		
	}
	
	// ======================================================
	// Set point values
	// ======================================================
	function set_point_values() {
		
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