<?php
	ob_start();
	
	?>
		<!-- Template: Option in image selects -->
		<template id="image-option-template">
			<?php
				ob_start();
				
				?>
					<option value="{value}" selected>{name}</option>
				<?php
				
				$option_template = ob_get_clean();
				echo $option_template;
			?>
		</template>
	<?php
	
	$wrapped_option_template = ob_get_clean();
?>