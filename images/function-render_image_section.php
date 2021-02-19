<?php
include_once('../php/function-render_component.php');
include_once('../images/function-render_face.php');
include_once('../images/template-option.php');
include_once('../images/template-image.php');
include_once('../images/template-upload.php');

script([
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-alpine.js',
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
	global $face_template;
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
	
	// Save a list of all artists tagged in this batch of images so that we can get those artists' releases and musicians
	$all_artist_ids = [];
	
	// Save default artist to list of artist ids
	if( strlen($default['artist']) ) {
		$all_artist_ids[ $default['artist'] ] = $default['artist'];
	}
	
	if(is_array($images) && !empty($images)) {
		foreach($images as $image) {
			
			// Save artist ids to list so we can get release/musician json lists for them later
			if( strlen($image['artist_ids']) ) {
				foreach( explode(',', $image['artist_ids']) as $temp_artist_id ) {
					if( is_numeric($temp_artist_id) ) {
						
						$all_artist_ids[ $temp_artist_id ] = $temp_artist_id;
						
					}
				}
			}
			
			$image['url'] = '/images/'.$image['id'].'.thumbnail.'.$image['extension'];
			
			// Attempt to set save a 'main artist' for this image so the musician and release selects can set e.g. data-source="musicians_1"
			if( strlen($image['artist_ids']) ) {
				$artist_id = explode(',', $image['artist_ids'])[0];
			}
			else {
				$artist_id = $default['artist'];
			}
			
			// For musicians tagged to image by face, get the html box for each
			$tagged_faces = '';
			if( is_array($image['musicians']) && !empty($image['musicians']) ) {
				foreach( $image['musicians'] as $musician ) {
					
					$tagged_faces .= render_face([
						'image_url'     => '/images/'.$image['id'].'.'.$image['extension'],
						'face'          => json_decode( $musician['face_boundaries'], true ),
						'musician_id'   => $musician['musician_id'],
						'musician_name' => is_numeric($musician['musician_id']) ? '(tagged)' : null,
						'artist_id'     => $artist_id,
					]);
					
				}
			}
			
			$rendered_images[] = render_component($image_template, [
				'id'                 => $image['id'],
				'item_type'          => $args['item_type'],
				'item_id'            => $args['item_id'],
				'description'        => str_replace("&#39;", '\&#39;', $image['description']),
				'credit'             => $image['credit'],
				'is_exclusive'       => $image['is_exclusive'] ? 'checked' : null,
				'is_default'         => $image['id'] == $args['default_id'] ? 'checked' : null,
				'artist_id'          => $artist_id,
				'artist_ids'         => render_options(($image['artist_ids'] ?: $default['artist']), $artist_list),
				'blog_id'            => render_options(($image['blog_id'] ?: $default['blog']), $blog_list),
				'musician_ids'       => render_options(($image['label_ids'] ?: $default['label']), $label_list),
				'musician_ids'       => render_options(($image['musician_ids'] ?: $default['musician']), $musician_list),
				'release_ids'        => render_options(($image['release_ids'] ?: $default['release']), $release_list),
				'scanned_by'         => $image['user_id'] == $_SESSION['user_id'] ? '1' : '0',
				'background_url'     => '/images/'.$image['id'].'.thumbnail.'.$image['extension'],
				'image_markdown'     => '!['.$image['description'].'](/images/'.$image['id'].'.'.$image['extension'].')',
				'image_url'          => '/images/'.$image['id'].'.'.$image['extension'],
				'delete_class'       => $_SESSION['can_delete_data'] || $_SESSION['user_id'] === $image['user_id'] ? null : 'any--hidden',
				'face_boundaries'    => str_replace(['{','}'], ['&#123;','&#125;'], sanitize($image['face_boundaries'])),
				'checked_image_type' => $image['image_content'],
				'extension'          => $image['extension'],
				'tagged_faces'       => $tagged_faces,
				'is_previous_upload' => true,
				'is_facsimile'       => $image['item_type'] && $args['item_type'] != $image['item_type'] ? '1' : null, // If we eventually want to make facsimiles different in image list somehow; currently this is only set to 1 (= can't edit) if auto-populating image in blog post
				'artist_is_set'      => $image['artist_ids'] || $default['artist']
			]);
			
		}
	}
	else {
		$rendered_images = [];
	}
	
	// Loop through all tagged artists and get release/musician json lists
	if( is_array($all_artist_ids) && !empty($all_artist_ids) ) {
		foreach($all_artist_ids as $artist_id) {
			
			render_json_list( 'musician', $artist_id, 'artist_id', null, null, [ 'append_id' => true ] );
			render_json_list( 'release', $artist_id, 'artist_id', null, null, [ 'append_id' => true ] );
			
		}
	}
	
	$args['no_default'] = is_numeric($args['default_id']) ? null : 'checked';
	$args['extant_images'] = implode("\n", $rendered_images);
	
	echo render_component($image_upload_template, $args);
}