<?php
	include_once('../php/include.php');
	include_once('../php/class-access_social_media.php');
	include_once('../php/class-access_image.php');
	include_once('../php/external/twitter-php/OAuth.php');
	include_once('../php/external/twitter-php/Twitter.php');
	require_once('../php/external/facebook/autoload.php');
	include_once('../php/class-access_social_media-key.php');
	
	// Set up image access
	$access_image = new access_image($pdo);
	
	// Set up FB
	$fb_page_id = "407931532968189";
	$fb = new \Facebook\Facebook([
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
	$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	
	// Grab params from Cron Job
	//parse_str($argv[1], $get_params);
	
	// Get top three artists from last week
	$sql_rankings = "
		SELECT
			SUM(artists_views.view_count) AS view_count,
			artists.romaji,
			artists.friendly,
			artists.name
		FROM artists_views
		LEFT JOIN artists ON artists.id=artists_views.artist_id
		WHERE artists_views.date_occurred > ? AND artists_views.date_occurred < ?
		GROUP BY artists_views.artist_id
		ORDER BY view_count DESC
		LIMIT 3
	";
	$stmt_rankings = $pdo->prepare($sql_rankings);
	$stmt_rankings->execute([
		date("Y-m-d", strtotime("-2 weeks sunday", time())),
		date("Y-m-d", strtotime("-1 weeks sunday", time()))
	]);
	$bands = $stmt_rankings->fetchAll();
	
	// Go ahead if results returned
	if(is_array($bands) && !empty($bands)) {
		
		// Loop through bands and get images
		foreach($bands as $band_key => $band) {
			$band_images[] = $access_image->get_image([ 'artist' => $band['friendly'], 'image_path_only' => true, 'not_vip' => true ]);
		}
		
		// Twitter
		//if($get_params['method'] === 'twitter') {
			// Build message
			$twitter_message = '
				Access Ranking ∙ アクセスランキング
				
				'.(date("m.d", strtotime("-2 weeks sunday", time()))).'～'.date("m.d", strtotime("-1 weeks sunday", time())).'
				
				🏆 '.($bands[0]['romaji'] ?: $bands[0]['name']).($bands[0]['romaji'] ? ' ∙ '.$bands[0]['name'] : null).' https://vk.gy/artists/'.$bands[0]['friendly'].'/
				🥈 '.($bands[1]['romaji'] ?: $bands[1]['name']).($bands[1]['romaji'] ? ' ∙ '.$bands[1]['name'] : null).' https://vk.gy/artists/'.$bands[1]['friendly'].'/
				🥉 '.($bands[2]['romaji'] ?: $bands[2]['name']).($bands[2]['romaji'] ? ' ∙ '.$bands[2]['name'] : null).' https://vk.gy/artists/'.$bands[2]['friendly'].'/
			';
			$twitter_message = str_replace("\t", '', $twitter_message);
			$twitter_message = trim($twitter_message);
			$twitter_message = html_entity_decode($twitter_message);
			
			// Tweet
			$twitter->send($twitter_message, $band_images);
		//}
		
		// Facebook
		//if($get_params['method'] === 'facebook') {
			// Build message
			$fb_message = '
				Access Ranking ∙ '.(date("m.d", strtotime("-2 weeks sunday", time()))).'～'.date("m.d", strtotime("-1 weeks sunday", time())).'
				
				🏆 '.($bands[0]['romaji'] ?: $bands[0]['name']).($bands[0]['romaji'] ? ' ∙ '.$bands[0]['name'] : null).'
				🥈 '.($bands[1]['romaji'] ?: $bands[1]['name']).($bands[1]['romaji'] ? ' ∙ '.$bands[1]['name'] : null).'
				🥉 '.($bands[2]['romaji'] ?: $bands[2]['name']).($bands[2]['romaji'] ? ' ∙ '.$bands[2]['name'] : null).'
				
				'.($bands[0]['romaji'] ?: $bands[0]['name']).': https://vk.gy/artists/'.$bands[0]['friendly'].'/
				'.($bands[1]['romaji'] ?: $bands[1]['name']).': https://vk.gy/artists/'.$bands[1]['friendly'].'/
				'.($bands[2]['romaji'] ?: $bands[2]['name']).': https://vk.gy/artists/'.$bands[2]['friendly'].'/
			';
			
			$fb_message = str_replace("\t", '', $fb_message);
			$fb_message = trim($fb_message);
			$fb_message = html_entity_decode($fb_message);
			// Covertly upload artist images to FB
			if(is_array($band_images) && !empty($band_images)) {
				foreach($band_images as $band_image) {
					$band_image = str_replace('../images/image_files/', 'https://vk.gy/images/', $band_image);
					
					// Upload to FB, get response, decoded & grab FB image ID
					$fb_response = $fb->post('/'.$fb_page_id.'/photos', [ 'url' => $band_image, 'published' => false ]);
					$fb_photo_id = $fb_response->getDecodedBody();
					$fb_photo_param[] = '{"media_fbid":"'.$fb_photo_id['id'].'"}';
				}
			}
			
			// Post to FB using recently-uploaded images
			$fb->post('/'.$fb_page_id.'/feed', [ 'message' => $fb_message, 'attached_media' => $fb_photo_param ]);
		//}
		
	}
?>