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
			
			
			<div style="  display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    grid-gap: 1rem;
    grid-auto-rows: minmax(200px, auto);
    grid-auto-flow: dense;
    padding: 10px;">
				
	
			<?php foreach($images as $image): ?>
				
				<?php
				$ratio = $image['height'] && $image['width'] ? $image['height'] / $image['width'] : 1;
				
				if( $ratio > 1) {
					$x = 'grid-row: span 2;';
				}
				if( $ratio > 2) {
					$x = 'grid-row: span 3;';
				}
				if( $ratio < 1 && $ratio > 0.6) {
					$x = 'grid-row: span 2; grid-column: span 2;';
				}
				if( $ratio < 0.6 ) {
					$x = 'grid-column: span 3;';
				}
				
				?>
				
				<a class="lazy" style="<?= $x; ?>background-size:cover;background-color:hsl(var(--background));background-position:center;" data-src="<?= $image['small_url']; ?>" href="<?= $image['url']; ?>" height="<?= $image['height'].'px'; ?>" width="<?= $image['width'].'px'; ?>"></a>
				
			<?php endforeach; ?>
	</div>
	
</div>