<?php
include_once('../php/include.php');
	
class access_badge {
	private $pdo;

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
		if($_SESSION['loggedIn'] && !$_SESSION['hide_badge_notices']) {
			
			// Get unseen badge, if exists
			$unseen_badge = $this->access_badge([ 'user_id' => $_SESSION['userID'], 'is_unseen' => true, 'get' => 'badge', 'limit' => 1 ]);
			
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
					$stmt_seen->execute([ 0, $_SESSION['userID'], $unseen_badge['id'] ]);
				}
			}
		}
		
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
	// Award badge
	// ======================================================
	function award_badge($friendly, $level = null) {
		
		// Check that badge name provided
		if(strlen($friendly)) {
			
			// Check that user signed in
			if($_SESSION['loggedIn']) {
				
				$sql_award = 'INSERT INTO users_badges (user_id, unique_id, level, is_unseen, badge_id) SELECT ?, CONCAT(?, "-", badges.id), ?, ?, badges.id FROM badges WHERE badges.friendly=? ON DUPLICATE KEY UPDATE users_badges.level=?, users_badges.is_unseen=?';
				$stmt_award = $this->pdo->prepare($sql_award);
				$stmt_award->execute([ $_SESSION['userID'], $_SESSION['userID'], $level, 1, $friendly, $level, 1 ]);
			}
		}
	}

	// ======================================================
	// Just for reference, badge conditions
	// ======================================================
	private $badge_minimums = [
		'comments' => [1, 5, 50, 100],
		'additions' => [1, 5, 50, 100],
		'patron' => ['$1', '$5', '$15', '6 months'],
		'collector' => [10, 50, 100, 1000],
		'avatar' => [1],
	];
}