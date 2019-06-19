<?php
include_once('../php/include.php');

// Set access functions if necessary
$access_artist = $access_artist ?: new access_artist($pdo);
$access_label = $access_label ?: new access_label($pdo);
$access_musician = $access_musician ?: new access_musician($pdo);
$access_release = $access_release ?: new access_release($pdo);

function render_json_list($input_type, $input = null, $input_id_type = null, $include_friendly = null) {
	global $pdo;
	global $access_artist, $access_label, $access_musician, $access_release;
	global $artist_list, $label_list, $musician_list, $release_list;
	global $list_is_rendered;
	
	// Check if already called
	if(!$list_is_rendered[$input_type]) {
		$list_is_rendered[$input_type] = true;
		
		// If not given array, get data
		if(is_array($input)) {
		}
		elseif(!is_array($input) && is_numeric($input) && strlen($input_type)) {
			$input = ${'access_' . $input_type}->{'access_' . $input_type}([ $input_id_type => $input, 'get' => 'name' ]);
		}
		elseif(!is_array($input) && !strlen($input)) {
			$input = ${'access_' . $input_type}->{'access_' . $input_type}([ 'get' => 'name' ]);
		}
		
		// Clean array
		$input = is_array($input) ? $input : [];
		$input = array_values($input);
		$num_input = count($input);
		
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
			
			// Year
			elseif($input_type === 'year') {
				$input[$i]['id'] = $input[$i]['year'];
				$input_chunk = [ $input[$i]['year'], null, $input[$i]['year'] ];
			}
			
			// Output chunk
			$output_list[$input[$i]['id']] = $input_chunk;
			unset($input_chunk);
		}
		
		// Clean output and save associative version to external variable
		$output_list = is_array($output_list) ? $output_list : [];
		${$input_type . '_list'} = $output_list;
		$output_list = array_values($output_list);
		
		// Echo json version of output
		echo '<template data-contains="'.$input_type.'s">'.json_encode($output_list).'</template>';
	}
}