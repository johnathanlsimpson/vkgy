<?php

include_once('../php/include.php');

// Pattern for allowed background images
$image_pattern = '^\/images\/\d+\.[a-z]+$';

// Make sure ID is numeric
$id = $_GET['id'];
$id = is_numeric($id) ? $id : null;

// Make sure image matches pattern
$image = urldecode( $_GET['image'] );
$image = preg_match('/'.$image_pattern.'/', $image) ? $image : null;

// Set other vars
$title = sanitize( urldecode( $_GET['title'] ) );
$supertitle = $_GET['is_feature'] ? 'feature' : 'news';

?>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="/scripts/external/script-jquery-3.2.1.js"></script>
<script src="/scripts/script-inlineSubmit.js"></script>

<style>
	<?php
		// Since this page will be viewed individually, need to manually include styles
		include('../style/style-colors-0.css');
		include('../style/style-critical.css');
		include('../style/style-shared.css');
		include('../blog/style-page-entry.css');
		include('../blog/style-page-card.css');
	?>
</style>

<div class="card callout" id="card" style="<?= $image ? 'background-image:url('.$image.');' : null; ?>">
	
	<div class="card__content callout__text">
		
		<div class="card__supertitle"><?= $supertitle; ?></div>
		
		<div class="card__title"><?= $title ?: 'Missing Entry'; ?></div>
		
	</div>
	
	<img class="card__logo" src="/style/vkgy-wordmark.svg" />
	
</div>

<script type="text/javascript">
	let cardElem = document.querySelector('#card');
	
	html2canvas(cardElem).then(function(canvas) {
		
		// Image to base64
		let blogID = <?= $id; ?>;
		let imageData = canvas.toDataURL('image/webp', 1);
		
		// Save image
		initializeInlineSubmit( $(cardElem), '/blog/function-save_card.php', {
			preparedFormData: {
				'image': imageData,
				'id': blogID,
			},
			callbackOnSuccess: function(formElem, returnedData) {
				//console.log('card image saved');
				//console.log(returnedData);
			},
			callbackOnError: function(formElem, returnedData) {
				//console.log('card image not saved');
				//console.log(returnedData);
			}
		});
		
	});
</script>