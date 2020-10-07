<?php

$page_title =  $access_video->clean_title($video['youtube_name'], $video['artist']);

$page_header = 'Watch video';

subnav([
	'Watch video' => '/videos/'.$video['id'].'/',
]);

$access_artist = new access_artist($pdo);

// Log view
include('../php/class-views.php');
$views = new views($pdo);
$views->add('video', $video['id']);

?>

<div class="col c4-AAAB">
	
	<!-- Left sidebar -->
	<!--<div></div>-->
	
	<!-- Center content -->
	<div>
		
		<div class="video__thumbnail any--margin module module--youtube">
			<a class="video__bg youtube__embed" data-id="<?= $video['youtube_id']; ?>" href="<?= $video['url']; ?>" style="background-image:url(<?= $video['thumbnail_url']; ?>);"></a>
		</div>
		
		<div class="video__under">
			
			<div class="video__details">
				
				<h2>
					<?= $access_video->clean_title($video['youtube_name'], $video['artist']); ?>
					<span class="any__note any--weaken"><?= $access_video->video_type_descriptions[ $video['type'] ] ?: $video['type']; ?></span>
				</h2>
				
				<div class="any--weaken any--margin">
					<?= $video['youtube_content']; ?>
				</div>
				
			</div>
			
			<div class="video__artist">
				<?= $access_artist->artist_card($video['artist']); ?>
			</div>
			
		</div>
		
		<?php if($video['is_flagged']): ?>
			<div class="text text--outlined text--error symbol__error">
				<?= lang('This video is awaiting approval.', 'この動画は承認待ちです。', 'hidden'); ?>
			</div>
		<?php endif; ?>
		
		<div class="text text--outlined">
			<div class="data__container">
				
				<div class="data__item">
					<h5>
						Views
					</h5>
					<?= $video['num_views']; ?>
				</div>
				
				<div class="data__item">
					<h5>
						Length
					</h5>
					<?= substr($video['length'], 3); ?>
				</div>
				
				<div class="data__item">
					<h5>
						Published
					</h5>
					<?= substr($video['date_occurred'], 0, 10); ?>
				</div>
				
				<div class="data__item">
					<h5>
						Added
					</h5>
					<?= substr($video['date_added'], 0, 10); ?>
				</div>
				
				<div class="data__item">
					<h5>
						Added by
					</h5>
					<a class="user" data-icon="<?= $video['user']['icon']; ?>" data-is-vip="<?= $video['user']['is_vip']; ?>" href="<?= $video['user']['url']; ?>"><?= $video['user']['username']; ?></a>
				</div>
				
			</div>
		</div>
		
		<!-- Comments -->
		<?php
			include('../comments/partial-comments.php');
			render_default_comment_section('video', $video['id'], $video['comments'], $markdown_parser);
		?>
		
	</div>
	
	<!-- Right sidebar -->
	<div>
		
		<div class="text text--outlined artist-video__container">
			<?php if( is_array($artist_videos) && !empty($artist_videos) ): ?>
				<?php foreach($artist_videos as $artist_video): ?>
					
					<div>
						<div class="video__thumbnail any--margin module module--youtube">
							<a class="artist-video__bg" href="<?= '/videos/'.$artist_video['id'].'/'; ?>" style="background-image:url(<?= $artist_video['thumbnail_url']; ?>);"></a>
						</div>
						
						<a class="artist" href="<?= $artist_video['artist']['url']; ?>"><?= lang( $video['artist']['romaji'] ?: $video['artist']['name'], $video['artist']['name'], 'hidden' ); ?></a><br />
						<span class="any--weaken-color"><?= $artist_video['youtube_name']; ?></span>
					</div>
					
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</div>
	
</div>

<style>
	.video__thumbnail {
		background: hsl(var(--background));
		max-height: none;
		padding: 0;
	}
	.video__thumbnail iframe {
		width: 100%;
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
		grid-template-columns: repeat(1, minmax(0, 1fr));
	}
	@media(min-width:500px) and (max-width:800px) {
		.artist-video__container {
			grid-gap: 1rem;
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	@media(min-width:1500px) {
		.artist-video__container {
			grid-gap: 1rem;
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	.artist-video__container .video__thumbnail {
		margin: 0 0 1rem 0;
	}
</style>