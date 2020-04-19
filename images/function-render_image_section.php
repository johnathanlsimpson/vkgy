<?php
include_once('../php/function-render_component.php');
include_once('../images/template-option.php');
include_once('../images/template-image.php');
include_once('../images/template-upload.php');

script([
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initDelete.js',
	'/images/script-uploadImage.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/images/style-render-image-section.css',
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
		
		if(strlen($option_id)) {
			$output[] = render_component($option_template, [
				'value' => $option_id,
				'name' => $option_name,
			]);
		}
	}
	
	return implode("\n", $output);
}

// Render image upload area and image edit areas
function render_image_section($images, $args = []) {
	global $image_template;
	global $image_upload_template;
	global $wrapped_image_template;
	global $wrapped_image_upload_template;
	global $wrapped_option_template;
	global $artist_list;
	global $blog_list;
	global $musician_list;
	global $release_list;
	
	// Render empty templates
	echo render_component($wrapped_image_upload_template);
	echo render_component($wrapped_image_template, $args);
	echo render_component($wrapped_option_template);
	
	$default[$args['item_type']] = $args['item_id'];
	
	if(is_array($images) && !empty($images)) {
		foreach($images as $image) {
			$rendered_images[] = render_component($image_template, [
				'id'             => $image['id'],
				'item_type'      => $args['item_type'],
				'item_id'        => $args['item_id'],
				'description'    => $image['description'],
				'credit'         => $image['credit'],
				'is_exclusive'   => $image['is_exclusive'] ? 'checked' : null,
				'is_default'     => $image['id'] == $args['default_id'] ? 'checked' : null,
				'artist_ids'     => render_options(($image['artist_ids'] ?: $default['artist']), $artist_list),
				'blog_id'        => render_options(($image['blog_id'] ?: $default['blog']), $blog_list),
				'musician_ids'   => render_options(($image['label_ids'] ?: $default['label']), $label_list),
				'musician_ids'   => render_options(($image['musician_ids'] ?: $default['musician']), $musician_list),
				'release_ids'    => render_options(($image['release_ids'] ?: $default['release']), $release_list),
				'scanned_by'     => $image['user_id'] == $_SESSION['user_id'] ? '1' : '0',
				'background_url' => '/images/'.$image['id'].'.thumbnail.'.$image['extension'],
				'image_markdown' => '![](/images/'.$image['id'].'.'.$image['extension'].')',
				'image_url'      => '/images/'.$image['id'].'.'.$image['extension'],
				'delete_class'   => $_SESSION['can_delete_data'] || $_SESSION['user_id'] === $image['user_id'] ? null : 'any--hidden',
			]);
		}
	}
	else {
		$rendered_images = [];
	}
	
	$args['extant_images'] = implode("\n", $rendered_images);
	
	echo render_component($image_upload_template, $args);
}