<?php

include_once('../php/include.php');

function guess_link_type( $url, $artist_id ) {
	
	global $pdo;
	
	// Possible link types
	$link_types = [
		'official',
		'other',
		'webshop',
		'blog',
		'fansite',
		'sns',
		'music',
		'video',
	];
	
	// Possible slugs indiciating that url is of a certain type
	// Order sort of matters--prob want more generic matches last
	$possible_slugs = [
		
		'sns' => [
			'twitter',
			'facebook',
			'instagram',
			'mixi',
		],
		
		'webshop' => [
			'store',
			'shop',
			'thebase',
		],
		
		'music' => [
			'linkco',
			'spotify',
			'apple',
		],
		
		'video' => [
			'youtu',
		],
		
		'blog' => [
			'ameba',
			'ameblo',
			'line',
			'livedoor',
			'blog',
		],
		
		'fansite' => [
			'grassthread',
			'yunisan',
			'visunavi',
			'vkdb',
			'visulog',
		],
		
		'official' => [
			'offi',
			'wix',
			'syncl',
		],
		
	];
	
	// Make sure URL provided
	if( strlen($url) ) {
		
		// Make sure artist provided
		if( is_numeric($artist_id) ) {
			
			// Get artist friendly for later
			$sql_artist = 'SELECT friendly FROM artists WHERE id=? LIMIT 1';
			$stmt_artist = $pdo->prepare($sql_artist);
			$stmt_artist->execute([ $artist_id ]);
			$artist_friendly = $stmt_artist->fetchColumn();
			
			// Push friendly name onto possible website slugs
			$possible_slugs['official'][] = $artist_friendly;
			
			// Get musicians' names
			$sql_musicians = 'SELECT musicians.id, IF( artists_musicians.as_name IS NOT NULL, COALESCE(artists_musicians.as_romaji, artists_musicians.as_name), musicians.friendly ) AS name FROM artists_musicians LEFT JOIN musicians ON musicians.id=artists_musicians.musician_id WHERE artist_id=?';
			$stmt_musicians = $pdo->prepare($sql_musicians);
			$stmt_musicians->execute([ $artist_id ]);
			$rslt_musicians = $stmt_musicians->fetchAll();
			
			// Loop through musicians and clean up name a bit
			if( is_array($rslt_musicians) && !empty($rslt_musicians) ) {
				foreach( $rslt_musicians as $musician ) {
					
					// Make sure we have friendly version
					$name = friendly( $musician['name'] );
					
					// Attempt to handle e.g. Sui-Sui-
					$name = preg_replace('/'.'([a-z]+)-(\1)'.'/', '$1', $name);
					
					// Split name at hyphens in case it's like midorikawa-you, and we'll search for both parts later
					$names = explode('-', $name);
					
					// If name has multiple parts, let's also search by the complete string
					if( count($names) > 1 ) {
						$names[] = $name;
					}
					
					// Put back into new array arranged by id
					$musicians[ $musician['id'] ] = $names;
					
				}
			}
			
			// Loop through groups of slug and if match found, set link type to slug type
			foreach( $possible_slugs as $slug_type => $slugs ) {
				foreach( $slugs as $slug ) {
					if( strpos( $url, $slug ) !== false ) {
						
						$output['type'] = $slug_type;
						break 2;
						
					}
				}
			}
			
			// If we have musicians, and link type is SNS, blog, or official, try to determine if it's for musician or band
			if( is_array($musicians) && !empty($musicians) ) {
				if( $output['type'] === 'blog' || $output['type'] === 'sns' || $output['type'] === 'official' ) {
					
					foreach( $musicians as $musician_id => $musician_names ) {
						foreach($musician_names as $musician_name) {
							if( strpos( $url, $musician_name ) !== false ) {
								
								$output['musician_id'] = $musician_id;
								break;
								
							}
						}
					}
					
				}
			}
			
			// Set default type
			$output['type'] = $output['type'] ?: 'other';
			
			// Transform slug type into number
			$output['type'] = array_search( $output['type'], $link_types );
			
		}
		else {
			//$output['result'] = 'No artist provided.';
		}
		
	}
	else {
		//$output['result'] = 'URL is empty.';
	}
	
	//$output['status'] = $output['status'] ?: 'error';
	//return json_encode($output);
	
	return $output;
	
}