<?php
	ob_start();
	?>
		<!-- Template: Face for tagging -->
		<template id="image-face-template">
			<?php
				ob_start();
				?>
					<div class="face__container">
						<a class="face__delete symbol__delete" href="#" x-on:click.prevent="removeFace($event.target)"></a>
						<div class="face__image" style="background-image:url({image_url});background-position:{background_position};background-size:{background_size};height:{height};width:{width};"></div>
						<select class="input" data-populate-on-click="true" data-source="musicians{source_attr_suffix}" data-face='{face_coordinates}' name='image_musician_id[{face_coordinates}]' placeholder="musician">
							<option value="{musician_id}" selected>{musician_name}</option>
						</select>
					</div>
				<?php
				$face_template = ob_get_clean();
			?>
		</template>
	<?php
	$wrapped_face_template = ob_get_clean();
?>