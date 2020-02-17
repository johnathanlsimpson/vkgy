<?php
include_once('../php/include.php');
	
class access_badge {
	private $pdo;
	private $badge_attributes;
	private $num_badges;

	// ======================================================
	// Connect
	// ======================================================
	function __construct($pdo) {
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		
		$this->pdo = $pdo;
		
		$this->init_badges();
	}

	// ======================================================
	// Get badge info
	// ======================================================
	function access_badge($input) {
		
		// Select
		if(1) {
			$sql_select = [
				'badge' => [
					'badges.id',
					'badges.friendly',
					'badges.name',
					'badges.date_occurred',
					'badges.description',
					'badges.description_level_1',
					'badges.description_level_2',
					'badges.description_level_3',
				],
				'user_badge' => [
					'badges.id',
					'badges.friendly',
					'badges.name',
					'users_badges.date_occurred',
					'users_badges.level',
					'users_badges.is_unseen',
					'(CASE WHEN users_badges.level = 1 THEN badges.description_level_1 WHEN users_badges.level = 2 THEN badges.description_level_2 WHEN users_badges.level = 3 THEN badges.description_level_3 ELSE badges.description END) AS description',
				],
				'user_list' => [
					'users.username',
					'users_badges.date_occurred',
					'users_badges.level',
				],
			];
			// From
			$sql_from = [
				'badges' => 'badges',
				'user_badges' => 'users_badges',
			];
			// Join
			$sql_join = [
				'badges' => 'badges ON badges.id=users_badges.badge_id',
				'users' => 'users ON users.id=users_badges.user_id',
			];
			// Where
			$sql_where = [
				'badge_id' => 'badges.id=?',
				'user_id' => 'users_badges.user_id=?',
				'user_badge' => 'users_badges.badge_id=?',
				'is_unseen' => 'users_badges.is_unseen=?',
			];
			// Values
			$sql_values = [
				'badge_id' => $input['id'],
				'user_id' => $input['user_id'],
				'user_badge' => $input['id'],
				'is_unseen' => 1,
			];
		}
		
		// Choose query elements
		if(1) {
			// -> List of users w/ specific badge
			if(is_numeric($input['id']) && $input['get'] === 'users') {
				$sql_select = $sql_select['user_list'];
				$sql_from = $sql_from['user_badges'];
				$sql_join = $sql_join['users'];
				$sql_where = $sql_where['user_badge'];
				$sql_values = $sql_values['user_badge'];
			}
			// -> Specific user's version of specific badge
			elseif(is_numeric($input['id']) && is_numeric($input['user_id']) && $input['get'] === 'badge') {
				$sql_select = $sql_select['user_badge'];
				$sql_from = $sql_from['user_badges'];
				$sql_join = $sql_join['badges'];
				$sql_where = [ $sql_where['user_id'], $sql_where['user_badge'] ];
				$sql_values = [ $sql_values['user_id'], $sql_values['user_badge'] ];
			}
			// -> Specific user's unseen badges
			elseif(is_numeric($input['user_id']) && $input['is_unseen'] && $input['get'] === 'badge') {
				$sql_select = $sql_select['user_badge'];
				$sql_from = $sql_from['user_badges'];
				$sql_join = $sql_join['badges'];
				$sql_where = [ $sql_where['user_id'], $sql_where['is_unseen'] ];
				$sql_values = [ $sql_values['user_id'], $sql_values['is_unseen'] ];
			}
			// -> All of specific user's badges
			elseif(is_numeric($input['user_id']) && $input['get'] === 'badge') {
				$sql_select = $sql_select['user_badge'];
				$sql_from = $sql_from['user_badges'];
				$sql_join = $sql_join['badges'];
				$sql_where = $sql_where['user_id'];
				$sql_values = $sql_values['user_id'];
			}
			// -> Generic version of specific badge
			elseif(is_numeric($input['id']) && $input['get'] === 'badge') {
				$sql_select = $sql_select['badge'];
				$sql_from = $sql_from['badges'];
				$sql_join = null;
				$sql_where = $sql_where['badge_id'];
				$sql_values = $sql_values['badge_id'];
			}
			// -> List of all generic badges
			elseif($input['get'] === 'badge') {
				$sql_select = $sql_select['badge'];
				$sql_from = $sql_from['badges'];
				$sql_join = null;
				$sql_where = null;
				$sql_values = null;
			}
		}
		
		// Build query
		$sql_lines[] = 'SELECT '.implode(', ', $sql_select);
		$sql_lines[] = 'FROM '.$sql_from;
		$sql_lines[] = $sql_join ? 'LEFT JOIN '.(is_array($sql_join) ? implode(' LEFT JOIN ', $sql_join) : $sql_join) : null;
		$sql_lines[] = $sql_where ? 'WHERE '.(is_array($sql_where) ? implode(' AND ', $sql_where) : $sql_where) : null;
		$sql_lines[] = is_numeric($input['limit']) ? 'LIMIT '.$input['limit'] : null;
		
		// Run query
		if(is_array($sql_lines) && !empty($sql_lines)) {
			$values_get = is_array($sql_values) ? $sql_values : (strlen($sql_where) ? [ $sql_values ] : null);
			$sql_get = implode(' ', array_filter($sql_lines));
			
			if($stmt_get = $this->pdo->prepare($sql_get)) {
				$stmt_get->execute($values_get);
				$rslt_get = $stmt_get->fetchAll();
			}
		}
		
		if(is_array($rslt_get) && !empty($rslt_get)) {
			if($input['limit'] === 1) {
				$rslt_get = reset($rslt_get);
			}
		}
		
		return $rslt_get;
	}

	// ======================================================
	// Notify user if new badge unlocked
	// ======================================================
	function notify_if_new_badge() {
		
		// If user is signed in
		/*if($_SESSION['is_signed_in'] && !$_SESSION['hide_badge_notices']) {
			
			// Get unseen badge, if exists
			$unseen_badge = $this->access_badge([ 'user_id' => $_SESSION['user_id'], 'is_unseen' => true, 'get' => 'badge', 'limit' => 1 ]);
			
			// If unseen badge found...
			if(is_array($unseen_badge) && !empty($unseen_badge)) {
				
				// Render badge
				$rendered_badge = $this->render_badge($unseen_badge);
				
				// Plop it into notification partial
				ob_start();
				
				include('../badges/partial-notification.php');
				
				$output = ob_get_clean();
				
				// Mark as seen
				$sql_seen = 'UPDATE users_badges SET is_unseen=? WHERE user_id=? AND badge_id=? LIMIT 1';
				if($stmt_seen = $this->pdo->prepare($sql_seen)) {
					$stmt_seen->execute([ 0, $_SESSION['user_id'], $unseen_badge['id'] ]);
				}
			}
		}*/
		
		return $output;
	}

	// ======================================================
	// Render badge from raw data
	// ======================================================
	function render_badge($badge) {
		
		// If at least name and friendly name provided
		if(strlen($badge['friendly']) && strlen($badge['name'])) {
			
			// Send to badge template
			ob_start();
			
			include('../badges/partial-badge.php');
			
			$output = ob_get_clean();
		}
		
		return $output;
	}

	// ======================================================
	// Check if badge should be awarded
	// ======================================================
	public function check_badge($args = []) {
		
		if(
			is_numeric($args['user_id']) &&
			is_numeric($args['point_type']) &&
			strlen($args['point_type_name'])
		) {
			
			// Clean type name
			$point_type_name = sanitize($args['point_type_name']);
			
			// Check if there's a badge awarded for this point type
			foreach($this->badges as $badge_key => $badge) {
				if(
					( is_array($badge['based_on_point_type']) && in_array($point_type_name, $badge['based_on_point_type']) )
					||
					( !is_array($badge['based_on_point_type']) && $badge['based_on_point_type'] === $point_type_name )
				) {
					$badge_id = $badge['id'];
					$badge_friendly = $badge_key;
					$badge_exists = true;
					break;
				}
			}
			
			// If badge exists for this type, check if user gets it
			if($badge_exists) {
				
				// Check user's current badge status for this badge
				$sql_badge = 'SELECT * FROM users_badges WHERE user_id=? AND badge_id=?';
				$stmt_badge = $this->pdo->prepare($sql_badge);
				$stmt_badge->execute([ $args['user_id'], $badge_id ]);
				$rslt_badge = $stmt_badge->fetch();
				
				// If user doesn't have badge, or doesn't have max level, check if they qualify to gain
				if( empty($rslt_badge) || $rslt_badge['level'] < count($this->badges[$badge_friendly]['level_points']) ) {
					
					// Get user's current number of points for this type
					$sql_count = 'SELECT SUM(point_value) AS num_points FROM users_points WHERE user_id=? AND point_type=?';
					$stmt_count = $this->pdo->prepare($sql_count);
					$stmt_count->execute([ $args['user_id'], $args['point_type'] ]);
					$num_points = $stmt_count->fetchColumn();
					
					// Loop through badge requirements backward, and see which one user qualifies for
					foreach( $this->badges[$badge_friendly]['level_points'] as $badge_level => $badge_points ) {
						if($badge_points <= $num_points) {
							$new_level = $badge_level;
						}
					}
					
					// If user qualifies for a new level...
					if(!is_numeric($rslt_badge['level']) || $new_level > $rslt_badge['level']) {
						$this->award_badge($args['user_id'], $badge_friendly, $new_level);
					}
					
				}
				
			}
			
		}
		
	}

	// ======================================================
	// Award badge
	// ======================================================
	function award_badge($user_id, $friendly, $level = null) {
		
		// Check that badge name provided
		if(is_numeric($user_id) && strlen($friendly)) {
			
			$sql_award = '
				INSERT INTO
					users_badges
					(user_id, unique_id, level, is_unseen, badge_id)
				SELECT
					?,
					CONCAT(?, "-", badges.id),
					?,
					?,
					badges.id
				FROM
					badges
				WHERE
					badges.friendly=?
				ON DUPLICATE KEY UPDATE
					users_badges.level=?,
					users_badges.is_unseen=?';
			$stmt_award = $this->pdo->prepare($sql_award);
			$stmt_award->execute([ $user_id, $user_id, $level, 1, $friendly, $level, 1 ]);
			
		}
	}

	// ======================================================
	// Set attributes for badges
	// ======================================================
	function init_badges() {
		$badges = [
			'psycho-letter' => [
				'name' => 'Psycho Letter',
				'based_on_point_type' => 'added-comment',
				'level_points' => [1, 5, 50, 100],
			],
			'kokuhaku-page' => [
				'name' => 'Kokuhaku Page',
				'based_on_point_type' => ['added-artist', 'added-release'],
				'level_points' => [1, 10, 50, 150],
			],
			'love-parade' => [
				'name' => 'Love Parade',
				'based_on_point_type' => 'vip-since',
				'level_points' => [1, 6, 12, 24],
			],
			'mad-collector' => [
				'name' => 'Mad Collector',
				'based_on_point_type' => 'collected-release',
				'level_points' => [1, 10, 50, 1000],
			],
			'mascade-face' => [
				'name' => 'Mascade Face',
				'based_on_point_type' => 'edited-avatar',
				'level_points' => [1],
			]
		];
		$this->badges = $badges;
		
		$sql_badges = 'SELECT id, friendly FROM badges';
		$stmt_badges = $this->pdo->prepare($sql_badges);
		$stmt_badges->execute();
		$rslt_badges = $stmt_badges->fetchAll();
		foreach($rslt_badges as $badge) {
			$this->badges[$badge['friendly']]['id'] = $badge['id'];
		}
		
		$num_badges = count($badges);
		$this->num_badges = $num_badges;
	}
}