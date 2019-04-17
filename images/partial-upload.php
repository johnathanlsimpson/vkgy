<?php
include_once('../php/function-render_component.php');
include_once('../images/template-option.php');
include_once('../images/template-image.php');
include_once('../images/template-image-upload.php');

script([
	'/scripts/script-uploadImage.js',
]);

style([
	'/images/style-template-image-upload.css',
]);

// Helper function to render <options> from list
function render_options($option_ids_string) {
	global $option_template;
	
	$option_ids = explode(',', $option_ids_string);
	$output = [];
	
	foreach($option_ids as $option_id) {
		$output[] = render_component($option_template, [
			'value' => $option_id
		]);
	}
	
	return implode("\n", $output);
}

// Render image upload area and image edit areas
function render_image_upload($item_type, $item_id, $images) {
	global $image_template;
	global $image_upload_template;
	
	if(is_array($images) && !empty($images)) {
		foreach($images as $image) {
			$rendered_images[] = render_component($image_template, [
				'id' => $image['id'],
				'item_type' => $item_type,
				'item_id' => $item_id,
				'description' => $image['description'],
				'credit' => $image['credit'],
				'is_exclusive' => $image['is_exclusive'] ? 'checked' : null,
				'is_default' => $default_id == $image['id'] ? 'checked' : null,
				'artist_ids' => render_options($image['artist_ids']),
				'musician_ids' => render_options($image['musician_ids']),
				'release_ids' => render_options($image['release_ids']),
				'scanned_by' => $image['user_id'] == $_SESSION['userID'] ? '1' : '0',
				'background_url' => '/images/'.$image['id'].'.thumbnail.jpg',
			]);
		}
	}
	else {
		$rendered_images = [];
	}
	
	echo render_component($image_upload_template, [
		'item_type' => $item_type,
		'item_id' => $item_id,
		'extant_images' => implode("\n", $rendered_images),
	]);
}