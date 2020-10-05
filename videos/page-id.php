<?php

$access_artist = new access_artist($pdo);

?>

<div class="col c4-ABBC">
	
	<!-- Left sidebar -->
	<div></div>
	
	<!-- Center content -->
	<div>
		
		<div class="video__thumbnail any--margin module module--youtube">
			<a class="video__bg youtube__embed" data-id="<?= $video['youtube_id']; ?>" href="<?= $video['url']; ?>" style="background-image:url(<?= $video['thumbnail_url']; ?>);"></a>
		</div>
		
		<div class="video__under">
			
			<div class="video__details">
				
				<h2>
					<?= $video['youtube_name']; ?>
					<span class="any__note any--weaken"><?= $video['type']; ?></span>
				</h2>
				
				<div class="any--weaken any--margin">
					<?= $video['youtube_content']; ?>
				</div>
				
			</div>
			
			<div class="video__artist">
				<?= $access_artist->artist_card($video['artist']); ?>
			</div>
			
		</div>
		
		<h2>
			Other videos by artist
		</h2>
		
		<div class="text text--outlined artist-video__container">
			<?php if( is_array($artist_videos) && !empty($artist_videos) ): ?>
				<?php foreach($artist_videos as $artist_video): ?>
					
					<div>
						<div class="video__thumbnail any--margin module module--youtube">
							<a class="artist-video__bg" href="<?= '/videos/'.$artist_video['id'].'/'; ?>" style="background-image:url(<?= $artist_video['thumbnail_url']; ?>);"></a>
						</div>
						
						<span class="any--weaken-color"><?= $artist_video['youtube_name']; ?></span>
					</div>
					
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</div>
	
	<!-- Right sidebar -->
	<div>
	</div>
	
</div>

<style>
	.video__thumbnail {
		background: hsl(var(--background));
		border-radius: 3px;
		padding: 0;
	}
	.video__bg {
		max-width: 100%;
	}
	.video__under {
		display: flex;
		flex-wrap: wrap;
		margin-right: var(--negative-gutter);
		margin-top: var(--gutter);
	}
	.video__details {
		flex-grow: 2;
		margin-right: var(--gutter);
	}
	.video__artist {
		flex-basis: 250px;
		flex-grow: 1;
		margin-right: var(--gutter);
	}
	
	.artist-video__bg {
		background-position: center;
		background-size: auto 133%;
		cursor: pointer;
		display: block;
		height: auto;
		margin: 0 auto;
		max-width: 640px;
		width: 100%;
	}
	.artist-video__bg:hover {
		opacity: 0.75;
	}
	.artist-video__bg::after {
		display: none;
	}
	
	.artist-video__container {
		display: grid;
		grid-gap: var(--gutter);
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	.artist-video__container .video__thumbnail {
		margin: 0 0 1rem 0;
	}
</style>