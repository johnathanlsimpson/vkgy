<?php
include_once('../php/include.php');
$access_social_media = $access_social_media ?: new access_social_media($pdo);
$access_image = $access_image ?: new access_image($pdo);

// Set to date to JST
$current_date = new DateTime(null, new DateTimeZone('JST'));
$current_date = $current_date->date;

// Get queued entries with scheduled date
$sql_queued = 'SELECT * FROM blog WHERE is_queued=? AND scheduled_date IS NOT NULL AND scheduled_date<=?';
$stmt_queued = $pdo->prepare($sql_queued);
$stmt_queued->execute([ 1, $current_date ]);
$rslt_queued = $stmt_queued->fetchAll();

if(is_array($rslt_queued) && !empty($rslt_queued)) {
	foreach($rslt_queued as $entry) {
		
		// Update post date and unqueue
		$sql_update = 'UPDATE blog SET is_queued=?, date_occurred=? WHERE id=? LIMIT 1';
		$stmt_update = $pdo->prepare($sql_update);
		if($stmt_update->execute([ 0, $current_date, $entry['id'] ])) {
			
			// Immediately post to socials
			if(strlen($entry['title']) && strlen($entry['friendly'])) {
				$social_post = $access_social_media->build_post(['title' => $title, 'url' => 'https://vk.gy/blog/'.$entry['friendly'].'/', 'id' => $entry['id'] ], 'blog_post');
				$access_social_media->post_to_social($social_post, 'both');
			}
			
			// Update images
			$images = $access_image->access_image([ 'blog_id' => $entry['id'], 'get' => 'name', 'show_queued' => true ]);
			if(is_array($images) && !empty($images)) {
				foreach($images as $image) {
					$sql_image = 'UPDATE images SET is_queued=?, date_occurred=? WHERE id=?';
					$stmt_image = $pdo->prepare($sql_images);
					$stmt_image->execute([ 0, $current_date, $entry['id'] ]);
				}
			}
		}
	}
}