<?php
	ob_start();
	
	?>
		<!-- Template: Image --> 
		<template id="image-template">
			<?php
				ob_start();
				
				?>
					<li class="image__template any--flex" data-get="image_status" data-get-into="data-image-status" x-data="{ description: '{description}', showDescription: false, showArtists: false, showMusicians: false, showReleases: false }" x-init="$watch('description', () => { triggerChange($refs.description); })">
						
						
						
						<div class="input__row" style="display: flex; flex-direction: column; margin-right: 1rem; align-items:flex-start;">
							
							<!-- Thumbnail -->
							<div class="input__group">
								<a class="image__image" data-get="image_url" data-get-into="href" href="{image_url}" style="background-color:hsl(var(--background--bold));background-image:url({background_url});" target="_blank">
									<span class="image__status symbol--standalone"></span>
								</a>
							</div>
							
							<!-- Default image -->
							<div class="input__group">
								<label class="input__radio">
									<input class="input__choice" name="image_is_default" type="radio" value="1" {is_default} />
									<span class="symbol__unchecked">main image</span>
								</label>
							</div>
							
							<div class="input__group" style="margin-bottom:auto;">
								<button class="input__button symbol__copy" x-on:click.prevent="copyMarkdown($refs.markdown)">copy code</button>
							</div>
							
							<!-- Delete -->
							<div class="input__group {delete_class}" style="">
								<button class="input__button symbol__trash image__delete" style="margin-left:0;">delete</button>
							</div>
							
						</div>
						
						<!-- Data -->
						<div class="image__data any--flex-grow">
							
							<!-- IDs -->
							<input data-get="image_id"      data-get-into="value"                         name="image_id"              value="{id}" hidden />
							<input                                                                        name="image_item_type"       value="{item_type}" hidden />
							<input                                                                        name="image_item_id"         value="{item_id}" hidden />
							<input                                                                        name="image_is_queued"       value="{is_queued}" hidden />
							<input class="any--hidden"      data-get="image_is_new" data-get-into="value" name="image_is_new"          value="0" disabled hidden />
							<input                                                                        name="image_face_boundaries" value='{face_boundaries}' />
							<input class="image__extension" data-get="image_extension"                                                 value="{extension}" hidden />
							
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
							
						<!-- Type -->
						<div class="input__row">
							<div class="input__group">
								
								<label class="input__label">Image type</label>
								
								<?php
									foreach(access_image::$allowed_image_contents as $value => $key) {
										?>
											<label class="input__radio">
												<input class="input__choice" name="image_type[{id}]" type="radio" value="<?= $value; ?>" {checked_image_type:<?= $value; ?>} x-on:change="description = getDescription($el)" />
												<span class="symbol__unchecked"><?= $key; ?></span>
											</label>
										<?php
									}
								?>
								
							</div>
						</div>
						
						<!-- Type note -->
						<!--<div class="input__row">
							<div class="input__note any--weaken">
								<span class="symbol__help"></span>The description and tagging options are based on image type.
							</div>
						</div>-->
						
						<div class="input__row">
							
							<div class="input__group">
								
								<label class="input__label">Credit URL</label>
								<input placeholder="https://website.com" />
								
								<label class="input__checkbox" style="margin-left:0.5rem;margin-top:0.5rem;">
									<input class="input__choice" type="checkbox" />
									<span class="symbol__unchecked">I scanned this</span>
								</label>
								
							</div>
							
						</div>
						
						<div class="input__row">
							<div class="input__group" x-show="!showDescription">
								
								<label class="input__label" style="height:1rem;">Description</label>
								
								<span style="margin-top:1rem;" x-text="description">{description}</span>&nbsp;<a class="symbol__edit" href="#" x-on:click.prevent="showDescription=true;$nextTick(() => { $refs.description.focus(); });">edit</a>
								
							</div>
							

							<!-- Description -->
							<div class="image__description input__group any--flex-grow" x-show="showDescription">
								<label class="input__label">Description</label>
								<input class="any--flex-grow" data-get="description" data-get-into="value" name="image_description" placeholder="description" value="{description}" x-model="description" x-ref="description" />
							</div>
								
						</div>
						<div class="input__row" style="display:none;">
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
								<!-- Credit url -->
								<div class="input__group">
									
									<label class="input__label">Credit url</label>
									<input class="any--flex-grow" name="image_credit" placeholder="http://website.com" value="{credit}" />
									
								</div>
								
						</div>
							
							
							
							<!-- Markdown code -->
							<div class="input__row any--hidden">
								<div class="input__group any--flex-grow">
									<label class="input__label">Markdown code</label>
									<span x-ref="markdown">![<span data-get="description" x-text="description">{description}</span>](<span data-get="image_url">{image_url}</span>)</span>
								</div>
							</div>
							
							<!-- Tagging options -->
							<div class="input__row" x-show="!showMusicians || !showReleases || !showArtists">
								<div class="input__group">
									
									<label class="input__label">Tag photo</label>
									
									<a class="symbol__plus" data-tag-type="musicians" style="line-height:2rem;margin-right:1rem;" x-on:click.prevent="showMusicians=true; $dispatch('show-faces');" x-show="!showMusicians">musicians</a>
									<a class="symbol__plus" data-tag-type="releases" style="line-height:2rem;margin-right:1rem;" x-on:click.prevent="showReleases=true" x-show="!showReleases">releases</a>
									<a class="symbol__plus" data-tag-type="artists" style="line-height:2rem;margin-right:1rem;" x-on:click.prevent="showArtists=true" x-show="!showArtists">artists</a>
									
								</div>
							</div>
							
							
							
							<!-- Description, default, delete -->
							<div class="input__row image__details" style="display:none;">
								
								<!-- Default button -->
								<!--<div class="input__group">
									
									
								</div>-->
								
								<!-- Delete -->
								<!---->
							</div>
							
							
							
							<div class="input__row" style="display:none;">
								
								<div class="input__group">
									
									<label class="input__label">tag sldkfj</label>
									
									<div style="cursor:crosshair;max-height: 300px; overflow-y: auto; margin-top:1rem;">
										
									<img class="xx" src="https://vk.gy/images/5582-ifa-group-shot.png" style="max-width: 100%; height: auto;" />
									
									</div>
								</div>
								
								<div class="input__group">
									<a class="a--outlined symbol__plus" style="display:inline-flex;align-items:center;height:160px;width:100px; text-align:center;flex-wrap:wrap;"><div style="width:100%;">
										
										add face</div></a>
								</div>
								
							</div>
							
							
							
							<!-- Tag artists -->
							<div class="input__row image__selects" x-show="showArtists">
								<div class="input__group any--flex-grow image__artists">
									
									<label class="input__label">Tag artists</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="image_artist_id[]" placeholder="artists" x-on:change="description = getDescription($el)" multiple>{artist_ids}</select>
									
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
							<div class="input__row image__selects" x-show="showReleases">
								<div class="input__group any--flex-grow image__releases">
									
									<label class="input__label">Tag releases</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="image_release_id[]" placeholder="releases" multiple>{release_ids}</select>
									
								</div>
							</div>
							
							
							<!-- Tag musicians -->
							<div class="input__row" x-show="showMusicians">
								<div class="input__group">
									
									<label class="input__label">Tag musicians by face</label>
									
									<div class="image__faces any--flex" style="flex-wrap:wrap;">
										
										<span class="loading" style="align-self:flex-start;">Detecting faces...</span>
										
										<a class="a--outlined a--padded" href="" style="align-items:center;flex-wrap:wrap;display:inline-flex;height:174px;margin-top:1rem;width:116px;text-align:center;justify-content:center;"><span class="symbol__plus symbol--standalone"><br />manually<br /></b>add face</span></a>
										
									</div>
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