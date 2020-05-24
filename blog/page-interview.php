<?php

style([
	'/blog/style-page-entry.css',
]);

if(is_array($entry) && !empty($entry)) {
	
	// Check if entry contains translation link; if so, remove from entry but save URL and language for later
	$translation_pattern = '<a href="((?:https:\/\/vk\.gy)?\/blog\/[A-z0-9-]+\/?)">'.'&#9888; ([A-z0-9 \.\&\#\;]+)<\/a>';
	if(preg_match('/'.$translation_pattern.'/', $entry['content'], $translation_match)) {
		$entry['content'] = str_replace($translation_match[0], '', $entry['content']);
		$translation_link = $translation_match[1];
		$translation_text = $translation_match[2];
		$translation_type = strpos($translation_match[2], 'English') !== false ? 'en' : 'ja';
	}
	
	// Make sure images array exists
	$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];
	
	// Set main image
	if(!empty($entry['images']) && is_numeric($entry['image_id'])) {
		$entry['image'] = $entry['images'][$entry['image_id']];
		
		$page_image = "https://vk.gy".str_replace('.', '.large.', $entry['image']['url']);
		
		$entry_has_image = true;
	}
	
	// Make blog entries show large versions of images
	$entry['content'] = str_replace('.medium.', '.large.', $entry['content']);
	
	// Set page author for Twitter
	$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
	$stmt_twitter = $pdo->prepare($sql_twitter);
	$stmt_twitter->execute([ $entry['user']['username'] ]);
	$rslt_twitter = $stmt_twitter->fetchColumn();
	if(!empty($rslt_twitter) && preg_match("/"."^[A-z0-9_]+$"."/", $rslt_twitter)) {
		$page_creator = $rslt_twitter;
	}
	
	// Set page description
	$page_description = preg_replace("/"."<.*?>"."/", "", strtok($entry["content"], "\n"))." (Continued…)";
	
	// Related: entries with same tag
	if(is_array($entry['tags']) && !empty($entry['tags'])) {
		foreach($entry['tags'] as $tag) {
			if(strpos($tag['friendly'], 'release') !== 0 && strpos($tag['friendly'], 'live') !== 0 && strpos($tag['friendly'], 'auto') !== 0) {
				$tag_types_to_search[] = $tag['id'];
				
				// Related: features
				if($tag['friendly'] === 'interview') {
					$sql_related_entries[] = 'SELECT blog_id, "feature" AS relation_type FROM blog_tags WHERE blog_id != ? AND tag_id=? ORDER BY id DESC LIMIT 12';
					$values_related_entries[] = [$entry['id'], $tag['id']];
				}
			}
		}
		
		if(is_array($tag_types_to_search) && !empty($tag_types_to_search)) {
			$sql_related_entries[] = 'SELECT blog_id, "same-tag" AS relation_type FROM blog_tags WHERE blog_id != ? AND ('.substr(str_repeat('tag_id=? OR ', count($tag_types_to_search)), 0, -4).') ORDER BY id DESC LIMIT 12';
			
			array_unshift($tag_types_to_search, $entry['id']);
			$values_related_entries[] = $tag_types_to_search;
		}
	}
	
	// Related: entries by same artist(s)
	if(is_array($entry['tags_artists']) && !empty($entry['tags_artists'])) {
		foreach($entry['tags_artists'] as $tag) {
			$artist_tags_to_search[] = $tag['id'];
		}
		
		if(is_array($artist_tags_to_search) && !empty($artist_tags_to_search)) {
			$sql_related_entries[] = 'SELECT blog_id, "same-artist" AS relation_type FROM blog_artists WHERE blog_id != ? AND ('.substr(str_repeat('artist_id=? OR ', count($artist_tags_to_search)), 0, -4).') ORDER BY id DESC LIMIT 12';
			
			array_unshift($artist_tags_to_search, $entry['id']);
			$values_related_entries[] = $artist_tags_to_search;
		}
	}
	
	// Get related entries: merge sql queries and values, then randomize and return
	if(is_array($sql_related_entries) && is_array($values_related_entries) && count($sql_related_entries) === count($values_related_entries)) {
		$values_ids_of_related_entries = [];
		
		foreach($values_related_entries as $values_set) {
			$values_ids_of_related_entries = array_merge($values_ids_of_related_entries, $values_set);
		}
		
		$sql_ids_of_related_entries = '
			SELECT blog_id, relation_type FROM
			(('.implode(') UNION (', $sql_related_entries).')) possibilities ORDER BY RAND()
		';
		$stmt_ids_of_related_entries = $pdo->prepare($sql_ids_of_related_entries);
		$stmt_ids_of_related_entries->execute($values_ids_of_related_entries);
		$rslt_ids_of_related_entries = $stmt_ids_of_related_entries->fetchAll();
		
		// For ids of related entries, go back and get actual entry info
		if(is_array($rslt_ids_of_related_entries) && !empty($rslt_ids_of_related_entries)) {
			foreach($rslt_ids_of_related_entries as $related_entry_id) {
				$related_entry_ids[] = $related_entry_id['blog_id'];
			}
			$related_entry_ids = array_values(array_unique($related_entry_ids));
			
			$sql_related_entries = 'SELECT blog.title, blog.friendly, blog.image_id, images.extension FROM blog LEFT JOIN images ON images.id=blog.image_id WHERE ('.substr(str_repeat('blog.id=? OR ', count($related_entry_ids)), 0, -4).') AND blog.is_queued=0 ORDER BY RAND() LIMIT 6';
			$stmt_related_entries = $pdo->prepare($sql_related_entries);
			$stmt_related_entries->execute($related_entry_ids);
			$entry['related'] = $stmt_related_entries->fetchAll();
		}
	}
	
	/////////////////////////////////////////
	
	// Split article into intro and main
	list($entry['intro'], $entry['content']) = explode('<hr />', $entry['content'], 2);
	
	// Attempt to get main artist of article
	if(preg_match('/'.'class="artist" href="\/artists\/([A-z0-9-]+)\/?"'.'/', $entry['intro'], $artist_match)) {
		if(is_array($entry['tags_artists']) && !empty($entry['tags_artists'])) {
			foreach($entry['tags_artists'] as $artist_tag) {
				if($artist_tag['friendly'] === $artist_match[1]) {
					$entry['artist'] = $artist_tag;
				}
			}
		}
	}
	else {
		if(is_array($entry['tags_artists']) && !empty($entry['tags_artists'])) {
			$entry['artist'] = reset($entry['tags_artists']);
		}
	}
	
	// Remove intro sentence from intro
	$entry['intro'] = explode('</p>', $entry['intro'], 2)[1];
	
	// Set header image (for features, will differ from page image)
	echo '<style>:root{--header-image:url(/artists/'.$entry['artist']['friendly'].'/main.jpg)}</style>';
	
	?>
		
		<style>
			/* Needs to go into normal stylesheet */
			
				.entry--interview > .ul--bulleted,
				.entry--interview > .ul--bulleted li {
					width: 100%;
				}
				.entry--interview > .ul--bulleted li {
					padding-bottom: 1.5rem;
					padding-top: 1.5rem;
				}
				.entry--interview > .ul--bulleted li::before {
					opacity: 0;
				}
				.entry--interview h2 {
					line-height: 1.6;
					padding: 0 2rem 0 1rem;
					width: calc(600px + 2rem);
				}
				.entry--interview h2::before {
					background: hsl(var(--attention--secondary));
					background-clip: content-box;
					bottom: 0;
					content: "";
					display: inline-block;
					left: 0;
					position: absolute;
					top: 0;
					width: 4px;
				}
				.entry--interview h2 + p {
					margin-top: 1.5rem;
				}
			
			/* Modify page header */
			.header__wrapper {
				border-width: 0;
				overflow: hidden;
				padding: 0;
			}
			.header__wrapper::after {
				z-index: 0;
			}
			.header__container {
				background-image: var(--header-image) !important;
				background-position: center 40%;
				background-size: 120% auto;
				border: inherit;
				border-width: 0 var(--gutter);
				overflow: visible;
				padding: 1rem var(--page-margin) 27% var(--page-margin);
			}
			.header__container::after {
				display: none;
			}
			.header__container::before {
				background: none;
				filter: none;
			}
			.quaternary-nav__container {
				z-index: 1;
			}
			.header__header {
				height: 0;
				opacity: 0;
				width: 0;
			}
			
			/* Header spacing */
			.entry__header header * {
				width: auto !important;
			}
			.entry__title {
				font-size: 2rem;
				line-height: 1;
			}
			@media(min-width:600px) {
				.entry__title {
					font-size: 6rem;
				}
				.entry__subtitle {
					display: block;
					font-family: var(--font--secondary);
					font-size: 3rem;
					text-transform: uppercase;
				}
				.entry__header p {
					font-size: 1.5rem;
				}
			}
			
			/* Content spacing */
			.text--centered p {
				margin-left: 0 !important;
			}
			.text--centered h2 {
				margin-left: 0;
				padding: 0 0 0 1rem !important;
				width: calc(600px + 1rem) !important;
			}
			.text--centered .module {
				background: hsl(var(--background));
				vertical-align: middle;
			}
			.text--centered .module>* {
				width: 100%;
			}
			.text--centered .module:not(.module--release) {
				padding: 0 !important;
			}
			.text--centered .module--image img {
				width: 100%;
			}
			.text--centered > ul > li {
				padding-left: 0;
			}
			
			/* Breakpoint layouts */
			@media(min-width:800px) {
				.interview__spacer.interview__spacer {
					max-width: 360px;
				}
				.interview__left.interview__left {
					max-width: none;
					min-width: 300px;
				}
			}
			@media(max-width:999.99px) {
				.interview__header.interview__header {
					margin-left: 0;
					margin-right: 0;
					width: 100%;
				}
				.interview__left {
					order: 2;
				}
				.interview__center.interview__center {
					margin-left: 0;
				}
			}
			@media(max-width:1399.99px) {
				.interview__spacer {
					width: 0 !important;
				}
			}
			@media(max-width:799.99px) {
				.interview__supplement {
					order: 1;
				}
			}
			
			/* Sidebar */
			.interview__supplement {
				align-self: stretch;
			}
			.interview__supplement .module--release:first-of-type {
				margin-top: 0;
			}
			.interview__supplement .module--release {
				background: none;
				flex-direction: column;
				margin-top: 1rem;
				padding-top: 0;
			}
			.interview__supplement .module--release > a {
				margin-left: -1rem !important;;
				margin-right: -1rem !important;;
				width: auto !important;
			}
			.interview__supplement .module--release > a img {
				width: 100%;
			}
			.interview__profile {
				position: -webkit-sticky;
				position: sticky;
				top: 4rem;
			}
			.interview__profile .card {
				margin-bottom: 1rem;
			}
			.interview__profile p {
				display: inline;
			}
			.interview__lineup:not(:empty) {
				margin-top: 1rem;
			}
			.interview__lineup li {
				max-width: 100%;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			.interview__blood {
				float: right;
				line-height: 0;
			}
			.interview__blood .any__note {
				color: hsl(var(--text));
			}
			.interview__blood * + * {
				margin-left: 5px;
			}
			.interview__blood [target="_blank"] {
				font-size: 1.5rem;
				vertical-align: bottom;
			}
			.interview__urls {
				border-top: 1px dotted hsl(var(--text--secondary));
				margin-top: 1.5rem;
				padding-top: 1.5rem;
			}
			.interview__urls .a--outlined {
				order: -1;
			}
			.interview__urls .symbol--standalone {
				font-size: 1.5rem;
				line-height: 0;
				vertical-align: bottom;
			}
			
			/* Center content */
			.interview__header {
				background: transparent;
				border-color: transparent;
			}
			.interview__attribution {
				border: 0 dotted hsl(var(--background--bold));
				border-width: 1px 0;
				margin-top: 1.5rem;
				padding-bottom: 1.5rem;
				padding-top: 1.5rem;
			}
			.interview__editor {
				margin-right: 1ch;
			}
			.interview__center .text--centered > :first-of-type > li:first-of-type {
				padding-top: 0;
			}
			.interview__center > .text {
				background: transparent;
				border-color: transparent;
			}
		</style>
		
		<article class="row <?= $entry_has_image ? null : 'entry--no-image'; ?> ">
			
			<!-- Top half of interview -->
			<div class="col c4-ABBC entry__header interview__top">
				
				<div class="interview__spacer interview__left"></div>
				
				<header class="text text--centered interview__header">
					
					<!-- Interview title -->
					<h1 class="entry__title">
						<a class="a--inherit" href="<?= '/blog/'.$entry['friendly'].'/'; ?>">
							<?= preg_replace('/'.'(an interview with|&#12408;&#12398;&#12452;&#12531;&#12479;&#12499;&#12517;&#12540;)(?: \[&#26085;&#26412;&#35486;&#29256;\])?'.'/i', '<span class="entry__subtitle">$1</span>', $entry['title']); ?>
						</a>
					</h1>
					
					<!-- Interview intro -->
					<?= $entry['intro']; ?>
					
					<!-- Interview details -->
					<div class="interview__attribution data__container">
						
						<div class="data__item">
							<div>
								<div class="h5">
									<?= lang('Published', '発売', 'hidden'); ?>
								</div>
								<time class="entry__date" datetime="<?= $entry['date_occurred']; ?>"><?= substr($entry['date_occurred'], 0, 10); ?></time>
							</div>
						</div>
						
						<div class="data__item">
							<div>
								<div class="h5">
									<?= lang('Conducted', '取材', 'hidden'); ?>
								</div>
								<a class="user" data-icon="<?= $entry['user']['icon']; ?>" data-is-vip="<?= $entry['user']['is_vip']; ?>" href="<?= $entry['user']['url']; ?>"><?= $entry['user']['username']; ?></a>
							</div>
						</div>
						
						<?php
							if(is_array($entry['edit_history']) && !empty($entry['edit_history'])) {
								?>
									<div class="data__item">
										<div>
											<div class="h5">
												<?= lang('Edited', '編む', 'hidden'); ?>
											</div>
											<?php
												foreach($entry['edit_history'] as $edit) {
													echo $shown_users[$edit['user']['id']] ? null : '<a class="user interview__editor" data-icon="'.$edit['user']['icon'].'" data-is-vip="'.$edit['user']['is_vip'].'" href="'.$edit['user']['url'].'">'.$edit['user']['username'].'</a>';
													$shown_users[$edit['user']['id']] = true;
												}
											?>
										</div>
									</div>
								<?php
							}
						?>

						<?php
							if($translation_link) {
								?>
									<div class="data__item">
										<div>
											<div class="h5">
												<?= lang('Translation', '翻訳', 'hidden'); ?>
											</div>
											<a href="<?= $translation_link; ?>"><?= $translation_text; ?></a>
										</div>
									</div>
								<?php
							}
						?>

					</div>

				</header>
				
				<div class="interview__spacer"></div>
				
			</div>
			
			<!-- Middle half of interview -->
			<div class="col c4-ABBC interview__middle">
				
				<!-- Interview aside -->
				<aside class="interview__supplement interview__left text any--weaken-color">
					<?php
						// Loop through references and chuck any releases mentioned in the entry into the sidebar
						if(is_array($entry['references']) && !empty($entry['references'])) {
							$num_references = count($entry['references']);
							for($i=$num_references; $i>0; $i--) {
								if($entry['references'][$i]['type'] === 'release') {
									
									// Render title if necessary
									if(!$release_title_shown) {
										echo '<h2>'.lang('Releases', 'リリース情報', 'div').'</h2>';
										$release_title_shown = true;
									}
									
									// Set reference
									$reference_datum = $entry['references'][$i];
									$cdjapan_link =
										'http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&amp;f=all&amp;q='.
										($reference_datum["upc"] ? str_replace(["-000", "-00", "-0"], "-", $reference_datum["upc"]) : str_replace(" ", "+", $reference_datum["quick_name"]));
									
									// Render reference (this is just ripped from parse_markdown; need to do better, later)
									ob_start();
									?>
										<div class="module module--release any--flex">
											<?php
												if(is_array($reference_datum['image']) && !empty($reference_datum['image'])) {
													?>
														<a style="margin-right: 1ch; width: 100px;" href="<?= '/images/'.$reference_datum['image']['id'].'-cover.'.$reference_datum['image']['extension']; ?>" target="_blank">
															<img alt="<?= $reference_datum['quick_name'].' cover'; ?>" src="<?= '/images/'.$reference_datum['image']['id'].'-cover.thumbnail.'.$reference_datum['image']['extension']; ?>" />
														</a>
													<?php
												}
											?>
											<div style="width: 100%;">
												<div class="h5">
													<?= $reference_datum['date_occurred']; ?>
												</div>
												<a class="artist" data-name="<?= $reference_datum['artist']['name']; ?>" href="<?= '/artists/'.$reference_datum['artist']['friendly'].'/'; ?>">
													<?= $reference_datum['artist']['romaji'] ? lang(($reference_datum['artist']['romaji'] ?: $reference_datum['artist']['name']), $reference_datum['artist']['name'], 'parentheses') : $reference_datum['artist']['name']; ?>
												</a>
												<br />
												<a class="symbol__release" href="<?= '/releases/'.$reference_datum['artist']['friendly'].'/'.$reference_datum['id'].'/'.$reference_datum['friendly'].'/'; ?>">
													<?php
														if($reference_datum['romaji']) {
															$romaji =
																($reference_datum['romaji']).
																($reference_datum['press_name'] ? (' '.$reference_datum['press_romaji'] ?: $reference_datum['press_name']) : null).
																($reference_datum['type_name'] ? (' '.$reference_datum['type_romaji'] ?: $reference_datum['type_name']) : null);
														}
														$name = 
															($reference_datum['name']).
															($reference_datum['press_name'] ? ' '.$reference_datum['press_name'] : null).
															($reference_datum['type_name'] ? ' '.$reference_datum['type_name'] : null);
														if(strlen($reference_datum['romaji']) && $reference_datum['romaji'] != $reference_datum['name']) {
															echo lang($romaji, $name, 'parentheses');
														}
														else {
															echo $name;
														}
													?>
												</a>
												<ol class="ol--inline" style="margin: 0; text-align: left; width: 100%;">
													<?php
														foreach($reference_datum["tracklist"] as $discs) {
															foreach($discs as $disc) {

																echo $disc['disc_name'] ? '<span class="module--disc">【'.($disc['disc_romaji'] ?: $disc['disc_name']).'】</span> ' : null;

																foreach($disc['sections'] as $section) {
																	foreach($section['tracks'] as $track) {
																		?>
																			<li style="<?php echo $track['track_num'] == 1 ? 'counter-reset: defaultcounter;' : null; ?>">
																				<?= $track['romaji'] ? lang($track['romaji'], $track['name'], 'parentheses') : $track['name']; ?>
																			</li>
																		<?php
																	}
																}
															}
														}
													?>
												</ol>
												<!--<a class="symbol__arrow-right-circled" href="<?= $cdjapan_link; ?>" target="_blank"><?= ($reference_datum['date_occurred'] > date('Y-m-d') ? 'Preorder' : 'Order').' at CDJapan'; ?></a>-->

												<a class="release__buy" href="<?= $cdjapan_link; ?>" target="_blank">
													<img src="/releases/cdj.gif" style="height:1rem;" /> CDJapan
												</a>
												&nbsp;
												<a class="release__buy" href="<?= 'https://magento.rarezhut.net/catalogsearch/result/?q='.html_entity_decode($reference_datum['artist']['name'].' '.$reference_datum['name']); ?>" target="_blank">
													<img src="/releases/rh.gif" style="height:1rem;" /> RarezHut
												</a>

											</div>
										</div>
									<?php
									$output = str_replace(["\n", "\t", "\r"], "", ob_get_clean());
									$output = str_replace('.thumbnail.', '.medium.', $output);
									echo $output;
									
								}
							}
						}
					?>
					
					<?php
						// Get artist's upcoming live schedule
						$access_live = new access_live($pdo);
						
						// Set up empty lives array
						$lives = [];
						
						// Loop through each upcoming month, grab lives, and add to array
						for($i=0; $i<5; $i++) {
							$month_string = substr($entry['date_occurred'], 0, 7).'-01 +'.$i.' months';
							$lives_month = $access_live->access_live([ 'artist_id' => $entry['artist']['id'], 'get' => 'name', 'date_occurred' => date('Y-m', strtotime($month_string)) ]);
							$lives = is_array($lives_month) ? array_merge($lives, $lives_month) : $lives;
						}
						
						if(is_array($lives) && !empty($lives)) {
							?>
								<h2>
									<?= lang('Events', 'イベント情報', 'div'); ?>
								</h2>
								<ul class="ul--compact any--margin">
									<?php
										foreach($lives as $live) {
											echo '<li>';
											echo '<div class="h5">';
											echo str_ireplace(['sun','mon','tue','wed','thu','fri','sat'], ["日","月","火","水","木","金","土"], lang( $live['date_occurred'], date('Y年m月d日（D）', strtotime($live['date_occurred'])), 'hidden' ));
											echo '</div>';
											echo '<div>';
											echo '<span class="any--weaken-size">'.lang( $live['area_romaji'] ?: $live['area_name'], $live['area_name'], 'hidden' ).'</span>';
											echo ' '.lang( $live['livehouse_romaji'] ?: $live['livehouse_name'], $live['livehouse_name'], 'hidden' );
											echo '</div>';
											echo '</li>';
										}
									?>
								</ul>
							<?php
						}
					?>
					
					<div class="interview__profile">
						<h2>
							<?= lang('Profile', 'プロフィール', 'div'); ?>
						</h2>
						<?php
							// Get additional artist info
							$entry['artist'] = $access_artist->access_artist([ 'id' => $entry['artist']['id'], 'get' => 'profile' ]);

							// Clean up URLs
							include_once('../artists/function-format_artist_urls.php');
							$entry['artist']['urls'] = format_artist_urls($entry['artist']['urls']);

							// Render artist card
							$access_artist->artist_card($entry['artist']);

							// Name, pronunciation, description
							if($entry['artist']['pronunciation'] || $entry['artist']['description']) {
								//$markdown_parser = new parse_markdown($pdo);
								echo '<strong>';
								echo '<a class="artist" href="/artists/'.$entry['artist']['friendly'].'/">'.lang( $entry['artist']['romaji'] ?: $entry['artist']['name'], $entry['artist']['name'], 'hidden' ).'</a>';
								echo '</strong>';
								echo $entry['artist']['romaji'] ? lang( ' ('.$entry['artist']['name'].')', null, 'hidden' ) : null;
								echo $entry['artist']['pronunciation'] ? ' ('.$entry['artist']['pronunciation'].')' : null;
								echo $entry['artist']['description'] ? ': '.$markdown_parser->parse_markdown($entry['artist']['description']) : null;
							}

							// Musicians
							if(is_array($entry['artist']['musicians']) && !empty($entry['artist']['musicians'])) {
								echo '<ul class="interview__lineup ul--compact">';

								// Loop through musicians still in band
								foreach($entry['artist']['musicians'] as $musician) {
									if($musician['to_end']) {

										echo '<li>';

										// Birth/links
										echo '<div class="interview__blood">';
										echo strlen($musician['blood_type']) ? '<span class="any__note">🩸'.$musician['blood_type'].'</span>' : null;
										echo strlen($musician['birth_date']) ? '<span class="any__note">🎂'.str_replace('-', '/', substr($musician['birth_date'], 5)).'</span>' : null;

										// Musician URLs
										if(is_array($entry['artist']['urls']) && !empty($entry['artist']['urls'])) {
											foreach($entry['artist']['urls'] as $url) {
												if($url['musician_id'] === $musician['id'] && $url['platform']) {
													echo '<a href="'.$url['content'].'" rel="nofollow" target="_blank">';
													echo '<span class="symbol--standalone symbol__'.$url['platform'].'"></span>';
													echo '</a>';
												}
											}
										}
										echo '</div>';

										// Position
										echo '<span class="any--weaken">';
										echo ['?', 'Vo', 'Gu', 'Ba', 'Dr', 'Ky', '?', 'Rd'][$musician['position']].'. ';
										echo '</span>';

										// Name
										echo '<a href="/musicians/'.$musician['id'].'/'.$musician['friendly'].'/">';
										if(strlen($musician['as_name'])) {
											echo lang( $musician['as_romaji'] ?: $musician['as_name'], $musician['as_name'], 'hidden' );
											echo lang( $musician['as_romaji'] ? '&nbsp;<span class="any--weaken">('.$musician['as_name'].')</span>' : null, null, 'hidden' );
										}
										else {
											echo lang( $musician['romaji'] ?: $musician['name'], $musician['name'], 'hidden' );
											echo lang( $musician['romaji'] ? '&nbsp;<span class="any--weaken">('.$musician['name'].')</span>' : null, null, 'hidden' );
										}
										echo '</a>';

										echo '</li>';

									}
								}

								echo '</ul>';
							}

							// Artist URLs
							if(is_array($entry['artist']['urls']) && !empty($entry['artist']['urls'])) {
								echo '<div class="interview__urls any--flex">';
								foreach($entry['artist']['urls'] as $url) {
									if( !is_numeric($url['musician_id']) && ($url['type'] == 1 || $url['platform']) ) {
										echo '<a class="a--padded '.($url['type'] == 1 ? 'a--outlined' : null).'" href="'.$url['content'].'" rel="nofollow" target="_blank">';
										echo $url['type'] == 1 ? 'official' : '<span class="symbol--standalone symbol__'.$url['platform'].'"></span>';
										echo '</a>';
									}
								}
								echo '</div>';
							}
						?>
					</div>
				</aside>
				
				<!-- Interivew content -->
				<div class="entry__content entry__main-column interview__center">

					<div class="text text--centered <?= $entry_is_feature ? 'entry--interview' : null; ?>">
						<?php
							echo $entry['content'];

							if($entry['sources']) {
								preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $entry['sources'], $twitter_matches);

								if(is_array($twitter_matches) && !empty($twitter_matches)) {
									for($i=0; $i<count($twitter_matches[0]); $i++) {
										$entry['sources'] = str_replace($twitter_matches[1][$i], '['.$twitter_matches[1][$i].'](https://twitter.com/'.$twitter_matches[2][$i].'/)', $entry['sources']);
									}
								}

								$sources = $entry['sources'];
								$sources = explode("\n", $sources);
								$sources = array_filter($sources);
								$sources = (count($sources) > 1 ? '* ' : null).implode("\n* ", $sources);
								$sources = $markdown_parser->parse_markdown($sources);
								$sources = str_replace('<ul class="ul--bulleted">', '<ul class="text text--outlined text--notice entry__sources">', $sources);
								?>
									<h5 style="margin-top: 3rem; width: 100%;">
										Sources
									</h5>
									<?= $sources; ?>
								<?php
							}

							if($entry['supplemental']) {
								preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $entry['supplemental'], $twitter_matches);

								if(is_array($twitter_matches) && !empty($twitter_matches)) {
									for($i=0; $i<count($twitter_matches[0]); $i++) {
										$entry['supplemental'] = str_replace($twitter_matches[1][$i], '['.$twitter_matches[1][$i].'](https://twitter.com/'.$twitter_matches[2][$i].'/)', $entry['supplemental']);
									}
								}

								$supplemental = $entry['supplemental'];
								$supplemental = explode("\n", $supplemental);
								$supplemental = array_filter($supplemental);
								$supplemental = implode("\n", $supplemental);
								$supplemental = preg_replace('/'.'^([^*])'.'/m', '* $1', $supplemental);
								$supplemental = $markdown_parser->parse_markdown($supplemental);
								$supplemental = str_replace('<ul class="ul--bulleted">', '<ul class="text text--outlined text--notice entry__sources">', $supplemental);
								?>
									<h5 style="margin-top: 3rem; width: 100%;">
										Links 
									</h5>
									<?= $supplemental; ?>
								<?php
							}
						?>
					</div>

					<?php
						// If entry has associated images, see if we need a separate image gallery
						if(is_array($entry['images']) && !empty($entry['images'])) {

							// Copy images array and reset keys
							$image_gallery = array_values($entry['images']);
							$num_images = count($entry['images']);

							// For each image, if already used in blog, unset from image gallery
							for($i=0; $i<$num_images; $i++) {
								if(strpos($entry['content'], '/images/'.$image_gallery[$i]['id']) !== false || $entry['image_id'] === $image_gallery[$i]['id']) {
									unset($image_gallery[$i]);
								}
							}

							// If any images remain in image gallery, display them
							if(is_array($image_gallery) && !empty($image_gallery)) {
								?>
									<h3>
										<?= lang('Other images', 'イメージギャラリー', 'div'); ?>
									</h3>
									<div class="text text--outlined">
										<div class="entry__thumbnails">
											<?php
												foreach($image_gallery as $image) {
													?>
														<a class="entry__thumbnail-link" href="<?= '/images/'.$image['id'].'.'.$image['extension']; ?>" target="_blank">
															<img class="entry__thumbnail" src="<?= '/images/'.$image['id'].'.thumbnail.'.$image['extension']; ?>" />
														</a>
													<?php
												}
											?>
										</div>
									</div>
								<?php
							}
						}
					?>
				</div>

			<div class="interview__spacer"></div>
				
			</div>
		</article>
	<?php
	
	include('../comments/partial-comments.php');
	render_default_comment_section('blog', $entry['id'], $entry['comments'], $markdown_parser);
}