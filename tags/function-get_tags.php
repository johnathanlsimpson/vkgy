<?php

function get_tags($pdo, $item_type, $item_id) {
	
	// Set up allowed defaults
	$allowed_item_types = [ 'artist', 'musician', 'release' ];
	$tag_types = [
		'artists' => [ 'subgenres', 'styles', 'other', 'admin', 'disputed' ],
		'musicians' => [ 'admin', 'disputed' ],
		'releases' => [ 'styles', 'other', 'admin', 'disputed' ]
	];
	
	// Set up DB names
	$item_type_plural = $item_type.'s';
	$tags_table = 'tags_'.$item_type_plural;
	$tags_items_table = $item_type_plural.'_tags';
	
	// Make sure item type is allowed
	if(is_numeric($item_id) && strlen($item_type) && in_array($item_type, $allowed_item_types)) {
		
		// Get all possible tags
		$sql_all_tags = 'SELECT * FROM '.$tags_table.' ORDER BY friendly ASC';
		$stmt_all_tags = $pdo->prepare($sql_all_tags);
		$stmt_all_tags->execute();
		$all_tags = $stmt_all_tags->fetchAll();
		
		// Loop through all possible tags and separate by tag type
		if(is_array($all_tags) && !empty($all_tags)) {
			foreach($all_tags as $numeric_key => $tag) {
				
				// Transform tag's type from number to string
				$type_key = $tag_types[ $item_type_plural ][ $tag['type'] ];
				
				// Move tag back into array with key as its type, then remove it from array where key was its ID
				$all_tags[$type_key][] = $tag;
				unset( $all_tags[$numeric_key] );
				
			}
		}
		
		// Get all *current* tags applied to artist
		$sql_curr_tags = '
		SELECT 
			'.$tags_table.'.*, 
			COUNT('.$tags_items_table.'.id) AS num_times_tagged, 
			SUM('.$tags_items_table.'.mod_agrees) AS mod_upvotes,
			SUM(IF('.$tags_items_table.'.user_agrees="1", 1, 0)) AS num_upvotes, 
			SUM(IF('.$tags_items_table.'.user_agrees="-1", 1, 0)) AS num_downvotes
		FROM '.$tags_items_table.' 
		LEFT JOIN '.$tags_table.' ON '.$tags_table.'.id='.$tags_items_table.'.tag_id 
		WHERE '.$tags_items_table.'.'.$item_type.'_id=? 
		GROUP BY '.$tags_items_table.'.tag_id';
		$stmt_curr_tags = $pdo->prepare($sql_curr_tags);
		$stmt_curr_tags->execute([ $item_id ]);
		$current_tags = $stmt_curr_tags->fetchAll();
		
		// Loop through current tags, transform and separate
		if(is_array($current_tags) && !empty($current_tags)) {
			foreach($current_tags as $numeric_key => $tag) {
				
				// Calc upvote:downvote ratio
				$tag['upvote_ratio'] = $tag['num_downvotes'] ? $tag['num_upvotes'] / $tag['num_downvotes'] : 1;
				$tag['num_upvotes'] = $tag['num_upvotes'] - $tag['num_downvotes'];
				
				// Transform tag's type from number to string
				$type_key = $tag_types[ $item_type_plural ][ $tag['type'] ];
				
				// Change tag type to disputed if necessary
				if($tag['upvote_ratio'] < 1) {
					$type_key = 'disputed';
				}
				
				// If mod reviewed, or upvote ratio is too low (and has a minimum num of votes), don't display
				// otherwise, add back to array under key type instead of ID
				if($tag['mod_upvotes'] > -1) {
					if($tag['num_times_tagged'] < 4 || $tag['upvote_ratio'] > 0.9) {
						$current_tags[$type_key][] = $tag;
					}
				}
				unset( $current_tags[$numeric_key] );
				
			}
		}
		
		// Grab all tags which have been added by the current user
		if($_SESSION['is_signed_in']) {
			
			$sql_user_tags = 'SELECT tag_id, user_agrees FROM '.$tags_items_table.' WHERE '.$item_type.'_id=? AND user_id=?';
			$stmt_user_tags = $pdo->prepare($sql_user_tags);
			$stmt_user_tags->execute([ $item_id, $_SESSION['user_id'] ]);
			$user_tags = $stmt_user_tags->fetchAll();
			
			// Save them as an array of just tag IDs, since that's all we need for later checks
			if(is_array($user_tags) && !empty($user_tags)) {
				foreach($user_tags as $key => $tag) {
					if($tag['user_agrees'] > 0) {
						$user_upvotes[$key] = $tag['tag_id'];
					}
					else {
						$user_downvotes[$key] = $tag['tag_id'];
					}
				}
			}
			
		}
		
		return [
			'all_tags' => $all_tags,
			'current_tags' => $current_tags,
			'user_upvotes' => $user_upvotes,
			'user_downvotes' => $user_downvotes,
			'tag_types' => $tag_types[ $item_type_plural ],
		];
		
	}
	
}