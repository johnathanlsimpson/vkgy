<?php

// Setup
include_once('../php/include.php');
include_once('../php/class-access_social_media.php');
include_once('../php/class-access_video.php');

$access_video = $access_video ?: new access_video($pdo);

$access_social_media = $access_social_media ?: new access_social_media($pdo);
$markdown_parser = $markdown_parser ?: new parse_markdown($pdo);
$date_occurred_pattern = '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$';
$current_date = new DateTime(null, new DateTimeZone('JST'));
$current_date = $current_date->format('Y-m-d H:i');

// If working with translation, swap out ID with original ID and save translation ID separately
if(is_numeric($_POST['blog_id'])) {
	$is_translation = true;
	$_POST['translation_id'] = $_POST['id'];
	$_POST['id'] = $_POST['blog_id'];
}

// Set vars for initial check if allowed
$id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$is_edit = is_numeric($id);

// If edit, get current article to confirm user has permission
if($is_edit && is_numeric($id)) {
	
	// Get current ver of entry
	$sql_curr_entry = 'SELECT * FROM blog WHERE id=? LIMIT 1';
	$stmt_curr_entry = $pdo->prepare($sql_curr_entry);
	$stmt_curr_entry->execute([ $id ]);
	$current_entry = $stmt_curr_entry->fetch();
	
	// Transform contributor IDs to array so we can run check against them
	if(is_array($current_entry) && !empty($current_entry)) {
		$current_entry['contributor_ids'] = json_decode($current_entry['contributor_ids'], true);
	}
	
}

// Check if user has permission to add/edit article
if($_SESSION['is_signed_in']) {
	if(
		(!$is_edit)
		||
		($is_edit && $_SESSION['user_id'] === $current_entry['user_id'])
		||
		($is_edit && !$is_queued && $_SESSION['can_add_data'])
		||
		($is_edit && $is_queued && $_SESSION['can_access_drafts'])
		||
		($is_edit && in_array(277, $_POST['tags'])) /* Need to reevaluate this one */
		||
		(in_array($_SESSION['user_id'], $current_entry['contributor_ids']))
	) {
		$is_allowed = true;
	}
}

// Translation article
if($is_translation) {
	
	// Set vars
	$id = is_numeric($_POST['translation_id']) ? $_POST['translation_id'] : null;
	$title = sanitize($_POST['name']);
	$content = sanitize($markdown_parser->validate_markdown($_POST['content']));
	$friendly = friendly($_POST['friendly']);
	
	if(is_numeric($id)) {
		if(strlen($title) && strlen($content)) {
			
			$sql_trans = 'UPDATE blog_translations SET title=?, content=? WHERE id=? LIMIT 1';
			$stmt_trans = $pdo->prepare($sql_trans);
			
			if($stmt_trans->execute([ $title, $content, $id ])) {
				$output['status'] = 'success';
				$output['id'] = $id;
				$output['url'] = '/blog/'.$friendly.'/';
				$output['edit_url'] = '/blog/'.$friendly.'/edit/';
			}
			else {
				$output['result'] = 'Something went wrong when updating the translation.';
			}
			
		}
		else {
			$output['result'] = 'Please enter a title and text.';
		}
	}
	else {
		$output['result'] = 'No ID provided.';
	}
	
}

// Original (English) article
else {

// Set basic content
$id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$title = sanitize($_POST['name']);
$content = sanitize($markdown_parser->validate_markdown($_POST['content']));
//$content_ja = sanitize($markdown_parser->validate_markdown($_POST['content_ja'])) ?: null;
$supplemental = sanitize($markdown_parser->validate_markdown($_POST['supplemental'])) ?: null;
$sources = sanitize($_POST['sources']) ?: null;
$friendly = friendly($_POST['friendly'] ?: $title);
$references = $markdown_parser->get_reference_data($content);
$is_queued = $_POST['is_queued'] ? 1 : 0;
$author_id = is_numeric($_POST['user_id']) ? $_POST['user_id'] : $_SESSION['user_id'];
$was_published = $_POST['was_published'] ? 1 : 0;
$sns_image_id = is_numeric($_POST['sns_image_id']) ? $_POST['sns_image_id'] : null;
$twitter_content = sanitize($_POST['twitter_content']);
$fb_content = sanitize($_POST['fb_content']);
$token = friendly($_POST['token']);
$artist_id = is_numeric($_POST['artist_id']) ? $_POST['artist_id'] : null;

// SNS overrides
$overrides['sns_body'] = sanitize($_POST['sns_body']);
$overrides['twitter_mentions'] = sanitize($_POST['twitter_mentions']);
$overrides['twitter_authors'] = sanitize($_POST['twitter_authors']);
$overrides['sns_image'] = is_numeric($_POST['sns_image_id']) ? $_POST['sns_image_id'] : null;
$sns_overrides = array_filter($overrides);
$sns_overrides = is_array($sns_overrides) && !empty($sns_overrides) ? json_encode($sns_overrides) : null;

// Double check 'was published' flag
if($is_edit && $current_entry['was_published']) {
	$was_published = 1;
}

// Format sources
/*if($sources) {
	preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $sources, $twitter_matches);
	
	if(is_array($twitter_matches) && !empty($twitter_matches)) {
		for($i=0; $i<count($twitter_matches[0]); $i++) {
			$twitter_authors[] = $twitter_matches[1][$i];
		}
	}
	
	$sources = explode("\n", $sources);
	$sources = array_filter($sources);
	$sources = implode("\n", $sources);
	$sources = sanitize($markdown_parser->validate_markdown($sources));
}*/

// Loop through manually specified contributors and add an edit so they're connected to the entry
$contributor_ids = $_POST['contributor_ids'];
if(is_array($contributor_ids) && !empty($contributor_ids)) {

	// Remove duplicates and main author
	$contributor_ids = array_unique($contributor_ids);
	if(is_array($contributor_ids) && !empty($contributor_ids)) {
		foreach($contributor_ids as $contributor_key => $contributor_id) {
			if($contributor_id == $author_id) {
				unset($contributor_ids[$contributor_key]);
			}
		}
	}
	$contributor_ids = array_values($contributor_ids);

	/*// Get extant edits for this entry to avoid multiple queries
	$sql_extant_edits = 'SELECT user_id FROM edits_blog WHERE blog_id=? GROUP BY user_id';
	$stmt_extant_edits = $pdo->prepare($sql_extant_edits);
	$stmt_extant_edits->execute([ $id ]);
	$rslt_extant_edits = $stmt_extant_edits->fetchAll();

	// Remove any contributors who are already in edit history or who are author
	if(is_array($rslt_extant_edits) && !empty($rslt_extant_edits)) {
		foreach($contributor_ids as $contributor_key => $contributor_id) {

			// Unset if already author
			if($contributor_id == $author_id) {
				unset($contributor_ids[$contributor_key]);
			}

			// Unset if already in edits
			foreach($rslt_extant_edits as $rslt_extant_edit) {
				if($rslt_extant_edit['user_id'] == $contributor_id) {
					unset($contributor_ids[$contributor_key]);
					break;
				}
			}

		}
	}

	// If still have contributors left over, they need to be added as an edit
	if(is_array($contributor_ids) && !empty($contributor_ids)) {
		foreach($contributor_ids as $contributor_key => $contributor_id) {
			$sql_contributor_edit = 'INSERT INTO edits_blog (blog_id, user_id, content) VALUES (?, ?, ?)';
			$stmt_contributor_edit = $pdo->prepare($sql_contributor_edit);
			$stmt_contributor_edit->execute([ $id, $contributor_id, 'Contributed.' ]);
		}
	}*/

}
$contributor_ids = is_array($contributor_ids) && !empty($contributor_ids) ? json_encode($contributor_ids) : null;

// Set up date
$date_scheduled = $_POST['date_scheduled'].' '.($_POST['time_scheduled'] ?: '00:00');
$date_scheduled = preg_match('/'.$date_occurred_pattern.'/', $date_scheduled) && $date_scheduled > $current_date ? $date_scheduled : null;

// ...If scheduled for future
if($date_scheduled) {
	$date_occurred = null;
	$is_queued = 1;
}

// ...If was scheduled for future but now posting immediately
elseif($is_edit && !$date_scheduled && !$current_entry['date_occurred']) {
	$date_occurred = $current_date;
}

// ...If was queued and now isn't
elseif($is_edit && !$date_scheduled && !$is_queued && $current_entry['is_queued']) {
	$date_occurred = $current_date;
}

// Check if friendly allowed
if($is_edit && $friendly === $current_entry['friendly']) {
	$friendly_is_allowed = true;
}
else {
	$sql_check_friendly = 'SELECT 1 FROM blog WHERE friendly=? LIMIT 1';
	$stmt_check_friendly = $pdo->prepare($sql_check_friendly);
	$stmt_check_friendly->execute([ $friendly ]);
	$friendly_is_allowed = $stmt_check_friendly->fetchColumn() ? false : true;
}

// Cycle through tags in POST, get blog entry x tag pairings in DB, add/delete accordingly
function update_tags($tag_table, $id_column, $entry_id, $tag_column, $new_tag_array, $pdo) {
	
	// Unset any non-numeric tags (array_filter would remove id's of 0)
	if(is_array($new_tag_array) && !empty($new_tag_array)) {
		foreach($new_tag_array as $key => $tag_id) {
			if(!is_numeric($tag_id)) {
				unset($new_tag_array[$key]);
			}
		}
	}
	
	// Remove non-unique tag values
	if(is_array($new_tag_array) && !empty($new_tag_array)) {
		$new_tag_array = array_unique($new_tag_array);
	}
	
	// Get current tags
	$sql_current_tags = 'SELECT id, '.$tag_column.' FROM '.$tag_table.' WHERE '.$id_column.'=?';
	$stmt_current_tags = $pdo->prepare($sql_current_tags);
	$stmt_current_tags->execute([ $entry_id ]);
	$rslt_current_tags = $stmt_current_tags->fetchAll();
	
	// Unset duplicate tags, set up delete for tags that are no longer wanted
	if(is_array($rslt_current_tags) && !empty($rslt_current_tags)) {
		foreach($rslt_current_tags as $tag) {
			if(in_array($tag[$tag_column], $new_tag_array)) {
				$ignore_key = array_search($tag[$tag_column], $new_tag_array);
				unset($new_tag_array[$ignore_key]);
			}
			else {
				$tags_to_delete[] = $tag[$tag_column];
			}
		}
	}
	
	// Add new tags
	if(is_array($new_tag_array) && !empty($new_tag_array)) {
		$sql_values = [];
		
		foreach($new_tag_array as $tag_id) {
			$sql_values[] = $tag_id;
			$sql_values[] = $entry_id;
			$sql_values[] = $_SESSION['user_id'];
		}
		
		$sql_add = 'INSERT INTO '.$tag_table.' ('.$tag_column.', '.$id_column.', user_id) VALUES '.substr(str_repeat('(?, ?, ?), ', count($new_tag_array)), 0, -2);
		$stmt_add = $pdo->prepare($sql_add);
		$stmt_add->execute($sql_values);
	}
	
	// Delete tags
	if(is_array($tags_to_delete) && !empty($tags_to_delete)) {
		$sql_delete = 'DELETE FROM '.$tag_table.' WHERE '.$id_column.'=? AND ('.substr(str_repeat($tag_column.'=? OR ', count($tags_to_delete)), 0, -4).')';
		$stmt_delete = $pdo->prepare($sql_delete);
		$stmt_delete->execute(array_merge([ $entry_id ], $tags_to_delete));
	}
}

// Add/update post
if(strlen($title) && strlen($friendly) && strlen($content)) {
	if($is_allowed) {
		if($friendly_is_allowed) {
			
			// Build query
			$keys_blog = [ 'title', 'friendly', 'content', 'content_ja', 'supplemental', 'sources', 'sns_image_id', 'sns_overrides', 'is_queued', 'date_scheduled', 'user_id', 'contributor_ids', 'token', 'artist_id' ];
			$values_blog = [ $title, $friendly, $content, $content_ja, $supplemental, $sources, $sns_image_id, $sns_overrides, $is_queued, $date_scheduled, $author_id, $contributor_ids, $token, $artist_id ];
			
			if($date_occurred) {
				$keys_blog[] = 'date_occurred';
				$values_blog[] = $date_occurred;
			}
			
			if($is_edit) {
				$sql_blog = 'UPDATE blog SET '.implode('=?, ', $keys_blog).'=? WHERE id=? LIMIT 1';
				$values_blog[] = $id;
			}
			else {
				
				// If saving new post, and no image supplied, grab main artist's default photo
				
				// If adding brand new post
				// Grab default image for first artist mentioned, in case no image is supplied by user
				/*if(is_array($references) && !empty($references)) {
					$last_ref = end($references);
					
					if($last_ref['type'] === 'artist') {
						$sql_default_image = 'SELECT artists.image_id FROM artists WHERE id=? LIMIT 1';
						$stmt_default_image = $pdo->prepare($sql_default_image);
						$stmt_default_image->execute([ $last_ref['id'] ]);
						$rslt_default_image = $stmt_default_image->fetchColumn();
						
						if(is_numeric($rslt_default_image)) {
							$keys_blog[] = 'image_id';
							$values_blog[] = $rslt_default_image;
						}
					}
				}*/
				
				//$keys_blog[] = 'user_id';
				//$values_blog[] = $author_id;
				$sql_blog = 'INSERT INTO blog ('.implode(', ', $keys_blog).') VALUES ('.substr(str_repeat('?, ', count($values_blog)), 0, -2).')';
			}
			
			$stmt_blog = $pdo->prepare($sql_blog);
			if($stmt_blog->execute($values_blog)) {
				
				if(!$is_edit) {
					$id = $pdo->lastInsertId();
					
					// If default image was automatically set, add to images_blog
					$sql_images_link = 'INSERT INTO images_blog (blog_id, image_id) VALUES (?, ?)';
					$stmt_images_link = $pdo->prepare($sql_images_link);
					$stmt_images_link->execute([ $id, $rslt_default_image ]);
				}
				
				// Output
				$output['status'] = 'success';
				$output['url'] = '/blog/'.$friendly.'/';
				$output['edit_url'] = '/blog/'.$friendly.'/edit/';
				$output['id'] = $id;
				$output['friendly'] = $friendly;
				$output['is_queued'] = $is_queued;
				
				// Update tag links
				update_tags('blog_tags', 'blog_id', $id, 'tag_id', $_POST['tags'], $pdo);
				
				if(is_array($references) && !empty($references)) {
					
					// Grab referenced artists and build list, so that we can tag them
					foreach($references as $reference) {
						if($reference['type'] === 'artist' && is_numeric($reference['id'])) {
							$artist_tags[] = $reference['id'];
						}
					}
					
					// Grab videos and try to add with main artist
					foreach($references as $reference) {
						if($reference['type'] === 'video') {
							if(is_array($artist_tags) && !empty($artist_tags)) {
								$video_artist_id = end($artist_tags);
								
								$video_data_data = $access_video->add_video($reference['id'], $video_artist_id);
							}
						}
					}
				}
				
				if(is_array($artist_tags)) {
					$artist_tags = array_unique($artist_tags);
				}
				
				if(is_array($artist_tags) && !empty($artist_tags)) {
					update_tags('blog_artists', 'blog_id', $id, 'artist_id', $artist_tags, $pdo);
				}
				
				// Update edit history
				$sql_edit_history = 'INSERT INTO edits_blog (blog_id, user_id, content) VALUES (?, ?, ?)';
				$stmt_edit_history = $pdo->prepare($sql_edit_history);
				$stmt_edit_history->execute([ $id, $_SESSION['user_id'], ($is_edit ? null : 'Created.') ]);
				
				// Get queued, extant social media post, if exists
				$extant_social_post = $access_social_media->get_post( $id, 'blog_post' );
				
				// Delete old social media post (if applicable) and generate new one, if post is live
				if(!$is_edit || ($is_edit && !$extant_social_post) || (is_array($extant_social_post) && is_numeric($extant_social_post['id']) && !$extant_social_post['is_completed'] )) {
					if($extant_social_post) {
						$access_social_media->delete_post( $extant_social_post['id'] );
					}
					
					if(!$is_queued && strlen($title) && strlen($friendly)) {
						
						
						
						
						
						
						
						/* Get tweet parts--eventually need to redo this so it's all in one location */
						
						if(!$overrides['twitter_authors']) {
							
							// Combine author ID and contributor IDs, remove duplicates, remove site owner
							$author_id = sanitize($_POST['author_id']);
							$contributor_ids = is_array($_POST['contributor_ids']) ? $_POST['contributor_ids'] : explode(',', sanitize($_POST['contributor_ids']));
							$contributor_ids = is_array($contributor_ids) ? $contributor_ids : [];
							$contributor_ids[] = $author_id;
							$contributor_ids = array_unique($contributor_ids);
							$contributor_ids = array_filter($contributor_ids, function($x) { return $x != 0 && $x != 1; });
							$contributor_ids = array_values($contributor_ids);
							
							// Get Twitter usernames of remaining contributors (if overrides not set)
							if( !$facebook_author && !$twitter_author && is_array($contributor_ids) && !empty($contributor_ids) ) {
								
								// Get user info
								$sql_author = 'SELECT username, twitter FROM users WHERE '.substr(str_repeat('id=? OR ', count($contributor_ids)), 0, -4).'';
								$stmt_author = $pdo->prepare($sql_author);
								$stmt_author->execute( $contributor_ids );
								$rslt_author = $stmt_author->fetchAll();
								
								// Use username as FB credit, Twitter handle as Twitter credit if possible
								if(is_array($rslt_author) && !empty($rslt_author)) {
									foreach($rslt_author as $author) {
										$facebook_author = $author['username'];
										$twitter_author .= $author['twitter'] ? '@'.$author['twitter'].' ' : $author['username'].' ';
									}
								}
								
							}
						}
						
						if($overrides['twitter_mentions']) {
							
							include_once('../blog/function-get_artist_twitters.php');
							
							// If artist specified, get Twitter handles for band and its members
							$artist_id = sanitize($_POST['artist_id']);
							if(is_numeric($artist_id)) {
								$artist_twitters = get_artist_twitters($artist_id, $pdo, $access_artist);
							}
							
						}
						
						// Check if post type manually set
						$post_type = sanitize($_POST['post_type']) ?: 'blog_post';
						
						
						
						
						
						
						
						
						
						
						// Build and save post
						$social_post = $access_social_media->build_post([
							'title' => $title,
							'url' => 'https://vk.gy'.$output['url'],
							'id' => $id,
							'sns_body' => $overrides['sns_body'] ?: null,
							'twitter_mentions' => $overrides['twitter_mentions'] ?: ($artist_twitters ?: null),
							'twitter_author' => $overrides['twitter_authors'] ?: ($twitter_author ?: null)
						], $post_type);
						
						$access_social_media->queue_post($social_post, 'both', date('Y-m-d H:i:s', strtotime('+15 minutes')));
						
						
					}
				}
				
				// Award point
				$access_points = new access_points($pdo);
				if($is_edit) {
					
					// 1 point for editing someone else's entry
					if($_SESSION['user_id'] !== $author_id) {
						$output['points'] += $access_points->award_points([ 'point_type' => 'edited-blog', 'allow_multiple' => false, 'item_id' => $id ]);
					}
					
				}
				else {
					
					// 10 points for adding a new entry
					if(!$is_queued && !$was_published) {
						$output['points'] += $access_points->award_points([ 'point_type' => 'added-blog' ]);
					}
					
				}
			}
			else {
				$output['result'] = 'Sorry, the post couldn\'t be updated.';
			}
		}
		else {
			$output['result'] = 'Please choose a different title or url-friendly name.';
		}
	}
	else {
		$output['result'] = 'Sorry, you\'re not allowed to edit this post.';
	}
}
else {
	$output['result'] = 'Please enter a title and text.';
}

// End if normal article
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);