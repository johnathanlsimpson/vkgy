<?php
	ob_start();
	?>
		<!-- Template: Face for tagging -->
		<template id="image-face-template">
			<?php
				ob_start();
				?>
					<div style="margin-top:1rem;display:inline-block;margin-right:1rem;width:116px;">
						<div style="background-image:url({image_url});background-position:{background_position};background-repeat:no-repeat;background-size:{background_size};display:inline-block;height:{height};width:{width};"></div>
						<select class="input" data-populate-on-click="true" data-source="musicians" data-face='{face_coordinates}' name="image_musician_id[{random_key}]" placeholder="musicians"></select>
					</div>
				<?php
				$face_template = ob_get_clean();
			?>
		</template>
	<?php
	$wrapped_face_template = ob_get_clean();
?>