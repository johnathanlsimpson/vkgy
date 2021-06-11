<?php

// ========================================================
// Includes
// ========================================================

include_once('../php/include.php');
include_once('../php/class-magazine.php');
$access_magazine = new magazine($pdo);

// ========================================================
// Main logic
// ========================================================

// Eventually need to replace with a more generic permission
if($_SESSION['can_add_livehouses']) {
	
	if(is_array($_POST) && !empty($_POST)) {
		
		// Separate magazine attributes data from magazine data
		$magazine_attributes = $_POST['attributes'];
		unset( $_POST['attributes'] );
		
		// Set up magazines--post is formatted such that name[1] and romaji[1] go together
		foreach( $_POST['name'] as $index => $name ) {
			
			// Loop through rest of $_POST at same level; e.g. $series[0][name] = $_POST[name][0];
			foreach( $_POST as $key => $values ) {
				$magazine[ $key ] = $_POST[ $key ][ $index ];
			}
			
			// Now update/add magazine
			$magazine_output = $access_magazine->update_magazine( $magazine );
			
			// Add to total output
			$output['status'] = $magazine_output['status'];
			if( $magazine_output['result'] ) {
				$output['result'][] = $magazine_output['result'];
			}
			
		}
		
		// Loop through magazine attributes--they're formatted e.g. attributes[name][1]
		if( is_array($magazine_attributes) && !empty($magazine_attributes) ) {
			foreach( $magazine_attributes['name'] as $index => $name ) {
				
				// Loop through rest of attributes at same level; e.g. $attribute[0][name] = $magazine_attributes[names][0]
				foreach( $magazine_attributes as $key => $values ) {
					$attribute[ $key ] = $magazine_attributes[ $key ][ $index ];
				}
				
				// is_default is weird cause extant attributes are set up as a radio element that revolves around attributes of the same type
				// e.g. selected attributes[is_default][{attribute_type}] = {id_of_default_attribute}
				if( is_numeric($attribute['id']) ) {
					$attribute['is_default'] = $magazine_attributes['is_default'][ $attribute['type'] ] == $attribute['id'] ? 1 : 0;
				}
				
				// ...otherwise, if adding a new attribute, is_default is a checkbox so we just have to see whether or not it was checked
				else {
					$attribute['is_default'] = $magazine_attributes['is_default']['new'] ? 1 : 0;
				}
				
				// Pass attribute to be updated
				$attribute_output = $access_magazine->update_attribute( $attribute );
				
				// Add to total output
				$output['status'] = $attribute_output['status'];
				if( $attribute_output['result'] ) {
					$output['result'][] = $attribute_output['result'];
				}
				
			}
		}
		
	}
	else {
		$output['result'][] = 'No data passed.';
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to add magazines.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
$output['points'] = $points;

echo json_encode($output);