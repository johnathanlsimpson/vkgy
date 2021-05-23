<?php
	include_once('../php/include.php');
	include_once('../php/external/twitter-php/OAuth.php');
	include_once('../php/external/twitter-php/Twitter.php');
	require_once('../php/external/facebook/autoload.php');
	
	class access_social_media {
		private $pdo;
		private $fb_page_id;
		private $fb;
		private $twitter;
		private $allowed_item_types;
		private $allowed_social_types;
		private $patreon_url;
		
		// ======================================================
		// Connect
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
			}
			
			$this->pdo = $pdo;
			
			// Set up allowed item types
			$this->allowed_item_types = [
				'artist_of_day',
				'flyer_of_day',
				'blog_post',
				'interview',
				'artist_ranking',
				'database_updates'
			];
			
			// Set up allowed social media types
			$this->allowed_social_types = [
				'twitter',
				'facebook',
				'both'
			];
			
			// Patreon link
			$this->patreon_url = 'https://patreon.com/vkgy';
			
			// Get FB/Twitter keys
			include('../php/class-access_social_media-key.php');
			
			// Set up Facebook
			$this->fb_page_id = "407931532968189";
			$this->fb = new \Facebook\Facebook([
				'app_id' => $fb_app_id,
				'app_secret' => $fb_app_secret,
				'default_graph_version' => 'v2.10',
				'default_access_token' => $fb_default_access_token
			]);
			
			// Set up Twitter	
			$consumerKey = $twitter_consumer_key;
			$consumerSecret = $twitter_consumer_secret;
			$accessToken = $twitter_access_token;
			$accessTokenSecret = $twitter_access_token_secret;
			$this->twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
		}
		
		// ======================================================
		// Get user's username and Twitter username
		// ======================================================
		function get_user($user_id) {
			$user_id = is_numeric($user_id) ? $user_id : $_SESSION['user_id'];
			
			$sql_user = 'SELECT id, username, twitter FROM users WHERE id=? LIMIT 1';
			$stmt_user = $this->pdo->prepare($sql_user);
			$stmt_user->execute([ $user_id ]);
			$rslt_user = $stmt_user->fetch();
			
			$output = $rslt_user;
			$output['twitter'] = preg_match("/"."^[A-z0-9_]+$"."/", $output['twitter']) ? '@'.$output['twitter'] : null;
			
			return $output;
		}
		
		// ======================================================
		// Build social media post
		// ======================================================
		function build_post($input, $item_type, $user_id = null) {
			
			// Get username/Twitter account
			$user = $this->get_user($user_id);
			
			// Check that input not empty, and input type allowed
			if(strlen($item_type) && in_array($item_type, $this->allowed_item_types)) {
				if(is_array($input) && !empty($input)) {
					
					// Artist of day: needs name, friendly
					if($item_type === 'artist_of_day' && strlen($input['filepath'])) {
						$output['url'] = 'https://vk.gy/';
						$output['image'] = $input['filepath'];
						$output['content'] = '
							🕵️ Today\'s artist ∙ 今日のアーティスト
							
							Do you recognize them? これは...?
							
							Learn more at https://vk.gy.
							
							'.(true ? null : '🙋 https://vk.gy/').'
							'.(true ? null : '👑 '.$this->patreon_url).'
						';
					}
					
					// Flyer of day: needs author, name, friendly, url, filename
					if($item_type === 'flyer_of_day' && strlen($input['artist']['name']) && strlen($input['artist']['friendly']) && strlen($input['filepath']) && strlen($input['url'])) {
						$output['image'] = $input['filepath'];
						$output['url'] = $input['url'];
						$output['content'] = '
							🖼️ Today\'s flyer ∙ 今日のフライヤー
							
							'.($input['artist']['romaji'] ? $input['artist']['romaji'].' ('.$input['artist']['name'].')' : $input['artist']['name']).'
							
							Info: https://vk.gy/artists/'.$input['artist']['friendly'].'/
							Past flyers: https://vk.gy/images/&type=flyer&order=desc
							
							#ヴィジュアル系フライヤー
							
							'.($user['twitter'] && $user['twitter'] != '@vkgy_' ? '✍️ '.($user['twitter'] ?: $user['username']) : null).'
							'.(true ? null : '🔎 '.$input['url']).'
							'.(true ? null : '👑 '.$this->patreon_url).'
						';
					}
					
					// Blog post
					if( ($item_type === 'blog_post' || $item_type === 'interview') && strlen($input['title']) && strlen($input['url'])) {
						
						// Heading
						$heading = $item_type === 'interview' ? '💬 Interview ∙ インタビュー' : '📰 News ∙ ニュース';
						
						// Body
						$body = $input['override_body'] ?: $input['title'];
						
						// Translations
						if(is_numeric($input['id'])) {
							$sql_translations = 'SELECT language, friendly FROM blog_translations WHERE blog_id=?';
							$stmt_translations = $this->pdo->prepare($sql_translations);
							$stmt_translations->execute([ $input['id'] ]);
							$rslt_translations = $stmt_translations->fetchAll();
							
							if(is_array($rslt_translations) && !empty($rslt_translations)) {
								$translations[] = '[EN] '.$input['url'];
								foreach($rslt_translations as $translation) {
									$language = [ 'ja' => '日本語版' ][ $translation['language'] ];
									$translations[] = '['.$language.'] https://vk.gy/blog/'.$translation['friendly'].'/';
								}
							}
						}
						$translations = is_array($translations) ? implode("\n\n", $translations) : null;
						
						// Twitter mentions
						if($input['override_twitter_mentions']) {
							$twitter_mentions = $input['override_twitter_mentions'];
						}
						else {
							$this->access_artist = $this->access_artist ?: new access_artist($this->pdo);
							include_once('../blog/function-get_artist_twitters.php');
							if(is_numeric($input['artist_id'])) {
								$twitter_mentions = get_artist_twitters($input['artist_id'], $this->pdo, $this->access_artist);
							}
						}
						$twitter_mentions = $twitter_mentions ? '📱 '.$twitter_mentions : null;
						
						// Twitter authors and normal authors
						if($input['override_twitter_authors']) {
							$twitter_authors = [ $input['override_twitter_authors'] ];
						}
						if($input['override_authors']) {
							$authors = [ $input['override_authors'] ];
						}
						if(!$input['override_twitter_authors'] /*|| !$input['override_authors']*/) {
							
							// Get contributor IDs
							$contributor_ids = is_array($input['contributor_ids']) ? $input['contributor_ids'] : [];
							
							// Make sure author (main user) is included
							if(is_numeric($input['user_id'])) {
								$contributor_ids[] = $input['user_id'];
							}
							
							// Get unique user IDs, then remove owner, then reset array
							$contributor_ids = array_unique($contributor_ids);
							$contributor_ids = array_filter($contributor_ids, function($x) { return $x != 0 && $x != 1; });
							$contributor_ids = array_values($contributor_ids);
							
							// If still have list of contributor IDs, get their usernames and Twitter handles
							if( is_array($contributor_ids) && !empty($contributor_ids) ) {
								
								// Get user info
								$sql_authors = 'SELECT username, twitter FROM users WHERE '.substr(str_repeat('id=? OR ', count($contributor_ids)), 0, -4).'';
								$stmt_authors = $this->pdo->prepare($sql_authors);
								$stmt_authors->execute( $contributor_ids );
								$rslt_authors = $stmt_authors->fetchAll();
								
								// Use username as FB credit, Twitter handle as Twitter credit if possible
								if(is_array($rslt_authors) && !empty($rslt_authors)) {
									foreach($rslt_authors as $author) {
										$authors[] = $author['username'];
										$twitter_authors[] = $author['twitter'] ? '@'.$author['twitter'].' ' : $author['username'].' ';
									}
								}
								
							}
							
						}
						$twitter_authors = is_array($twitter_authors) ? '✍️ '.implode(' ', $twitter_authors) : null;
						$authors = is_array($authors) ? '✍️ '.implode(' ', $authors) : null;
						
						$output['contributors'] = $input['contributor_ids'];
						
						// URL
						$url = $input['url'];
						
						// Output
						$output['heading'] = $heading;
						$output['body'] = $body;
						$output['translations'] = $translations;
						$output['twitter_mentions'] = $twitter_mentions;
						$output['twitter_authors'] = $twitter_authors;
						//$output['authors'] = $authors;
						$output['url'] = $url;
						$output['content'] = "
							$heading
							
							$body
							
							$translations
							
							$twitter_mentions
							
							$twitter_authors
						";
						
					}
					
					// Database updates
					if($item_type === 'database_updates' && is_array($input['artists']) && !empty($input['artists'])) {
						$output['url'] = 'https://vk.gy/database/';
						$output['content'] = '
							📚 Today\'s vk.gy updates
							最近の更新 🖼️
							
						';
						
						foreach($input['artists'] as $artist) {
							if(strlen($artist['quick_name']) && strlen($artist['friendly'])) {
								$output['content'] .= '￮'.$artist['quick_name'].' https://vk.gy/artists/'.$artist['friendly'].'/'."\n";
							}
						}
					}
				}
				else {
					$output['result'] = 'Empty item input.';
				}
			}
			else {
				$output['result'] = 'Unallowed item type.';
			}
			
			// Clean output
			if(is_array($output) && $output['content']) {
				$output['content']   = str_replace("\t", '', $output['content']);
				$output['content']   = preg_replace('/'."\n{2,}".'/', "\n\n", $output['content']);
				$output['content']   = sanitize($output['content']);
				$output['content']   = trim($output['content']);
				$output['item_id']   = is_numeric($input['id']) ? $input['id'] : null;
				$output['item_type'] = $item_type;
			}
			
			return $output;
		}
		
		// ======================================================
		// Find extant social media post
		// ======================================================
		function get_post($item_id, $item_type) {
			if(is_numeric($item_id) && in_array($item_type, $this->allowed_item_types)) {
				$sql_extant_post = 'SELECT id, is_completed FROM queued_social WHERE item_id=? AND item_type=? LIMIT 1';
				$stmt_extant_post = $this->pdo->prepare($sql_extant_post);
				$stmt_extant_post->execute([ $item_id, $item_type ]);
				$rslt_extant_post = $stmt_extant_post->fetch();
			}
			
			return($rslt_extant_post);
		}
		
		// ======================================================
		// Delete queued social media post
		// ======================================================
		function delete_post($post_id) {
			if(is_numeric($post_id)) {
				$sql_delete_post = 'DELETE FROM queued_social WHERE id=? LIMIT 1';
				$stmt_delete_post = $this->pdo->prepare($sql_delete_post);
				if($stmt_delete_post->execute([ $post_id ])) {
					return true;
				}
			}
		}
		
		// ======================================================
		// Take pre-built social media post, post appropriately
		// ======================================================
		function post_to_social($input, $social_type) {
			
			// Check that requested social media type is allowed, and that content of post not empty
			if(in_array($social_type, $this->allowed_social_types) && is_array($input) && !empty($input['content'])) {
				
				// De-sanitize content
				$input['content'] = html_entity_decode($input['content'], ENT_QUOTES, "UTF-8");
				
				// Post to Twitter
				if($social_type === 'twitter' || $social_type === 'both') {
					
					// Tweet with image
					if(strlen($input['image'])) {
						if($this->twitter->send($input['content'], $input['image'])) {
							$output['status'] = 'success';
						}
						else {
							$output['result'] = 'Couldn\'t tweet with image.';
						}
					}
					
					// Plain tweet
					else {
						// Add URL to bottom of tweet
						$input['content'] .= "\n\n".$input['url'];
						
						if($this->twitter->send($input['content'])) {
							$output['status'] = 'success';
						}
						else {
							$output['result'] = 'Couldn\'t tweet.';
						}
					}
				}
				
				// Post to Facebook
				if($social_type === 'facebook' || $social_type === 'both') {
					
					// Prepare post with image
					if(strlen($input['image'])) {
						
						// Covertly upload image to FB, get response, decoded & grab FB image ID
						$fb_image = str_replace('../images/image_files/', 'https://vk.gy/images/', $input['image']);
						$fb_response = $this->fb->post('/'.$this->fb_page_id.'/photos', [ 'url' => $fb_image, 'published' => false ]);
						$fb_photo_id = $fb_response->getDecodedBody();
						$fb_photo_param = [ '{"media_fbid":"'.$fb_photo_id['id'].'"}' ];
						
					}
					
					// Post
					if($this->fb->post(
						$this->fb_page_id.'/feed/', [
						'message' => $input['content'],
						'link' => $fb_photo_param ? null : ($input['url'] ?: null),
						'attached_media' => $fb_photo_param ?: null
					])) {
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t post to Facebook.';
					}
					
				}
				
			}
			
			$output['status'] = $output['status'] ?: 'error';
			
			return $output;
		}
		
		// ======================================================
		// Queue social media post for later posting
		// ======================================================
		function queue_post($input, $social_type, $date_occurred) {
			
			// Check that post content not empty, post type is specified, social media type is specified, and post date is specified
			if(
				is_array($input) && 
				strlen($input['content']) && 
				in_array($input['item_type'], $this->allowed_item_types) && 
				in_array($social_type, $this->allowed_social_types) && 
				strlen($date_occurred)
			) {
				
				// Check if social media post is already queued
				$sql_check = 'SELECT id FROM queued_social WHERE social_type=? AND item_type=? AND item_id=? LIMIT 1';
				$stmt_check = $this->pdo->prepare($sql_check);
				$stmt_check->execute([ $social_type, $input['item_type'], $input['item_id'] ]);
				$rslt_check = $stmt_check->fetchColumn();
				
				// If already queued, update queued social media post
				if(is_numeric($rslt_check)) {
					$sql_update = 'UPDATE queued_social SET content=?, image=?, date_occurred=?, url=? WHERE id=? LIMIT 1';
					$stmt_update = $this->pdo->prepare($sql_update);
					if($stmt_update->execute([ $input['content'], $input['image'], $date_occurred, $input['url'], $rslt_check ])) {
						
						// Return that social media post updated
						$output['result'] = 'Social media post updated, and post time reset.';
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t edit queued social media post.';
					}
				}
				
				// Otherwise, add new social media post to queue
				else {
					$sql_queue = 'INSERT INTO queued_social (content, image, date_occurred, url, social_type, item_type, item_id) VALUES (?, ?, ?, ?, ?, ?, ?)';
					$stmt_queue = $this->pdo->prepare($sql_queue);
					if($stmt_queue->execute([ $input['content'], $input['image'], $date_occurred, $input['url'], $social_type, $input['item_type'], $input['item_id'] ])) {
						
						// Return that social media post queued
						$output['result'] = 'Social media post added.';
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t add social media post.';
					}
				}
			}
			else {
				$output['result'] = 'Missing elements required to queue social media post.';
			}
			
			$output['status'] = $output['status'] ?: 'error';
			
			return $output;
		}
	}
?>