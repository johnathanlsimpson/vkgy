<?php

// Setup
include_once('../php/include.php');

$access_artist = $access_artist ?: new access_artist($pdo);

// Given artist ID, grab its twitter handle + handles of any musicians in the band
function get_artist_twitters($artist_id, $pdo, $access_artist) {
	
	if( is_numeric($artist_id) ) {
		
		// Get all artist's musicians
		$artist = $access_artist->access_artist([ 'id' => $artist_id, 'get' => 'profile' ]);
		
		// Loop through musicans and get IDs of ones still in band
		if(is_array($artist) && !empty($artist) && is_array($artist['musicians']) && !empty($artist['musicians'])) {
			foreach($artist['musicians'] as $musician) {
				if($musician['to_end']) {
					$extant_musician_ids[] = $musician['id'];
				}
			}
		}
		
		// Query
		$sql_urls = '
		
		SELECT
			potential_urls.content
		FROM
			(
				
				SELECT
					content,
					musician_id
				FROM
					artists_urls
				WHERE
					artist_id=?
					AND
					is_retired=?
					
			) potential_urls
		WHERE
			potential_urls.content LIKE CONCAT("%", ?, "%")
			AND
			(
				potential_urls.musician_id IS NULL
				'.( is_array($extant_musician_ids) && !empty($extant_musician_ids) ? str_repeat( 'OR potential_urls.musician_id=? ', count($extant_musician_ids) ) : null ).'
			)
		ORDER BY
			potential_urls.musician_id
			
		';
		
		// Values
		$values_urls = [ $artist_id, 0, 'twitter' ];
		if(is_array($extant_musician_ids) && !empty($extant_musician_ids)) {
			foreach($extant_musician_ids as $musician_id) {
				$values_urls[] = $musician_id;
			}
		}
		
		// Results
		$stmt_urls = $pdo->prepare($sql_urls);
		$stmt_urls->execute( $values_urls );
		$rslt_urls = $stmt_urls->fetchAll();
		
		// Get resultant URLs and check for Twitter handles
		$twitter_username_pattern = 'twitter\.com\/(?:\#\!\/)?([A-z0-9-_]+)';
		if(is_array($rslt_urls) && !empty($rslt_urls)) {
			foreach($rslt_urls as $url) {
				if(preg_match('/'.$twitter_username_pattern.'/', $url['content'], $match)) {
					$twitter_usernames[] = '@'.$match[1];
				}
			}
		}
		
	}
	
	return is_array($twitter_usernames) && !empty($twitter_usernames) ? implode(' ', $twitter_usernames) : null;
	
}