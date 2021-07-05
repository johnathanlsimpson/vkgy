<?php
	include_once('../php/include.php');
	include_once('../php/class-access_comment.php');
	include_once('../php/class-song.php');
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
			$this->access_song = new song($pdo);
			
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
		// Guess song associated with video
		// ======================================================
		public function guess_song( $video_id ) {
			
			if( is_numeric($video_id) ) {
				
				// Get video and artist
				$video = $this->access_video([ 'id' => $video_id, 'get' => 'basics' ]);
				
				// Get possible songs
				$songs = $this->access_song->access_song([ 'artist_id' => $video['artist']['id'], 'get' => 'name' ]);
				
				// Order songs by longest flat name so we're less likely to get incorrect songs
				usort($songs, function($a, $b) {
					return mb_strlen( html_entity_decode( $b['flat_name'] ) ) - mb_strlen( html_entity_decode( $a['flat_name'] ) );
				});
				
				// Clean name as much as possible for best result
				$cleaned_youtube_name = $this->clean_title( $video['youtube_name'], $video['artist'] );
				
				// Then flatten cleaned name
				$flattened_youtube_name = $this->access_song->flatten_song_title( $cleaned_youtube_name );
				
				// Then loop through each song and if that song's flat title is within the flattend YT name, assume it's the right song
				foreach($songs as $song) {
					if( strpos( $flattened_youtube_name, $song['flat_name'] ) !== false ) {
						$matched_song = $song;
						break;
					}
				}
				
			}
			
			return $matched_song;
			
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
						
						// Get artist class
						if(!$this->access_artist) {
							include_once('../php/class-access_artist.php');
							$this->access_artist = new access_artist($this->pdo);
						}
						
						// If user is an admin (who can approve channel anyway), approve and add channel to artist's links
						if($is_flagged && $_SESSION['can_bypass_video_approval'] ) {
							
							$is_flagged = false;
							$link_output = $this->access_link->add_link( 'https://youtube.com/channel/'.$video_data['channel_id'], $artist_id );
							
							//$this->access_artist->update_url($artist_id, 'https://youtube.com/channel/'.$video_data['channel_id']);
						}
						
						// Get artist
						$artist = $this->access_artist->access_artist([ 'id' => $artist_id, 'get' => 'name' ]);
						
						// Do quick cleanup of name
						$youtube_name = $video_data['name'];
						$cleaned_name = $this->clean_title( $youtube_name, $artist );
						
						// Try to find matching song in DB
						$song = $this->access_song->guess_song([ 'name' => $cleaned_name, 'artist_id' => $artist_id, 'type' => 'fuzzy' ]);
						
						// If we found a song, let's update the clean name, set romaji, and set song ID
						if( is_array($song) && !empty($song) ) {
							$song_id = $song['id'];
						}
						
						// If we didn't find a song, let's save the cleaned name
						// Actually, let's *not* save it for now until our cleaning algorithm is a little better
						else {
						}
						
						// Set up values
						$values_video = [
							'artist_id'       => $artist_id,
							'youtube_id'      => $video_id,
							'song_id'         => is_numeric($song_id) ? $song_id : null,
							'type'            => $this->guess_video_type( $youtube_name ),
							'youtube_name'    => sanitize( $youtube_name ),
							'youtube_content' => sanitize($video_data['content']),
							'name'            => $cleaned_name,
							'image_url'       => sanitize( $video_data['image_url'] ) ?: null,
							'date_occurred'   => sanitize($video_data['date_occurred']),
							'user_id'         => $_SESSION['user_id'],
							'is_flagged'      => $is_flagged ? 1 : 0,
						];
						
						// Prepare query
						$sql_video = 'INSERT INTO videos ('.implode( ',', array_keys($values_video) ).') VALUES ('.substr( str_repeat( '?,', count($values_video) ), 0, -1 ).')';
						$stmt_video = $this->pdo->prepare($sql_video);
						
						// Run query
						if( $stmt_video->execute( array_values( $values_video ) ) ) {
							
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
				
				if( preg_match('/'.$artist_pattern.'/ui', $name) ) {
					$name = preg_replace('/'.$artist_pattern.'/ui', '', $name, 1);
				}
				elseif( strlen($artist_romaji) ) {
					$name = preg_replace('/'.$romaji_pattern.'/ui', '', $name, 1);
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
				
				// If complex MV pattern matched, remove it
				if( preg_match('/'.$mv_pattern.'/ui', $name, $mv_matches, PREG_OFFSET_CAPTURE) ) {
					
					$mv_position = mb_strpos( $name, $mv_matches[0][0], 0, 'UTF-8' );
					$mv_length = mb_strlen( $mv_matches[0][0], 'UTF-8' );
					
					$name = mb_substr( $name, 0, $mv_position, 'UTF-8' ).mb_substr( $name, $mv_position + $mv_length, null, 'UTF-8' );
					
				}
				
				// Replace 'full album' 'nth single' etc
				$nth_release_pattern = '((?:first )?(?:\d+(?:th|nd|rd|st))? ?(?:maxi|mini|full|digital|new|concept|ミニ|フル|ニュー|コンセプト)?(?:\-|・)?(?:single|album|digital|EP|track|mini|シングル|アルバム))';
				$name = preg_replace('/'.$nth_release_pattern.'/ui', '', $name);
				
				// Remove release date
				$date_pattern = '(\d{4}(?:\.\d+){0,2} release)';
				$name = preg_replace('/'.$date_pattern.'/ui', '', $name);
				
				// Now do simple search/replace for common strings
				foreach([
					'spot',
					'live ver.',
					'official audio',
					'live clip',
					'【Live】',
					'(youtube mix)',
					'hd',
					'OFFICIAL LIVE',
					'(hd)',
					'【FULL】',
					' full',
					'(未発表デモテープ)',
					'lyric',
					'lylic',
					'自主制作',
					'デモ音源',
					'promotion edit ver.',
					'【無料公開】',
					'【公式】',
					'試聴Teaser',
					'(Audio)',
				] as $search) {
					$name = str_ireplace($search, '', $name);
				}
				
				// Do quick trim for next steps
				$name = trim($name);
				
				// Now set a list of bracket pairs that we'll use
				$bracket_pairs = [
					'[]',
					'()',
					'【】',
					'--',
					'「」',
					'『』',
					' -',
					'- ',
				];
				
				// Loop through bracket pairs, remove empty pairs
				foreach($bracket_pairs as $bracket_pair) {
					if( mb_strpos( $name, $bracket_pair ) === 0 ) {
						$name = mb_substr( $name, 2 );
					}
					elseif( mb_substr( $name, -2 ) == $bracket_pair ) {
						$name = mb_substr( $name, 0, -2 );
					}
				}
				
				// Do quick trim for next steps
				$name = trim($name);
				
				// Now loop through the pairs again and if they're surrounding the entire remaining string, remove
				foreach($bracket_pairs as $bracket_pair) {
					
					$char_before = mb_substr($name, 0, 1, 'UTF-8');
					$char_after = mb_substr($name, -1, 1, 'UTF-8');
					
					if( $char_before.$char_after === $bracket_pair ) {
						$name = mb_substr( $name, 1, -1 );
						break;
					}
					
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
				
				'lyric' => [
					'lyric video',
					'lyrics',
					'lyric',
					'リリックビデオ',
					'デモ音源',
					'audio',
				],
				
				'trailer' => [
					'trailer',
					'全曲',
					'視聴',
					'試聴',
					'全曲試聴',
					'short',
				],
				
				'cm-1' => [
					'mv spot',
					'music video spot',
					'pv spot',
				],
				
				'mv' => [
					'mv',
					'music video',
					'musicvideo',
					'official video',
					'full spot',
					'spot full',
					'pv',
				],
				
				'cm-2' => [
					'clip',
					'spot',
					'teaser',
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
						
						// Allows multiple passes
						$type_name = preg_replace('/'.'\-\d'.'/', '', $type_name);
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
					
					// In case we want to inspect data
					//echo $_SESSION['username'] === 'inartistic' ? print_r($data_item,true) : null;
					
					$output[$data_item->id] = [
						'name'          => $snippet['title'],
						'content'       => $snippet['description'],
						'date_occurred' => date('Y-m-d H:i:s', strtotime($snippet['publishedAt'])),
						'channel_id'    => $snippet['channelId'],
						'channel_name'  => $snippet['channelTitle'],
						'num_likes'     => number_format($statistics['likeCount'] ?: 0),
						'num_views'     => number_format($statistics['viewCount']),
						'image_url'     => end( $snippet['thumbnails'] )->url,
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
			
			if( $args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'name' ) {
				$sql_select[] = 'videos.song_id';
				$sql_select[] = 'videos.image_url';
				$sql_select[] = 'IF( videos.is_custom=0 AND songs.name IS NOT NULL, songs.name, COALESCE( videos.name, videos.youtube_name ) ) AS name';
				$sql_select[] = 'IF( videos.is_custom=0 AND songs.romaji IS NOT NULL, songs.romaji, COALESCE( videos.romaji, "" ) ) AS romaji';
			}
			
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
				$sql_select[] = 'views_videos_daily.num_views';
				$sql_select[] = 'videos.is_flagged';
				$sql_select[] = 'videos.artist_id';
				$sql_select[] = 'videos.user_id';
			}
			if($args['get'] === 'count') {
				$sql_select[] = 'COUNT(1) AS num_videos';
			}
			
			// FROM ------------------------------------------------
			$sql_from = 'videos';
			
			// JOIN ------------------------------------------------
			
			// Songs
			if( $args['get'] === 'all' || $args['get'] === 'basics' || $args['get'] === 'name' ) {
				$sql_join[] = 'LEFT JOIN songs ON songs.id=videos.song_id';
			}
			
			if($args['get'] === 'all' || $args['get'] === 'basics') {
				$sql_join[] = 'LEFT JOIN views_videos_daily ON views_videos_daily.video_id=videos.id';
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
			// Song
			if( is_numeric($args['song_id']) ) {
				$sql_where[] = 'videos.song_id=?';
				$sql_values[] = $args['song_id'];
			}
			
			// ORDER -----------------------------------------------
			$sql_order = $args['order'] ? (is_array($args['order']) && !empty($args['order']) ? $args['order'] : [ $args['order'] ]) : [ 'videos.date_occurred DESC' ];
			
			// PAGINATION ------------------------------------------
			
			// Specific page
			if( is_numeric($args['page']) || $args['page'] === 'last' ) {
				
				// Get page totals (considering any filters in use)
				$sql_total = '
					SELECT COUNT(videos.id) AS num_items
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
						
						// Save all returned artist/release IDs so we can get artists
						for($i=0; $i<$num_videos; $i++) {
							$artist_ids[] = $rslt_videos[$i]['artist_id'];
						}
						
						// Remove duplicates and empties
						$artist_ids = array_filter(array_unique($artist_ids));
						
						// Get artists
						if(is_array($artist_ids) && !empty($artist_ids)) {
							$artists = $this->access_artist->access_artist([ 'ids' => $artist_ids, 'get' => 'name', 'associative' => true ]);
						}
						
						for($i=0; $i<$num_videos; $i++) {
							
							// Get user data
							$rslt_videos[$i]['user'] = $this->access_user->access_user([ 'id' => $rslt_videos[$i]['user_id'], 'get' => 'name' ]);
							
							// Attach artists
							if(is_numeric($rslt_videos[$i]['artist_id'])) {
								$rslt_videos[$i]['artist'] = $artists[$rslt_videos[$i]['artist_id']];
							}
							
						}
						
					}
					
					// Get additional data
					if($args['get'] === 'all') {
							
							for($i=0; $i<$num_videos; $i++) {
								
								// Get comments
								$rslt_videos[$i]['comments'] = $this->access_comment->access_comment([ 'id' => $rslt_videos[$i]['id'], 'get_user_likes' => true, 'type' => 'video', 'get' => 'all' ]);
								
								// Get song
								if( is_numeric( $rslt_videos[$i]['song_id'] ) ) {
									$rslt_videos[$i]['song'] = $this->access_song->access_song([ 'id' => $rslt_videos[$i]['song_id'], 'get' => 'basics' ]);
								}
								
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
				
				if( is_numeric($args['id']) || strlen($args['youtube_id']) || $args['limit'] == 1 ) {
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