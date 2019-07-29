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
						$schedule_lines = $lives[$y][$m][$d];
						$schedule_content = null;
						foreach($schedule_lines as $schedule_key => $schedule_line) {
							$schedule_content .=
								($schedule_key ? ', ' : null).
								'<a class="a--inherit symbol__company" href="/lives/&id='.$schedule_line['id'].'">'.
								lang(($schedule_line['area_romaji'] ?: $schedule_line['area_name']), $schedule_line['area_name'], 'hidden').
								' '.
								lang(($schedule_line['livehouse_romaji'] ?: $schedule_line['livehouse_name']), $schedule_line['livehouse_name'], 'hidden').
								'</a>';
						}
						
						// Make spot in history for live
						array_splice($history, ($i + 1), 0, 'live');
						
						// Update newly-made spot with new history line
						$history[$i + 1] = [
							'date_occurred' => $schedule_line['date_occurred'],
							'content' => $schedule_content,
							'type' => [ 'schedule', 'is_uneditable' ],
						];
						
						// Reset counter for loop
						$num_history = count($history);
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

// For certain history types, highlight any (?)'s
function flag_questions($history) {
	$num_history = count($history);
	
	for($i=0; $i<$num_history; $i++) {
		if(in_array('lineup', $history[$i]['type']) && !is_array($history[$i]['content'])) {
			$history[$i]['content'] = str_replace([' (&#63;)', ' (?)'], ' <span class="artist__question">(?)</span>', $history[$i]['content']);
		}
	}
	
	return $history;
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

// Loop through history entries tagged -release, and try to merge releases with multiple types
// Expects array of one day's history
function format_releases($history) {
	$num_history = count($history);
	
	// For any entries marked release, pull them out and combine any that have the same base name
	for($i=0; $i<$num_history; $i++) {
		if(in_array('release', $history[$i]['type']) && is_array($history[$i]['content'])) {
			$base_name = $history[$i]['content']['romaji'] ?: $history[$i]['content']['name'];
			$releases_on_day[$base_name][] = array_merge($history[$i], [ 'history_key' => $i ]);
		}
	}
	
	if(is_array($releases_on_day) && !empty($releases_on_day)) {
		
		// For each set of releases with same base name, transform into links
		foreach($releases_on_day as $release_set) {
			
			// For releases on same day, sort by name/press/type
			usort($release_set, function() {
				return $a['content']['quick_name'] <=> $b['content']['quick_name'];
			});
			
			// Loop through release sets , and transform content to link to release
			// Show base release name only if first release in set, or if press/type not provided
			foreach($release_set as $release_set_key => $release) {
				
				$release_name = lang( ($release['content']['romaji'] ?: $release['content']['name']), $release['content']['name'], 'hidden' );
				$press_name = $release['content']['press_name'] ? lang( ($release['content']['press_romaji'] ?: $release['content']['press_name']), $release['content']['press_name'], 'hidden' ) : null;
				$type_name = $release['content']['type_name'] ? lang( ($release['content']['type_romaji'] ?: $release['content']['type_name']), $release['content']['type_name'], 'hidden' ) : null;
				
				$new_content =
					'<a class="'.($release_set_key === 0 ? 'symbol__release' : null).'" href="'.$release['content']['url'].'">'.
					($release_set_key === 0 || (!$press_name && !$type_name) ? $release_name : null).
					($release_name && $press_name ? ' ' : null).
					$press_name.
					(($release_name && !$press_name && $type_name) || ($press_name && $type_name) ? ' ' : null).
					$type_name.
					'</a>';
				
				// Set content of first item in release set to new link
				if($release_set_key === 0) {
					$release_set[0]['content'] = $new_content;
				}
				else {
					$link_separator = ' &nbsp; <span class="any--weaken">/</span> &nbsp; ';
					$release_set[0]['content'] .= $link_separator.$new_content;
				}
				
				// If last release in set, add CDJapan link
				if($release_set_key + 1 === count($release_set)) {
					$cdjapan_aff_id = 'PytJTGW7Lok/6128/A549875';
					$cdjapan_link = 'http://www.cdjapan.co.jp/aff/click.cgi/'.$cdjapan_aff_id.'/searches?f=all&q='.str_replace('-', '+', friendly($release['content']['romaji'] ?: $release['content']['name']));
					$release_set[0]['content'] .= ' &nbsp; <a class="any__note a--inherit" href="'.$cdjapan_link.'">BUY</a>';
				}
				
				// If nth (> first) release in release set, unset its entry from day, since it will be combined into one entry
				if($release_set_key > 0) {
					unset($history[$release['history_key']]);
				}
			}
			
			// After transforming release set and adding links to first item in set, put into original array
			$history[$release_set[0]['history_key']]['content'] = $release_set[0]['content'];
		}
	}
	
	// After transforming day, reset keys and return
	if(is_array($history) && !empty($history)) {
		$history = array_values($history);
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