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
			
			// Get tags and set post type
			$sql_tags = 'SELECT blog_tags.blog_id FROM tags LEFT JOIN blog_tags ON blog_tags.tag_id=tags.id AND blog_tags.blog_id=? WHERE tags.friendly=? LIMIT 1';
			$stmt_tags = $pdo->prepare($sql_tags);
			$stmt_tags->execute([ $entry['id'], 'interview' ]);
			$rslt_tags = $stmt_tags->fetchColumn();
			$post_type = $rslt_tags == $entry['id'] ? 'interview' : 'blog_post';
			
			// Set other vars
			$title = sanitize($entry['title']);
			$id = sanitize($entry['id']);
			$artist_id = sanitize($entry['artist_id']);
			$user_id = sanitize($entry['user_id']);
			$contributor_ids = json_decode($entry['contributor_ids'], true);
			$url = 'https://vk.gy/blog/'.sanitize($entry['friendly']);
			
			// Set overrides
			$overrides = json_decode($entry['sns_overrides'], true);
			$override_body = $overrides['body'] ?: null;
			$override_twitter_mentions = $overrides['twitter_mentions'] ?: null;
			$override_twitter_authors = $overrides['twitter_authors'] ?: null;
			//$override_authors = $overrides['authors'] ?: null;
			
			// Send to SNS builder and get output
			$sns_post = $access_social_media->build_post([
				'title'                     => $title,
				'id'                        => $id,
				'artist_id'                 => $artist_id,
				'user_id'                   => $user_id,
				'contributor_ids'           => $contributor_ids,
				'url'                       => $url,
				'override_body'             => $override_body,
				'override_twitter_mentions' => $override_twitter_mentions,
				'override_twitter_authors'  => $override_twitter_authors,
				//'override_authors'          => $override_authors,
			], $post_type);
			
			$access_social_media->post_to_social($sns_post, 'both');
			
		}
	}
}