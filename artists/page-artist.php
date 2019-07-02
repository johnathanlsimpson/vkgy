<?php

// Page setup
include('../artists/head.php');

style([
	'../artists/style-page-artist.css'
]);

script([
	'/scripts/script-pronounce.js',
	'/scripts/script-initDelete.js',
	'/scripts/script-lazyLoadYouTube.js',
	'/artists/script-page-artist.js',
]);

// Back/forward navigation
if(is_array($rslt_next) && !empty($rslt_next)) {
	if(count($rslt_next) === 2) {
		$rslt_next[] = [ 'romaji' => $artist['romaji'], 'name' => $artist['name'], 'type' => $rslt_next[0]['type'] === 'previous' ? 'next' : 'previous' ];
	}
	foreach($rslt_next as $directional_artist) {
		subnav([
			[
				'text' => lang( (  $directional_artist['romaji'] ?:   $directional_artist['name']),   $directional_artist['name'], 'hidden' ),
				'url' => strlen(  $directional_artist['friendly']) ? '/artists/'.  $directional_artist['friendly'].'/' : null,
				'position' =>   $directional_artist['type'] === 'rand' ? 'center' : (  $directional_artist['type'] === 'previous' ? 'left' : 'right'),
			]
		], 'directional');
	}
}

// Pull out default image from images array
if(!empty($artist['images']) && is_numeric($artist['image_id'])) {
	$artist['image'] = $artist['images'][$artist['image_id']];
	unset($artist['images'][$artist['image_id']]);
	$artist['images'] = array_values($artist['images']);
}

// Remove empty arrays
foreach(['musicians', 'history', 'lives', 'images', 'videos', 'labels', 'official_links', 'edit_history'] as $key) {
	if(is_array($artist[$key]) && !empty($artist[$key])) {
	}
	else {
		unset($artist[$key]);
	}
}

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

// Set up permissions
$artist_is_removed;
$artist_is_stub = $artist['musicians'] || $artist['history'] ? false : true;
$artist_is_viewable = $artist_is_removed && $_SESSION['is_vip'] || !$artist_is_removed ? true : false;
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
								<div class="text text--outlined symbol__error">
									This artist doesn't have much information yet. Please comment below if you have any information.
								</div>
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
								<h2><?= lang('History', '活動', 'div'); ?></h2>
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
				<style>
					.artist__question {
						color: hsl(var(--accent));
					}
				</style>
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
																			foreach($schedule_day as $live) {
																				$area = '<a class="a--inherit" href="/lives/&area_id='.$live['area_id'].'">'.lang(($live['area_romaji'] ?: $live['area_name']), $live['area_name'], 'hidden').'</a>';
																				$livehouse = '<a class="a--inherit" href="/lives/&livehouse_id='.$live['livehouse_id'].'">'.lang(($live['livehouse_romaji'] ?: $live['livehouse_name']), $live['livehouse_name'], 'hidden').'</a>';
																				echo $area.' '.$livehouse;
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
			<div class="artist__right">
				
				<!-- Stats -->
				<div class="text text--outlined artist__details--second">
					<?php include('partial-stats.php'); ?>
				</div>
				
				<?php
					if($artist_is_viewable) {
						
						// Sidebar: videos (unless viewing all videos)
						if($artist['video'] && $_GET['section'] != 'videos') {
							?>
								<div class="text side__video-container">
									<a class="lazy side__video-link youtube__embed" data-id="<?= $artist['video'][0]['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$artist['video'][0]['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$artist['video'][0]['youtube_id']; ?>" target="_blank"></a>
									<a class="symbol__previous side__videos-link" href="<?= '/artists/'.$artist['friendly'].'/videos/'; ?>"><?= lang('More videos', 'その他', 'hidden'); ?></a>
									<span class="symbol__error any--weaken side__video-notice"><?= lang('Please <a class="a--inherit" href="/artists/'.$artist['friendly'].'/videos/">report</a> unofficial videos.', '非公式の動画を<a class="a--inherit" href="/artists/'.$artist['friendly'].'/videos/">報告して</a>ください。', 'hidden'); ?></span>
								</div>
							<?
						}
						
						// Sidebar: images
						if($artist["images"]) {
								?>
									<div>
										<h3><?= lang('Images', '画像', 'div'); ?></h3>
										<input class="obscure__input" id="obscure-images" type="checkbox" <?= count($artist['images']) > 4 ? 'checked' : null; ?> >
										<div class="text text--outlined obscure__container obscure--faint">
											<ul class="ul--inline any--flex images__container">
												<?php
													foreach($artist["images"] as $image) {
														?>
															<li class="images__item obscure__item">
																<a class="images__link" href="<?= $image["url"]; ?>" style="background-image: url(<?= strtolower(str_replace(".", ".thumbnail.", $image["url"])); ?>);" target="_blank"></a>
															</li>
														<?php
													}
												?>
											</ul>
											<label class="input__button obscure__button" for="obscure-images">Show all</label>
										</div>
									</div>
								<?php
						}
						
						// Label history
						if($artist["labels"]) {
							?>
								<h3><?= lang('Label history', '所属レーベル', 'div'); ?></h3>
								<div class="label__container any--margin">
									<?php
										foreach($artist["labels"] as $period_key => $period) {
											?>
												<div class="label__period">
													<?php
														foreach($period as $organization_key => $organization) {
															?>
																<div class="label__organization">
																	<?php
																		foreach($organization as $company_key => $company) {
																			?>
																				<div class="label__company any--weaken-size">
																					<?php
																						if(!empty($company["friendly"])) {
																							?>
																								<a class="symbol__company" href="/labels/<?php echo $company["friendly"]; ?>/">
																									<?php echo $company["quick_name"]; ?>
																								</a>
																							<?php
																							echo $company["romaji"] ? " (".$company["name"].")" : null;
																						}
																						else {
																							echo $company["quick_name"].($company["romaji"] ? " (".$company["name"].")" : null);
																						}
																						if(is_array($company["notes"])) {
																							foreach($company["notes"] as $note) {
																								?>
																									<span class="any__note"><?php echo $note; ?></span>
																								<?php
																							}
																						}
																					?>
																				</div>
																			<?php
																			if($company_key + 1 < count($organization)) {
																				?>
																					<div class="label__company label__line"><span class="symbol__line"></span></div>
																				<?php
																			}
																		}
																	?>
																</div>
															<?php
															if($organization_key + 1 < count($period)) {
																?>
																	<span class="label__comma any--weaken-color"> &amp; </span>
																<?php
															}
														}
													?>
												</div>
											<?php
											if($period_key + 1 < count($artist["labels"])) {
												?>
													<span class="symbol__down-caret label__next"></span>
												<?php
											}
										}
									?>
								</div>
							<?php
						}
						
						// Links
						if($artist["official_links"]) {
							?>
								<h3><?= lang('Links', 'リンク', 'div'); ?></h3>
								<input class="obscure__input" id="obscure-links" type="checkbox" <?= count($artist['official_links']) > 4 ? 'checked' : null; ?> >
								<div class="any--weaken text text--outlined obscure__container obscure--faint">
									<ul>
										<?php
											foreach($artist["official_links"] as $link) {
												?>
													<li class="obscure__item">
														<a href="<?= $link['url']; ?>" target="_blank"><?= $link['domain']; ?></a>
														<a class="a--inherit" href="http://web.archive.org/web/1/<?= $link['url']; ?>" target="_blank">(saved)</a>
													</li>
												<?php
											}
										?>
									</ul>
									<label class="input__button obscure__button" for="obscure-links">Show all</label>
								</div>
							<?php
						}
						
						// Tags
						include("../artists/page-tags.php");
						
						// Popularity
						include("../artists/partial-ranking.php");
					}
				?>
			</div>
			
		</div>
		
		<!-- Bottom -->
		<div class="artist__bottom">
			<?php
				// Comments
				include('../comments/partial-comments.php');
				render_default_comment_section('artist', $artist['id'], $artist['comments'], $markdown_parser);
				
				// Edit history
				if(is_array($artist["edit_history"]) && !empty($artist["edit_history"])) {
					?>
						<h3><?= lang('Edit history', '変更履歴', 'div'); ?></h3>
						<input class="obscure__input" id="show-edits" type="checkbox" <?php echo count($artist["edit_history"]) > 4 ? "checked" : null; ?> />
						<div class="text text--outlined obscure__container obscure--faint">
							<ul class="ul--compact">
								<?php
									for($i = 0; $i < count($artist["edit_history"]); $i++) {
										?>
											<li class="obscure__item">
												<span class="h4"><?= substr($artist["edit_history"][$i]["date_occurred"], 0, 10); ?></span>
												<a class="user" href="<?php echo '/users/'.$artist["edit_history"][$i]["username"].'/'; ?>"><?= $artist["edit_history"][$i]["username"]; ?></a>
												<?php
													foreach($artist['edit_history'][$i]['content'] as $change) {
														echo strlen($change) ? '<span class="symbol__edit any--weaken">'.$change.'</span> ' : null;
													}
												?>
											</li>
										<?php
									}
								?>
							</ul>
							<label class="input__button obscure__button" for="show-edits">
								Show all
							</label>
						</div>
					<?php
				}
			?>
		</div>
		
	</div>
	
</div>