<?php
include_once('../php/function-render_component.php');
include_once('../images/template-option.php');
include_once('../images/template-image.php');
include_once('../images/template-image-upload.php');

script([
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initDelete.js',
	'/images/script-uploadImage.js',
]);

style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
	'/images/style-template-image-upload.css',
]);

// Helper function to render <options> from list
function render_options($option_ids_string, $option_list) {
	global $option_template;
	
	$option_ids = explode(',', $option_ids_string);
	$output = [];
	
	foreach($option_ids as $option_id) {
		if(isset($option_list[$option_id])) {
			$option_name = $option_list[$option_id][2];
		}
		
		$output[] = render_component($option_template, [
			'value' => $option_id,
			'name' => $option_name,
		]);
	}
	
	return implode("\n", $output);
}

// Render image upload area and image edit areas
function render_image_upload($item_type, $item_id, $item_name, $default_description, $images) {
	global $image_template;
	global $image_upload_template;
	global $artist_list;
	global $musician_list;
	global $release_list;
	
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
				'artist_ids' => render_options($image['artist_ids'], $artist_list),
				'musician_ids' => render_options($image['musician_ids'], $musician_list),
				'release_ids' => render_options($image['release_ids'], $release_list),
				'scanned_by' => $image['user_id'] == $_SESSION['userID'] ? '1' : '0',
				'background_url' => '/images/'.$image['id'].'.thumbnail.jpg',
			]);
		}
	}
	else {
		$rendered_images = [];
	}
	
	echo render_component($image_upload_template, [
		'default_item_type' => $item_type,
		'default_item_id' => $item_id,
		'default_item_name' => $item_name,
		'default_description' => $default_description,
		'extant_images' => implode("\n", $rendered_images),
	]);
}