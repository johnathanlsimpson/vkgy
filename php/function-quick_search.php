​<?php

include_once('../php/include.php');

function quick_search( $search_term ) {
	
	global $pdo;
	
	$search_term_length = strlen($search_term);
	
	if( $search_term_length ) {
		
		$friendly_search_term = friendly( $search_term );
		$friendly_search_term = $friendly_search_term != '-' ? $friendly_search_term : null;
		
		// Values are same for both queries
		$values = [
			-1,
			$search_term,
			$search_term,
			$search_term,
			$friendly_search_term,
		];
		
		// Exact search (for artists like D)
		$sql_exact = '
			SELECT
				artists.id,
				artists.name,
				artists.romaji,
				artists.friendly,
				IF( images.id IS NOT NULL, CONCAT("/images/", images.id, ".thumbnail.", images.extension), "" ) AS thumbnail_url
			FROM
				artists
			LEFT JOIN images ON images.id=artists.image_id
			WHERE
				artists.is_vkei>?
				AND (
					artists.name=?
					OR
					artists.romaji=?
					OR
					artists.pronunciation=?
					OR
					artists.friendly=?
				)
			ORDER BY artists.friendly ASC';
		$stmt_exact = $pdo->prepare($sql_exact);
		$stmt_exact->execute( $values );
		$exact_artists = $stmt_exact->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
		
		// Combine results
		$artists = $exact_artists;
		
		// Fuzzy search--only if more than one letter is provided
		if( $search_term_length > 1 ) {
			
			$sql_fuzzy = '
				SELECT
					artists.id,
					artists.name,
					artists.romaji,
					artists.friendly,
					IF( images.id IS NOT NULL, CONCAT("/images/", images.id, ".thumbnail.", images.extension), "" ) AS thumbnail_url
				FROM
					artists
				LEFT JOIN images ON images.id=artists.image_id
				WHERE
					artists.is_vkei>?
					AND (
						artists.name LIKE CONCAT(?, "%")
						OR
						artists.romaji LIKE CONCAT(?, "%")
						OR
						artists.pronunciation LIKE CONCAT(?, "%")
						OR
						artists.friendly LIKE CONCAT(?, "%")
					)
				ORDER BY artists.friendly ASC
				LIMIT 10';
			$stmt_fuzzy = $pdo->prepare($sql_fuzzy);
			$stmt_fuzzy->execute( $values );
			$fuzzy_artists = $stmt_fuzzy->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
			
			// Combine results
			$artists = $artists + $fuzzy_artists;
			
		}
		
		// Super fuzzy search--only if string is long enough and we don't have 10 results yet
		if( count($artists) < 10 && $search_term_length > 3 ) {
			
			$sql_super_fuzzy = '
				SELECT
					artists.id,
					artists.name,
					artists.romaji,
					artists.friendly,
					IF( images.id IS NOT NULL, CONCAT("/images/", images.id, ".thumbnail.", images.extension), "" ) AS thumbnail_url
				FROM
					artists
				LEFT JOIN images ON images.id=artists.image_id
				WHERE
					artists.is_vkei>?
					AND (
						artists.name LIKE CONCAT("%", ?, "%")
						OR
						artists.romaji LIKE CONCAT("%", ?, "%")
						OR
						artists.pronunciation LIKE CONCAT("%", ?, "%")
						OR
						artists.friendly LIKE CONCAT("%", ?, "%")
					)
				ORDER BY artists.friendly ASC
				LIMIT 10';
			$stmt_super_fuzzy = $pdo->prepare($sql_super_fuzzy);
			$stmt_super_fuzzy->execute( $values );
			$super_fuzzy_artists = $stmt_super_fuzzy->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
			
			// Combine results
			$artists = $artists + $super_fuzzy_artists;
			
		}
		
		// Combine results
		$artists = array_slice( $artists, 0, 10 );
		
	}
	
	return $artists;
	
}