<?php

$page_title = 'Videos';

$page_header = 'Videos';

subnav([
	'Videos' => '/videos/'
]);

script([
	'/scripts/script-pagination.js',
	'/videos/script-index.js',
]);

style([
	'/style/style-pagination.css',
]);

?>

<div class="col c4-ABBB">
	
	<div class="videos__sidebar">
		
	</div>
	
	<div class="videos__wrapper pagination__wrapper">
		<?php include('../videos/partial-index.php'); ?>
	</div>
	
</div>