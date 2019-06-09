<?php
	include_once("../releases/head.php");

	$access_release = new access_release($pdo);
	$access_user = new access_user($pdo);

	script([
		"/scripts/external/script-selectize.js",
		"/scripts/external/script-inputmask.js",
		"/scripts/script-initSelectize.js",
		"/releases/script-page-index.js"
	]);

	style([
		"/style/external/style-selectize.css",
		"/releases/style-page-index.css"
	]);

	subnav([
		lang('Release calendar', '新譜一覧', ['secondary_class' => 'any--hidden']) => '/releases/',
		lang('Search', 'サーチ', ['secondary_class' => 'any--hidden']) => '/search/releases/',
	]);

	$page_header = lang('VK release information', 'V系リリース情報', ['container' => 'div']);
?>

<?php
	if($error) {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error symbol__error">
						<?php echo $error; ?>
					</div>
				</div>
			</div>
		<?php
	}

	$sql_recently_edited = 'SELECT MAX(id) AS id FROM edits_releases GROUP BY release_id ORDER BY id DESC LIMIT 20';
	$stmt_recently_edited = $pdo->prepare($sql_recently_edited);
	$stmt_recently_edited->execute();
	foreach($stmt_recently_edited->fetchAll() as $rslt_recently_edited) {
		$recently_edited_ids[] = $rslt_recently_edited['id'];
	}

	$edited_releases = $access_release->access_release([ 'edit_ids' => $recently_edited_ids, 'get' => 'list' ]);

	$recent_releases = $access_release->access_release([ 'get' => 'calendar', 'start_date' => date('Y-m-d', strtotime('-1 month')) ]);

	if(is_array($recent_releases) && !empty($recent_releases)) {
		$recent_releases = array_reverse($recent_releases);

		foreach($recent_releases as $release) {
			$day = $release['date_occurred'];
			$month = substr($day, 0, 7);
			$alph_key = $release['artist']['friendly'].'-'.$release['friendly'].'-'.$release['id'];
			$release['medium'] = strtolower(preg_replace('/'.'\((?!CD|DVD)[\w- ]+\)'.'/', '(other)', $release['medium']));

			$release_ids_by_name[$alph_key] = $release['id'];
			$releases_by_date[$month][] = $release;
		}

		ksort($release_ids_by_name);
		$release_ids_by_name = array_values($release_ids_by_name);
		$release_ids_by_name = array_flip($release_ids_by_name);
	}
?>

<?php
	if(is_array($releases_by_date) && !empty($releases_by_date)) {
		?>
			<div class="col c3-AAB">
				<div class="calendar__wrapper">
					<h2>
						<?php echo lang('Visual kei release calendar', 'ビジュアル系新譜一覧', ['container' => 'div']); ?>
					</h2>

					<input class="any--hidden" id="order-by--date-asc"  name="order-by" type="radio" checked />
					<input class="any--hidden" id="order-by--date-desc" name="order-by" type="radio" />
					<input class="any--hidden" id="order-by--name"      name="order-by" type="radio" />
					<input class="any--hidden" id="filter--all" name="filter" value="all" type="radio" checked />
					<input class="any--hidden" id="filter--cd" name="filter" value="cd" type="radio" />
					<input class="any--hidden" id="filter--dvd" name="filter" value="dvd" type="radio" />
					<input class="any--hidden" id="filter--other" name="filter" value="other" type="radio" />

					<div class="calendar__controls any--flex any--flex-space-between">
						<div>
							<label class="input__checkbox-label" for="order-by--date-asc">date <span class="symbol__up-caret"></span></label>
							<label class="input__checkbox-label" for="order-by--date-desc">date <span class="symbol__down-caret"></span></label>
							<label class="input__checkbox-label" for="order-by--name">A-Z</label>
						</div>
						<div>
							<label class="input__checkbox-label" for="filter--all">all</label>
							<label class="input__checkbox-label" for="filter--cd">CD</label>
							<label class="input__checkbox-label" for="filter--dvd">DVD</label>
							<label class="input__checkbox-label" for="filter--other">other</label>
						</div>
					</div>

					<div class="calendar__container text text--outlined">
						<?php
							foreach($releases_by_date as $month => $day) {
								$header_en =
									(substr($month, 5, 2) === '00' ? 'TBA' : date('F', strtotime($month.'-01'))).
									($month < date('Y-01') || $month > date('Y-12-31') ? ' '.substr($month, 0, 4) : null);
								$header_jp =
									substr($month, 0, 4).'年'.
									(substr($month, 5, 2) === '00' ? ' 未定' : substr($month, 5, 2).'月');

								?>
									<h2 class="calendar__header">
										<?php echo lang($header_en, $header_jp, ['container' => 'div']); ?>
									</h2>
								<?php

								foreach($day as $release) {
									$artist_image = '/artists/'.$release['artist']['friendly'].'/main.small.jpg';
									$cover_image = str_replace('.jpg', '.thumbnail.jpg', $release['image']['url']);
									$artist_url = '/artists/'.$release['artist']['friendly'].'/';
									$artist_name = $release['artist']['quick_name'].($release['artist']['romaji'] ? ' <span class="any--weaken">('.$release['artist']['name'].')</span>' : null);
									$release_name = $release['quick_name'].($release['romaji'] ? ' <span class="any--weaken">('.$release['name'].')</span>' : null);

									?>
										<div
												 class="calendar__item text text--outlined text--compact any--flex any__obscure"
												 data-medium="<?php echo $release['medium']; ?>"
												 style="order: <?php echo $release_ids_by_name[$release['id']]; ?>">
											<a class="calendar__cover" href="<?php echo str_replace(['.small', '.thumbnail'], '', ($cover_image ?: $artist_image)); ?>" target="_blank" title="<?php echo ($cover_image ? '&ldquo;'.$release['quick_name'].'&rdquo; cover' : $release['artist']['quick_name'].' image'); ?>">
												<img alt="<?php echo '&ldquo;'.$release['quick_name'].'&rdquo; cover'; ?>" data-src="<?php echo $cover_image ?: $artist_image; ?>" />
											</a>
											<div class="calendar__content">
												<h5 class="calendar__date">
													<?php echo $release['date_occurred']; ?>
												</h5>
												<a class="calendar__artist artist" href="<?php echo $artist_url; ?>"><?php echo $artist_name; ?></a>
												<a class="calendar__title symbol__release" href="<?php echo '/releases/'.$release['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/'; ?>"><?php echo $release_name; ?></a>
												<a class="calendar__buy any--weaken symbol__exit" href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q=<?php echo str_replace('-', '+', $release['friendly']); ?>">Search CDJapan</a>
											</div>
										</div>
									<?php
								}

								?>
									<h2 class="calendar__header calendar__header--reverse">
										<?php echo lang($header_en, $header_jp, ['container' => 'div']); ?>
									</h2>
								<?php
							}
						?>
					</div>
				</div>

				<?php
					if(is_array($edited_releases)) {
						?>
							<div>
								<h2>
									<?php echo lang('Recently updated', '最近の更新', ['container' => 'div']); ?>
								</h2>
								<div class="text">
									<ul>
										<?php
											$edited_release_ids = array_keys($edited_releases);
											$num_edited_releases = count($edited_releases);
											$edited_releases = array_values($edited_releases);

											for($i=0; $i<$num_edited_releases; $i++) {
												?>
													<li>
														<div class="h5">
															<?php
																echo $edited_releases[$i]['date_edited'].' by '.'<a class="user a--inherit" href="/users/'.$edited_releases[$i]['username'].'/">'.$edited_releases[$i]['username'].'</a>';
															?>
														</div>
														<div>
															<a class="artist any--weaken-size" href="/releases/<?php echo $edited_releases[$i]["artist"]["friendly"]; ?>/"><?php echo $edited_releases[$i]["artist"]["quick_name"]; ?></a>
															<br />
															<a class="symbol__release" href="/releases/<?php echo $edited_releases[$i]["artist"]["friendly"]."/".$edited_releases[$i]["id"]."/".$edited_releases[$i]["friendly"]; ?>/"><?php echo $edited_releases[$i]["quick_name"]; ?></a>
														</div>
													</li>
												<?php
											}
										?>
									</ul>
								</div>
							</div>
						<?php
					}
				?>
			</div>
		<?php
	}
?>

<style>
	.calendar__container {
		align-items: stretch;
		display: flex;
		flex-wrap: wrap;
		padding: 0 0 1rem 1rem;
	}
	.calendar__controls * {
		display: inline-block;
	}
	.calendar__header, .calendar__item {
		margin: 1rem 1rem 0 0;
	}
	.calendar__header {
		padding-bottom: 0;
		width: 100%;
	}
	.calendar__header + .calendar__header,
	.calendar__item + .calendar__header:not(:last-of-type) {
		margin-top: 3rem;
	}
	.calendar__item {
		background-position: center;
		background-size: cover;
		flex-basis: 300px;
		flex-grow: 1;
		max-width: 100%;
	}
	.calendar__item::after {
		content: "";
		display: block;
		flex: none;
		margin-right: 0.5rem;
		order: -1;
		width: 100px;
	}
	.calendar__content > * {
		display: block;
	}
	.calendar__artist, .calendar__title {
		line-height: 1;
		margin-top: 1rem;
	}
	.calendar__date {
		white-space: nowrap;
	}
	.calendar__buy {
		margin-top: 1rem;
		text-transform: uppercase;
		white-space: nowrap;
	}
	.calendar__cover {
		left: 0.5rem;
		position: absolute;
	}
	.calendar__cover img {
		max-height: 100px;
		max-width: 100px;
	}
	[data-src]:not(.loaded) {
		/*opacity: 0;*/
	}

	/* Show controls as active when checked */
	[id="order-by--date-asc"]:checked ~ .calendar__controls [for="order-by--date-asc"],
	[id="order-by--date-desc"]:checked ~ .calendar__controls [for="order-by--date-desc"],
	[id="order-by--name"]:checked ~ .calendar__controls [for="order-by--name"],
	[id="filter--all"]:checked ~ .calendar__controls [for="filter--all"],
	[id="filter--cd"]:checked ~ .calendar__controls [for="filter--cd"],
	[id="filter--dvd"]:checked ~ .calendar__controls [for="filter--dvd"],
	[id="filter--other"]:checked ~ .calendar__controls [for="filter--other"] {
		color: hsl(var(--text));
	}

	/* Set order and filter styles */
	[id="order-by--date-desc"]:checked ~ .calendar__container {
		flex-flow: row-reverse wrap-reverse;
	}
	[id^="order-by--date-asc"]:checked ~ .calendar__container .calendar__header--reverse,
	[id^="order-by--date-desc"]:checked ~ .calendar__container .calendar__header:not(.calendar__header--reverse) {
		display: none;
	}
	[id^="order-by--date"]:checked ~ .calendar__container .calendar__item {
		order: unset !important;
	}
	[id^="order-by--name"]:checked ~ .calendar__container .calendar__header {
		display: none;
	}
	[id^="filter--cd"]:checked ~ .calendar__container .calendar__item:not([data-medium*="(cd"]),
	[id^="filter--dvd"]:checked ~ .calendar__container .calendar__item:not([data-medium*="(dvd"]),
	[id^="filter--other"]:checked ~ .calendar__container .calendar__item:not([data-medium*="(other"]) {
		display: none;
	}
</style>

<?php
	include("../search/page-releases.php");

	$pageTitle = "Release calendar | リリースカレンダー";
?>