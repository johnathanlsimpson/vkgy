<?php
	ob_start();
	
	?>
		<!-- Template: Upload image -->
		<template id="image-upload-template">
			<?php
				ob_start();
				?>
					<input class="any--hidden obscure__input" id="obscure-images" type="checkbox" />
					
					<!-- Container -->
					<div class="text obscure__container obscure--height" data-hide-selects="{hide_selects}" data-hide-markdown="{hide_markdown}" data-hide-blog="{hide_blog}" data-hide-artists="{hide_artists}" data-hide-labels="{hide_labels}" data-hide-musicians="{hide_musicians}" data-hide-releases="{hide_releases}">
						
						<!-- Upload area -->
						<label class="text h2 image__drop" for="image-upload">
							<?= lang('Drop images here', 'ここに画像をドロップ', 'div'); ?>
						</label>
						
						<div class="image__paste">
							<span class="any--weaken-color">Or paste image here:</span>
							<input placeholder="paste image or URL" name="image_url" />
						</div>
						
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<input class="any--hidden" name="image_item_type" value="{item_type}" hidden disabled />
								<input class="any--hidden" name="image_item_id" value="{item_id}" hidden disabled />
								<input class="any--hidden" name="image_item_name" value="{item_name}" hidden disabled />
								<input class="any--hidden" name="image_is_queued" value="{is_queued}" hidden disabled />
								<input class="any--hidden" name="image_description" value="{description}" hidden disabled />
								<input class="any--flex-grow" accept="image/*" id="image-upload" name="images" type="file" multiple hidden />
							</div>
						</div>
						
						<!-- Images/results area -->
						<ul class="image__results">
							{extant_images}
							
							<li class="image__no-default any--flex">
								<div class="input__row">
									<div class="input__group">
										<label class="input__label">Unset default image</label>
										
										<label class="input__radio">
											<input class="input__choice" name="image_is_default" type="radio" value="0" {no_default}  />
											<span class="symbol__unchecked">No default image</span>
										</label>
									</div>
								</div>
							</li>
						</ul>
						
						<label class="input__button obscure__button" for="obscure-images">Show section</label>
					</div>
				<?php
				
				$image_upload_template = ob_get_clean();
				echo $image_upload_template;
			?>
		</template>
	<?php
	
	$wrapped_image_upload_template = ob_get_clean();
?>