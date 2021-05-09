<?php

$page_title = 'Edit images';

include_once('../artists/head.php');
include_once('../php/function-render_component.php');
include_once('../php/function-render_json_list.php');
include_once('../images/function-calculate_face_box.php');

script([
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-tribute.js',
	'/scripts/external/script-inputmask.js',
	
	'/scripts/script-initDelete.js',
	'/scripts/script-initSelectize.js',
	
	'/artists/script-page-edit-images.js',
]);

style([
	'/style/external/style-tribute.css',
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	
	'/artists/style-page-edit-images.css',
]);

subnav([
	'Edit images' => '/artists/'.$artist['friendly'].'/images/edit/',
]);

render_json_list('artist');
render_json_list('musician', $artist['musicians']);
render_json_list('release', $artist['id'], 'artist_id');

// Loop through images and make note of which musicians are tagged in them
if( is_array($artist['images']) && !empty($artist['images']) ) {
		
	foreach( $artist['images'] as $image) {
		
		// If this is an image of a single musician, let's check for plainly tagged musicians (i.e. let's not show a group image in which one dude is tagged but we're not sure where he is in the photo)
		if( $image['image_content'] == 2 ) {
			
			// Explode and clean a list of plain musician ids
			$plainly_tagged_musicians = explode(',', $image['musician_ids']);
			$plainly_tagged_musicians = array_filter($plainly_tagged_musicians, function($x) { return is_numeric($x); });
			
			if( is_array($plainly_tagged_musicians) && !empty($plainly_tagged_musicians) ) {
				foreach($plainly_tagged_musicians as $musician_id) {
					
					$musicians_images[ $musician_id ][] = [ 'image_id' => $image['id'] ];
					
				}
			}
			
		}
		
		// Otherwise if the image is a group image or a flyer, let's look for musicians tagged by their face
		elseif( $image['image_content'] == 1 || $image['image_content'] == 3 ) {
			
			// Get any fully tagged musicians that have boundaries specified
			$fully_tagged_musicians = $image['musicians'];
			$fully_tagged_musicians = array_filter($fully_tagged_musicians, function($x) { return is_array($x) && is_numeric($x['musician_id']) && strlen($x['face_boundaries']); });
			
			if( is_array($fully_tagged_musicians) && !empty($fully_tagged_musicians) ) {
				foreach($fully_tagged_musicians as $musician) {
					
					$musicians_images[ $musician['musician_id'] ][] = [ 'image_id' => $musician['image_id'], 'face_boundaries' => $musician['face_boundaries'] ];
					
				}
			}
			
		}
		
	}
	
}

?>

<div class="col c1">
	<div>
		<h2>
			Edit image gallery
		</h2>
		<?php
			include('../images/function-render_image_section.php');
			render_image_section($artist['images'], [
				'item_type' => 'artist',
				'item_id' => $artist['id'],
				'item_name' => $artist['quick_name'],
				'description' => sanitize( $artist['quick_name'].' group photo', 'alpine' ),
				'default_id' => $artist['image_id'],
				'hide_blog' => true,
				'hide_labels' => true,
				'hide_markdown' => true,
			]);
		?>
	</div>
</div>

<div class="col c1" id="musicians">
	
	<div>
		
		<h2>
			Set musician profile photos
		</h2>
		
		<div class="text text--outlined any--weaken-color">
			<p class="symbol__help">Below, you can set which picture will appear for a musician on the artist's profile page.
			You may only choose from “musician” photos in which the musician is tagged, or “group” or “flyer” photos in which the musician is tagged by face.</p>
			<p class="symbol__error">After tagging the musician in photos in the above section, you'll have to refresh the page for them to appear as options below.</p>
		</div>
		
		<?php if( is_array($artist['musicians']) && !empty($artist['musicians']) ): ?>
			
			<ul class="text">
				
				<?php foreach( $artist['musicians'] as $musician ): ?>
				
					<li class="input__row">
					
						<div class="input__group">
							
							<label class="input__label"><?= $musician['as_name'] ? ( $musician['as_romaji'] ? lang($musician['as_romaji'], $musician['as_name'], 'hidden') : $musician['as_name'] ) : ( $musician['romaji'] ? lang($musician['romaji'], $musician['name'], 'hidden') : $musician['name'] ); ?></label>
							
							<?php if( is_array( $musicians_images[ $musician['id'] ] ) && !empty( $musicians_images[ $musician['id'] ] ) ): ?>
							
								<?php
									foreach( $musicians_images[ $musician['id'] ] as $musician_image ) {
										
										$image_key = $musician_image['image_id'];
										$face_boundaries = $musician_image['face_boundaries'];
										$image = $artist['images'][$image_key];
										
										// If we're showing part of a group image using face boundaries, need to do some calculations
										if( $face_boundaries ) {
											
											$face_boundaries = json_decode( $face_boundaries, true );
											$face_box = calculate_face_box([ 'image_height' => $image['height'], 'image_width' => $image['width'], 'face' => $face_boundaries ]);
											$face_styles = $face_box['css'];
											$thumbnail = $image['small_url'];
											
										}
										
										// If it's just a single musician image, we can just show it normally
										else {
											
											$thumbnail = $image['thumbnail_url'];
											$face_styles = null;
											
										}
										
										echo '<label class="musician__label input__radio">';
										echo '<input class="musician__default input__choice" data-artist="'.$artist['id'].'" data-musician="'.$musician['id'].'" name="musician_default['.$musician['id'].'" type="radio" value="'.$image['id'].'" '.($image['id'] == $musician['image_id'] ? 'checked' : null).' />';
										echo '<span class="symbol__unchecked" data-role="status"></span>';
										echo '<div class="musician__thumbnail lazy" data-src="'.$thumbnail.'" style="'.$face_styles.'"></div>';
										echo '</label>';
										
									}
									
									// No default
									// We have artist profiles set up to auto-assign image if the artists_musicians entry has null for image_id,
									// and we can't just set it to -1 or some other value that doesn't exist as an image... so just disable it for now and fix later
									/*echo '<label class="musician__label input__radio">';
									echo '<input class="musician__default input__choice" data-artist="'.$artist['id'].'" data-musician="'.$musician['id'].'" name="musician_default['.$musician['id'].'" type="radio" value="" />';
									echo '<span class="symbol__unchecked" data-role="status"></span>';
									echo 'no default';
									echo '</label>';*/
								?>
							
							<?php else: ?>
								<span class="any--weaken symbol__error">This musician is not tagged in any applicable images.</span>
							<?php endif; ?>
							
						</div>
				
					</li>
				
				<?php endforeach; ?>
				
			</ul>
		
		<?php else: ?>
			
			<div class="text text--outlined symbol__error">This artist has no musicians.</div>
		
		<?php endif; ?>
		
	</div>
	
</div>