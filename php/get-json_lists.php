<?php

include_once('../php/include.php');
include_once('../php/function-render_json_list.php');

$allowed_list_types = [ 'musicians', 'releases', 'songs' ];

$item_id    = sanitize($_POST['item_id']);
$id_column  = sanitize($_POST['id_column']);
$list_types = $_POST['list_types'];

if( is_numeric($item_id) ) {
	
	if( is_array($list_types) && !empty($list_types) ) {
		foreach( $list_types as $list_type ) {
			
			if( in_array( $list_type, $allowed_list_types ) ) {
				
				// Remove trailing 's' if necessary
				$list_type = substr( $list_type, -1 ) == 's' ? substr( $list_type, 0, -1 ) : $list_type;
				
				ob_start();
				
				render_json_list( $list_type, $item_id, $id_column, null, null, [ 'append_id' => true ] );
				
				$list = ob_get_clean();
				
				if( strlen($list) && strpos($list, 'template') === 1 ) {
					
					$output['status'] = 'success';
					$output['lists'][ $list_type ] = $list;
					
				}
				else {
					$output['result'] = 'List is empty.';
				}
				
			}
			else {
				$output['result'] = 'List type not allowed.';
			}
			
		}
	}
	else {
		$output['result'] = 'No lists requested.';
	}
	
}
else {
	$output['result'] = 'That artist doesn\'t exist.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);