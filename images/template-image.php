<!-- Template: Image -->
<template id="image-template">
	<?php
		ob_start();
		
		?>
			<form class="image__template any--flex li">
				<!-- Thumbnail -->
				<div class="image__image" data-get="image_style" data-get-into="style" style="background-image:url({background_url});">
					<span class="image__status" data-role="status"></span>
				</div>
				
				<!-- Data -->
				<div class="image__data any--flex-grow">
					
					<!-- IDs -->
					<input data-get="id" data-get-into="value" name="id" value="{id}" hidden />
					<input name="item_type" value="{item_type}" hidden />
					<input name="item_id" value="{item_id}" hidden />
					
					<!-- Description, default, delete -->
					<div class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Description</label>
							<input class="any--flex-grow" name="description" value="{description}" />
						</div>
						<div class="input__group">
							<input class="input__checkbox" name="is_default" type="checkbox" value="1" {is_default} />
							<label class="input__checkbox-label symbol__unchecked" data-get="default_button_text">Default {item_type} image?</label>
						</div>
						<div class="input__group">
							<label class="input__checkbox-label symbol__trash symbol--standalone image__delete"></label>
						</div>
					</div>
					
					<!-- Artists, musicians, releases -->
					<div class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Artists</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="artist_id[]" multiple><option data-get="artist_id" data-get-into="value"></option>{artist_ids}</select>
						</div>
						<div class="input__group any--flex-grow">
							<label class="input__label">Musicians</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="musicians" name="musician_id[]" multiple><option data-get="musician_id" data-get-into="value"></option>{musician_ids}</select>
						</div>
						<div class="input__group any--flex-grow">
							<label class="input__label">Releases</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="release_id[]" multiple><option data-get="release_id" data-get-into="value"></option>{release_ids}</select>
						</div>
					</div>
					
					<!-- Credits -->
					<div class="input__row">
						<div class="input__group">
							<label class="input__label">Credit</label>
							<input class="input__checkbox" name="is_exclusive" type="checkbox" value="1" {is_exclusive} />
							<label class="input__checkbox-label symbol__unchecked" data-get="scanned_by_text" data-get-into="data-scanned-by-user" data-scanned-by-user="{scanned_by}">Scanned by</label>
						</div>
						<div class="input__group any--flex-grow">
							<label class="input__label">Other credit</label>
							<input class="any--flex-grow" name="credit" placeholder="http://theirwebsite.com" value="{credit}" />
						</div>
					</div>
					
				</div>
				
				<!-- Result -->
				<div class="image__result text text--notice" data-role="result">{result}</div>
			</form>
		<?php
		
		$image_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $image_template);
	?>
</template>