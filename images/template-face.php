<?php
	ob_start();
	?>
		<!-- Template: Face for tagging -->
		<template id="image-face-template">
			<?php
				ob_start();
				?>
					<div class="face__container">
						<a class="face__close symbol__trash" href="#"></a>
						<div class="face__image" style="background-image:url({image_url});background-position:{background_position};background-size:{background_size};height:{height};width:{width};"></div>
						<select class="input" data-populate-on-click="true" data-source="musicians" data-face='{face_coordinates}' name="image_musician_id[{random_key}]" placeholder="musicians"></select>
					</div>
				<?php
				$face_template = ob_get_clean();
			?>
		</template>
	<?php
	$wrapped_face_template = ob_get_clean();
?>