<?php

// Page setup
include('../artists/head.php');

style([
	'../artists/style-page-artist.css'
]);

script([
	'/artists/script-page-artist.js',
]);

// Build in-page nav
$in_page_navs = [
	is_array($artist['musicians'][1]) ? [ 'lineup', 'Lineup', 'メンバー' ] : null,
	is_array($artist['musicians'][2]) ? [ 'former', 'Former', '元メンバー' ] : null,
	is_array($artist['musicians'][3]) ? [ 'staff', 'Staff', 'スタッフ' ] : null,
	$artist['history'] ? [ 'history', 'History', '活動' ] : null,
	$artist['lives'] ? [ 'schedule', 'Lives', 'ライブ' ] : null,
	[ 'comments', 'Comment', 'コメント' ],
];
$in_page_navs = array_filter($in_page_navs);
?>

<div class="col c4-ABBB">
	<!-- In-page nav -->
	<ul class="ul--compact artist__nav any--flex">
		<?php
			foreach($in_page_navs as $in_page_nav) {
				?>
					<a class="li" href="<?= '#'.$in_page_nav[0]; ?>"><?= lang($in_page_nav[1], $in_page_nav[2], 'div'); ?></a>
				<?php
			}
		?>
	</ul>
	
	<!-- Content container -->
	<div class="artist__content any--flex-grow">
		
		<?php
			if($artist_is_removed) {
				?>
					<!-- Error notice -->
					<div class="col c1">
						<div class="text text--outlined text--error symbol__error">
							<?= $artist_is_viewable ? 'This article has been locked, and is only viewable to VIP members. Please use discretion.' : 'Sorry, this article has been removed.'; ?>
						</div>
					</div>
				<?php
			}
		?>
		
		<div class="col c4-AAAB artist__top">
			
			<!-- Content: left -->
			<div class="artist__left">
				
				<!-- Mobile: top stats -->
				<div class="text text--outlined artist__details--first">
					<?php include('partial-stats.php'); ?>
				</div>
				
				<?php
					// If artist viewable, and requested videos, show videos
					if($artist_is_viewable && $_GET['section'] === 'videos') {
						include('partial-videos.php');
					}
					
					// If artist viewable, but default view requested, show everything else
					elseif($artist_is_viewable) {
						
						// Exclusive banner
						if($artist_is_exclusive) {
							?>
								<div class="text text--outlined any__obscure any__obscure--faint" style="background-image: url(/support/patreon-back.png); background-size: cover;">
									<div class="h5 symbol__star--full">vkgy exclusive</div>
									<div>This artist profile features exclusive information discovered by vkgy contributors! <a class="symbol__next" href="/search/artists/?tags[]=exclusive#result">View others</a></div>
								</div>
							<?php
						}
						
						// Stub notice
						if($artist_is_stub) {
							?>
								<div class="text text--outlined symbol__error"><?= lang('This artist doesn\'t have much information yet. Please comment below if you have any information.', 'このアーティストにとって、情報は限られています。追加情報がある場合は、気軽にコメントしてください。', 'hidden'); ?></div>
							<?php
						}
						
						// Non-visual notice
						if($artist_is_non_visual) {
							?>
								<div class="text text--outlined symbol__error"><?= lang('This is artist is non-VK. Information may be truncated or inaccurate, and is provided only to give context to related artists/musicians.', 'このアーティストは非V系です。情報は、切り捨てられたり不正確になったりする場合があります。', 'hidden'); ?></div>
							<?php
						}
						
						// Lineup
						if($artist['musicians']) {
							include('partial-lineup.php');
						}
						
						// History
						if($artist['history']) {
							?>
								<span id="history"></span>
								<input id="history__all" name="history__filter" type="radio" checked hidden />
								<input id="history__activity" name="history__filter" type="radio" hidden />
								<input id="history__release" name="history__filter" type="radio" hidden />
								<input id="history__member" name="history__filter" type="radio" hidden />
								<input id="history__live" name="history__filter" type="radio" hidden />
								<input id="history__other" name="history__filter" type="radio" hidden />
								<div class="history__nav any--flex">
									<h2>
										<?= lang('History', '活動', 'div'); ?>
									</h2>
									<div>
										<label class="input__checkbox-label symbol__unchecked" for="history__all"><?= lang('all', '全て', 'hidden'); ?></label>
										<label class="input__checkbox-label symbol__unchecked" for="history__activity"><?= lang('activity', '活動', 'hidden'); ?></label>
										<label class="input__checkbox-label symbol__unchecked" for="history__release"><?= lang('release', 'リリース', 'hidden'); ?></label>
										<label class="input__checkbox-label symbol__unchecked" for="history__member"><?= lang('member change', 'メンバーチェンジ', 'hidden'); ?></label>
										<label class="input__checkbox-label symbol__unchecked" for="history__live"><?= lang('live', 'イベント', 'hidden'); ?></label>
										<label class="input__checkbox-label symbol__unchecked" for="history__other"><?= lang('other', 'その他', 'hidden'); ?></label>
									</div>
								</div>
								<div class="text bio__container">
									<?php
										foreach($artist['history'] as $y => $history_year) {
											?>
												<ul>
													<h2><?= $y; ?></h2>
													<?php
														foreach($history_year as $history_month) {
															foreach($history_month as $history_day) {
																foreach($history_day as $history_event_num => $history_event) {
																	if(strlen($history_event['content'])) {
																		
																		// For certain types, set font to weaken
																		$event_class = null;
																		foreach(['is_uneditable', 'lineup', 'setlist'] as $weaken_type) {
																			if(in_array($weaken_type, $history_event['type'])) {
																				$event_class = 'any--weaken';
																				
																				if(in_array('is_uneditable', $history_event['type']) && in_array('release', $history_event['type'])) {
																					if(strpos($history_event['content'], '/releases/omnibus/') !== false) {
																						$history_event['content'] = preg_replace('/'.'(releases\/omnibus\/\d+\/[A-z0-9-]*\/)'.'/', '$1&prev_next_artist='.$artist['id'], $history_event['content']);
																					}
																				}
																			}
																		}
																		if(count($history_event['type']) === 1 && $history_event['type'][0] === 'note') {
																			$event_class = 'any--weaken';
																		}
																		
																		?>
																			<li class="bio__item" data-item-num="<?= $history_event_num; ?>" data-item-type="<?= implode(' ', $history_event['type']); ?>">
																				<h4 class="bio__date <?= $history_event_num ? 'bio__date--multiple' : null; ?>"><?= $history_event['date_occurred']; ?></h4>
																				<div class="bio__content <?= $event_class; ?>">
																					<h5 class="bio__title"></h5>
																					<?= $history_event['content']; ?>
																				</div>
																			</li>
																		<?php
																	}
																}
															}
														}
													?>
												</ul>
											<?php
										}
									?>
								</div>
							<?php
						}
						
						// Schedule
						if($artist['lives']) {
							?>
								<span id="schedule"></span>
								<h2><?= lang('Live history', 'ライブ一覧', 'div'); ?></h2>
								<input class="obscure__input" id="obscure-lives" type="checkbox" <?= $num_lives > 3 ? 'checked' : null; ?> />
								<div class="text a obscure__container obscure--height">
									<?php
										foreach($artist['lives'] as $year => $schedule_year) {
											?>
												<ul class="obscure__item ul--compact">
													<?php
														foreach($schedule_year as $month => $schedule_month) {
															foreach($schedule_month as $day => $schedule_day) {
																?>
																	<li class="any--weaken">
																		<span class="h4"><?php
																			echo '<a class="a--inherit" href="/lives/&date_occurred='.$year.'">'.$year.'</a>';
																			echo '-';
																			echo '<a class="a--inherit" href="/lives/&date_occurred='.$year.'-'.$month.'">'.$month.'</a>';
																			echo '-';
																			echo '<a class="a--inherit" href="/lives/&date_occurred='.$year.'-'.$month.'-'.$day.'">'.$day.'</a>';
																		?></span>
																		&nbsp;
																		<?php
																			foreach($schedule_day as $live_key => $live) {
																				echo $live_key ? ', ' : null;
																				echo '<a class="a--inherit" href="/lives/&id='.$live['id'].'">'.lang(($live['area_romaji'] ?: $live['area_name']), $live['area_name'], 'hidden').' '.lang(($live['livehouse_romaji'] ?: $live['livehouse_name']), $live['livehouse_name'], 'hidden').'</a>';
																				echo $_SESSION['is_admin'] ? '<a class="symbol__edit a--inherit" href="/lives/'.$live['id'].'/edit/" style="margin-left:1ch;">Edit</a>' : null;
																			}
																		?>
																	</li>
																<?php
															}
														}
													?>
												</ul>
											<?php
										}
									?>
									<label class="input__button obscure__button" for="obscure-lives">Show all</label>
								</div>
							<?php
						}
					}
				?>
			</div>
			
			<!-- Sidebar -->
			<div class="artist__right"><?php include('partial-sidebar.php'); ?></div>
			
		</div>
		
		<?php include('partial-bottom.php'); ?>
		
	</div>
	
</div>