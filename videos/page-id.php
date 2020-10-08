<?php

$page_title =  $access_video->clean_title($video['youtube_name'], $video['artist']);

$page_header = 'Watch video';

subnav([
	'Watch video' => '/videos/'.$video['id'].'/',
]);

script([
	'/releases/script-list.js',
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
					<div class="h5"><?= $access_video->video_type_descriptions[ $video['type'] ] ?: $video['type']; ?></div>
					<?= $access_video->clean_title($video['youtube_name'], $video['artist']); ?>
				</h2>
				
				<div class="any--weaken any--margin">
					<?= $video['youtube_content']; ?>
				</div>
				
			</div>
			
			<div class="video__artist">
				
				<?php if($_SESSION['username'] === 'inartistic'): ?>
				<label class="input__dropdown video__artist" style="margin-bottom: 1rem; width: calc(100% - 1rem);">
					
					<input class="input__choice" type="checkbox" />
					<span class="symbol__down-caret symbol--right">add to list</span>
					
					<ul class="input__dropdown-list list__menu">
						
						<?php
							$sql_lists = 'SELECT lists.*, IF(lists_items.id, 1, 0) AS is_listed FROM lists LEFT JOIN lists_items ON lists_items.list_id=lists.id AND lists_items.item_id=? AND lists_items.item_type=? WHERE lists.user_id=?';
							$stmt_lists = $pdo->prepare($sql_lists);
							$stmt_lists->execute([ $video['id'], 1, $_SESSION['user_id'] ]);
							$lists = $stmt_lists->fetchAll();
							
							if( is_array($lists) && !empty($lists) ) {
								foreach($lists as $list) {
									?>
										<li class="input__dropdown-item list__item">
											<label class="input__checkbox list__button" data-list-id="<?= $list['id']; ?>" data-item-id="<?= $video['id']; ?>" data-item-type="video" for="<?= 'list-'.$list['id']; ?>">
												<input class="input__choice" id="<?= 'list-'.$list['id']; ?>" type="checkbox" <?= $list['is_listed'] ? 'checked' : null; ?> />
												<span class="symbol__unchecked" data-role="status"><?= $list['name']; ?></span>
											</label>
										</li>
									<?php
								}
							}
						?>
						
					</ul>
					
				</label>
				<?php endif; ?>
				
				<div class="video__artist" style="margin-right: 0;">
					<?= $access_artist->artist_card($video['artist']); ?>
				</div>
				
			</div>
			
		</div>
		
		<style>
			.input__dropdown {
				box-shadow: inset 0 0 0 1px hsl(var(--background--bold));
				color: hsl(var(--text--secondary));
				cursor: pointer;
				display: inline-flex;
				font-family: var(--font--secondary);
				max-width: 100%;
				user-select: none;
			}
			.input__dropdown > .symbol--right::before {
				margin-left: 0.5rem;
				transform: translateY(-1px);
			}
			.input__dropdown > .input__choice:checked + .symbol--right {
				color: hsl(var(--text));
			}
			.input__dropdown > .input__choice:checked + .symbol--right:hover::before {
				-moz-clip-path: url(#symbol__up-caret);
				-webkit-clip-path: url(#symbol__up-caret);
				clip-path: url(#symbol__up-caret);
			}
			.input__dropdown-list {
				background: hsl(var(--background));
				border-radius: 0 0 3px 3px;
				display: none;
				left: 0;
				min-width: 100%;
				position: absolute;
				right: 0;
				top: 2rem;
				white-space: nowrap;
				z-index: 1;
			}
			.input__dropdown > .input__choice:checked ~ .input__dropdown-list {
				display: block;
			}
			.input__dropdown-item {
				border: none;
				padding: 0.5rem 0 0 0;
			}
			
			.list__menu {
				padding: 0 0.5rem 0.5rem 0.5rem;
			}
			.list__item {
				padding: 0.5rem 0 0 0 !important;
			}
			.list__button {
				display: block;
				margin: 0 !important;
				z-index: 2;
			}
		</style>
		
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
					<?= $video['num_views'] ?: 1; ?>
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
		
		<h3>
			Other videos by this artist
		</h3>
		<div class="text text--outlined artist-video__container">
			<?php if( is_array($artist_videos) && !empty($artist_videos) ): ?>
				<?php foreach($artist_videos as $artist_video): ?>
					
					<div>
						<div class="video__thumbnail any--margin module module--youtube" style="margin-bottom:0.5rem;">
							<a class="artist-video__bg" href="<?= '/videos/'.$artist_video['id'].'/'; ?>" style="background-image:url(<?= $artist_video['thumbnail_url']; ?>);"></a>
						</div>
						
						<a href="<?= '/videos/'.$artist_video['id'].'/'; ?>""><?= $access_video->clean_title($artist_video['youtube_name'], $video['artist']); ?></a><br />
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