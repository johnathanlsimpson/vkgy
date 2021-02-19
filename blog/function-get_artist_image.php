<?php

include_once('../php/include.php');
include_once('../php/class-access_image.php');

$access_image = new access_image($pdo);

$artist_id = $_POST['artist_id'];

if( is_numeric($artist_id) ) {
	
	$sql_image = 'SELECT * FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.id=? LIMIT 1';
	$stmt_image = $pdo->prepare($sql_image);
	$stmt_image->execute([ $artist_id ]);
	$image = $stmt_image->fetch();
	
	if( is_array($image) && !empty($image) ) {
		
		$output['description'] = $image['description'];
		$output['credit'] = $image['credit'];
		$output['is_exclusive'] = $image['is_exclusive'];
		$output['artist_id'] = $artist_id;
		
		$output['status']            = 'success';
		$output['image_id']          = $image['id'];
		$output['image_url']         = '/images/'.$image['id'].'.'.$image['extension'];
		$output['image_style']       = 'background-image: url(/images/'.$image['id'].'.thumbnail.'.$image['extension'].');';
		$output['image_markdown']    = '![](/images/'.$image['id'].'.'.$image['extension'].')';
		$output['is_exclusive_for']  = 'is-exclusive-'.$image['id'];
		$output['is_default_for']    = 'is-default-'.$image['id'];
		$output['image_extension']   = $image['extension'];
		$output['is_facsimile']      = 1;
		
	}
	
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);