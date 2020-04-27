<?php
	ob_start();
	
	?>
		<!-- Template: Image -->
		<template id="image-template">
			<?php
				ob_start();
				
				?>
					<li class="image__template any--flex" data-get="image_status" data-get-into="data-image-status">
						<!-- Thumbnail -->
						<a class="image__image" data-get="image_url" data-get-into="href" href="{image_url}" style="background-image:url({background_url});" target="_blank">
							<span class="image__status symbol--standalone"></span>
						</a>
						
						<!-- Data -->
						<div class="image__data any--flex-grow">
							
							<!-- IDs -->
							<input data-get="image_id" data-get-into="value" name="image_id" value="{id}" hidden />
							<input name="image_item_type" value="{item_type}" hidden />
							<input name="image_item_id" value="{item_id}" hidden />
							<input name="image_is_queued" value="{is_queued}" hidden />
							
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
							
							<!-- Markdown code -->
							<div class="input__row image__markdown">
								<div class="input__group any--flex-grow">
									<label class="input__label">Markdown code</label>
									<span data-get="image_markdown">{image_markdown}</span>
								</div>
							</div>
							
							<!-- Description, default, delete -->
							<div class="input__row image__details">
								<div class="input__group any--flex-grow">
									<label class="input__label">Description</label>
									<input class="any--flex-grow" data-get="description" data-get-into="value" name="image_description" placeholder="description" value="{description}" />
								</div>
								<div class="input__group">
									
									<label class="input__radio">
										<input class="input__choice" name="image_is_default" type="radio" value="1" {is_default} />
										<span class="symbol__unchecked"><?= 'Default '.$item_type.' image?'; ?></span>
									</label>
									
								</div>
								<div class="input__group {delete_class}">
									<label class="input__radio symbol__trash symbol--standalone image__delete"></label>
								</div>
							</div>
							
							<!-- Artists, musicians, releases -->
							<div class="input__row image__selects">
								<div class="input__group any--flex-grow image__blog">
									<label class="input__label">Blogs</label>
									<select class="input" name="image_blog_id" placeholder="entry id">{blog_id}</select>
								</div>
								<div class="input__group any--flex-grow image__artists">
									<label class="input__label">Artists</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="image_artist_id[]" placeholder="artists" multiple>{artist_ids}</select>
								</div>
								<div class="input__group any--flex-grow image__labels">
									<label class="input__label">Labels</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="labels" name="image_label_id[]" placeholder="labels" multiple>{label_ids}</select>
								</div>
								<div class="input__group any--flex-grow image__musicians">
									<label class="input__label">Musicians</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="musicians" name="image_musician_id[]" placeholder="musicians" multiple>{musician_ids}</select>
								</div>
								<div class="input__group any--flex-grow image__releases">
									<label class="input__label">Releases</label>
									<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="image_release_id[]" placeholder="releases" multiple>{release_ids}</select>
								</div>
							</div>
							
							<!-- Credits -->
							<div class="input__row">
								<div class="input__group">
									<label class="input__label">Credit</label>
									
									<label class="input__radio">
										<input class="input__choice" name="image_is_exclusive[]" type="checkbox" value="1" {is_exclusive} />
										<span class="symbol__checkbox--unchecked">Scanned by&nbsp;</span>
									</label>
									
								</div>
								<div class="input__group any--flex-grow">
									<label class="input__label">Other credit</label>
									<input class="any--flex-grow" name="image_credit" placeholder="http://theirwebsite.com" value="{credit}" />
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