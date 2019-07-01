<?php
	include_once('../php/include.php');
	
	class access_video {
		
		
		
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
		// Given ID, pull data from YouTube
		// ======================================================
		function get_youtube_data($input_id) {
			include('../php/class-access_video-key.php');
			
			$handle = curl_init();
			$url = 'https://www.googleapis.com/youtube/v3/videos?id='.$input_id.'&key='.$youtube_key.'&part=snippet,statistics';
			
			curl_setopt_array($handle, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_REFERER => 'https://vk.gy'
			]);
			
			$data = curl_exec($handle);
			$data = json_decode($data);
			curl_close($handle);
			
			if(is_object($data) && !empty($data)) {
				$snippet = (array) $data->items[0]->snippet;
				$statistics = (array) $data->items[0]->statistics;
				
				$output = [
					'name' => $snippet['title'],
					'content' => explode("\n", $snippet['description'])[0],
					'date_occurred' => date('Y-m-d H:i:s', strtotime($snippet['publishedAt'])),
					'channel_id' => $snippet['channelId'],
					'num_likes' => number_format($statistics['likeCount'] ?: 0),
					'num_views' => number_format($statistics['viewCount']),
				];
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
				$sql_select[] = 'artists_videos.id';
				$sql_select[] = 'artists_videos.youtube_id';
				$sql_select[] = 'artists_videos.is_flagged';
			}
			if($args['get'] === 'all') {
				$sql_select[] = 'artists_videos.date_added';
				$sql_select[] = 'artists_videos.artist_id';
				$sql_select[] = 'artists_videos.release_id';
				$sql_select[] = 'users.username';
			}
			if($args['get'] === 'count') {
				$sql_select[] = 'COUNT(*) AS num_videos';
			}
			
			// FROM ------------------------------------------------
			$sql_from = 'artists_videos';
			if($args['get'] === 'all') {
				$sql_join[] = 'LEFT JOIN users ON users.id=artists_videos.user_id';
			}
			
			// WHERE -----------------------------------------------
			if(is_numeric($args['id'])) {
				$sql_where[] = 'artists_videos.id=?';
				$sql_values[] = $args['id'];
			}
			if(is_numeric($args['artist_id'])) {
				$sql_where[] = 'artists_videos.artist_id=?';
				$sql_values[] = $args['artist_id'];
			}
			if(is_numeric($args['release_id'])) {
				$sql_where[] = 'artists_videos.release_id=?';
				$sql_values[] = $args['release_id'];
			}
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'artists_videos.date_added DESC' ];
			
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
							
							// Loop through videos and attach artists/release
							for($i=0; $i<$num_videos; $i++) {
								if(is_numeric($rslt_videos[$i]['artist_id'])) {
									$rslt_videos[$i]['artist'] = $artists[$rslt_videos[$i]['artist_id']];
								}
								if(is_numeric($rslt_videos[$i]['release_id'])) {
									$rslt_videos[$i]['release'] = $releases[$rslt_videos[$i]['release_id']];
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