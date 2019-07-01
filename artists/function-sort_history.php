<?php

// Change history types from numbers to words
function parse_history_types($history, $access_artist) {
	$num_history = count($history);
	
	for($i=0; $i<$num_history; $i++) {
		$types = [];
		
		foreach($history[$i]['type'] as $type) {
			$types[] = is_numeric($type) ? $access_artist->artist_bio_types[$type] : $type;
		}
		
		$history[$i]['type'] = $types;
	}
	
	return $history;
}

// Loop through history and, if live occurred on same date, inject it into history
function insert_lives_into_history($history, $lives) {
	if(is_array($lives) && !empty($lives)) {
		$num_history = count($history);
		
		for($i=0; $i<$num_history; $i++) {
			list($y, $m, $d) = explode('-', $history[$i]['date_occurred']);
			
			// If last entry for particular day, check if we have a corresponding live schedule entry
			if(
				(isset($history[$i + 1]) && $history[$i + 1]['date_occurred'] != $history[$i]['date_occurred'])
				||
				($i +1 === count($history))
			) {
				
				// If entry ISN'T live schedule, go ahead with check
				if(!in_array('schedule', $history[$i]['type'])) {
					if(is_array($lives) && !empty($lives) && is_array($lives[$y][$m][$d])) {
						
						// Create new bio line based on live
						$schedule_line = $lives[$y][$m][$d][0];
						$schedule_content =
							'<a class="a--inherit symbol__company" href="/lives/&area_id='.$schedule_line['area_id'].'">'.
								lang(($schedule_line['area_romaji'] ?: $schedule_line['area_name']), $schedule_line['area_name'], 'hidden').
							'</a>'.
							' '.
							'<a class="a--inherit" href="/lives/&livehouse_id='.$schedule_line['livehouse_id'].'">'.
								lang(($schedule_line['livehouse_romaji'] ?: $schedule_line['livehouse_name']), $schedule_line['livehouse_name'], 'hidden').
							'</a>';
						
						// Make spot in history for live
						array_splice($history, ($i + 1), 0, 'live');
						
						// Update newly-made spot with new history line
						$history[$i + 1] = [
							'date_occurred' => $schedule_line['date_occurred'],
							'content' => $schedule_content,
							'type' => [ 'schedule', 'is_uneditable' ],
						];
					}
				}
			}
		}
	}
	
	return $history;
}

// Parse Markdown to HTML
function parse_history_markdown($history, $markdown_parser) {
	$num_history = count($history);
	
	for($i=0; $i<$num_history; $i++) {
		if(in_array('is_uneditable', $history[$i]['type'])) {
		}
		else {
			$history[$i]['content'] = $markdown_parser->parse_markdown($history[$i]['content']);
		}
	}
	
	return $history;
}

// Transform activity area into link
function link_activity_area($history, $artist_areas) {
	if(is_array($artist_areas) && !empty($artist_areas)) {
		
		$num_history = count($history);
		for($i=0; $i<$num_history; $i++) {
			
			// If line is tagged formation or activity, look for mention of area (that is also in $artist['areas']) and replace it with ink
			if(in_array('formation', $history[$i]['type']) || in_array('activity', $history[$i]['type'])) {
				foreach($artist_areas as $active_area) {
					if($active_area['romaji']) {
						$history[$i]['content'] = str_replace(
							$active_area['romaji'].' ('.$active_area['name'].')',
							'<a href="/search/artists/&area='.$active_area['romaji'].'#result">'.lang($active_area['romaji'].' ('.$active_area['name'].')', $active_area['name'], 'hidden').'</a>',
							$history[$i]['content']
						);
					}
				}
			}
		}
	}
	
	return $history;
}

// Loop through history entries tagged -release, and try to merge releases with multiple types
function format_releases($history) {
	$num_history = count($history);
	
	for($i=0; $i<$num_history; $i++) {
		
		// Make sure event is tagged release and that content is array (releases should always be array)
		if(in_array('release', $history[$i]['type']) && is_array($history[$i]['content'])) {
			
			// Save base name of release
			$release_name = $history[$i]['content']['romaji'] ?: $history[$i]['content']['name'];
			
			// Set up CDJapan link
			$cdjapan_aff_id = 'PytJTGW7Lok/6128/A549875';
			$cdjapan_link = 'https://www.cdjapan.co.jp/aff/click.cgi/'.$cdjapan_aff_id.'/searches?term.f=all&q='.str_replace('-', '+', friendly($release_name));
			
			// For the first release, save it as a normal link
			$history[$i]['content'] =
				'<a class="symbol__release" href="'.$history[$i]['content']['url'].'">'.
				lang(
					($history[$i]['content']['romaji'] ?: $history[$i]['content']['name']).
					($history[$i]['content']['press_name'] ? ' ' : null).
					($history[$i]['content']['press_romaji'] ?: $history[$i]['content']['press_name']).
					($history[$i]['content']['type_name'] ? ' ' : null).
					($history[$i]['content']['type_romaji'] ?: $history[$i]['content']['type_name']),
					
					$history[$i]['content']['name'].
					($history[$i]['content']['press_name'] ? ' '.$history[$i]['content']['press_name'] : null).
					($history[$i]['content']['type_name'] ? ' '.$history[$i]['content']['type_name'] : null),
					
					'hidden'
				).
				'</a>';
			
			// Check how many other entries on this day
			$m = $i + 1;
			$still_checking = true;
			$num_same_day_events = 0;
			while($still_checking) {
				if($history[$m]['date_occurred'] === $history[$i]['date_occurred']) {
					$still_checking = true;
					$num_same_day_events++;
					$m++;
				}
				else {
					$still_checking = false;
				}
			}
			
			// If other entries on same day, look through any that are also a release
			for($n=1; $n<=$num_same_day_events; $n++) {
				$p = $i + $n;
				
				// If next entry is release
				if(in_array('release', $history[$p]['type']) && is_array($history[$p]['content'])) {
					
					$tmp_content = '<a class="symbol__release" href="'.$history[$p]['content']['url'].'">';
					
					// If has same base name as current release and has press or type name, we'll save it as name only
					if(($history[$p]['content']['romaji'] ?: $history[$p]['content']['name']) === $release_name) {
						
						if(strlen($history[$p]['content']['press_name'])) {
							$tmp_content .= lang( ($history[$p]['content']['press_romaji'] ?: $history[$p]['content']['press_name']), $history[$p]['content']['press_name'], 'hidden' );
						}
						if(strlen($history[$p]['content']['press_name']) && strlen($history[$p]['content']['type_name'])) {
							$tmp_content .= ' ';
						}
						if(strlen($history[$p]['content']['type_name'])) {
							$tmp_content .= lang( ($history[$p]['content']['type_romaji'] ?: $history[$p]['content']['type_name']), $history[$p]['content']['type_name'], 'hidden' );
						}
					}
					
					// If release has different name, we'll save the full name
					// But also set this one's name as the new base name
					else {
						$release_name = $history[$p]['content']['romaji'] ?: $history[$p]['content']['name'];
						
						$tmp_content .= lang(
							($history[$p]['content']['romaji'] ?: $history[$p]['content']['name']).
							($history[$p]['content']['press_name'] ? ' ' : null).
							($history[$p]['content']['press_romaji'] ?: $history[$p]['content']['press_name']).
							($history[$p]['content']['type_name'] ? ' ' : null).
							($history[$p]['content']['type_romaji'] ?: $history[$p]['content']['type_name']),
							
							$history[$p]['content']['name'].
							($history[$p]['content']['press_name'] ? ' '.$history[$p]['content']['press_name'] : null).
							($history[$p]['content']['type_name'] ? ' '.$history[$p]['content']['type_name'] : null),
							
							'hidden'
						);
					}
					
					$tmp_content .= '</a>';
					
					// Add new link to current history entry
					$history[$i]['content'] .= ' &nbsp; <span class="any--weaken">/</span> &nbsp; '.$tmp_content;
					
					// Unset from history array
					unset($history[$p]['content']);
				}
			}
			
			// After combining releases, add CDJapan link
			$history[$i]['content'] .= ' <a class="any__note a--inherit" href="'.$cdjapan_link.'">BUY</a>';
			
			// Since entries may be removed, reset count
			$num_history = count($history);
		}
		
	}
	
	return $history;
}

// Grab formation and disbandment dates
function get_formation_dates($history) {
	$num_history = count($history);
	$potential_start = [];
	$potential_end = [];
	
	for($i=0; $i<$num_history; $i++) {
		$y = substr($history[$i]['date_occurred'], 0, 4);
		
		if(in_array('formation', $history[$i]['type']) && $y > '0000') {
			$dates[] = [ 'year' => $y, 'id' => $i, 'type' => 'start' ];
			$years[] = $y;
			$ids[] = $i;
		}
		if(in_array('disbandment', $history[$i]['type']) && $y > '0000') {
			$dates[] = [ 'year' => $y, 'id' => $i, 'type' => 'end' ];
			$years[] = $y;
			$ids[] = $i;
		}
	}
	
	if(is_array($dates) && is_array($years) && is_array($ids)) {
		array_multisort($years, SORT_ASC, $ids, SORT_ASC, $dates);
		
		foreach($dates as $date_key => $date) {
			$output .=
				($date['type'] === 'start' && $date_key > 0 ? ', ' : null).
				($date['type'] === 'end' ? '~' : null).
				$date['year'].
				($date['type'] === 'start' && $date_key + 1 === count($dates) ? '~' : null);
		}
	}
	
	return $output;
}

// Reorder history by event type, and save into date-structured array
function structure_by_date($history) {
	$tmp_history = [];
	$num_history = count($history);
	
	// Rearrange as y->m->d->event
	for($i=0; $i<$num_history; $i++) {
		list($y, $m, $d) = explode('-', $history[$i]['date_occurred']);
		$y = $y === '0000' ? '????' : $y;
		
		$tmp_history[$y][$m][$d][] = $history[$i];
	}
	
	// Unset history, so we can add back to it
	$history = [];
	
	// Loop through each day and rearrange that day's events
	foreach($tmp_history as $y => $history_year) {
		foreach($history_year as $m => $history_month) {
			foreach($history_month as $d => $history_day) {
				$num_events = count($history_day);
				
				// For each type, if a history entry has that type, move it to the bottom of the day
				// Repeat for each applicable type until they're ordered
				foreach(['member', 'schedule', 'setlist', 'release', 'lineup', 'disbandment'] as $type) {
					for($i=0; $i<$num_events; $i++) {
						if(in_array($type, $history_day[$i]['type'])) {
							$history_day[] = $history_day[$i];
							unset($history_day[$i]);
						}
					}
					
					$history_day = array_values($history_day);
				}
				
				// For formation events, push to front
				for($i=0; $i<$num_events; $i++) {
					if(in_array('formation', $history_day[$i]['type'])) {
						$tmp_event = $history_day[$i];
						unset($history_day[$i]);
						array_unshift($history_day, $tmp_event);
					}
				}
				
				// Save reordered day back to output
				foreach($history_day as $event_key => $event) {
					$history[$y][$m][$d][] = $event;
				}
			}
		}
	}
	
	return $history;
}

// Make ordered lists inline
function inline_lists($history) {
	$num_history = count($history);
	
	for($i=0; $i<$num_history; $i++) {
		$history[$i] = str_replace('<ol>', '<ol class="ol--inline">', $history[$i]);
	}
	
	return $history;
}