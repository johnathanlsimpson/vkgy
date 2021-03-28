<?php

$large_header = true;

// Page setup
include_once('../artists/head.php');

style([
	'../artists/style-page-artist.css'
]);

script([
	'/scripts/external/script-list.js',
	'/scripts/script-pronounce.js',
	'/artists/script-partial-sidebar.js',
	'/artists/script-page-artist.js',
]);

// Show removed page with message
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

<!-- Images, lineup, data row -->
<div class="col c4-ABBB">
			
	<!-- Sidebar -->
	<div class="artist__left any--margin"><?php include('partial-sidebar.php'); ?></div>
	
	<div class="col c4-AAAB any--margin">
		
		<!-- Sidebar: images, video, tags -->
		<div class="artist__center">
			<h2 class="h1 artist__title">
				<?= lang('Members', 'メンバー', 'div'); ?>
			</h2>

			<?php

				// If artist viewable, but default view requested, show everything else
				if($artist_is_viewable) {

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
							<div class="text text--outlined symbol__error"><?= lang('This artist is non-VK. Information may be truncated or inaccurate, and is provided only to give context to related artists/musicians.', 'このアーティストは非V系です。情報は、切り捨てられたり不正確になったりする場合があります。', 'hidden'); ?></div>
						<?php
					}

					// Lineup
					if($artist['musicians']) {
						include('partial-lineup.php');
					}
					
					// History
					if( $artist['history'] ) {
						
						// Count history items to see if we need to collapse area
						foreach( $artist['history'] as $year ) {

							foreach( $year as $month ) {

								foreach( $month as $day ) {

									foreach( $day as $line ) {

										$num_history_lines++;

										if( $num_history_lines === 50 ) {

											break;

										}

									}

								}

							}

						}
						
						?>
							
							<h2 class="h1" id="history">
								<?= lang('History', '活動', 'div'); ?>
							</h2>
							
							<input class="obscure__input" id="show-history" type="checkbox" <?= $num_history_lines >= 50 ? 'checked' : null; ?> />
							<div class="history__container text obscure__container obscure--height obscure--faint" <?= $num_history_lines >= 50 ? 'style="min-height:60vh;"' : null; ?> >
								
								<details class="history__filters">

									<summary class="filters__control">
										
										<span class="filters__open input__button symbol__filter">filter</span>
										<span class="filters__close input__button close">close</span>
										
									</summary>

									<div class="input__row">

										<div class="input__group">
											<label class="input__label">sort</label>
											<button class="filters__sort sort" data-sort="sort-date" data-order="desc">Date</button>
										</div>
										
										<div class="input__group">
											
											<label class="input__label">show</label>
											<label class="input__radio">
												<input class="input__choice filter" type="radio" value="all" checked />
												<span class="symbol__unchecked">all</span>
											</label>

											<?php foreach( [ 'activity', 'release', 'member', 'live', 'other' ] as $filter_name ): ?>
												<label class="input__checkbox">
													<input class="input__choice filter" type="checkbox" value="<?= $filter_name; ?>" />
													<span class="symbol__unchecked"><?= $filter_name; ?></span>
												</label>
											<?php endforeach; ?>

										</div>
										
									</div>

								</details>
								
								<ul class="history__list list">
									<?php
										foreach( $artist['history'] as $year => $months ) {
											foreach( $months as $month => $days ) {
												foreach( $days as $day => $items ) {
													foreach( $items as $item_key => $item ) {

														// Modify omnibus links
														if( in_array( 'is_uneditable', $item['type'] ) && in_array( 'release', $item['type'] ) && strpos( $item['content'], '/releases/omnibus/' ) !== false ) {
															$item['content'] = preg_replace('/'.'(releases\/omnibus\/\d+\/[A-z0-9-]*\/)'.'/', '$1&prev_next_artist='.$artist['id'], $item['content']);
														}

														// Classes for li
														$li_classes = [];

														if( $item_key ) {
															$li_classes[] = 'dupe-date';
														}

														if( $year != $previous_year ) {
															$li_classes[] = 'new-year';
														}

														$li_classes = implode(' ', $li_classes);

														// Classes for content
														$span_classes = [];

														foreach( [ 'is_uneditable', 'lineup', 'setlist' ] as $weakened_type ) {
															if( in_array( $weakened_type, $item['type'] ) ) {
																$span_classes[] = 'any--weaken';
															}
														}
														
														$item['content'] = str_replace('class="symbol__release" href="/releases/magazine/', 'class="a--inherit" href="/releases/magazine"', $item['content']);

														if( count($item['type']) === 1 && $item['type'][0] === 'note' ) {
															$span_classes[] = 'any--weaken';
														}

														$span_classes = array_merge( $span_classes, $item['type'] );

														$span_classes = implode(' ', array_unique($span_classes));
														
														// Add count to date to preserve order for same-day items when sorting
														$sort_date = $item['date_occurred'].'-'.$item_key;

														?>
															<li class="history__item any--flex <?= $li_classes; ?>" data-year="<?= $year; ?>" data-sort-date="<?= $sort_date; ?>">
																<h4 class="history__date date">
																	<?= $item['date_occurred']; ?>
																</h4>
																<div class="history__content <?= $span_classes; ?>">
																	<?= $item['content']; ?>
																</div>
															</li>
														<?php

														$previous_year = $year;

													}
												}
											}
										}
									?>
								</ul>
								
								<label class="input__button obscure__button" for="show-history">show full history</label>
								
							</div>
							
						<?php
					}
			
					// Schedule
					if($artist['lives']) {
						?>
							<span id="schedule"></span>
							<h2><?= lang('Live history', 'ライブ一覧', 'div'); ?></h2>

							<div class="text text--outlined">
								<?php
									foreach($artist['lives'] as $year => $schedule_year) {
										?>
											<details>
												<summary class="h2" style="padding-bottom:0;">
													<?= $year; ?>
												</summary>
												<ul class="obscure__item ul--compact" style="margin:1rem 0;">
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
																				echo $_SESSION['can_add_data'] ? '<a class="symbol__edit a--inherit" href="/lives/'.$live['id'].'/edit/" style="margin-left:1ch;">Edit</a>' : null;
																			}
																		?>
																	</li>
																<?php
															}
														}
													?>
												</ul>
											</details>
										<?php
									}
								?>
							</div>
							</details>
						<?php
					}
					
				}
				
			?>
		</div>

		<div class="artist__right">

			<!-- Stats -->
			<h3 class="artist__title h1">
				<?= lang('Data', 'データ', 'div'); ?>
			</h3>
			<div class="text text--outlined">
				<?php include('partial-stats.php'); ?>
			</div>
			
			<?php
				// Similarly named artists
				// Get artists with same name or same pronunciation, then combine results (to eliminate duplicates) and remove current artist from result
				$possible_dupes_by_name = $access_artist->access_artist([ 'name' => $artist['name'], 'get' => 'name', 'associative' => true ]);
				$possible_dupes_by_pronunciation = strlen($artist['pronunciation']) ? $access_artist->access_artist([ 'name' => $artist['pronunciation'], 'get' => 'name', 'associative' => true ]) : [];
				$possible_dupes = (is_array($possible_dupes_by_name) ? $possible_dupes_by_name : []) + (is_array($possible_dupes_by_pronunciation) ? $possible_dupes_by_pronunciation : []);
				unset($possible_dupes[$artist['id']]);

				if(is_array($possible_dupes) && !empty($possible_dupes)) {
					?>
						<h3>
							<?= lang('Similarly named', '同じ名前', 'div'); ?>
						</h3>
						<div class="text text--outlined any--weaken-color">
							<?php
								foreach($possible_dupes as $dupe_key => $dupe) {
									if(strlen($dupe['romaji'])) {
										echo lang(
											'<a class="artist a--inherit" href="/artists/'.$dupe['friendly'].'/">'.$dupe['romaji'].'</a> ('.$dupe['name'].')',
											'<a class="artist a--inherit" href="/artists/'.$dupe['friendly'].'/">'.$dupe['name'].'</a>',
											'hidden'
										);
									}
									else {
										echo '<a class="artist a--inherit" href="/artists/'.$dupe['friendly'].'/">'.$dupe['name'].'</a>';
									}

									echo '&nbsp;<span class="any--weaken">'.$dupe['friendly'].'</span><br />';
								}
							?>
						</div>
					<?php
				}

				// Links
				if($artist["urls"]) {
					?>
						<h3><?= lang('Links', 'リンク', 'div'); ?></h3>
						<input class="obscure__input" id="obscure-links" type="checkbox" <?= count($artist['urls']) > 4 ? 'checked' : null; ?> >
						<div class="any--weaken text text--outlined obscure__container obscure--faint">
							<ul>
								<?php
									foreach($artist["urls"] as $url) {
										?>
											<li class="obscure__item">
												<a class="<?= $url['platform'] ? 'symbol__'.$url['platform'] : null; ?>" href="<?= $url['content']; ?>" target="_blank"><?= $url['display_name']; ?></a>
												<a class="a--inherit" href="http://web.archive.org/web/1/<?= $url['content']; ?>" target="_blank">(saved)</a>
												<?php
													if(is_array($url['musician']) && !empty($url['musician'])) {
														echo '<div>';
														echo ['?', 'V', 'G', 'B', 'D', 'K', '?', 'S'][$url['musician']['position']].'. ';
														if($url['musician']['as_name']) {
															echo $url['musician']['as_romaji'] ? lang($url['musician']['as_romaji'], $url['musician']['as_name'], 'hidden') : $url['musician']['as_name'];
														}
														else {
															echo $url['musician']['romaji'] ? lang($url['musician']['romaji'], $url['musician']['name'], 'hidden') : $url['musician']['name'];
														}
														echo '</div>';
													}
												?>
											</li>
										<?php
									}
								?>
							</ul>
							<label class="input__button obscure__button" for="obscure-links">Show all</label>
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
			
			?>

		</div>
		
	</div>
	
</div>

<div class="row any--margin" style="background:hsl(var(--background--alt));padding-top:3rem;padding-bottom:3rem;">
	<?php
		// Comments
		include('../comments/partial-comments.php');
		render_default_comment_section('artist', $artist['id'], $artist['comments'], $markdown_parser);
	?>
</div>

<!-- Bottom -->
<div class="col c1">
	<div>
	<?php

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
										<a class="user" data-icon="<?= $artist["edit_history"][$i]['user']['icon']; ?>" data-is-vip="<?= $artist["edit_history"][$i]['user']['is_vip']; ?>" href="<?= $artist["edit_history"][$i]['user']['url']; ?>"><?= $artist["edit_history"][$i]['user']['username']; ?></a>
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