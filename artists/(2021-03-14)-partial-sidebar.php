<?php

style([
	'/artists/style-partial-sidebar.css',
]);

script([
	'/scripts/script-pronounce.js',
	'/artists/script-partial-sidebar.js',
]);

?>


<?php
if($artist_is_viewable) {
	
	// Sidebar: videos (unless viewing all videos)
	if($artist['video']) {
		?>
			<div class="text side__video-container">
				<a class="lazy side__video-link youtube__embed" data-id="<?= $artist['video'][0]['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$artist['video'][0]['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$artist['video'][0]['youtube_id']; ?>" target="_blank"></a>
				<a class="symbol__previous side__videos-link" href="<?= '/artists/'.$artist['friendly'].'/videos/'; ?>"><?= lang('More videos', 'その他', 'hidden'); ?></a>
				<span class="symbol__error any--weaken side__video-notice"><?= lang('Please <a class="a--inherit" href="/artists/'.$artist['friendly'].'/videos/">report</a> unofficial videos.', '非公式の動画を<a class="a--inherit" href="/artists/'.$artist['friendly'].'/videos/">報告して</a>ください。', 'hidden'); ?></span>
			</div>
		<?php
	}
	
	// Sidebar: images
	if($artist["images"]) {
			?>
				<div>
					<h3><?= lang('Images', '画像', 'div'); ?></h3>
					<input class="obscure__input" id="obscure-images" type="checkbox" <?= count($artist['images']) > 12 ? 'checked' : null; ?> >
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
								<span class="symbol__triangle symbol--down label__next"></span>
							<?php
						}
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
	
	// Tags
	$item_type = 'artist';
	include('../tags/partial-tags.php');
	
	// Popularity
	include("../artists/partial-ranking.php");
}