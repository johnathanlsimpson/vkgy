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
			if( strpos($entry['title'], sanitize('日本語')) === false ) {
				if(strlen($entry['title']) && strlen($entry['friendly'])) {
					
					// SNS defaults
					$title = $entry['title'];
					$url = 'https://vk.gy/blog/'.$entry['friendly'].'/';
					$id = $entry['id'];
					
					// Combine author ID and contributor IDs, remove duplicates, remove site owner
					$author_id = sanitize($entry['user_id']);
					$contributor_ids = $entry['contributor_ids'] ? json_decode($entry['contributor_ids'], true) : null;
					$contributor_ids = is_array($contributor_ids) ? $contributor_ids : [];
					$contributor_ids[] = $author_id;
					$contributor_ids = array_unique($contributor_ids);
					$contributor_ids = array_filter($contributor_ids, function($x) { return $x != 0 && $x != 1; });
					$contributor_ids = array_values($contributor_ids);
					
					// Get Twitter usernames of remaining contributors (if overrides not set)
					if( !$facebook_author && !$twitter_authors && is_array($contributor_ids) && !empty($contributor_ids) ) {
						
						// Get user info
						$sql_author = 'SELECT username, twitter FROM users WHERE '.substr(str_repeat('id=? OR ', count($contributor_ids)), 0, -4).'';
						$stmt_author = $pdo->prepare($sql_author);
						$stmt_author->execute( $contributor_ids );
						$rslt_author = $stmt_author->fetchAll();
						
						//print_r($rslt_author);
						
						// Use username as FB credit, Twitter handle as Twitter credit if possible
						if(is_array($rslt_author) && !empty($rslt_author)) {
							foreach($rslt_author as $author) {
								$facebook_author = $author['username'];
								$twitter_authors .= $author['twitter'] ? '@'.$author['twitter'].' ' : $author['username'].' ';
							}
						}
						
					}
					
					// If artist specified, get Twitter handles for band and its members
					include_once('../blog/function-get_artist_twitters.php');
					$access_artist = $access_artist ?: new access_artist($pdo);
					$artist_id = sanitize($entry['artist_id']);
					if(is_numeric($artist_id)) {
						$twitter_mentions = get_artist_twitters($artist_id, $pdo, $access_artist);
					}
					
					// Get translations
					$sql_trans = 'SELECT * FROM blog_translations WHERE blog_id=?';
					$stmt_trans = $pdo->prepare($sql_trans);
					$stmt_trans->execute([ $entry['id'] ]);
					$rslt_trans = $stmt_trans->fetchAll();
					
					if(is_array($rslt_trans) && !empty($rslt_trans)) {
						$translations = '[En] https://vk.gy/blog/'.$entry['friendly'].'/';
						foreach($rslt_trans as $translation) {
							$translations .= "\n\n".'['.[ 'ja' => '日本語版' ][ $translation['language'] ].'] https://vk.gy/blog/'.$translation['friendly'].'/';
						}
					}
					
					// Get SNS overrides
					$overrides = $entry['sns_overrides'] ? json_decode($entry['sns_overrides'], true) : null;
					if(is_array($overrides) && !empty($overrides)) {
						
						$title = $overrides['sns_body'] ?: $title;
						$twitter_mentions = $overrides['twitter_mentions'] ?: $twitter_mentions;
						$twitter_authors = $overrides['twitter_authors'] ?: $twitter_authors;
						
					}
					
					// (Temporary) Check if interview
					$sql_tag = 'SELECT 1 FROM blog_tags WHERE blog_id=? AND tag_id=? LIMIT 1';
					$stmt_tag = $pdo->prepare($sql_tag);
					$stmt_tag->execute([ $entry['id'], 25 ]);
					$rslt_tag = $stmt_tag->fetchColumn();
					if($rslt_tag) {
						$post_type = 'interview';
					}
					
					// Build post
					$social_post = $access_social_media->build_post([
						'title'            => $title,
						'url'              => $url,
						'id'               => $id,
						'translations'     => $translations,
						'twitter_mentions' => $twitter_mentions,
						'twitter_author'   => $twitter_authors,
					], $post_type ?: 'blog_post');
					
					/*$social_post = $access_social_media->build_post([
						'title' => $entry['title'],
						'url' => 'https://vk.gy/blog/'.$entry['friendly'].'/',
						'content_ja' => $entry['content_ja'],
						'id' => $entry['id'],
						'twitter_authors' => $twitter_authors
					], 'blog_post');*/
					
					$access_social_media->post_to_social($social_post, 'both');
					//echo $_SESSION['username'] === 'inartistic' ? '<pre>'.print_r($entry, true).'*'.print_r($social_post, true).'</pre>' : null;
					
				}
			}
		}
	}
}