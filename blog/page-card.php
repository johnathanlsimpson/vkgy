<?php

include_once('../php/include.php');

// Pattern for allowed background images
$image_pattern = '^\/images\/\d+\.[a-z]+$';

// Make sure ID is numeric
$blog_id = $_GET['blog_id'];
$blog_id = is_numeric($blog_id) ? $blog_id : null;

// Make sure image matches pattern
$image_url = urldecode( $_GET['image_url'] );
$image_url = preg_match('/'.$image_pattern.'/', $image_url) ? $image_url : null;

// Set other vars
$title = sanitize( urldecode( $_GET['title'] ) );
$supertitle = $_GET['is_feature'] ? 'feature' : 'news';

?>

<input name="blog_id" type="hidden" value="<?= $blog_id; ?>" />

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.7/dist/html2canvas.min.js"></script>
<script src="/scripts/external/script-jquery-3.2.1.js"></script>
<script src="/scripts/script-inlineSubmit.js"></script>

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">

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

<div class="card callout" id="card" style="<?= $image_url ? 'background-image:url('.$image_url.');' : null; ?>">
	
	<div class="card__content callout__text">
		
		<div class="card__supertitle"><?= $supertitle; ?></div>
		
		<div class="card__title"><?= $title ?: 'Missing Entry'; ?></div>
		
	</div>
	
	<img class="card__logo" src="/style/vkgy-wordmark.svg" />
	
</div>

<script src="/blog/script-page-card.js"></script>