<?php
include_once('../php/include.php');
include_once('../php/class-song.php');

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
$access_user = $access_user ?: new access_user($pdo);
$access_song = $access_song ?: new song($pdo);

// Grab data of a certain type and output it as a json object
function render_json_list($input_type, $input = null, $input_id_type = null, $include_friendly = null, $first_option_id = null, $args = []) {
	global $pdo;
	global $access_artist, $access_label, $access_musician, $access_release, $access_user, $access_song;
	global $artist_list, $label_list, $musician_list, $release_list, $user_list;
	global $list_is_rendered;
	
	// Name used in data-contains attribute--also used to check if we need to get it again
	$data_contains_name = $input_type.'s'.( $args['append_id'] && is_numeric($input) ? '_'.$input : null );
	
	// Check if list was already generated
	if( !$list_is_rendered[ $data_contains_name ] ) {
		$list_is_rendered[ $data_contains_name ] = true;
		
		// If provided array of data, or asked to leave empty, do nothing
		if( is_array($input) || $args['leave_empty'] ) {
		}
		
		// If given ID and data type, get data for that ID
		elseif(!is_array($input) && is_numeric($input) && strlen($input_type)) {
			
			/* Temporary */
			// If getting songs, let's also get all tracks from that artist, since all tracks aren't in DB yet
			if($input_type === 'song' ) {
				
				// Get all tracks
				$sql_tracks = 'SELECT id, name, romaji FROM releases_tracklists WHERE artist_id=? AND song_id IS NULL GROUP BY COALESCE(romaji, name) ORDER BY COALESCE(romaji, name) ASC';
				$stmt_tracks = $pdo->prepare($sql_tracks);
				$stmt_tracks->execute([ $input ]);
				$tracks = $stmt_tracks->fetchAll();
				
				// If we got all tracks, loop through, do a rough name cleaning, and attempt to remove dupes/notes
				if( is_array($tracks) && !empty($tracks) ) {
					foreach( $tracks as $track ) {
						
						// Clean name and get friendly
						// May be better to replace this with the cleaning/flattening function from songs
						$name = clean_song_title( $track['name'] );
						$romaji = clean_song_title( $track['romaji'] );
						$friendly = friendly( $romaji ?: $name );
						$quick_name = $romaji ? $romaji.' ('.$name.')' : $name;
						
						// Add to array to eliminate dupes
						$temp_songs[ $friendly ] = [ '', $name, $romaji, $friendly, $quick_name ];
						
					}
				}
				
				// Make sure we have an array
				$temp_songs = is_array($temp_songs) ? array_values( $temp_songs ) : [];
				
			}
			
			// Get results from search
			$input = ${'access_' . $input_type}->{'access_' . $input_type}([ $input_id_type => $input, 'get' => 'name' ]);
			
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
					[ 'O.', 'V. ', 'G. ', 'B. ', 'D. ', 'K. ', 'S. ' ][ $input[$i]['position'] ].
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
				
				$input_chunk[] = $input[$i]['name'];
				$input_chunk[] = $input[$i]['romaji'];
				$input_chunk[] = $input[$i]['friendly'];
				$input_chunk[] = ( $input[$i]['romaji'] ? $input[$i]['romaji'].' ('.$input[$i]['name'].')' : $input[$i]['name'] ).( strlen($input[$i]['hint']) ? ' ('.$input[$i]['hint'].')' : null );
				
			}
			
			// Year
			elseif($input_type === 'year') {
				$input[$i]['id'] = $input[$i]['year'];
				$input_chunk = [ $input[$i]['year'], null, $input[$i]['year'] ];
			}
			
			// User
			elseif($input_type === 'user') {
				$input_chunk[] = $input[$i]['username'];
				$input_chunk[] = $input[$i]['username'];
			}
			
			// Output chunk
			$output_list[ $input[$i]['id'] ] = $input_chunk;
			unset($input_chunk);
		}
		
		/* Temporary */
		// If getting songs, be sure to add potential songs from query of tracks
		if( $input_type === 'song' ) {
			$output_list = array_merge( $output_list, $temp_songs );
		}
		
		// Clean output and save associative version to external variable
		$output_list = is_array($output_list) ? $output_list : [];
		${$input_type . '_list'} = $output_list;
		$output_list = array_values($output_list);
		
		// Echo JSON version of output
		echo '<template data-contains="'.$data_contains_name.'">'.json_encode($output_list).'</template>';
	}
	
}