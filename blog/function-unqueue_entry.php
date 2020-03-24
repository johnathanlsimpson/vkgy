<?php
include_once('../php/include.php');
include_once('../php/class-access_social_media.php');
$access_social_media = $access_social_media ?: new access_social_media($pdo);
$access_image = $access_image ?: new access_image($pdo);

// Set to date to JST
$current_date = new DateTime(null, new DateTimeZone('JST'));
$current_date = $current_date->format('Y-m-d H:i');

// Get queued entries with scheduled date
$sql_queued = 'SELECT * FROM blog WHERE is_queued=? AND date_scheduled IS NOT NULL AND date_scheduled<=?';
$stmt_queued = $pdo->prepare($sql_queued);
$stmt_queued->execute([ 1, $current_date ]);
$rslt_queued = $stmt_queued->fetchAll();

if(is_array($rslt_queued) && !empty($rslt_queued)) {
	foreach($rslt_queued as $entry) {
		
		// Update post date and unqueue
		$sql_update = 'UPDATE blog SET is_queued=?, date_occurred=?, date_scheduled=? WHERE id=? LIMIT 1';
		$stmt_update = $pdo->prepare($sql_update);
		if($stmt_update->execute([ 0, $current_date, null, $entry['id'] ])) {
			
			// Make images visible
			$images = $access_image->access_image([ 'blog_id' => $entry['id'], 'get' => 'name', 'show_queued' => true ]);
			
			if(is_array($images) && !empty($images)) {
				foreach($images as $image) {
					$sql_image = 'UPDATE images SET is_queued=?, date_added=? WHERE id=?';
					$stmt_image = $pdo->prepare($sql_image);
					$stmt_image->execute([ 0, $current_date, $image['id'] ]);
				}
			}
			
			// Format sources
			if($entry['sources']) {
				preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $entry['sources'], $twitter_matches);
				
				if(is_array($twitter_matches) && !empty($twitter_matches)) {
					for($i=0; $i<count($twitter_matches[0]); $i++) {
						$twitter_authors[] = $twitter_matches[1][$i];
					}
				}
			}
			
			// Immediately post to socials, if not just translated ver
			if( strpos($entry['title'], '[&#26085;&#26412;&#35486;]') === false ) {
				if(strlen($entry['title']) && strlen($entry['friendly'])) {
					$social_post = $access_social_media->build_post([
						'title' => $entry['title'],
						'url' => 'https://vk.gy/blog/'.$entry['friendly'].'/',
						'content_ja' => $entry['content_ja'],
						'id' => $entry['id'],
						'twitter_authors' => $twitter_authors
					], 'blog_post');
					$access_social_media->post_to_social($social_post, 'both');
				}
			}
		}
	}
}