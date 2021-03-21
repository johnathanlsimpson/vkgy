<?php

include_once('../php/include.php');

include_once('../artists/head.php');

$hide_sidebar_tags = true;

$page_title = 'Photos';

$access_image = new access_image($pdo);
$images = $access_image->access_image([ 'artist_id' => $artist['id'], 'get' => 'all' ]);

subnav([
	'Add/edit images' => '/artists/'.$artist['friendly'].'/images/edit/',
], 'interact', true);

?>

<div class="col c1">
	
	<div style="display: grid; grid-template-columns: repeat( auto-fit, minmax(200px, 1fr) );grid-gap: 1rem;grid-template-rows: masonry;">
			
			<?php foreach($images as $image): ?>
				
				<a style="" href="<?= $image['url']; ?>"><img class="lazy" data-src="<?= $image['thumbnail_url']; ?>" height="<?= $image['height'].'px'; ?>" width="<?= $image['width'].'px'; ?>" style="width:100%;height:auto;" /></a>
				
			<?php endforeach; ?>
		
	</div>
	
</div>