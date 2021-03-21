<?php

style([
	'/artists/style-partial-sidebar.css',
]);

// Images
// ============================================

// Pull out default image from array of other images
if( !empty($artist['images']) && is_numeric($artist['image_id']) ) {
	
	$artist['image'] = $artist['images'][ $artist['image_id'] ];
	unset($artist['images'][$artist['image_id']]);
	$artist['images'] = array_values($artist['images']);
	
}

// Move any other group photos to front of images array
if( is_array($artist['images']) && !empty($artist['images']) ) {

	$num_artist_images = count($artist['images']);

	for( $i=0; $i<$num_artist_images; $i++ ) {

		// Move group images to front of array
		if( $artist['images'][$i]['image_content'] == 1 ) {

			$tmp_image = $artist['images'][$i];
			unset($artist['images'][$i]);
			array_unshift( $artist['images'], $tmp_image );

		}

	}

}

// Tags
// ============================================
include_once('../php/class-tag.php');

$item_type = 'artist';
$item_id = $artist['id'];

$access_tag = new tag($pdo);
$tags = $access_tag->access_tag([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'all', 'separate' => true ]);

// Loop through tags and set some flags
if( is_array($tags) && !empty($tags) && is_array($tags['tagged']) ) {
	foreach($tags['tagged'] as $tag_type => $tagged_tags) {
		foreach($tagged_tags as $tag) {

			// Set flags
			if($tag['friendly'] === 'exclusive') {
				$artist_is_exclusive = true;
			}
			else if($tag['friendly'] === 'removed') {
				$artist_is_removed = true;
			}
			else if($tag['friendly'] === 'non-visual') {
				$artist_is_non_visual = true;
			}

		}
	}
}


?>

<?php if($_SESSION['username'] === 'inartistic'): ?>

		
			<div class="text text--outlined">
				<?php include('partial-stats.php'); ?>
			</div>
<?php endif; ?>

<div class="artist__images any--flex">
	
	<?php if( $artist['image'] ): ?>
		<a class="artist__image--main" href="<?= $artist['image']['url']; ?>">
			<img src="<?= $artist['image']['medium_url']; ?>" height="<?= $artist['image']['height'].'px'; ?>" width="<?= $artist['image']['width'].'px'; ?>" />
		</a>
	<?php elseif( !$artist['image'] && !$artist['images'] ): ?>
		<div class="artist__image--empty"></div>
	<?php endif; ?>
	
	<?php
		if( is_array($artist['images']) && !empty($artist['images']) ) {
			
			$image_limit = min( ( $artist['image'] ? 2 : 3 ), $num_artist_images );
			
			for( $i=0; $i<$image_limit; $i++ ) {
			
				if( $i + 1 === $image_limit && $num_artist_images > 2 ) {
					?>
						<a class="artist__image" href="<?= '/artists/'.$artist['friendly'].'/images/'; ?>" style="<?= 'background-image:url('.$artist['images'][$i]['thumbnail_url'].');'; ?>">
							<span><?= ( $num_artist_images - 1 ).' other image'.( $num_artist_images > 2 ? 's' : null ); ?></span>
						</a>
					<?php
				}
				else {
					?>
						<a class="artist__image" href="<?= $artist['images'][$i]['url']; ?>" style="<?= 'background-image:url('.$artist['images'][$i]['thumbnail_url'].');'; ?>"></a>
					<?php
				}
				
			}
			
		}
	?>
	
</div>
	
<a class="a--padded a--outlined any--small-margin symbol__plus" href="<?= '/artists/'.$artist['friendly'].'/images/edit/'; ?>">add images</a>

<?php if( $artist['video'] ): ?>
	<h5 style="margin-bottom:0.5rem;">
		<?= lang('Latest MV', '最近のMV', 'hidden'); ?>
	</h5>
	<div class="any--small-margin">
		<a class="lazy video__thumbnail" data-src="<?= 'https://img.youtube.com/vi/'.$artist['video']['youtube_id'].'/hqdefault.jpg'; ?>" href="<?= '/videos/'.$artist['video']['id'].'/'; ?>"></a>
		<a class="a--cutout any--weaken-size" href="<?= '/videos/'.$artist['video']['id'].'/'; ?>"><?= $access_video->clean_title($artist['video']['youtube_name'], $artist['video']['artist']); ?></a>
	</div>
<?php endif; ?>

<?php if( !$hide_sidebar_tags ): ?>
	<div class="artist__tags any--small-margin">
		<?php
			if( is_array($tags) && !empty($tags) ) {
				$item_type = 'artist';
				include('../tags/partial-tags.php');
			}
			else {
				echo '<span class="any--weaken">No tags yet</span>';
			}
		?>
	</div>

	<a class="a--padded a--outlined symbol__plus" href="<?= '/artists/'.$artist['friendly'].'/tags/'; ?>">add tags</a>
<?php endif; ?>