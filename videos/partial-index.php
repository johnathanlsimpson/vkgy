<form action="/videos/function-bulk_edit.php" class="videos__container" enctype="multipart/form-data" method="post" name="form_moderation">
	
	<!-- Pagination (top) -->
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
	
	<?php if($error): ?>
		<div class="text text--outlined text--error symbol__error" style="margin:0;grid-column:1/-1;"><?= $error; ?></div>
	<?php endif; ?>
	
	<!-- Videos -->
	<?php foreach($videos as $video_key => $video): ?>
		<?php if( !$video['is_flagged'] || $_SESSION['can_approve_data'] ): ?>
		<div class="videos__video <?= $video['is_flagged'] ? 'video--flagged' : null; ?>" data-flagged-reason="<?= $video['flagged_reason']; ?>">
			
			<input class="input__choice symbol--orphan videos__checkbox" id="<?= 'ids-'.$video['id']; ?>" name="ids[]" type="checkbox" value="<?= $video['id']; ?>" />
			
			<div class="videos__thumbnail any--weaken module module--youtube">
				<a class="videos__bg" data-id="<?= $video['youtube_id']; ?>" href="<?= '/videos/'.$video['id'].'/'; ?>" style="background-image:url(<?= $video['thumbnail_url']; ?>);"></a>
			</div>
			
			<div class="videos__select symbol--parent moderation--show">
				<label class="input__checkbox symbol--orphan" for="<?= 'ids-'.$video['id']; ?>">
					<span class="symbol__unchecked symbol--standalone" />
				</label>
			</div>
			
			<a class="videos__artist artist any--weaken-size" href="<?= '/artists/'.$video['artist']['friendly'].'/'; ?>"><?= lang($video['artist']['romaji'] ?: $video['artist']['name'], $video['artist']['name'], 'hidden'); ?></a>
			
			<div class="videos__name">
				
				<span class="videos__data any--weaken">
					
					<?= substr($video['date_occurred'], 0, 4) < date('Y') ? substr($video['date_occurred'], 0, 4) : substr($video['date_occurred'], 5, 5); ?>
					
					&middot;
					
					<span class="videos__type"><?= $access_video->video_type_descriptions[ $video['type'] ] ?: $video['type']; ?></span>
					
					<br class="moderation--show" />
					
					<a class="a--inherit user moderation--show" data-icon="<?= $video['user']['icon']; ?>" data-is-vip="<?= $video['user']['is_vip']; ?>" href="<?= $video['user']['url']; ?>"><?= $video['user']['username']; ?></a>
					
				</span>
				
				<a href="<?= '/videos/'.$video['id'].'/'; ?>">
					<?= $video['romaji'] ? lang($video['romaji'], $video['name'], 'parentheses') : ( is_numeric($video['song_id']) ? $video['name'] : $access_video->clean_title($video['name'], $video['artist']) ); ?>
				</a>
				
			</div>
			
		</div>
		<?php endif; ?>
	<?php endforeach; ?>
	
	<!-- Moderation -->
	<div class="text text--docked any--flex moderation--show moderation__controls">
		
		<div class="input__row moderation__row">
			
			<div class="input__group">
				
				<label class="input__label">with selected videos</label>
				
				<?php foreach([ 'approve', 'change_type'/*, 'delete'*/ ] as $action_key => $action_name): ?>
					<label class="input__radio">
						<input class="input__choice" name="action" type="radio" value="<?= $action_name; ?>" <?= $action_key === 0 ? 'checked' : null; ?> />
						<span class="symbol__unchecked"><?= str_replace('_', ' ', $action_name); ?></span>
					</label>
				<?php endforeach; ?>
				
			</div>
			
			<div class="input__group moderation__type any--hidden">
				
				<label class="input__label">Type</label>
				<select class="input selectized" name="type" placeholder="video type">
					<?php foreach($access_video->video_types as $type_name => $type_value): ?>
						<option value="<?= $type_name; ?>"><?= $access_video->video_type_descriptions[$type_name] ?: $type_name; ?></option>
					<?php endforeach; ?>
				</select>
				
			</div>
			
			<div class="input__group">
				<button class="moderation__submit" type="submit">
					Save
				</button>
				<span data-role="status"></span>
			</div>
			
		</div>
		
	</div>
	
	<!-- Pagination (Bottom) -->
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
	
</form>

<style>
	select.input {
		height: 2rem;
		line-height: 2rem;
		padding-bottom: 0;
		padding-top: 0;
	}
	.input__group :last-child {
		margin-right: 0;
	}
	
	.videos__container {
		--num-columns: 3;
		display: grid;
		grid-gap: var(--gutter);
		grid-template-columns: repeat(var(--num-columns), minmax(0, 1fr));
	}
	@media (max-width:699.99px), (min-width:800px) and (max-width:1000px) {
		.videos__container {
			--num-columns: 2;
		}
	}
	.videos__pagination {
		grid-column: 1 / -1;
	}
	
	.videos__video {
		display: flex;
		flex-direction: column;
		word-break: break-word;
	}
	.video--flagged::after {
		/*box-shadow: inset 0 0 0 3px hsl(var(--accent)), inset 0 0 0 4px hsl(var(--background--secondary));
		bottom: 0;
		color: hsl(var(--text--secondary));
		content: "flagged";
		display: block;
		font-family: var(--font--secondary);
		left: 0;
		line-height: 1rem;
		padding-top: 25%;
		pointer-events: none;
		position: absolute;
		right: 0;
		text-align: center;
		text-transform: uppercase;
		top: 0;*/
		background: hsl(var(--accent));
		border-radius: 0 0 3px 0;
		color: hsl(var(--background--secondary));
		content: "flagged " attr(data-flagged-reason);
		font-size: 0.8em;
		line-height: 1.5rem;
		padding: 0 0.25rem;
		position: absolute;
		text-transform: uppercase;
	}
	.video--flagged .videos__thumbnail {
		opacity: 0.75;
	}
	/*.videos__checkbox:checked ~ .videos__select .symbol__unchecked::before {
		-moz-clip-path: url(#symbol__checkbox--checked);
		-webkit-clip-path: url(#symbol__checkbox--checked);
		clip-path: url(#symbol__checkbox--checked);
		color: hsl(var(--text));
	}
	.videos__checkbox:checked + .videos__thumbnail .videos__bg {
		border: 3px solid hsl(var(--interactive));
		box-shadow: inset 0 0 0 1px hsl(var(--background--secondary));
	}*/
	
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
		padding-top: 56%;
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
		margin: 0;
		margin-bottom: auto;
	}
	
	.videos__artist {
		align-self: flex-start;
		background: hsl(var(--background--secondary));
		border-radius: 0 3px 0 0 ;
		display: inline-block;
		line-height: 1.5rem;
		margin: 0;
		max-width: 100%;
		overflow: hidden;
		padding: 0;
		padding-right: 0.5rem;
		text-overflow: ellipsis;
		vertical-align: center;
		white-space: nowrap;
	}
	.moderation__choice:not(:checked) + .videos__row .videos__artist {
		margin-bottom: 0.5rem;
		margin-top: -1.5rem;
	}
	.moderation__choice:checked + .videos__row .videos__name {
	}
	.videos__data {
		background: linear-gradient(to right, hsla(var(--background--secondary),0), hsla(var(--background--secondary),1) 0.5rem);
		float: right;
		line-height: 1.25rem;
		margin: 0;
		padding: 0.25rem 0 0 1rem;
		text-align: right;
	}
	.moderation__choice:checked + .videos__row .videos__data {
		margin-top: -1.5rem;
	}
	
	.videos__select {
		background: hsl(var(--background--secondary));
		border-radius: 3px 0 0 0;
		height: 2.5rem;
		margin: 0;
		margin-bottom: 0.5rem;
		margin-left: auto;
		margin-top: -2.5rem;
		padding: 0.5rem 0 0 0.5rem;
	}
	.videos__select .input__checkbox {
		margin: 0;
	}
	
	.moderation__controls {
		grid-column: 1 / -1;
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