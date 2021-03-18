<?php

include_once('../php/include.php');

// Set potential sizes
$image_size_searches = [ '.thumbnail.', '.small.', '.medium.', '.large.' ];

// If background not specified, default to page image if specified
$background_image = $background_image ?: ($page_image ?: null);
$background_image = str_replace($image_size_searches, '.', $background_image);
$background_image = str_replace('https://vk.gy', '', $background_image);

// For background image, let's get its details so we can do some calculations
if( $background_image ) {
	
	// If given /artists/dali/main.jpg have to get image id and then grab info
	if( preg_match('/'.'\/artists\/(.*)\/main'.'/', $background_image, $matches) ) {
		
		$artist_friendly = $matches[1];
		
		$sql_image = 'SELECT images.* FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.friendly=? LIMIT 1';
		$values_image = [ $artist_friendly ];
		
	}
	
	// Otherwise get info from id
	elseif( preg_match('/'.'\/images\/(\d)+'.'/', $background_image, $matches) ) {
		
		$image_id = $matches[1];
		
		$sql_image = 'SELECT images.* FROM images WHERE images.id=? LIMIT 1';
		$values_image = [ $image_id ];
		
	}
	
	else {
		unset($background_image);
	}
	
	// If we have a query, run it and try to get info
	if( $sql_image ) {
		
		$stmt_image = $pdo->prepare($sql_image);
		$stmt_image->execute($values_image);
		$image = $stmt_image->fetch();
		
		if( is_array($image) && !empty($image) ) {
			
			$background_image = $image;
			
		}
		else {
			unset($background_image);
		}
		
	}
	
}

// If we found a background image, let's do some calculations for how to display
if( is_array($background_image) && !empty($background_image) ) {

	// Calculate additional data
	if( $background_image['width'] && $background_image['height'] ) {
		$background_image['ratio'] = $background_image['width'] / $background_image['height'];
		$background_image['orientation'] = $background_image['ratio'] > 1 ? 'horizontal' : 'vertical';
		$background_image['resolution'] = $background_image['width'] * $background_image['height'];
	}

	// Set other sizes
	$background_image['url'] = '/images/'.$background_image['id'].($background_image['friendly'] ? '-'.$background_image['friendly'] : null).'.'.$background_image['extension'];
	$background_image['thumbnail_url'] = str_replace('.', '.thumbnail.', $background_image['url']);
	$background_image['large_url'] = str_replace('.', '.large.', $background_image['url']);

	// If background resolution is too small in total or in the dimension of its orientation, don't use it
	if( $background_image['resolution'] < 120000 || 500 > ( $background_image['orientation'] === 'horizontal' ? $background_image['width'] : $background_image['height'] ) ) {
		unset($background_image);
	}

}

// Make sure background image is an empty array at least
$background_image = $background_image ?: [];