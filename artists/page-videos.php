<?php

// Page setup
include('../artists/head.php');

script([
	'/scripts/script-initDelete.js',
	'/artists/script-page-videos.js',
]);

style([
	'/artists/style-page-videos.css',
]);

?>

<div class="col c4-AAAB artist__top">
	
	<!-- Content: left -->
	<div class="artist__left">
		
		<!-- Videos -->
		<h2><?= lang('Videos', '動画', 'div'); ?></h2>
		<ul class="text">
			<?php
				if(is_array($artist['videos']) && !empty($artist['videos'])) {
					foreach($artist['videos'] as $video) {
						?>
							<li class="any--flex video__item">
								
								<!-- Flagged notice -->
								<div class="video__flag-notice text text--compact text--outlined text--error symbol__error any--flex <?= $video['is_flagged'] ? null : 'any--hidden'; ?>">
									<?= lang('This video is awaiting approval.', 'この動画は承認待ちです。', 'hidden'); ?>
									<?php
										if($_SESSION['is_admin']) {
											?>
												<a class="video__delete a--padded" data-id="<?= $video['id']; ?>" style="margin-left:auto;" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&method=deny'; ?>" rel="nofollow">Deny</a>
												<a class="video__approve a--padded a--outlined" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&artist_id='.$artist['id'].'&channel_id='.$video['data']['channel_id'].'&method=approve'; ?>" rel="nofollow">Approve</a>
											<?php
										}
									?>
								</div>

								<?php
									// Video
									if(!$video['is_flagged'] || $_SESSION['is_admin']) {
										?>
											<div class="video__container">
												<a class="lazy side__video-link youtube__embed" data-id="<?= $video['youtube_id']; ?>" data-src="https://img.youtube.com/vi/<?= $video['youtube_id']; ?>/mqdefault.jpg" href="https://youtu.be/<?= $video['youtube_id']; ?>" target="_blank"></a>
												<div class="any--weaken-color">
													<a class="video__report symbol__error any--weaken-size a--inherit" href="<?= '/artists/function-update_video.php?id='.$video['id'].'&method=report'; ?>" rel="nofollow"><?= lang('Report unofficial video', '非公式の動画を報告して', 'hidden'); ?></a>
													<?php
														if($_SESSION['is_admin']) {
															?>
																<button class="video__delete input__checkbox-label symbol__trash symbol--standalone any--no-wrap" data-id="<?= $video['id']; ?>"></button>
															<?php
														}
													?>
												</div>
											</div>
											
											<div class="video__details">
												<a href="<?= 'https://youtu.be/'.$video['youtube_id']; ?>" target="_blank"><?= $video['data']['name']; ?></a>
												<p class="any--weaken-color"><?= $video['data']['content']; ?></p>
												<div class="video__data data__container">
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
				}
			?>

			<div class="video__empty-notice symbol__error">
				<?= lang('This artist has no videos yet. Please add official videos below.', '動画はありません。 下記に公式の動画を追加してください。', 'hidden'); ?> 
			</div>
		</ul>
		
		<!-- Add videos -->
		<h3>
			<?= lang('Add official videos', '公式の動画を追加する', 'div'); ?>
		</h3>
		<form action="/artists/function-add_video.php" class="text text--outlined" enctype="multipart/form-data" method="post" name="form__add-video">
			<?php
				if($_SESSION['is_signed_in']) {
					?>
						<input name="artist_id" value="<?= $artist['id']; ?>" hidden />
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">YouTube URL</label>
								<input name="url" placeholder="https://youtu.be/hc_qINWpLPw" />
							</div>
							<div class="input__group">
								<button type="submit">
									<?= lang('Add', '追加する', 'hidden'); ?>
								</button>
								<span data-role="status"></span>
							</div>
						</div>
						
						<div class="video__add-result text text--compact text--outlined text--notice"></div>
						
						<div class="video__add-notice any--weaken-color symbol__help">
							<?= lang('Videos from unofficial sources are not allowed. Videos from unknown channels are subject to approval.', '非公式の動画は禁止されています。不明なチャンネルから動画を追加する場合、vkgyスタッフがそれを承認または拒否します。', 'hidden'); ?>
						</div>
					<?php
				}
				else {
					?>
						<span class="symbol__error" style="float: left;"></span>
						<?= lang('Please <a href="/account/">sign in or register</a> to add videos.', '動画を追加するには、<a href="/account/">サインインするか登録して</a>ください。', 'hidden'); ?>
					<?php
				}
			?>
		</form>
	</div>
	
	<!-- Sidebar -->
	<div class="artist__right"><?php include('partial-sidebar.php'); ?></div>
</div>

<div class="col c1">
	<?php include('partial-bottom.php'); ?>
</div>