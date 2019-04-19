<!-- Template: Image -->
<template id="image-template">
	<?php
		ob_start();
		
		?>
			<li class="image__template any--flex" data-get="image_status" data-get-into="data-image-status">
				<!-- Thumbnail -->
				<div class="image__image" data-get="image_style" data-get-into="style" style="background-image:url({background_url});">
					<span class="image__status" data-role="status"></span>
				</div>
				
				<!-- Data -->
				<div class="image__data any--flex-grow">
					
					<!-- IDs -->
					<input data-get="id" data-get-into="value" name="id" value="{id}" hidden />
					<input data-get="item_type" data-get-into="value" name="item_type" value="{item_type}" hidden />
					<input data-get="item_id" data-get-into="value" name="item_id" value="{item_id}" hidden />
					
					<!-- Markdown code -->
					<div class="input__row image__markdown">
						<div class="input__group any--flex-grow">
							<label class="input__label">Markdown code</label>
							<span data-get="image_markdown">{image_markdown}</span>
						</div>
					</div>
					
					<!-- Description, default, delete -->
					<div class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Description</label>
							<input class="any--flex-grow" data-get="description" data-get-into="value" name="description" placeholder="description" value="{description}" />
						</div>
						<div class="input__group">
							<input class="input__checkbox" data-get="is_default_for" data-get-into="id" id="is-default-{id}" name="is_default" type="checkbox" value="1" {is_default} />
							<label class="input__checkbox-label symbol__unchecked" data-get="is_default_for" data-get-into="for" for="is-default-{id}">Default <?php echo $item_type; ?> image?</label>
						</div>
						<div class="input__group">
							<label class="input__checkbox-label symbol__trash symbol--standalone image__delete"></label>
						</div>
					</div>
					
					<!-- Artists, musicians, releases -->
					<div class="input__row image__selects">
						<div class="input__group any--flex-grow image__blog">
							<label class="input__label">Blogs</label>
							<select class="input" name="blog_id" placeholder="entry id">{blog_id}</select>
						</div>
						<div class="input__group any--flex-grow image__artists">
							<label class="input__label">Artists</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="artist_id[]" placeholder="artists" multiple>{artist_ids}</select>
						</div>
						<div class="input__group any--flex-grow image__labels">
							<label class="input__label">Labels</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="labels" name="label_id[]" placeholder="labels" multiple>{label_ids}</select>
						</div>
						<div class="input__group any--flex-grow image__musicians">
							<label class="input__label">Musicians</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="musicians" name="musician_id[]" placeholder="musicians" multiple>{musician_ids}</select>
						</div>
						<div class="input__group any--flex-grow image__releases">
							<label class="input__label">Releases</label>
							<select class="input" data-populate-on-click="true" data-multiple="true" data-source="releases" name="release_id[]" placeholder="releases" multiple>{release_ids}</select>
						</div>
					</div>
					
					<!-- Credits -->
					<div class="input__row">
						<div class="input__group">
							<label class="input__label">Credit</label>
							<input class="input__checkbox" data-get="is_exclusive_for" data-get-into="id" id="is-exclusive-{id}" name="is_exclusive" type="checkbox" value="1" {is_exclusive} />
							<label class="input__checkbox-label symbol__unchecked" data-get="is_exclusive_for" data-get-into="for" for="is-exclusive-{id}">Scanned by </label>
						</div>
						<div class="input__group any--flex-grow">
							<label class="input__label">Other credit</label>
							<input class="any--flex-grow" name="credit" placeholder="http://theirwebsite.com" value="{credit}" />
						</div>
					</div>
					
				</div>
				
				<!-- Result -->
				<div class="image__result text text--notice" data-role="result">{result}</div>
			</li>
		<?php
		
		$image_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $image_template);
	?>
</template>