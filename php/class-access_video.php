<?php
	include_once('../php/include.php');
	
	class access_video {
		private $curl_handler;
		
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			
			$this->pdo = $pdo;
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
					$sql_artist = 'SELECT id FROM artists WHERE '.(is_numeric($artist_id) ? 'id=? AND ' : null).' official_links LIKE CONCAT("%", ?, "%") LIMIT 1';
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
						if($is_flagged && $_SESSION['is_admin']) {
							$is_flagged = false;
							
							// Get artist class
							if(!$this->access_artist) {
								include_once('../php/class-access_artist.php');
								$this->access_artist = new access_artist($this->pdo);
							}
							
							$this->access_artist->add_website($artist_id, 'https://youtube.com/channel/'.$video_data['channel_id']);
						}
						
						$values_video = [
							$artist_id,
							is_numeric($release_id) ? $release_id : null,
							$_SESSION['user_id'],
							$video_id,
							$video_data['date_occurred'],
							$is_flagged ? 1 : 0,
						];
						
						$sql_video = 'INSERT INTO videos (artist_id, release_id, user_id, youtube_id, date_occurred, is_flagged) VALUES (?, ?, ?, ?, ?, ?)';
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
				'part=snippet,statistics';
			
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
			
			if(is_object($data) && !empty($data)) {
				foreach($data->items as $data_item) {
					$snippet = (array) $data_item->snippet;
					$statistics = (array) $data_item->statistics;
					
					$output[$data_item->id] = [
						'name' => $snippet['title'],
						'content' => explode("\n", $snippet['description'])[0],
						'date_occurred' => date('Y-m-d H:i:s', strtotime($snippet['publishedAt'])),
						'channel_id' => $snippet['channelId'],
						'num_likes' => number_format($statistics['likeCount'] ?: 0),
						'num_views' => number_format($statistics['viewCount']),
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
		// Build 'video' object
		// ======================================================
		function access_video($args = []) {
			$sql_select = $sql_join = $sql_where = $sql_values = $sql_order = [];
			
			// SELECT ----------------------------------------------
			if($args['get'] === 'basics' || $args['get'] === 'all') {
				$sql_select[] = 'videos.id';
				$sql_select[] = 'videos.youtube_id';
				$sql_select[] = 'videos.is_flagged';
			}
			if($args['get'] === 'all') {
				$sql_select[] = 'videos.date_added';
				$sql_select[] = 'videos.artist_id';
				$sql_select[] = 'videos.release_id';
				$sql_select[] = 'users.username';
			}
			if($args['get'] === 'count') {
				$sql_select[] = 'COUNT(*) AS num_videos';
			}
			
			// FROM ------------------------------------------------
			$sql_from = 'videos';
			if($args['get'] === 'all') {
				$sql_join[] = 'LEFT JOIN users ON users.id=videos.user_id';
			}
			
			// WHERE -----------------------------------------------
			if(is_numeric($args['id'])) {
				$sql_where[] = 'videos.id=?';
				$sql_values[] = $args['id'];
			}
			if(is_numeric($args['artist_id'])) {
				$sql_where[] = 'videos.artist_id=?';
				$sql_values[] = $args['artist_id'];
			}
			if(is_numeric($args['release_id'])) {
				$sql_where[] = 'videos.release_id=?';
				$sql_values[] = $args['release_id'];
			}
			if($args['is_approved']) {
				$sql_where[] = 'videos.is_flagged=?';
				$sql_values[] = 0;
			}
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'videos.date_occurred DESC' ];
			
			// LIMIT -----------------------------------------------
			$sql_limit = $args['limit'] ?: ($args['get'] === 'count' || strlen($args['artist_id']) ? null : '100');
			
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
					if($args['get'] === 'all') {
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
								// Save video IDs to get YT data later
								$video_youtube_ids[] = $rslt_videos[$i]['youtube_id'];
								
								// Attach artists
								if(is_numeric($rslt_videos[$i]['artist_id'])) {
									$rslt_videos[$i]['artist'] = $artists[$rslt_videos[$i]['artist_id']];
								}
								
								// Attach releases
								if(is_numeric($rslt_videos[$i]['release_id'])) {
									$rslt_videos[$i]['release'] = $releases[$rslt_videos[$i]['release_id']];
								}
							}
							
							// Get YT data
							$youtube_data = $this->get_youtube_data($video_youtube_ids, true);
							
							if(is_array($youtube_data) && !empty($youtube_data)) {
								for($i=0; $i<$num_videos; $i++) {
									$rslt_videos[$i]['data'] = $youtube_data[$rslt_videos[$i]['youtube_id']];
								}
							}
					}
				}
				
				// FORMAT OUTPUT -------------------------------------
				$rslt_videos = is_array($rslt_videos) ? $rslt_videos : [];
				
				if($args['get'] === 'count' && is_array($rslt_videos) && !empty($rslt_videos)) {
					$rslt_videos = reset($rslt_videos)['num_videos'];
				}
				
				return $rslt_videos;
			}
		}
	}
?>