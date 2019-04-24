<!-- Template: Upload image -->
<template id="image-upload-template">
	<?php
		ob_start();
		
		?>
			<input class="any--hidden obscure__input" id="obscure-images" type="checkbox" <!--checked--> />
			
			<!-- Container -->
			<div class="text obscure__container obscure--height" data-hide-selects="{hide_selects}" data-hide-markdown="{hide_markdown}" data-hide-blog="{hide_blog}" data-hide-artists="{hide_artists}" data-hide-labels="{hide_labels}" data-hide-musicians="{hide_musicians}" data-hide-releases="{hide_releases}">
				
				<!-- Upload area -->
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--hidden" name="image_item_type" value="{item_type}" hidden disabled />
						<input class="any--hidden" name="image_item_id" value="{item_id}" hidden disabled />
						<input class="any--hidden" name="image_item_name" value="{item_name}" hidden disabled />
						<input class="any--hidden" name="image_is_queued" value="{is_queued}" hidden disabled />
						<input class="any--hidden" name="image_description" value="{description}" hidden disabled />
						<input class="any--flex-grow" name="images" type="file" multiple />
					</div>
				</div>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<span class="any--weaken symbol__help">If “scanned by” is selected, the image will be watermarked with the user's username; full-res, unwatermarked version can be viewed by original uploader, and by all VIP users.</span>
					</div>
				</div>
				
				<!-- Images/results area -->
				<ul class="image__results">{extant_images}</ul>
				
				<label class="input__button obscure__button" for="obscure-images">Show section</label>
			</div>
		<?php
		
		$image_upload_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $image_upload_template);
	?>
</template>