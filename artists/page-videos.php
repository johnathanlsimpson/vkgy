<?php

// Page setup
include('../artists/head.php');
include_once('../php/function-render_component.php');

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
		<ul class="text video__wrapper">
			<template id="template-video">
				<?php
					ob_start();
					
					?>
						<li class="any--flex video__item">
							<!-- Flagged notice -->
							<div class="video__flag-notice text text--compact text--outlined text--error symbol__error any--flex {approval_notice_class}">
								<?= lang('This video is awaiting approval.', 'この動画は承認待ちです。', 'hidden'); ?>
								<?php
									if($_SESSION['can_approve_data']) {
										?>
											<a class="video__delete a--padded" data-id="{video_id}" href="/artists/function-update_video.php?id={video_id}&amp;method=deny" rel="nofollow">Deny</a>
											<a class="video__approve a--padded a--outlined" href="/artists/function-update_video.php?id={video_id}&amp;artist_id={artist_id}&amp;channel_id={channel_id}&amp;method=approve" rel="nofollow">Approve</a>
										<?php
									}
								?>
							</div>
							
							<!-- Video container -->
							<div class="video__container {video_class}">
								<a class="lazy side__video-link youtube__embed" data-id="{youtube_id}" data-src="https://img.youtube.com/vi/{youtube_id}/mqdefault.jpg" href="https://youtu.be/{youtube_id}" target="_blank"></a>
								<div class="any--weaken-color">
									<a class="video__report symbol__error any--weaken-size a--inherit" href="/artists/function-update_video.php?id={video_id}&amp;method=report" rel="nofollow"><?= lang('Report unofficial video', '非公式の動画を報告して', 'hidden'); ?></a>
									<?php
										if($_SESSION['can_delete_data']) {
											?>
												<button class="video__delete input__radio symbol__trash symbol--standalone any--no-wrap" data-id="{video_id}"></button>
											<?php
										}
									?>
								</div>
							</div>
							
							<!-- Video details -->
							<div class="video__details">
								<a href="https://youtu.be/{youtube_id}" target="_blank">{name}</a>
								<p class="any--weaken-color">{content}</p>
								<div class="video__data data__container">
									<div class="data__item">
										<h5>Views</h5>
										{num_views}
									</div>
									<div class="data__item">
										<h5>Likes</h5>
										{num_likes}
									</div>
									<div class="data__item">
										<h5>Date</h5>
										{date_occurred}
									</div>
									<div class="data__item">
										<h5>Added</h5>
										<a class="user" data-icon="{user_icon}" data-is-vip="{user_is_vip}" href="{user_url}">{username}</a>
									</div>
									<div class="data__item {release_class}">
										<h5>Release</h5>
										<a class="symbol__release" href="{release_url}">{release_name}</a>
									</div>
								</div>
							</div>
						</li>
					<?php
					
					$video_template = ob_get_clean();
					echo preg_replace('/'.'\s+'.'/', ' ', $video_template);
				?>
			</template>
			
			<?php
				if(is_array($artist['videos']) && !empty($artist['videos'])) {
					foreach($artist['videos'] as $video) {
						$replacements = [
							'approval_notice_class' => $video['is_flagged'] ? null : 'any--hidden',
							'admin_class' => $_SESSION['is_moderator'] ? null : 'any--hidden',
							'video_class' => $video['is_flagged'] && !$_SESSION['is_moderator'] ? 'any--hidden' : null,
							'release_class' => $video['release'] ? null : 'any--hidden',
							'video_id' => $video['id'],
							'artist_id' => $artist['id'],
							'channel_id' => $video['data']['channel_id'],
							'youtube_id' => $video['youtube_id'],
							'name' => $video['data']['name'],
							'content' => $video['data']['content'],
							'num_views' => $video['data']['num_views'],
							'num_likes' => $video['data']['num_likes'],
							'date_occurred' => substr($video['data']['date_occurred'], 0, 10),
							'username' => $video['user']['username'],
							'user_icon' => $video['user']['icon'],
							'user_is_vip' => $video['user']['is_vip'],
							'release_url' => '/release/'.$artist['friendly'].'/'.$video['release']['id'].'/'.$video['release']['friendly'].'/',
							'release_name' => lang($video['release']['quick_name'], $video['release']['name'], 'hidden'),
						];
						
						echo render_component($video_template, $replacements);
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