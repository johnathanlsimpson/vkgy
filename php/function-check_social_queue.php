<?php
	include_once('../php/include.php');
	include_once('../php/class-access_social_media.php');
	$access_social_media = new access_social_media($pdo);
	
	// Get queued social media posts
	$sql_queued_posts = 'SELECT * FROM queued_social WHERE date_occurred < ? AND is_completed IS NULL';
	$stmt_queued_posts = $pdo->prepare($sql_queued_posts);
	$stmt_queued_posts->execute([ date('Y-m-d H:i:s') ]);
	$rslt_queued_posts = $stmt_queued_posts->fetchAll();
	
	if(is_array($rslt_queued_posts) && !empty($rslt_queued_posts)) {
		foreach($rslt_queued_posts as $queued_post) {
			if($queued_post['date_occurred'] > date('Y-m-d H:i:s', strtotime('-1 week'))) {
				$is_recent = true;
			}
			
			// For certain item types, specify which database its corresponding item is in
			$item_types = [
				'blog_post' => 'blog',
				'flyer_of_day' => 'images',
				'artist_of_day' => 'artists'
			];
			
			// Check if item in post still exists
			if(in_array($queued_post['item_type'], array_keys($item_types))) {
				$sql_check_item = 'SELECT 1 FROM '.$item_types[$queued_post['item_type']].' WHERE id=? LIMIT 1';
				$stmt_check_item = $pdo->prepare($sql_check_item);
				$stmt_check_item->execute([ $queued_post['item_id'] ]);
				
				if($stmt_check_item->fetchColumn()) {
					$item_exists = true;
				}
			}
			else {
				$item_exists = true;
			}
			
			// Post queued post to social media, remove from queue
			if($is_recent && $item_exists) {
				$rslt_queued_post = $access_social_media->post_to_social($queued_post, $queued_post['social_type']);
				
				if($rslt_queued_post['status'] === 'success') {
					$sql_remove_post = 'UPDATE queued_social SET is_completed=? WHERE id=? LIMIT 1';
					$stmt_remove_post = $pdo->prepare($sql_remove_post);
					$stmt_remove_post->execute([ 1, $queued_post['id'] ]);
				}
			}
			else {
				$sql_remove_post = 'UPDATE queued_social SET is_completed=? WHERE id=? LIMIT 1';
				$stmt_remove_post = $pdo->prepare($sql_remove_post);
				$stmt_remove_post->execute([ 1, $queued_post['id'] ]);
			}
		}
	}
?>