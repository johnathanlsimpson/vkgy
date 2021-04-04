<?php

include_once('../php/include.php');

$access_points = new access_points($pdo);
$markdown_parser = new parse_markdown($pdo);

$musician_id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$musician = $_POST;

if( is_numeric($musician_id) && $_SESSION['can_add_data'] ) {
	
	// Make sure they have name and at least one artist link
	if( strlen($musician['name']) && preg_match('/'.'\(\d+\)'.'/', $musician['history']) ) {
	
		// Clean values
		foreach( $musician as $key => $value ) {
			$value = sanitize($value);
			$musician[$key] = strlen($value) ? $value : null;
		}
		
		// Further clean some values
		$musician['history'] = $markdown_parser->validate_markdown($musician['history']);
		$musician['name'] = $musician['name'] ? trim($musician['name']) : null;
		$musician['romaji'] = $musician['romaji'] ? trim($musician['romaji']) : null;
		$musician['friendly'] = !$musician['friendly'] || $musician['friendly'] === '-' ? friendly($musician['romaji'] ?: $musician['name']) : $musician['friendly'];

		// Format birthdate
		if( strlen($musician['birth_year']) || strlen($musician['birth_date']) ) {

			$b = $musician['birth_year'].( strlen($musician['birth_year']) && strlen($musician['birth_date']) ? '-' : null ).$musician['birth_date'];

			if(preg_match('/'.'^\d{4}-\d{2}-\d{2}$'.'/', $b)) {
			}
			elseif(preg_match('/'.'^\d{2}-\d{2}$'.'/', $b)) {
				$b = '0000-'.$b;
			}
			elseif(preg_match('/'.'^[Ss](\d{2})'.'/', $b, $match)) {
				$b = str_replace($match[0], $match[1] + 1925, $b);
			}
			elseif(preg_match('/'.'^[Hh](\d{2})'.'/', $b, $match)) {
				$b = str_replace($match[0], $match[1] + 1988, $b);
			}
			elseif(preg_match('/'.'^[Rr](\d{2})'.'/', $b, $match)) {
				$b = str_replace($match[0], $match[1] + 2019, $b);
			}
			if(preg_match('/'.'^\d{4}$'.'/', $b)) {
				$b .= '-00-00';
			}

			$musician['birth_date'] = $b;
			
		}
		
		// Clean numeric entries
		foreach([ 'usual_position', 'gender', 'birthplace' ] as $key) {
			$musician[$key] = is_numeric($musician[$key]) ? $musician[$key] : null;
		}

		// Keys included in query
		$musician_keys = [
			'name',
			'romaji',
			'usual_position',
			'gender',
			'blood_type',
			'birth_date',
			'birthplace',
			'friendly',
			'history',
		];

		// Set up values
		foreach($musician_keys as $key) {
			$values_update[] = $musician[$key];
		}
		$values_update[] = $musician_id;

		// Run query
		$sql_update = 'UPDATE musicians SET '.implode('=?, ', $musician_keys).'=? WHERE id=? LIMIT 1';
		$stmt_update = $pdo->prepare($sql_update);
		if( $stmt_update->execute($values_update) ) {
			
			// Check for existing artist-musician links
			$sql_links = 'SELECT id, artist_id, musician_id FROM artists_musicians WHERE musician_id=?';
			$stmt_links = $pdo->prepare($sql_links);
			$stmt_links->execute([ $musician_id ]);
			$artist_links = $stmt_links->fetchAll();

			// Delete any artist-musician links that weren't mentioned in the musician's history when the edit was submitted
			if( is_array($artist_links) && !empty($artist_links) ) {
				foreach( $artist_links as $artist_link ) {
					
					if( strpos( $musician['history'], '('.$artist_link['artist_id'].')') === false ) {
						
						$sql_delete_link = 'DELETE FROM artists_musicians WHERE id=? LIMIT 1';
						$stmt_delete_link = $pdo->prepare($sql_delete_link);
						$stmt_delete_link->execute([ $artist_link['id'] ]);
						
					}

					$extant_links[] = $artist_link['artist_id'];
					
				}
			}

			// Grab any new artist-musician links
			preg_match_all('/'.'\((\d+)\)(?:\/.+?\/)?(?:\[.+?\])?((?: \((?!\d+).+?\))*)'.'/', $musician['history'], $bands_in_database);

			// Save new artist-musician links
			if(is_array($bands_in_database) && !empty($bands_in_database)) {
				
				$bands_in_database['full_matches'] = $bands_in_database[0];
				$bands_in_database['ids'] = $bands_in_database[1];
				$bands_in_database['notes'] = $bands_in_database[2];

				unset($bands_in_database[0], $bands_in_database[1], $bands_in_database[2]);

				foreach($bands_in_database['ids'] as $band_in_db_key => $band_in_db_id) {
					if(!in_array($band_in_db_id, $extant_links)) {

						// Check if roadie
						if(strpos($bands_in_database['notes'][$band_in_db_key], 'roadie') !== false) {
							$position_name = 'roadie';
							$position = 6;
						}

						// Check if on different position
						if(preg_match('/'.'\(on (vocals|guitar|bass|drums|keys)\)'.'/', $bands_in_database['notes'][$band_in_db_key], $position_match)) {
							$position = array_search($position_match[1], ['other', 'vocals', 'guitar', 'bass', 'drums', 'keys']);
						}

						$position = is_numeric($position) ? $position : $musician['usual_position'];

						// Check if support
						if(strpos($bands_in_database['notes'][$band_in_db_key], 'support') !== false) {
							$position_name = 'support '.['other', 'vocals', 'guitar', 'bass', 'drums', 'keys'][$position];
						}

						// Check if pseudonym
						if(preg_match('/'.'\(as ([A-z0-9&#;]+)(?: \(([A-z0-9&#;]+))?\)'.'/', $bands_in_database['notes'][$band_in_db_key], $as_name_match)) {
							$as_name = $as_name_match[2] ?: $as_name_match[1];
							$as_romaji = $as_name_match[2] ? $as_name_match[1] : null;
						}

						$sql_add_link = 'INSERT INTO artists_musicians (artist_id, musician_id, position, position_name, as_name, as_romaji) VALUES (?, ?, ?, ?, ?, ?)';
						$values_add_link = [ $band_in_db_id, $musician_id, $position, $position_name, $as_name, $as_romaji ];
						$stmt_add_link = $pdo->prepare($sql_add_link);
						$stmt_add_link->execute($values_add_link);

						unset($position, $position_name, $as_name, $as_romaji);
						
					}
				}
				
			}
			
			// Update change history
			if( strlen($musician['changes']) ) {

				// Explode changes input and clean
				$changes = $musician['changes'];
				$changes = preg_match('/'.'^[\-\w\[\]\,]+$'.'/', $changes) ? $changes : null;
				$changes = explode(',', $changes);
				$changes = array_filter($changes, function($x) { return strlen($x) && $x != 'changes'; });
				$changes = array_unique($changes);

				if( is_array($changes) && !empty($changes) ) {

					// Prepare SQL statements
					$sql_edits = 'INSERT INTO edits_musicians (musician_id, user_id, content) VALUES '.substr( str_repeat('(?, ?, ?), ', count($changes)), 0, -2 );
					$stmt_edits = $pdo->prepare($sql_edits);

					// Prepare values
					foreach($changes as $change) {
						
						$values_edits[] = $musician_id;
						$values_edits[] = $_SESSION['user_id'];
						$values_edits[] = $change;
						
					}
					
					// Log edits
					if( $stmt_edits->execute($values_edits) ) {
					}
					
				}
					
			}
			
			// Award a point for editing musician
			$output['points'] += $access_points->award_points([ 'point_type' => 'edited-musician', 'allow_multiple' => false, 'item_id' => sanitize($musician_id) ]);
			
			$output["status"] = 'success';
			
		}
		else {
			$output['result'] = 'The musician couldn\'t be updated.';
		}
		
	}
	else {
		$output['result'] = 'Each musician needs a name and at least one band in their history.';
	}
	
}
else {
	$output['result'] = 'Sorry, you don\'t have permission to edit musicians.';
}

$output['status'] = $output['status'] ?: 'error';
	
echo json_encode($output);