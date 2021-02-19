<?php

include_once('../php/include.php');
include_once('../php/function-render_json_list.php');

$allowed_item_types = [ 'musician', 'release' ];

$artist_id = sanitize($_POST['artist_id']);
$id_column = sanitize($_POST['id_column']);

if( is_numeric($artist_id) ) {
	
	foreach([ 'musician', 'release' ] as $item_type) {
		
		ob_start();
		
		render_json_list( $item_type, $artist_id, $id_column, null, null, [ 'append_id' => true ] );
		
		$list = ob_get_clean();
		
		if( strlen($list) && strpos($list, 'template') === 1 ) {
			
			$output['status'] = 'success';
			$output[ $item_type.'_list' ] = $list;
			
		}
		else {
			$output['result'] = 'List is empty.';
		}
		
	}
	
}
else {
	$output['result'] = 'That artist doesn\'t exist.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);