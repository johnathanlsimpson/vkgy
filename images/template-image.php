<?php
	ob_start();
	
	?>
		<!-- Template: Image --> 
		<template id="image-template">
			<?php
				ob_start();
				
				?>
					<li class="image__template any--flex" data-get="image_status" data-get-into="data-image-status" x-data="{ description: '{description}', imageContent: '{checked_image_type}', addFace: false, showDescription: false, showArtists: false, showMusicians: false, showReleases: false, tagStyle: '', extension: '{extension}', imageContent: '{checked_image_type}' }" x-init="$watch('description', () => { triggerChange($refs.description); }); $watch('showMusicians', value => { if(value) { $dispatch('show-faces'); } })">
						
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
									<span class="symbol__unchecked">default</span>
								</label>
							</div>
							
							<div class="input__group" style="margin-top:auto;">
								<button class="input__button symbol__copy" x-on:click.prevent="copyMarkdown($refs.markdown)">copy</button>
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
							<input                                                                        name="image_face_boundaries" value='{face_boundaries}' hidden />
							<input class="image__extension" data-get="image_extension"                                                 value="{extension}" x-model="extension" hidden />
							
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
													<input class="input__choice" name="image_type[{id}]" type="radio" value="<?= $value; ?>" {checked_image_type:<?= $value; ?>} x-on:change="description = getDescription($el); imageContent = $el.querySelector('[name^=image_type]:checked').value; if(imageContent == 1) { showMusicians = true; }" />
													<span class="symbol__unchecked"><?= $key; ?></span>
												</label>
											<?php
										}
									?>
								</div>
							</div>

							<div class="input__row">

								<!-- Credit url -->
								<div class="input__group">

									<label class="input__label">Credit url</label>
									<input class="any--flex-grow" name="image_credit" placeholder="https://url.com" value="{credit}" />

								</div>

								<!-- Watermark -->
								<div class="input__group">

									<label class="input__checkbox">
										<input class="input__choice" name="image_is_exclusive[]" type="checkbox" value="1" {is_exclusive} />
										<span class="symbol__checkbox--unchecked">I scanned this</span>
									</label>

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
									
									<a class="image__tag symbol__plus" data-tag-type="musicians" x-on:click.prevent="showMusicians=true;" x-show="!showMusicians && (imageContent <= 3 || imageContent == 0)">musicians</a>
									<a class="image__tag symbol__plus" data-tag-type="releases" x-on:click.prevent="showReleases=true; tagStyle='order:-1;'" x-show="!showReleases">releases</a>
									<a class="image__tag symbol__plus" data-tag-type="artists" x-on:click.prevent="showArtists=true; tagStyle=''" x-show="!showArtists">artists</a>
									
								</div>
							</div>
							
							<!-- Tag artists -->
							<div class="input__row image__selects" x-show="showArtists || showReleases">
								<div class="input__group any--flex-grow image__artists" x-show="showArtists">
									
									<label class="input__label">Tag artists</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="image_artist_id[]" placeholder="artists" x-on:change="description = getDescription($el)" multiple>{artist_ids}</select>
									
								</div>
							
							<!--<div class="input__group any--flex-grow image__labels">
								<label class="input__label">Labels</label>
								<select class="input" data-populate-on-click="true" data-multiple="true" data-source="labels" name="image_label_id[]" placeholder="labels" multiple>{label_ids}</select>
							</div>-->
							
							<!-- Tag releases -->
								<div class="input__group any--flex-grow image__releases" x-show="showReleases" x-bind:style="tagStyle">
									
									<label class="input__label">Tag releases</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="image_release_id[]" placeholder="releases" multiple>{release_ids}</select>
									
								</div>
							</div>
							
							<!-- Tag musicians by face -->
							<div class="input__row" x-show="showMusicians && (imageContent == 1 || imageContent == 3)" >
								<div class="input__group">
									
									<label class="input__label">Tag musicians by face</label>
									
									<div class="image__faces any--flex">
										
										<span class="face__loading loading any--hidden">Detecting faces...</span>
										
										{tagged_faces}
										
										<a class="face__add a--outlined a--padded any--hidden" x-on:click.prevent="addFace=true"><span class="symbol__plus symbol--standalone"></span></a>
										
									</div>
									
								</div>
							</div>
							
							<!-- Mark additional faces -->
							<div class="input__row" x-show="addFace && (imageContent == 1 || imageContent == 3)">
								<div class="input__group">
									
									<label class="input__label">Mark faces</label>
									
									<div class="input__note symbol__help">
										Click in the center of a member's face to tag him.
									</div>
									
									<div class="add-face__container">
										<img class="add-face__image" src="{image_url}" />
									</div>
									
								</div>
							</div>
							
							<!-- Tag musicians w/out face -->
							<div class="input__row" x-show="(showMusicians && imageContent != 4) || imageContent == 2">
								<div class="input__group any--flex-grow image__musicians">
									
									<label class="input__label">Tag <span x-show="imageContent == 1 || imageContent == 3">other</span> musician<span x-show="imageContent != 2">s</span></label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="musicians" name="image_musician_id[]" placeholder="musicians" multiple>{musician_ids}</select>
									
									<div class="input__note symbol__help any--weaken-color" x-show="imageContent != 1 && imageContent != 3">
										Musicians can be tagged by face in group photos or flyers.
									</div>
									
								</div>
							</div>
							
							<div class="input__row">
							
								<!-- Show description -->
								<div class="input__group" x-show="!showDescription">

									<label class="image__description-label input__label">Description</label>
									<span class="image__description-span" x-text="description">{description}</span>&nbsp;<a class="symbol__edit image__edit" href="#" x-on:click.prevent="showDescription=true;$nextTick(() => { $refs.description.focus(); });">edit</a>

								</div>

								<!-- Edit description -->
								<div class="image__description input__group any--flex-grow" x-show="showDescription">

									<label class="input__label">Description</label>
									<input class="any--flex-grow" data-get="description" data-get-into="value" name="image_description" placeholder="description" value="{description}" x-model="description" x-ref="description" />

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