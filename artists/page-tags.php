<?php

include_once('../php/include.php');

include_once('../artists/head.php');

$hide_sidebar_tags = true;

$page_title = 'Tag artist';

?>

<div class="col c4-ABBB">
	
	<div>
		<?php include('../artists/partial-sidebar.php'); ?>
	</div>
	
	<div>
			<?php include('../tags/partial-add.php'); ?>
			<?php
				if( is_array($tags) && !empty($tags) ) {
					$item_type = 'artist';
					include('../tags/partial-tags.php');
				}
			?>
		
	</div>
	
</div>