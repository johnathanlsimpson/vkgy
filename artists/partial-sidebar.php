<?php
	style([
		'/artists/style-partial-sidebar.css',
	]);
	
	script([
		'/scripts/script-pronounce.js',
		'/artists/script-partial-sidebar.js',
	]);

		
?>

<!-- Stats -->
<div class="text text--outlined artist__details--second">
	<?php include('partial-stats.php'); ?>
</div>

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