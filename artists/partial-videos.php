<h2><?= lang('Videos', '動画', 'div'); ?></h2>
<ul class="text">
	<div class="text text--compact text--outlined text--notice symbol__help">
		<?= lang('Videos from unofficial sources are not allowed. Please report any unofficial videos.', '非公式の動画は禁止されています。非公式の動画を報告してください。', 'hidden'); ?>
	</div>
	<?php
		
		include('../php/class-access_video.php');
		$access_video = new access_video($pdo);
		$artist['videos'] = $access_video->access_video([ 'artist_id' => $artist['id'], 'get' => 'all' ]);
		
		foreach($artist['videos'] as $video) {
			$video['data'] = $access_video->get_youtube_data($video['youtube_id']);
			
			?>
				<li class="any--flex artist__video" style="flex-wrap: wrap;">
					<?php
						if($video['is_flagged']) {
							?>
								<div class="text text--compact text--outlined text--error symbol__error any--flex" style="align-items:center; margin: 0 0 1rem 0; width: 100%;">
									<?= lang('This video is awaiting approval.', 'この動画は承認待ちです。', 'div'); ?>
									<?php
										if($_SESSION['is_admin']) {
											?>
												<a class="video__delete a--padded" style="margin-left:auto;" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&method=deny'; ?>" rel="nofollow">Deny</a>
												<a class="video__approve a--padded a--outlined" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&method=approve'; ?>" rel="nofollow">Approve</a>
											<?php
										}
									?>
								</div>
							<?php
						}
						if(!$video['is_flagged'] || $_SESSION['is_admin']) {
							?>
								<div style="margin-right: 1rem; width:100%; max-width: 320px;">
									<a class="lazy side__video-link youtube__embed" data-id="<?= $video['youtube_id']; ?>" data-src="https://img.youtube.com/vi/<?= $video['youtube_id']; ?>/mqdefault.jpg" href="https://youtu.be/<?= $video['youtube_id']; ?>" target="_blank"></a>
									<div class="any--weaken-color">
										<a class="symbol__error any--weaken-size a--inherit" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&method=report'; ?>" rel="nofollow"><?= lang('Report unofficial video', '非公式の動画を報告して', 'hidden'); ?></a>
										<?= $_SESSION['is_admin'] ? '<a class="video__delete input__label symbol__trash a--inherit" href="/artists/function-update_video.php?id='.$video['id'].'&method=delete">Delete</a>' : null; ?>
									</div>
								</div>
								
								<div style="flex: 1; max-width: 100%; min-width: 300px;">
									<a href="<?= 'https://youtu.be/'.$video['youtube_id']; ?>" target="_blank"><?= $video['data']['name']; ?></a>
									<p class="any--weaken-color"><?= $video['data']['content']; ?></p>
									<div class="data__container" style="margin-top: 1rem;">
										<div class="data__item">
											<h5>Views</h5>
											<?= $video['data']['num_views']; ?>
										</div>
										<div class="data__item">
											<h5>Likes</h5>
											<?= $video['data']['num_likes']; ?>
										</div>
										<div class="data__item">
											<h5>Date</h5>
											<?= substr($video['data']['date_occurred'], 0, 10); ?>
										</div>
										<div class="data__item">
											<h5>Added</h5>
											<a class="user" href="<?= '/users/'.$video['username'].'/'; ?>"><?= $video['username']; ?></a>
										</div>
										<div class="data__item <?= $video['release'] ? null : 'any--hidden'; ?>">
											<h5>Release</h5>
											<a class="symbol__release" href="<?= '/release/'.$artist['friendly'].'/'.$video['release']['id'].'/'.$video['release']['friendly'].'/'; ?>"><?= lang($video['release']['quick_name'], $video['release']['name'], 'hidden'); ?></a>
										</div>
									</div>
								</div>
							<?php
						}
					?>
				</li>
			<?php
		}
	?>
</ul>

<h3>
	Add videos
</h3>
<div class="text text--outlined">
	<div class="input__row">
		<div class="input__group any--flex-grow">
			<label class="input__label">Video URL</label>
			<input placeholder="https://youtu.be/hc_qINWpLPw" />
		</div>
		<div class="input__group">
			<button>
				Add
			</button>
		</div>
	</div>
	<div class="any--weaken-color symbol__help" style="margin-top: 1rem;">
		<?= lang('Videos from unofficial sources are not allowed. Videos from unknown channels are subject to approval.', '非公式の動画は禁止されています。不明なチャンネルから動画を追加する場合、vkgyスタッフがそれを承認または拒否します。', 'div'); ?>
	</div>
</div>

<style>
	.artist__video + .artist__video {
	}
</style>

<script>
	var youtubeElems = document.querySelectorAll('.youtube__embed');
	
	for(var i=0; i<youtubeElems.length; i++) {
		youtubeElems[i].addEventListener('click', function(event) {
			event.preventDefault();
			
			var height = this.offsetHeight;
			var width = this.offsetWidth;
			var iframe = document.createElement('iframe');
			
			iframe.setAttribute('frameborder', '0' );
			iframe.setAttribute('height', height );
			iframe.setAttribute('width', width );
			iframe.setAttribute('src', 'https://youtube.com/embed/' + this.dataset.id + '?rel=0&showinfo=0&autoplay=0' );
			
			this.innerHTML = '';
			this.parentNode.replaceChild(iframe, this);
		});
	}
</script>