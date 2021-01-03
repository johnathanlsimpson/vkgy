<?php
	ob_start();
	
	?>
		<!-- Template: Image -->
		<template id="image-template">
			<?php
				ob_start();
				
				?>
					<li class="image__template any--flex" data-get="image_status" data-get-into="data-image-status">
						
						<div style="box-shadow:0 0 5px 0 green; display: flex; flex-direction: column; margin-right: 1rem;">
							
							<!-- Thumbnail -->
							<a class="image__image" data-get="image_url" data-get-into="href" href="{image_url}" style="background-image:url({background_url});margin-right:0;" target="_blank">
								<span class="image__status symbol--standalone"></span>
							</a>
							
							<!-- Default image -->
							<label class="input__radio">
								<input class="input__choice" name="image_is_default" type="radio" value="1" {is_default} />
								<span class="symbol__unchecked"><?= 'main'; ?></span>
							</label>
							
							<!-- Delete -->
							<div class="input__group {delete_class}" style="padding-left:0 !important;margin-top:0 !important;">
								<label class="input__radio symbol__trash symbol--standalone image__delete" style="margin-left:0;"></label>
							</div>
							
							<label class="input__checkbox"><span class="symbol__copy">copy code</span></label>
							
						</div>
						
						<!-- Data -->
						<div class="image__data any--flex-grow">
							
							<!-- IDs -->
							<input data-get="image_id" data-get-into="value" name="image_id" value="{id}" hidden />
							<input name="image_item_type" value="{item_type}" hidden />
							<input name="image_item_id" value="{item_id}" hidden />
							<input name="image_is_queued" value="{is_queued}" hidden />
							<input class="any--hidden" data-get="image_is_new" data-get-into="value" name="image_is_new" value="0" disabled hidden />
							<input name="image_face_boundaries" value='{face_boundaries}' />
							
							<!-- Description, default, delete -->
							<div class="input__row image__message">
								<div class="input__group any--flex-grow">
									
									<!-- Result -->
									<div class="image__result text text--error symbol__error">{result}</div>
									
									<!-- Loading -->
									<div class="image__loading">
										Uploading...
									</div>
									
								</div>
							</div>
							
							
						<!-- Left -->
						<div class="input__row" style="box-shadow: 0 0 10px 0 blue; display: flex; align-items: flex-start;">
							
							<!-- Type -->
							<div style="box-shadow: 0 0 5px 0 green; flex-basis: 300px;flex-grow:1;">
								<div class="input__group">
									
									<label class="input__label">Image type</label>
									
									<?php
										foreach([ 'group photo', 'musician', 'flyer', 'logo', 'release', 'other' ] as $value => $key) {
											?>
												<label class="input__radio">
													<input class="input__choice" name="image_type" type="radio" value="<?= $value; ?>" <?= $key === 'group photo' ? 'checked' : null; ?> />
													<span class="symbol__unchecked"><?= $key; ?></span>
												</label>
											<?php
										}
									?>
									
								</div>
								
								<!-- Description -->
								<div class="input__group any--flex-grow">
									<label class="input__label">Description</label>
									<input class="any--flex-grow" data-get="description" data-get-into="value" name="image_description" placeholder="description" value="{description}" />
								</div>
								
							</div>
							
							<div style="box-shadow: 0 0 5px 0 pink; flex-basis:300px;flex-grow:1;">
								<!-- Credit url -->
								<div class="input__group">
									
									<label class="input__label">Credit url</label>
									<input class="any--flex-grow" name="image_credit" placeholder="http://website.com" value="{credit}" />
									
								</div>
								
								<!-- Watermark -->
								<div class="input__group">
									
									<!--<label class="input__label">Credit</label>
									
									<label class="input__radio">
										<input class="input__choice" name="image_is_exclusive[]" type="checkbox" value="1" {is_exclusive} />
										<span class="symbol__checkbox--unchecked">Scanned by&nbsp;</span>
									</label>-->
									
									<label class="input__label">Watermark</label>
									
									<label class="input__radio">
										<input class="input__choice" type="radio" />
										<span class="symbol__unchecked">no watermark</span>
									</label>
									
									<label class="input__radio">
										<input class="input__choice" type="radio" />
										<span class="symbol__unchecked">vkgy username</span>
									</label>
									
								</div>
							</div>
						</div>
							
							
							
							<!-- Markdown code -->
							<!--<div class="input__row image__markdown">
								<div class="input__group any--flex-grow">
									<label class="input__label">Markdown code</label>
									<span data-get="image_markdown">{image_markdown}</span>
								</div>
							</div>-->
							
							<!-- Tagging options -->
							<div class="input__row">
								<div class="input__group">
									
									<label class="input__label">Tag photo</label>
									
									<a class="image__show-tags symbol__plus" data-tag-type="musicians" style="line-height:2rem;margin-right:1rem;">musicians</a>
									<a class="image__show-tags symbol__plus" data-tag-type="releases" style="line-height:2rem;margin-right:1rem;">releases</a>
									<a class="image__show-tags symbol__plus" data-tag-type="artists" style="line-height:2rem;margin-right:1rem;">artists</a>
									
								</div>
							</div>
							
							
							
							<!-- Description, default, delete -->
							<div class="input__row image__details">
								
								<!-- Default button -->
								<!--<div class="input__group">
									
									
								</div>-->
								
								<!-- Delete -->
								<!---->
							</div>
							
							<!-- Tag artists -->
							<div class="input__row image__tags--artists image__selects any--hidden">
								<div class="input__group any--flex-grow image__artists">
									
									<label class="input__label">Tag artists</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="image_artist_id[]" placeholder="artists" multiple>{artist_ids}</select>
									
								</div>
							</div>
							
								<!--<div class="input__group any--flex-grow image__blog">
									<label class="input__label">Blogs</label>
									<select class="input" name="image_blog_id" placeholder="entry id">{blog_id}</select>
								</div>-->
								
								<!---->
								<!--<div class="input__group any--flex-grow image__labels">
									<label class="input__label">Labels</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="labels" name="image_label_id[]" placeholder="labels" multiple>{label_ids}</select>
								</div>-->
								<!--<div class="input__group any--flex-grow image__musicians">
									<label class="input__label">Musicians</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="musicians" name="image_musician_id[]" placeholder="musicians" multiple>{musician_ids}</select>
								</div>-->
							
							<!-- Tag releases -->
							<div class="input__row image__tags--releases image__selects any--hidden">
								<div class="input__group any--flex-grow image__releases">
									
									<label class="input__label">Tag releases</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="image_release_id[]" placeholder="releases" multiple>{release_ids}</select>
									
								</div>
							</div>
							
							
							<!-- Tag musicians -->
							<div class="input__row image__tags--musicians any--hidden">
								<div class="input__group">
									
									<label class="input__label">Tag musicians</label>
									
									<div class="image__musician-tags"></div>
								<?php
									/*include_once('../images/function-detect_faces.php');
									include_once('../images/function-get_face_box.php');
									$image = 'https://vk.gy/images/36627-kizu-group-photo.png';
									
$sample_response = '[{"start_x":63,"start_y":392,"end_x":196,"end_y":560},{"start_x":1810,"start_y":406,"end_x":1937,"end_y":570},{"start_x":523,"start_y":477,"end_x":655,"end_y":611},{"start_x":1107,"start_y":569,"end_x":1253,"end_y":707}]';

$faces = json_decode($sample_response, true);

foreach($faces as $face) {
	
	$face_box = get_face_box([ 'image_url' => $image, 'image_height' => 1333, 'image_width' => 2000, 'face' => $face, 'desired_width' => 116 ]);
	
	$face_css  = 'background-position: -'.$face_box['box_left'].'px -'.$face_box['box_top'].'px;';
	$face_css .= 'background-size: '.$face_box['image_width'].'px '.$face_box['image_height'].'px;';
	$face_css .= 'height: '.$face_box['box_height'].'px;';
	$face_css .= 'width: '.$face_box['box_width'].'px;';
	
	echo '<div style="margin-top:1rem;display:inline-block;margin-right:1rem;width:116px;" ondragover="return false">';
	echo '<div style="background-image:url('.$image.');background-repeat:no-repeat;display:inline-block;'.$face_css.'"></div>';
	?>
		<select class="input" data-populate-on-click="true" data-source="musicians" name="image_musician_id[]" placeholder="musicians"><option>musician</option>{musician_ids}</select>
	<?php
	echo '</div>';
	
}*/
								?>
								</div>
							</div>
							
						</div>
					</li>
				<?php
				
				$image_template = ob_get_clean();
				echo $image_template;
			?>
		</template>
	<?php
	
	$wrapped_image_template = ob_get_clean();
?>