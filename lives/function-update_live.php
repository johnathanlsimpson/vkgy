<?php

include_once('../php/include.php');

$access_live = new access_live($pdo);

if($_SESSION['is_signed_in']):

if(is_array($_POST) && !empty($_POST)) {
	$livehouse_id = sanitize($_POST['livehouse_id']);
	$name = sanitize($_POST['name']) ?: null;
	$romaji = sanitize($_POST['romaji']) ?: null;
	
	$date_occurred = sanitize($_POST['date_occurred']);
	$date_occurred = str_replace(['y', 'm', 'd'], '0', $date_occurred);
	$date_occurred = preg_match('/'.'^\d{4}-\d{2}-\d{2}$'.'/', $date_occurred) ? $date_occurred : '0000-00-00';
	
	// Clean lineup string and make array
	$lineup = sanitize($_POST['lineup']);
	$lineup = str_replace(["\r\n", "\n\n"], "\n", $lineup);
	$lineup = trim($lineup);
	$lineup = array_filter(explode("\n", $lineup));
	
	// Make the lineup array deeper so we can add some attributes and statuses
	foreach($lineup as $lineup_key => $content) {
		$lineup[$lineup_key] = [
			'content' => $content,
			'is_sponsor' => strpos($content, '(sponsor)') !== false ? 1 : 0,
			'is_guest' => strpos($content, '(guest)') !== false ? 1 : 0,
			'is_opening' => strpos($content, '(opening act)') !== false ? 1 : 0
		];
	}
	
	$type = is_numeric($_POST['type']) ? $_POST['type'] : 0;
	$type = in_array($type, $access_live->live_types) ? $type : 0;
	
	if(is_numeric($_POST['id'])) {
		$id = sanitize($_POST['id']);
		$is_edit = true;
	}
	
	if(is_numeric($livehouse_id)) {
		if(is_array($lineup) && !empty($lineup)) {
			
			// Edit
			if($is_edit) {
				
				// Get all artists currently linked to this concert;
				// if artist still in textarea, don't need to add it twice
				// Also get num of duplicates so we can remove if needed
				$sql_extant_artists = 'SELECT artist_id, COUNT(*) - 1 AS num_duplicates, is_sponsor FROM lives_artists WHERE lives_artists.live_id=? GROUP BY lives_artists.artist_id';
				$stmt_extant_artists = $pdo->prepare($sql_extant_artists);
				$stmt_extant_artists->execute([ $id ]);
				$extant_artists = $stmt_extant_artists->fetchAll();
				
				if(is_array($extant_artists) && !empty($extant_artists)) {
					
					// Delete dupes and remove from 'to be added' list
					foreach($extant_artists as $extant_artist_key => $extant_artist) {
						
						// Delete dupes
						if($extant_artist['num_duplicates'] > 0) {
							$sql_delete_dupe = 'DELETE FROM lives_artists WHERE artist_id=? AND live_id=? LIMIT ?';
							$stmt_delete_dupe = $pdo->prepare($sql_delete_dupe);
							
							if($stmt_delete_dupe->execute([ $extant_artist['artist_id'], $id, $extant_artist['num_duplicates'] ])) {
							}
							else {
								$output['result'][] = 'Couldn\'t delete duplicates.';
							}
						}
						
						// Loop through inputted lineup, delete from DB any artist-live links which
						// have info that has changed (e.g. is_sponsored)
						foreach($lineup as $lineup_key => $lineup_line) {
							
						}
						
						// Loop through inputted lineup, check if they're already in DB and if anything has changed
						foreach($lineup as $lineup_key => $lineup_line) {
							
							// If this artist is already linked to the live in the DB, we prob want to ignore it
							if( strpos($lineup_line['content'], '('.$extant_artist['artist_id'].')') === 0 ) {
								
								// If is_sponsor attribute has been changed by user, then we want it to be deleted from DB
								// so we'll leave is_in_lineup as false, but still leave it in $lineup so it gets added after
								if( $lineup_line['is_sponsor'] != $extant_artist['is_sponsor'] ) {
									$extant_artists[$extant_artist_key]['is_in_lineup'] = false;
								}
								
								// If artist is already linked and nothing about the link has changed, then mark
								// is_in_lineup as true so it won't be deleted, but unset it from $lineup so it won't be re-added
								else {
									$extant_artists[$extant_artist_key]['is_in_lineup'] = true;
									unset($lineup[$lineup_key]);
								}
								
							}
							
						}
						
					}
					
					// Delete artists that were in lineup but now are not
					foreach($extant_artists as $extant_artist) {
						if(!$extant_artist['is_in_lineup']) {
							$sql_delete_extant = 'DELETE FROM lives_artists WHERE artist_id=? AND live_id=?';
							$stmt_delete_extant = $pdo->prepare($sql_delete_extant);
							
							if($stmt_delete_extant->execute([ $extant_artist['artist_id'], $id ])) {
							}
							else {
								$output['result'][] = 'Couldn\'t delete extant artist from lineup.';
							}
						}
					}
					
				}
				
				// Reset lineup array keys
				$lineup = array_values($lineup);
				
				// Loop through lineup array again and pull any newly-linked artists
				if(is_array($lineup) && !empty($lineup)) {
					foreach($lineup as $lineup_key => $lineup_line) {
						
						if( preg_match('/'.'^\((\d+)\)'.'/', $lineup_line['content'], $artist_match) ) {
							$artists_to_add[] = array_merge($lineup_line, [ 'id' => $artist_match[1] ] );
							unset($lineup[$lineup_key]);
						}
						
					}
				}
				
				// Lineup should now contain only bands not in DB, so make it a pretty string
				foreach($lineup as $lineup_key => $lineup_line) {
					$lineup[$lineup_key] = $lineup_line['content'] ?: null;
				}
				$lineup = array_filter($lineup);
				$lineup = implode("\n", $lineup);
				$lineup = trim($lineup);
				$lineup = strlen($lineup) ? $lineup : null;
				
				// Link newly-added artists to live
				if(is_array($artists_to_add) && !empty($artists_to_add)) {
					
					$values_add_artists = [];
					foreach($artists_to_add as $artist_to_add) {
						$values_add_artists[] = $id; // live_id
						$values_add_artists[] = $artist_to_add['id']; // artist_id
						$values_add_artists[] = $artist_to_add['is_sponsor']; // is_sponsor
					}
					
					$sql_add_artists = 'INSERT INTO lives_artists (live_id, artist_id, is_sponsor) VALUES '.substr(str_repeat('(?, ?, ?), ', count($artists_to_add)), 0, -2);
					$stmt_add_artists = $pdo->prepare($sql_add_artists);
					
					if($stmt_add_artists->execute($values_add_artists)) {
					}
					else {
						$output['result'][] = 'Couldn\'t add new artist(s) to live.'.$sql_add_artists.print_r($values_add_artists, true);
					}
				}
				
				// Do other edits
				$sql_edit_live = 'UPDATE lives SET date_occurred=?, livehouse_id=?, lineup=?, name=?, romaji=?, type=? WHERE id=? LIMIT 1';
				$stmt_edit_live = $pdo->prepare($sql_edit_live);
				
				if($stmt_edit_live->execute([ $date_occurred, $livehouse_id, $lineup, $name, $romaji, $type, $id ])) {
					
					// Output
					$output['id'] = $id;
					$output['url'] = '/lives/&id='.$id;
					$output['edit-url'] = '/lives/'.$id.'/edit/';
					$output['status'] = 'success';
					
					// Award point
					$access_points = new access_points($pdo);
					$output['points'] += $access_points->award_points([ 'point_type' => 'edited-live', 'allow_multiple' => false, 'item_id' => $id ]);
				}
				else {
					$output['result'][] = 'Couldn\'t update live.';
				}
			}
			
			// Add new
			else {
				
				// Check that live doesn't already exist
				$sql_extant_live = 'SELECT id FROM lives WHERE date_occurred=? AND livehouse_id=? LIMIT 1';
				$stmt_extant_live = $pdo->prepare($sql_extant_live);
				$stmt_extant_live->execute([ $date_occurred, $livehouse_id ]);
				$extant_live = $stmt_extant_live->fetchColumn();
				
				if(is_numeric($extant_live)) {
					$output['result'][] = 'A live with that date and livehouse already exists. <a href="/lives/'.$extant_live.'/edit/">Edit it instead?</a>';
				}
				else {
					
					// Get IDs of artists in DB, then remove from lineup array so it will only contain non-DB artists
					foreach($lineup as $lineup_key => $lineup_line) {
						
						if(preg_match('/'.'^\((\d+)\)'.'/', $lineup_line['content'], $artist_match)) {
							unset($lineup[$lineup_key]);
							$live_artists[] = array_merge( $lineup_line, ['id' => $artist_match[1]] );
						}
						
						$lineup[$lineup_key] = $lineup_line['content'] ?: null;
						
					}
					$lineup = array_filter($lineup);
					$lineup = implode("\n", $lineup);
					$lineup = trim($lineup);
					$lineup = strlen($lineup) ? $lineup : null;
					
					// Add live
					$sql_add_live = 'INSERT INTO lives (date_occurred, livehouse_id, lineup, user_id, name, romaji, type) VALUES (?, ?, ?, ?, ?, ?, ?)';
					$stmt_add_live = $pdo->prepare($sql_add_live);
					if($stmt_add_live->execute([ $date_occurred, $livehouse_id, $lineup, $_SESSION['user_id'], $name, $romaji, $type ])) {
						$id = $pdo->lastInsertId();
						
						// Output
						$output['id'] = $id;
						$output['url'] = '/lives/&id='.$id;
						$output['edit-url'] = '/lives/'.$id.'/edit/';
						$output['status'] = 'success';
						
						// Add artists to live
						if(is_array($live_artists) && !empty($live_artists)) {
							
							foreach($live_artists as $live_artist) {
								$values_add_artists[] = $id; // live_id
								$values_add_artists[] = $live_artist['id']; // artist_id
								$values_add_artists[] = $live_artist['is_sponsor']; // is_sponsor
							}
							
							$sql_add_artists = 'INSERT INTO lives_artists (live_id, artist_id, is_sponsor) VALUES '.substr(str_repeat('(?, ?, ?), ', count($live_artists)), 0, -2);
							$stmt_add_artists = $pdo->prepare($sql_add_artists);
							if($stmt_add_artists->execute($values_add_artists)) {
							}
							else {
								$output['result'] = 'Couldn\'t link artists to live.';
							}
						}
					}
					else {
						$output['result'][] = 'Couldn\'t add new live.';
					}
				}
			}
			
		}
		else {
			$output['result'][] = 'Please add at least one artist to the lineup.';
		}
	}
	else {
		$output['result'][] = 'No livehouse selected.';
	}
}
else {
	$output['result'][] = 'Data empty.';
}

endif;

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);