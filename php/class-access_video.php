<?php
	include_once('../php/include.php');
	include_once('../php/class-access_comment.php');
	include_once('../php/class-link.php');
	
	class access_video {
		private $curl_handler;
		public $video_types;
		public $video_type_descriptions;
		public $flag_reasons;
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			
			$this->pdo = $pdo;
			
			// Access user
			$this->access_user = new access_user($this->pdo);
			$this->access_comment = new access_comment($this->pdo);
			$this->access_link = new link($this->pdo);
			
			// Video types
			$this->video_types = [
				'other' => 0,
				'mv' => 1,
				'live' => 2,
				'trailer' => 3,
				'cm' => 4,
				'lyric' => 5,
			];
			
			// Video type descriptions
			$this->video_type_descriptions = [
				'mv' => 'MV',
				'cm' => 'CM',
			];
			
			// Flags
			$this->flag_reasons = [
				'unapproved' => 1,
				'unofficial' => 2,
				'broken'     => 3,
			];
			
		}
		
		
		
		// ======================================================
		// Pull ID from YouTube link
		// ======================================================
		function get_youtube_id($input_url) {
			$id_pattern = '(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})';
			
			if(preg_match('/'.$id_pattern.'/', $input_url, $id_match)) {
				return $id_match[1];
			}
		}
		
		
		
		// ======================================================
		// Given ID, add video to database
		// ======================================================
		function add_video($video_id, $artist_id = null) {
			
			// Check if video already in DB with that artist
			$sql_check = 'SELECT 1 FROM videos WHERE youtube_id=? AND artist_id=? LIMIT 1';
			$stmt_check = $this->pdo->prepare($sql_check);
			$stmt_check->execute([ $video_id, $artist_id ]);
			if($stmt_check->fetchColumn()) {
			}
			
			// If not in DB already, try to get video data from YT
			else {
				$video_data = $this->get_youtube_data($video_id)[0];
			}
			
			if(is_array($video_data) && !empty($video_data)) {
				
				// If channel ID found, allow upload to continue
				if(strlen($video_data['channel_id'])) {
					
					// Set default as being flagged; unset later if channel already approved
					$is_flagged = true;
					
					// If artist provided, check if video is from official channel
					// If artist not provided, try to find artist with that channel listed
					$sql_artist = 'SELECT artist_id FROM artists_urls WHERE '.(is_numeric($artist_id) ? 'artist_id=? AND ' : null).' content LIKE CONCAT("%", ?, "%") LIMIT 1';
					$values_artist[] = 'youtube.com/channel/'.$video_data['channel_id'];
					if(is_numeric($artist_id)) {
						array_unshift($values_artist, $artist_id);
					}
					
					$stmt_artist = $this->pdo->prepare($sql_artist);
					$stmt_artist->execute($values_artist);
					$rslt_artist = $stmt_artist->fetchColumn();
					
					// If artist was found by searching official channel, do one more check
					// to see if video is already uploaded
					if(is_numeric($rslt_artist)) {
						$artist_id = $rslt_artist;
						
						// If video with same ID/artist is found, unset artist ID so query doesn't continue
						$sql_check = 'SELECT 1 FROM videos WHERE youtube_id=? AND artist_id=? LIMIT 1';
						$stmt_check = $this->pdo->prepare($sql_check);
						$stmt_check->execute([ $video_id, $artist_id ]);
						if($stmt_check->fetchColumn()) {
							unset($artist_id);
						}
						
						// If video not already in DB, continue
						else {
							$is_flagged = false;
						}
					}
					
					// If artist was provided, or was found by searching links, go ahead
					if(is_numeric($artist_id)) {
						
						// If user is an admin (who can approve channel anyway), approve and add channel to artist's links
						if($is_flagged && $_SESSION['can_bypass_video_approval'] ) {
							$is_flagged = false;
							
							// Get artist class
							if(!$this->access_artist) {
								include_once('../php/class-access_artist.php');
								$this->access_artist = new access_artist($this->pdo);
							}
							
							$link_output = $this->access_link->add_link( 'https://youtube.com/channel/'.$video_data['channel_id'], $artist_id );
							
							//$this->access_artist->update_url($artist_id, 'https://youtube.com/channel/'.$video_data['channel_id']);
						}
						
						$values_video = [
							$artist_id,
							is_numeric($release_id) ? $release_id : null,
							$_SESSION['user_id'],
							$video_id,
							sanitize($video_data['name']),
							sanitize($video_data['content']),
							$this->guess_video_type($video_data['name']),
							sanitize($video_data['date_occurred']),
							$is_flagged ? 1 : 0,
						];
						
						$sql_video = 'INSERT INTO videos (artist_id, release_id, user_id, youtube_id, youtube_name, youtube_content, type, date_occurred, is_flagged) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
						$stmt_video = $this->pdo->prepare($sql_video);
						if($stmt_video->execute($values_video)) {
							
							$output = array_merge($video_data, [
								'approval_notice_class' => $is_flagged ? null : 'any--hidden',
								'video_id' => $this->pdo->lastInsertID(),
								'artist_id' => $artist_id,
								'youtube_id' => $video_id,
								'username' => $_SESSION['username']
							]);
							
						}
						
					}
				}
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Given title, attempt to remove superfluous info
		// ======================================================
		function clean_title($name, $artist) {
			
			if( strlen($name) && is_array($artist) && !empty($artist) ) {
				
				$name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
				
				$artist_name = html_entity_decode($artist['name'], ENT_QUOTES, 'UTF-8');
				$artist_romaji = html_entity_decode($artist['romaji'], ENT_QUOTES, 'UTF-8');
				
				$artist_pattern =
					''.
					' ?(\- )?(\| ?|\/ ?)?'. // hyphen or slash after title
					'[\[\【]?'.      // opening brackets
					preg_quote($artist_name, '/').    // name
					'[\]\】]?'.      // closing brackets
					' ?(: |\- )?(\| ?|\/ ?)?'. // hyphen or slash before title
					'';
				
				$romaji_pattern =
					''.
					' ?(\- )?(\| ?|\/ ?)?'. // hyphen or slash after title
					'[\[\【]?'.      // opening brackets
					preg_quote($artist_romaji, '/').    // name
					'[\]\】]?'.      // closing brackets
					' ?(: |\- )?(\| ?|\/ ?)?'. // hyphen or slash before title
					'';
				
				if( preg_match('/'.$artist_pattern.'/u', $name) ) {
					$name = preg_replace('/'.$artist_pattern.'/u', '', $name, 1);
				}
				elseif( strlen($artist_romaji) ) {
					$name = preg_replace('/'.$romaji_pattern.'/u', '', $name, 1);
				}
				
				$mv_pattern =
				'('.
				'(【公式】|official|full-?|photo|studio)?'.
				' ?'.
				'(lyric|リリック)?'.
				' ?'.
				'(mv|music ?video|music ?clip|pv|video|ビデオ|version)'.
				'-? ?'.
				'(full version|full ?ver.?|full|official)?'.
				' ?'.
				'(cm|spot|trailer)?'.
				')';
				
				if( preg_match('/'.$mv_pattern.'/ui', $name, $mv_matches, PREG_OFFSET_CAPTURE) ) {
					
					$mv_position = mb_strpos( $name, $mv_matches[0][0], 0, 'UTF-8' );
					$mv_length = mb_strlen( $mv_matches[0][0], 'UTF-8' );
					
					$char_before = mb_substr($name, $mv_position - 1, 1, 'UTF-8');
					$char_after = mb_substr($name, $mv_position + $mv_length, 1, 'UTF-8');
					
					$bracket_pairs = [
						'[]',
						'()',
						'【】',
						'--',
					];
					
					foreach($bracket_pairs as $bracket_pair) {
						if( $char_before.$char_after === $bracket_pair ) {
							$mv_position--;
							$mv_length = $mv_length + 2;
							break;
						}
					}
					
					$name = mb_substr( $name, 0, $mv_position, 'UTF-8' ).mb_substr( $name, $mv_position + $mv_length, null, 'UTF-8' );
					
				}
				
				$name = sanitize($name);
				
			}
			
			return $name;
			
		}
		
		
		
		// ======================================================
		// Given title, guess type of video
		// ======================================================
		function guess_video_type($name) {
			
			// Un-sanitize and clean
			$name = html_entity_decode( $name, ENT_QUOTES, 'UTF-8' );
			$name = strtolower($name);
			
			// Strings to search for
			$search_strings = [
				
				'cm' => [
					'clip',
					'spot',
					'teaser',
				],
				
				'lyric' => [
					'lyric video',
					'lyrics',
					'lyric',
					'リリックビデオ',
				],
				
				'mv' => [
					'mv',
					'music video',
					'musicvideo',
					'official video',
					'pv',
				],
				
				'trailer' => [
					'trailer',
					'全曲',
					'視聴',
				],
				
				'live' => [
					'live',
					'ライブ',
				],
				
			];
			
			// Search for strings and set type accordingly
			foreach( $search_strings as $type_name => $strings ) {
				foreach( $strings as $string ) {
					
					if( strpos($name, $string) !== false ) {
						return $this->video_types[$type_name];
					}
					
				}
			}
			
			// Default to 'other'
			return $this->video_types['other'];
			
		}
		
		
		
		// ======================================================
		// Given ID, pull data from YouTube
		// ======================================================
		function get_youtube_data($input_ids, $associative = false) {
			
			// Get YT API key
			include('../php/class-access_video-key.php');
			
			// Clean up input
			if(!is_array($input_ids) && strlen($input_ids)) {
				$input_ids = [ $input_ids ];
			}
			
			// Begin building URL
			$url =
				'https://www.googleapis.com/youtube/v3/videos?'.
				'key='.$youtube_key.'&'.
				'id='.implode(',', $input_ids).'&'.
				'part=snippet,statistics,contentDetails';
			
			// Open curl, run API call, close curl
			$curl_handler = curl_init();
			curl_setopt_array($curl_handler, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_REFERER => 'https://vk.gy'
			]);
			$data = curl_exec($curl_handler);
			curl_close($curl_handler);
			
			// Transform data
			$data = json_decode($data);
			
			if(is_object($data) && !empty($data) && $data->items && !empty($data->items)) {
				foreach($data->items as $data_item) {
					$snippet = (array) $data_item->snippet;
					$statistics = (array) $data_item->statistics;
					$details = (array) $data_item->contentDetails;
					
					$output[$data_item->id] = [
						'name' => $snippet['title'],
						'content' => $snippet['description'],
						'date_occurred' => date('Y-m-d H:i:s', strtotime($snippet['publishedAt'])),
						'channel_id' => $snippet['channelId'],
						'num_likes' => number_format($statistics['likeCount'] ?: 0),
						'num_views' => number_format($statistics['viewCount']),
						'length' => preg_replace('/'.'PT(\d+)M(\d+)S'.'/', '00:$1:$2', $details['duration']),
					];
				}
			}
			
			// Reset keys unless asked for associative array
			if(is_array($output) && $associative !== true) {
				$output = array_values($output);
			}
			
			return $output;
		}
		
		
		
		// ======================================================
		// Check if user needs permission to add w/out approval
		// ======================================================
		function check_user_video_permissions($video_id) {
			
			if(is_numeric($video_id)) {
				
				// Find other approved videos which were added by the user who uploaded the video in question
				$sql_approved = '
					SELECT approved_videos.user_id, approved_videos.artist_id
					FROM videos 
					LEFT JOIN videos approved_videos ON approved_videos.user_id=videos.user_id AND approved_videos.is_flagged=0 
					WHERE videos.id=?
					GROUP BY approved_videos.artist_id';
				$stmt_approved = $this->pdo->prepare($sql_approved);
				$stmt_approved->execute([ $video_id ]);
				$rslt_approved = $stmt_approved->fetchAll();
				
				// If at least five videos, spread over at least five artists, have been approved, allow user to upload w/out needing approval
				if( is_array($rslt_approved) && !empty($rslt_approved) && count($rslt_approved) >= 5 ) {
					
					$this->give_user_video_permission($rslt_approved[0]['user_id']);
					
				}
				
			}
			
		}
		
		
		
		// ======================================================
		// Give permission to add w/out approval
		// ======================================================
		function give_user_video_permission($user_id) {
			
			if( is_numeric($user_id) ) {
				
				$access_user = new access_user($this->pdo);
				
				// Give user permission to bypass approval
				$access_user->change_permission($user_id, 'can_bypass_video_approval', true );
				
				// Approve all videos from user with default ('not yet reviewed') flag
				$sql_approve = 'UPDATE videos SET is_flagged=? WHERE user_id=? AND is_flagged=?';
				$stmt_approve = $this->pdo->prepare($sql_approve);
				$stmt_approve->execute([ 0, $user_id, 1 ]);
				
			}
			
		}
		
		
		
		// ======================================================
		// Build 'video' object
		// ======================================================
		function access_video($args = []) {
			$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
			
			// SELECT ----------------------------------------------
			if($args['get'] === 'basics' || $args['get'] === 'all') {
				$sql_select[] = 'videos.id';
				$sql_select[] = 'videos.youtube_id';
				$sql_select[] = 'CONCAT("https://youtu.be/", videos.youtube_id) AS url';
				$sql_select[] = 'CONCAT("https://img.youtube.com/vi/", videos.youtube_id, "/mqdefault.jpg") AS thumbnail_url';
				$sql_select[] = 'videos.is_flagged';
				$sql_select[] = 'videos.youtube_name';
				$sql_select[] = 'videos.youtube_content';
				$sql_select[] = 'videos.type';
				$sql_select[] = 'videos.date_occurred';
				$sql_select[] = 'videos.date_added';
				$sql_select[] = 'views_daily_videos.num_views';
				$sql_select[] = 'videos.length';
				$sql_select[] = 'videos.is_flagged';
				$sql_select[] = 'videos.artist_id';
				$sql_select[] = 'videos.release_id';
				$sql_select[] = 'videos.user_id';
			}
			if($args['get'] === 'count') {
				$sql_select[] = 'COUNT(*) AS num_videos';
			}
			
			// FROM ------------------------------------------------
			$sql_from = 'videos';
			
			// JOIN ------------------------------------------------
			if($args['get'] === 'all' || $args['get'] === 'basics') {
				$sql_join[] = 'LEFT JOIN views_daily_videos ON views_daily_videos.video_id=videos.id';
			}
			
			// WHERE -----------------------------------------------
			// ID
			if(is_numeric($args['id'])) {
				$sql_where[] = 'videos.id=?';
				$sql_values[] = $args['id'];
			}
			// Multiple IDs
			if( is_array($args['ids']) && !empty($args['ids']) ) {
				
				$args['ids'] = array_filter( $args['ids'], 'is_numeric' );
				
				if( is_array($args['ids']) && !empty($args['ids']) ) {
					
					$sql_where[] = 'videos.id IN ('.substr(str_repeat('?, ', count($args['ids'])), 0, -2).')';
					$sql_values = array_merge($sql_values, $args['ids']);
					
				}
				
			}
			if(is_numeric($args['artist_id'])) {
				$sql_where[] = 'videos.artist_id=?';
				$sql_values[] = $args['artist_id'];
			}
			// Release ID
			if(is_numeric($args['release_id'])) {
				$sql_where[] = 'videos.release_id=?';
				$sql_values[] = $args['release_id'];
			}
			// Date published
			if( preg_match('/'.'^\d{4}(-\d{2})?(-\d{2})?$'.'/', $args['date_occurred']) ) {
				$sql_where[] = 'videos.date_occurred LIKE CONCAT(?, "%")';
				$sql_values[] = friendly($args['date_occurred']);
			}
			// Single type
			if(is_numeric($args['type'])) {
				$sql_where[] = 'videos.type=?';
				$sql_values[] = $args['type'];
			}
			// Multiple types
			if(is_array($args['type']) && !empty($args['type'])) {
				foreach($args['type'] as $type_key => $type) {
					if(is_numeric($type)) {
						$type_wheres[] = 'videos.type=?';
						$sql_values[] = $type;
					}
				}
				if(is_array($type_wheres) && !empty($type_wheres)) {
					$sql_where[] = '('.implode(' OR ', $type_wheres).')';
				}
			}
			// Approval
			if($args['is_approved']) {
				$sql_where[] = 'videos.is_flagged=?';
				$sql_values[] = 0;
			}
			// Flagged
			if( is_numeric($args['is_flagged']) && $args['is_flagged'] >= 0 ) {
				$sql_where[] = 'videos.is_flagged=?';
				$sql_values[] = $args['is_flagged'];
			}
			// User
			if( is_numeric($args['user_id']) ) {
				$sql_where[] = 'videos.user_id=?';
				$sql_values[] = $args['user_id'];
			}
			// YouTube ID
			if( strlen($args['youtube_id']) ) {
				$sql_where[] = 'videos.youtube_id=?';
				$sql_values[] = $args['youtube_id'];
			}
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'videos.date_occurred DESC' ];
			
			// PAGINATION ------------------------------------------
			
			// Specific page
			if( is_numeric($args['page']) || $args['page'] === 'last' ) {
				
				// Get page totals (considering any filters in use)
				$sql_total = '
					SELECT COUNT(id) AS num_items
					FROM '.$sql_from.'
					'.(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).'
					'.(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null);
				$stmt_total = $this->pdo->prepare($sql_total);
				$stmt_total->execute($sql_values);
				
				// Calculations
				$limit = $args['limit'] && is_numeric($args['limit']) ? $args['limit'] : 100;
				$num_items = $stmt_total->fetchColumn() ?: 0;
				$num_pages = ceil( $num_items / $limit );
				$current_page = $args['page'];
				$offset = $current_page * $limit - $limit;
				$offset = $offset ?: 0;
				
				// If requested page > extant pages, reset to last page
				if( $current_page > $num_pages ) {
					$current_page = $num_pages;
				}
				
				// Set query limit
				$sql_limit = $offset.', '.$limit;
				
				// Pass meta data
				$item_count = [
					'num_items' => $num_items,
					'num_pages' => $num_pages,
					'current_page' => $current_page,
					'limit' => $limit,
				];
				
			}
			
			// Specific limit
			elseif( is_numeric($args['limit']) ) {
				$sql_limit = $args['limit'];
			}
			
			// Default limit
			else {
				$sql_limit = 100;
			}
			
			// LIMIT -----------------------------------------------
			$sql_limit = $sql_limit ?: ( $args['limit'] ?: ($args['get'] === 'count' || strlen($args['artist_id']) ? null : '100') );
			
			// BUILD QUERY -----------------------------------------
			$sql_videos = '
				SELECT '.implode(', ', $sql_select).'
				FROM '.$sql_from.'
				'.(is_array($sql_join) && !empty($sql_join) ? implode(' ', $sql_join) : null).'
				'.(is_array($sql_where) && !empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).'
				ORDER BY '.implode(', ', $sql_order).'
				'.(strlen($sql_limit) ? 'LIMIT '.$sql_limit : null).'
			';
			$stmt_videos = $this->pdo->prepare($sql_videos);
			
			// EXECUTE QUERY ---------------------------------------
			if($stmt_videos->execute($sql_values)) {
				$rslt_videos = $stmt_videos->fetchAll();
				
				if(is_array($rslt_videos) && !empty($rslt_videos)) {
					$num_videos = count($rslt_videos);
					
					// FORMAT DATA -------------------------------------
					
					// Get additional data
					if( $args['get'] === 'basics' || $args['get'] === 'all' ) {
						
						// Get artist class
						if(!$this->access_artist) {
							include_once('../php/class-access_artist.php');
							$this->access_artist = new access_artist($this->pdo);
						}
						
						// Get release class
						if(!$this->access_release) {
							include_once('../php/class-access_release.php');
							$this->access_release = new access_release($this->pdo);
						}
						
						// Save all returned artist/release IDs so we can get artists
						for($i=0; $i<$num_videos; $i++) {
							$artist_ids[] = $rslt_videos[$i]['artist_id'];
							$release_ids[] = $rslt_videos[$i]['release_id'];
						}
						
						// Remove duplicates and empties
						$artist_ids = array_filter(array_unique($artist_ids));
						$release_ids = array_filter(array_unique($release_ids));
						
						// Get artists
						if(is_array($artist_ids) && !empty($artist_ids)) {
							$artists = $this->access_artist->access_artist([ 'ids' => $artist_ids, 'get' => 'name', 'associative' => true ]);
						}
						
						// Get releases
						if(is_array($release_ids) && !empty($release_ids)) {
							$releases = $this->access_release->access_release([ 'ids' => $release_ids, 'get' => 'name', 'associative' => true ]);
						}
						
						for($i=0; $i<$num_videos; $i++) {
							
							// Get user data
							$rslt_videos[$i]['user'] = $this->access_user->access_user([ 'id' => $rslt_videos[$i]['user_id'], 'get' => 'name' ]);
							
							// Attach artists
							if(is_numeric($rslt_videos[$i]['artist_id'])) {
								$rslt_videos[$i]['artist'] = $artists[$rslt_videos[$i]['artist_id']];
							}
							
							// Attach releases
							if(is_numeric($rslt_videos[$i]['release_id'])) {
								$rslt_videos[$i]['release'] = $releases[$rslt_videos[$i]['release_id']];
							}
							
						}
						
					}
					
					// Get additional data
					if($args['get'] === 'all') {
							
							for($i=0; $i<$num_videos; $i++) {
								
								// Get comments
								$rslt_videos[$i]['comments'] = $this->access_comment->access_comment([ 'id' => $rslt_videos[$i]['id'], 'get_user_likes' => true, 'type' => 'video', 'get' => 'all' ]);
								
								/*// If don't have video name or description (legacy code), get it from YT and store it
								//if( !strlen($rslt_videos[$i]['youtube_name']) || !strlen($rslt_videos[$i]['youtube_content']) ) {
								if( !strlen($rslt_videos[$i]['youtube_content']) ) {
									
									// Get data
									$youtube_data = $this->get_youtube_data($rslt_videos[$i]['youtube_id'], true);
									$youtube_data = is_array($youtube_data) && !empty($youtube_data) ? reset($youtube_data) : [];
									
									// Save it
									if( strlen($youtube_data['name']) ) {
										
										// Vars
										$youtube_name = sanitize($youtube_data['name']);
										$youtube_content = sanitize($youtube_data['content']);
										$youtube_date_occurred = sanitize($youtube_data['date_occurred']);
										$video_type = $this->guess_video_type($youtube_name);
										$length = sanitize($youtube_data['length']);
										
										// Save
										$sql_data = 'UPDATE videos SET youtube_name=?, youtube_content=?, date_occurred=?, type=?, length=? WHERE id=? LIMIT 1';
										$stmt_data = $this->pdo->prepare($sql_data);
										$stmt_data->execute([ $youtube_name, $youtube_content, $youtube_date_occurred, $video_type, $length, $rslt_videos[$i]['id'] ]);
										
										// Pass back to result
										$rslt_videos[$i]['youtube_name'] = $youtube_name;
										$rslt_videos[$i]['youtube_content'] = $youtube_content;
										$rslt_videos[$i]['date_occurred'] = $youtube_date_occurred;
										$rslt_videos[$i]['type'] = $video_type;
										$rslt_videos[$i]['length'] = $length;
										
									}
									
									// Unset
									unset($youtube_data, $youtube_name, $youtube_content, $youtube_date_occurred, $video_type);
									
								}*/
								
							}
							
							if(is_array($youtube_data) && !empty($youtube_data)) {
								for($i=0; $i<$num_videos; $i++) {
									$rslt_videos[$i]['data'] = $youtube_data[$rslt_videos[$i]['youtube_id']];
								}
							}
						
					}
					
					// Attach page counts to first item (this is kinda dumb tbh but we'll redo it eventually)
					if( $args['page'] && is_array($item_count) && !empty($item_count) ) {
						$rslt_videos[0]['meta'] = $item_count;
					}
					
					// Get video type
					if( $args['get'] === 'all' || $args['get'] === 'basics' ) {
						
						// Loop through and get type name and flagged reason
						for($i=0; $i<$num_videos; $i++) {
							
							$rslt_videos[$i]['type'] = array_search($rslt_videos[$i]['type'], $this->video_types);
							$rslt_videos[$i]['flagged_reason'] = array_search($rslt_videos[$i]['is_flagged'], $this->flag_reasons);
							
						}
						
					}
					
				}
				
				// FORMAT OUTPUT -------------------------------------
				$rslt_videos = is_array($rslt_videos) ? $rslt_videos : [];
				
				if( is_numeric($args['id']) || strlen($args['youtube_id']) ) {
					$rslt_videos = reset($rslt_videos);
				}
				
				if($args['get'] === 'count' && is_array($rslt_videos) && !empty($rslt_videos)) {
					$rslt_videos = reset($rslt_videos)['num_videos'];
				}
				
				return $rslt_videos;
			}
		}
	}
?>