<?php
include_once('../php/include.php');

// Given song title, clean up and escape for output in a json list
function clean_song_title($input) {
	$input = preg_replace("/"."\s+"."/u", " ", $input);
	$input = preg_replace("/"."^[\s\n\t]*(.+?)[\s\n\t]*$"."/", "$1", $input);
	$input = preg_replace("/"."^\s*(.+?)\s*$"."/u", "$1", $input);
	$input = str_replace("&#92;", "\\", $input);
	$input = str_replace(["\\(", "\\)"], ["\\\\&#40;", "\\\\&#41;"], $input);
	$input = sanitize($input);
	$input = str_replace(["&#65374;", "&#8764;", "&#8765;", "&#12316;"], "~", $input);
	$input = str_replace(["&#65378;", "&#65379;", "&#65339;", "&#65341;", "&#65288;", "&#65289;"], ["&#12300;", "&#12301;", "[", "]", "(", ")"], $input);
	$input = preg_replace("/"."(.*?)\"(.+?)\"(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
	$input = preg_replace("/"."(.*?)&#34;(.+?)&#34;(.*?)"."/", "$1&ldquo;$2&rdquo;$3", $input);
	$input = preg_replace("/"."(\(.+\))"."/", "", $input);
	$input = preg_replace("/"."^ (.*)"."/", "$1", $input);
	$input = preg_replace("/"."(.*?) $"."/", "$1", $input);
	$input = preg_replace("/"."(.*?) \\$"."/", "$1", $input);
	return $input;
}

// Set access functions if necessary
$access_artist = $access_artist ?: new access_artist($pdo);
$access_label = $access_label ?: new access_label($pdo);
$access_musician = $access_musician ?: new access_musician($pdo);
$access_release = $access_release ?: new access_release($pdo);

// Grab data of a certain type and output it as a json object
function render_json_list($input_type, $input = null, $input_id_type = null, $include_friendly = null, $first_option_id = null) {
	global $pdo;
	global $access_artist, $access_label, $access_musician, $access_release;
	global $artist_list, $label_list, $musician_list, $release_list;
	global $list_is_rendered;
	
	// Check if list was already generated
	if(!$list_is_rendered[$input_type]) {
		$list_is_rendered[$input_type] = true;
		
		// If provided array of data, do nothing, and format it later
		if(is_array($input)) {
		}
		
		// If given ID and data type, get data for that ID
		elseif(!is_array($input) && is_numeric($input) && strlen($input_type)) {
			
			// If given artist ID and type is songs, do a manual search for all tracks by that artist
			if($input_type === 'song') {
				$sql_songs = 'SELECT id, name, romaji FROM releases_tracklists WHERE artist_id=? GROUP BY COALESCE(romaji, name) ORDER BY COALESCE(romaji, name) ASC';
				$stmt_songs = $pdo->prepare($sql_songs);
				$stmt_songs->execute([ sanitize($input) ]);
				$input = $stmt_songs->fetchAll();
			}
			
			// Otherwise do a generic search
			else {
				$input = ${'access_' . $input_type}->{'access_' . $input_type}([ $input_id_type => $input, 'get' => 'name' ]);
			}
		}
		
		// If given name, do generic search for name
		elseif(!is_array($input) && !strlen($input)) {
			if($input_type === 'livehouse') {
				$sql_livehouses = 'SELECT lives_livehouses.id, CONCAT_WS(" ", COALESCE(areas.romaji, areas.name), COALESCE(lives_livehouses.romaji, lives_livehouses.name)) AS romaji, CONCAT_WS(" ", areas.name, lives_livehouses.name) AS name FROM lives_livehouses LEFT JOIN areas ON areas.id=lives_livehouses.area_id';
				$stmt_livehouses = $pdo->prepare($sql_livehouses);
				$stmt_livehouses->execute();
				$input = $stmt_livehouses->fetchAll();
			}
			else {
				$input = ${'access_' . $input_type}->{'access_' . $input_type}([ 'get' => 'name' ]);
			}
		}
		
		// Clean array
		$input = is_array($input) ? $input : [];
		$input = array_values($input);
		$num_input = count($input);
		
		// If given 'first option', add to array of data
		// Next function will loop through and overwrite it
		// with the data of the same ID from the database call,
		// but in the top position
		if(is_numeric($first_option_id)) {
			array_unshift($input, [ 'id' => $first_option_id ]);
			$num_input++;
		}
		
		// Loop through array and build chunk
		for($i=0; $i<$num_input; $i++) {
			$input_chunk[] = $input[$i]['id'];
			
			// Clean values
			foreach($input[$i] as $key => $value) {
				$input[$i][$key] = str_replace(['&#92;', '&#34;'], ['\\', '"'], $value);
			}
			
			// Areas
			if($input_type === 'area') {
				$input_chunk[] = null;
				$input_chunk[] = ($input[$i]['romaji'] ? $input[$i]['romaji'].' ('.$input[$i]['name'].')' : $input[$i]['name']);
			}
			
			// Artist
			elseif($input_type === 'artist') {
				$input_chunk[] = $include_friendly ? $input[$i]['friendly'] : '';
				$input_chunk[] =
					($input[$i]['quick_name'].($input[$i]['romaji'] ? ' ('.$input[$i]['name'].')' : null)).
					(friendly($input[$i]['quick_name']) != $input[$i]['friendly'] ? ' ('.$input[$i]['friendly'].')' : null);
			}
			
			// Label
			elseif($input_type === 'label') {
				$input_chunk[] = $include_friendly ? $input[$i]['friendly'] : '';
				$input_chunk[] = $input[$i]['quick_name'].($input[$i]['romaji'] ? ' ('.$input[$i]['name'].')' : null);
			}
			
			// Musician
			elseif($input_type === 'musician') {
				$input_chunk[] = friendly($input[$i]['as_romaji'] ?: $input[$i]['as_name'] ?: $input[$i]['romaji'] ?: $input[$i]['name']);
				$input_chunk[] =
					($input[$i]["as_romaji"] ?: $input[$i]["as_name"] ?: $input[$i]["romaji"] ?: $input[$i]["name"]).
					($input[$i]["as_romaji"] ? " (".$input[$i]["as_name"].")" : (!$input[$i]["as_name"] && $input[$i]["romaji"] ? " (".$input[$i]["name"].")" : null));
			}
			
			// Livehouses
			elseif($input_type === 'livehouse') {
				$input_chunk[] = null;
				$input_chunk[] = ($input[$i]['romaji'] ? $input[$i]['romaji'].' ('.$input[$i]['name'].')' : $input[$i]['name']);
			}
			
			// Release
			elseif($input_type === 'release') {
				$input_chunk[] = $input[$i]['friendly'];
				$input_chunk[] = $input[$i]['quick_name'];
			}
			
			// Song
			elseif($input_type === 'song') {
				$input_chunk['name'] = clean_song_title( $input[$i]['name'] );
				$input_chunk['romaji'] = clean_song_title( $input[$i]['romaji'] );
				$input_chunk['friendly'] = strtolower( preg_replace( '/'.'[^A-z0-9]'.'/', '', html_entity_decode($input_chunk['romaji'] ?: $input_chunk['name'], ENT_QUOTES, 'UTF-8') ) );
				$input_chunk['quick_name'] = $input[$i]['romaji'] ? clean_song_title($input[$i]['romaji']).' ('.clean_song_title($input[$i]['name']).')' : clean_song_title($input[$i]['name']);
			}
			
			// Year
			elseif($input_type === 'year') {
				$input[$i]['id'] = $input[$i]['year'];
				$input_chunk = [ $input[$i]['year'], null, $input[$i]['year'] ];
			}
			
			// Output chunk
			$output_list[ $input[$i]['id'] ] = $input_chunk;
			unset($input_chunk);
		}
		
		// If getting song list, do some additional cleaning to remove duplicates (but allow different JP names with same romaji)
		if($input_type === 'song') {
			$new_output = [];
			
			// Loop through and remove blanks and dupes
			foreach($output_list as $output_key => $output_chunk) {
				
				// Skip if no name provided
				if(strlen($output_chunk['name'])) {
					
					// If already have one song with same romaji, ignore if duplicate name
					if(isset( $new_output[$output_chunk['friendly']] )) {
						
						$prev_name = strtolower( str_replace( ' ', '', html_entity_decode( $new_output[$output_chunk['friendly']]['name'], ENT_QUOTES, 'UTF-8' ) ) );
						$this_name = strtolower( str_replace( ' ', '', html_entity_decode( $output_chunk['name'], ENT_QUOTES, 'UTF-8' ) ) );
						
						if($prev_name != $this_name) {
							$new_output[ $output_chunk['friendly'].$output_chunk['name'] ] = $output_chunk;
						}
						
					}
					else {
						$new_output[ $output_chunk['friendly'] ] = $output_chunk;
					}
					
				}
				
			}
			
			// Undo associative keys
			$output_list = array_values($new_output);
		}
		
		// Clean output and save associative version to external variable
		$output_list = is_array($output_list) ? $output_list : [];
		${$input_type . '_list'} = $output_list;
		$output_list = array_values($output_list);
		
		// Echo json version of output
		echo '<template data-contains="'.$input_type.'s">'.json_encode($output_list).'</template>';
	}
}