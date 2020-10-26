<?php

include_once('../php/include.php');

// This really needs to be in a class
$allowed_item_types = [
	'release',
	'video'
];

$name = sanitize($_POST['name']);
$friendly = friendly($name);
$item_id = is_numeric($_POST['item_id']) ? $_POST['item_id'] : null;
$item_type = array_search($_POST['item_type'], $allowed_item_types);

if( $_SESSION['is_signed_in'] && strlen($name) ) {
	
	// Check number of lists by users
	$sql_num_lists = 'SELECT COUNT(1) FROM lists WHERE user_id=? GROUP BY user_id';
	$stmt_num_lists = $pdo->prepare($sql_num_lists);
	$stmt_num_lists->execute([ $_SESSION['user_id'] ]);
	$num_lists = $stmt_num_lists->fetchColumn();
	
	if( $num_lists < 6 || $_SESSION['can_have_more_lists'] ) {
		
		// Check that list doesn't exist
		$sql_exists = 'SELECT 1 FROM lists WHERE user_id=? AND name=? LIMIT 1';
		$stmt_exists = $pdo->prepare($sql_exists);
		$stmt_exists->execute([ $_SESSION['user_id'], $name ]);
		$rslt_exists = $stmt_exists->fetchColumn();
		
		if( !$rslt_exists ) {
		
			// Add new list
			$sql_add = 'INSERT INTO lists (user_id, name, friendly) VALUES (?, ?, ?)';
			$stmt_add = $pdo->prepare($sql_add);
			if( $stmt_add->execute([ $_SESSION['user_id'], $name, $friendly ]) ) {
				
				$list_id = $pdo->lastInsertId();
				
				// Add item to newly made list
				if( is_numeric($list_id) && is_numeric($item_id) && strlen($item_type) ) {
					
					$sql_link = 'INSERT INTO lists_items (list_id, item_id, item_type) VALUES (?, ?, ?)';
					$stmt_link = $pdo->prepare($sql_link);
					
					if($stmt_link->execute([ $list_id, $item_id, $item_type ])) {
						
						$output['status'] = 'success';
						$output['name'] = $name;
						$output['list_id'] = $list_id;
						$output['item_id'] = $item_id;
						$output['item_type'] = $item_type;
						
					}
					else {
						$output['result'] = 'Couldn\'t add item to new list.';
					}
					
				}
				else {
					$output['result'] = 'Something went wrong.';
				}
				
			}
			else {
				$output['result'] = 'Couldn\'t create new list.';
			}
			
		}
		else {
			$output['result'] = 'You already have a list with that name.';
		}
		
	}
	else {
		$output['result'] = 'Sorry, the number of lists is limited.';
	}
	
}
else {
	$output['result'] = 'Please specify a name.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);