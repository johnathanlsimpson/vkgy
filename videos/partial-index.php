<div class="videos__container">

	<div class="videos__pagination pagination">
		<?php
			foreach($pagination as $page) {
				
				// Set classes for pagination links
				$page['classes'] = implode(' ', array_filter([
					($page['is_active']                                            ? 'a--outlined'          : null),
					($page['is_previous']                                          ? 'symbol__previous'     : null),
					($page['is_next']                                              ? 'symbol__next'         : null),
					($page['is_previous'] || $page['is_next']                      ? 'pagination__arrow'    : 'pagination__num'),
					($page['is_active']                                            ? 'pagination--active'   : null),
					($page['is_first']                                             ? 'pagination--first'    : null),
					($page['is_last']                                              ? 'pagination--last'     : null),
					($page['show_ellipsis_before'] || $page['show_ellipsis_after'] ? 'pagination--ellipsis' : null),
					($page['is_disabled']                                          ? 'pagination--disabled' : null),
					'a--padded',
					'pagination__link',
				]));
				
				// Render pagination links
				?>
					<a class="<?= $page['classes']; ?>" href="<?= $page['url']; ?>">
						<?= $page['is_previous'] ? '<span class="any--hidden">Previous page</span>' : null; ?>
						<?= $page['is_next'] ? '<span class="any--hidden">Next page</span>' : null; ?>
						<?= $page['page_num']; ?>
					</a>
				<?php
				
			}
		?>
	</div>
	
	<?php foreach($videos as $video_key => $video): ?>
		<div class="videos__video">
			
			<div class="videos__thumbnail module module--youtube">
				<a class="videos__bg" data-id="<?= $video['youtube_id']; ?>" href="<?= '/videos/'.$video['id'].'/'; ?>" style="background-image:url(<?= $video['thumbnail_url']; ?>);"></a>
			</div>
			
			<span class="any--weaken" style="background:hsl(var(--background--secondary));border-radius:3px 0 0 0;padding:0;margin-top:-1.25rem;align-self:flex-end;max-width:100%;overflow:hidden;">
				<a class="a--inherit videos__artist artist" style="margin:0 0 0 0.5rem;padding:0.25rem 0 0 0;line-height:1rem;vertical-align:middle;" href="<?= '/artists/'.$video['artist']['friendly'].'/'; ?>"><?= lang($video['artist']['romaji'] ?: $video['artist']['name'], $video['artist']['name'], 'hidden'); ?></a>
			</span>
			
			<div class="videos__name any--weaken-color">
				<span class="any__note"><?= $access_video->video_type_descriptions[ $video['type'] ] ?: $video['type']; ?></span>
				<a class="" href="<?= '/videos/'.$video['id'].'/'; ?>">
					<?= $access_video->clean_title($video['youtube_name'], $video['artist']); ?>
					<span class="any--weaken-size">(<?= substr($video['date_occurred'], 0, 4) < date('Y') ? substr($video['date_occurred'], 0, 4) : substr($video['date_occurred'], 5, 5); ?>)</span>
				</a>
			</div>
			
		</div>
	<?php endforeach; ?>

	<div class="videos__pagination pagination">
		<?php
			foreach($pagination as $page) {
				
				// Set classes for pagination links
				$page['classes'] = implode(' ', array_filter([
					($page['is_active']                                            ? 'a--outlined'          : null),
					($page['is_previous']                                          ? 'symbol__previous'     : null),
					($page['is_next']                                              ? 'symbol__next'         : null),
					($page['is_previous'] || $page['is_next']                      ? 'pagination__arrow'    : 'pagination__num'),
					($page['is_active']                                            ? 'pagination--active'   : null),
					($page['is_first']                                             ? 'pagination--first'    : null),
					($page['is_last']                                              ? 'pagination--last'     : null),
					($page['show_ellipsis_before'] || $page['show_ellipsis_after'] ? 'pagination--ellipsis' : null),
					($page['is_disabled']                                          ? 'pagination--disabled' : null),
					'a--padded',
					'pagination__link',
				]));
				
				// Render pagination links
				?>
					<a class="<?= $page['classes']; ?>" href="<?= $page['url']; ?>">
						<?= $page['is_previous'] ? '<span class="any--hidden">Previous page</span>' : null; ?>
						<?= $page['is_next'] ? '<span class="any--hidden">Next page</span>' : null; ?>
						<?= $page['page_num']; ?>
					</a>
				<?php
				
			}
		?>
	</div>
	
</div>

<style>
	
	.videos__container {
		display: grid;
		grid-gap: var(--gutter);
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	.videos__pagination {
		grid-column: 1 / -1;
	}
	
	.videos__video {
		display: flex;
		flex-direction: column;
		word-break: break-word;
	}
	
	.videos__thumbnail {
		margin: 0;
		padding: 0;
	}
	.videos__bg {
		background-position: center;
		background-size: auto 133%;
		cursor: pointer;
		display: block;
		height: auto;
		margin: 0 auto;
		max-width: 640px;
		width: 100%;
	}
	.videos__bg:hover {
		opacity: 0.75;
	}
	.videos__bg::after {
		display: none;
	}
	
	.videos__name {
		line-height: 1.5rem;
		margin: 0.5rem 0 auto 0;
	}
	.videos__artist {
		align-self: flex-start;
		display: inline-block;
		margin: 0.5rem 0 0 0;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
</style>

<?php
function strip_name($name, $artist) {
	
	if( strlen($name) && is_array($artist) && !empty($artist) ) {
		
		$name = str_replace($artist['name'], '', $name);
		
	}
	
	return $name;
	
}
?>