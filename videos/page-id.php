<?php

include_once('../lists/function-render_lists.php');
include_once('../videos/function-render_report.php');

$page_title = 
	$access_video->clean_title($video['youtube_name'], $video['artist']).' MV by '.($video['artist']['romaji'] ?: $video['artist']['name']).
	($video['artist']['romaji'] ? ' | '.$video['artist']['name'] : null);
$page_description =
	'「'.$access_video->clean_title($video['youtube_name'], $video['artist']).'」 music video (MV) by '.($video['artist']['romaji'] ?: $video['artist']['name']).'. '.
	$video['artist']['name'].' ミュージックビデオ、MV、動画';

$page_header = 'Watch video';

subnav([
	'Watch video' => '/videos/'.$video['id'].'/',
]);

script([
	'/lists/script-list.js',
]);

$access_artist = new access_artist($pdo);

?>
<div class="col c4-AAAB">
	
	<!-- Center content -->
	<div>
		
		<a class="video__thumbnail" data-id="<?= $video['youtube_id']; ?>" href="<?= $video['url']; ?>" style="background-image:url(<?= str_replace('mqdefault', 'hqdefault', $video['thumbnail_url']); ?>);"></a>
		
		<?php if($video['is_flagged']): ?>
			<div class="moderation__container text text--outlined text--error symbol__error">
				
				<?php if($video['is_flagged'] == 1): ?>
					This video is from a new user. Please check that it's from an official source before approving.
				<?php else: ?>
					This video is flagged <span class="any__note"><?= $allowed_report_types[ $video['is_flagged'] ]; ?></span>. Please review it and take appropriate action.
				<?php endif; ?>
				
				<br /><br />
				
				<div class="input__row">
					
					<!-- Approve -->
					<div class="input__group">
						<button class="moderation__button input__button symbol__like" data-id="<?= $video['id']; ?>" value="approve">approve video</button>
					</div>
					
					<!-- Approve all -->
					<div class="input__group" style="margin-right:auto;">
						<button class="moderation__button input__button symbol__join" data-id="<?= $video['id']; ?>" value="approve_all">approve user</button>
					</div>
					
					<!-- Delete -->
					<div class="input__group">
						<button class="moderation__button input__button symbol__delete" data-id="<?= $video['id']; ?>" value="delete">delete video</button>
					</div>
					
				</div>
				
			</div>
		<?php endif; ?>
		
		<div class="col c3-AAB">
			
			<div class="video__left">
				
				<!-- Name -->
				<h2 class="video__name">
					<div class="h5"><?= $access_video->video_type_descriptions[ $video['type'] ] ?: $video['type']; ?></div>
					<?= $access_video->clean_title($video['youtube_name'], $video['artist']); ?>
				</h2>
				
				<?php /*if($_SESSION['username'] === 'inartistic'): ?>
				<!-- Like -->
				<button class="video__like input__button symbol__star--empty" type="button">like</button>
				<?php endif;*/ ?>
				
				<div class="any--flex">
					<!-- List -->
					<?= render_lists_dropdown([ 'item_id' => $video['id'], 'item_type' => 'video' ]); ?>
				</div>
				
				<!-- Description -->
				<input class="obscure__input" id="obscure-description" type="checkbox" <?= substr_count($video['youtube_content'], '<br />') > 12 ? 'checked' : null; ?> />
				<div class="video__description any--weaken any--margin obscure__container obscure--height obscure--faint" style="min-height:12rem;">
					<?= $video['youtube_content'] ?: '<em>No description.</em>'; ?>
					<label class="input__button obscure__button" for="obscure-description">full description</label>
				</div>
				
			</div>
			
			<div class="video__right">
				
				<?php if( is_array($next_artist_video) && !empty($next_artist_video) ): ?>
					
					<div class="h5">
						Next video
					</div>
					<a href="<?= '/videos/'.$next_artist_video['id'].'/'; ?>">
						<?= lang($video['artist']['romaji'] ?: $video['artist']['name'], $video['artist']['name'], 'hidden'); ?> - 
						<?= $access_video->clean_title($next_artist_video['youtube_name'], $video['artist']); ?>
					</a>
					
					<a class="video__thumbnail" href="<?= '/videos/'.$next_artist_video['id'].'/'; ?>" style="background-image:url(<?= $next_artist_video['thumbnail_url']; ?>);"></a>
					
				<?php endif; ?>
				
				<div style="margin-top:1.5rem;">
					<?= $access_artist->artist_card($video['artist']); ?>
				</div>
				
			</div>
			
		</div>
		
		<style>
			
		</style>
		
		<div class="text text--outlined">
			<div class="data__container">
				
				<div class="data__item">
					<h5>
						Views
					</h5>
					<?= $video['num_views'] ?: 1; ?>
				</div>
				
				<div class="data__item">
					<h5>
						Length
					</h5>
					<?= $video['length'] > 0 ? substr($video['length'], 3) : '?'; ?>
				</div>
				
				<div class="data__item">
					<h5>
						Published
					</h5>
					<a class="a--inherit" href="<?= '/videos/&date_occurred='.substr($video['date_occurred'], 0, 4); ?>"><?= substr($video['date_occurred'], 0, 4); ?></a><?= substr($video['date_occurred'], 4, 6); ?>
				</div>
				
				<div class="data__item">
					<h5>
						Added
					</h5>
					<?= substr($video['date_added'], 0, 10); ?>
				</div>
				
				<div class="data__item" style="margin-right:auto;">
					<h5>
						Added by
					</h5>
					<a class="user" data-icon="<?= $video['user']['icon']; ?>" data-is-vip="<?= $video['user']['is_vip']; ?>" href="<?= $video['user']['url']; ?>"><?= $video['user']['username']; ?></a>
				</div>
				
				<div class="data__item">
					<h5>
						Moderate
					</h5>
					<?php if( $_SESSION['can_approve_data'] ): ?>
						<button class="moderation__button input__button symbol__delete" data-id="<?= $video['id']; ?>" value="delete">delete video</button>
					<?php endif; ?>
				
					<?php if( $video['is_flagged'] == 0 ): ?>
						<?= render_report_dropdown([ 'item_id' => $video['id'], 'is_flagged' => $video['is_flagged'] ]); ?>
					<?php endif; ?>
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
		
		<h3>
			Other videos by this artist
		</h3>
		<div class="text text--outlined artist-video__container">
			<?php if( is_array($artist_videos) && !empty($artist_videos) ): ?>
				<?php foreach($artist_videos as $artist_video): ?>
					
					<div>
						<a class="video__thumbnail" href="<?= '/videos/'.$artist_video['id'].'/'; ?>" style="background-image:url(<?= $artist_video['thumbnail_url']; ?>);"></a>
						<a href="<?= '/videos/'.$artist_video['id'].'/'; ?>"><?= $access_video->clean_title($artist_video['youtube_name'], $video['artist']); ?></a><br />
					</div>
					
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</div>
	
</div>

<style>
	/*.video__thumbnail {
		background: hsl(var(--background));
		max-height: none;
		padding: 0;
	}
	.video__thumbnail iframe {
		width: 100%;
	}
	.video__bg {
		max-width: 100%;
	}*/
	.video__under {
		display: flex;
		flex-wrap: wrap;
		margin-right: var(--negative-gutter);
		margin-top: var(--gutter);
	}
	.video__details {
		flex-basis: calc(66% - var(--gutter));
		flex-grow: 2;
		flex-shrink: 1;
		margin-right: var(--gutter);
	}
	.video__artist {
		flex-basis: calc(33% - var(--gutter));
		flex-grow: 1;
		flex-shrink: 1;
		margin-right: var(--gutter);
	}
	.video__description {
		margin-top: 3rem;
	}
	
	/*.artist-video__bg {
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
	}*/
	
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