<?php
	
	include_once('../lists/function-render_lists.php');
	include_once("../php/class-parse_markdown.php");
	$markdown_parser = new parse_markdown($pdo);

$page_description =
	'Tracklist &amp; reviews for 「'.($release['romaji'] ?: $release['name']).'」 by '.$release['artist']['quick_name'].'. '.
	$release['artist']['name'].'「'.$release['name'].'」曲・情報・レビュー';
	
	script([
		'/lists/script-list.js',
	]);
	
	if(!empty($release)) {
		include_once("../releases/head.php");
		
		$release['images'] = is_array($release['images']) ? $release['images'] : [];
		
		// If images set, grab main image and separate from rest, and also set SNS image
		if(!empty($release['images']) && is_numeric($release['image_id'])) {
			$release['image'] = $release['images'][$release['image_id']];
			
			// Set page image to release cover
			$page_image = 'https://vk.gy/images/'.$release['image']['id'].'.opengraph.'.$release['image']['extension'];
			
			unset($release['images'][$release['image_id']]);
			
			$release['images'] = array_values($release['images']);
		}
		
		// If no image, set page title to artist image
		else {
			$page_image = 'https://vk.gy/artists/'.$release['artist']['friendly'].'/main.opengraph.jpg';
		}
		
		background("/artists/".$release["artist"]["friendly"]."/main.large.jpg");
		
		style([
			'/releases/style-page-id.css',
			'/releases/style-partial-tracklist.css'
		]);
		
		script([
			"/scripts/script-rateAlbum.js",
			"/releases/script-page-id.js"
		]);
			
		breadcrumbs([
			$release["artist"]["quick_name"] => "/releases/".$release["artist"]["friendly"]."/",
			$release["quick_name"] => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/"
		]);
		
		if($_SESSION["is_signed_in"]) {
			subnav([
				"Edit" => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/edit/"
			]);
		}
		
		$pageTitle = $release["quick_name"]." - ".$release["artist"]["quick_name"];
		
		$artist = $release['artist'];
		include('../artists/head.php');
		
		subnav([
			'Edit release' => '/releases/'.$release['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/edit/'.(is_numeric($_GET['prev_next_artist']) ? '&prev_next_artist='.sanitize($_GET['prev_next_artist']) : null),
		], 'interact', true);
		
		if(is_array($release["prev_next"]) && !empty($release["prev_next"])) {
			foreach($release["prev_next"] as $link) {
				subnav([
					[
						'text' => lang($link['romaji'] ?: $link['name'], $link['name'], 'hidden'),
						'url' => $link['url'],
						'position' => $link['type'] === 'next' ? 'right' : 'left',
					],
				], 'directional');
			}
		}
		
		?>
			<div class="col c1" itemscope itemtype="http://schema.org/MusicAlbum" data-url="<?php echo "https://vk.gy/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/"; ?>">
				<span itemtype="byArtist" content="<?php echo $release["artist"]["quick_name"]; ?>" data-artist="<?php echo $release["artist"]["name"]; ?>" data-artistsort="<?php echo $release["artist"]["romaji"]; ?>"></span>
			</div>
			
			<?php
				// Hide article if removed, unless VIP user
				if($release_is_removed <= $_SESSION['is_vip']) {
					if($release_is_removed) {
						?>
							<div class="col c1">
								<div>
									<div class="text text--outlined text--error symbol__vip">
										This article has been locked, and is only viewable to VIP members. Please use discretion.
									</div>
								</div>
							</div>
						<?php
					}
					
					// If is omnibus (etc) but was reached by clicking prev/next in an artist's disco, show notice
					if($needs_traversal_notice) {
						?>
							<div class="col c1">
								<div>
									<div class="text text--outlined text--notice symbol__help">
										<?php
											$traversal_artist_url = '/releases/'.$traversal_artist['friendly'].'/';
											$traversal_reset_url = '/releases/'.$release['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/';
											echo lang(
												'The &ldquo;previous/next release&rdquo; links above are based on <a class="artist" data-name="'.$traversal_artist['name'].'" href="'.$traversal_artist_url.'">'.($traversal_artist['romaji'] ?: $traversal_artist['name']).'</a>\'s discography. <a class="symbol__next" href="'.$traversal_reset_url.'">Reset?</a>',
												'上記の「戻る/進む」リンクは、<a class="artist" data-name="'.$traversal_artist['name'].'" href="'.$traversal_artist_url.'">'.$traversal_artist['name'].'</a>のディスコグラフィーに基づいています。 <a class="symbol__next" href="'.$traversal_reset_url.'">リセットする?</a>',
												'hidden'
											);
										?>
									</div>
								</div>
							</div>
						<?php
					}
					
					?>
						<div class="col c3-AAB">
							<div>
								<div>
									<div class="text any--flex release__head" data-coverurl="<?php echo $release['image'] ? "https://vk.gy".$release['image']['url'] : null; ?>">
										<?php
											if($release['image']) {
												?>
													<a class="release__image-link <?php echo $release['image']['is_exclusive'] ? "release__image--exclusive" : null; ?>" href="<?php echo $release['image']['url']; ?>" target="_blank">
														<img alt="<?php echo $release["artist"]["quick_name"]." - ".$release["quick_name"]; ?>" class="release__image" src="<?php echo preg_replace("/"."\.(\w+)$"."/", ".medium.$1", $release['image']['url']); ?>" />
													</a>
												<?php
											}
										?>
										
										<div class="any--flex-grow">
											<?php
												if($release['artist']['display_name']) {
													?>
														<h3>
															<span class="any__note"><?= lang('as', '名義', 'hidden'); ?></span>
															<a itemprop="creditedTo" class="symbol__artist" href="/artists/<?php echo $release["artist"]["friendly"]; ?>/">
																<?= $release['artist']['display_romaji'] ? lang($release['artist']['display_romaji'], $release['artist']['display_name'], 'parentheses') : $release['artist']['display_name']; ?>
															</a>
														</h3>
													<?php
												}
											?>
											<div class="h2">
												<div class="any--en">
													<span itemprop="name" data-album="<?php echo $release["name"]." ".($release["press_name"] ?: null)." ".($release["type_name"] ?: null); ?>" data-albumsort="<?php echo $release["romaji"] ? $release["romaji"]." ".($release["press_romaji"] ?: $release["press_name"])." ".($release["type_romaji"] ?: $release["type_name"]) : null; ?>">
														<?php
															echo $release["romaji"] ?: $release["name"];
															
															if($release["press_name"]) {
																?>
																	<span class="any--weaken a--outlined"><?php echo $release["press_romaji"] ?: $release["press_name"]; ?></span>
																<?php
															}
															
															if($release["type_name"]) {
																?>
																	<span class="any--weaken a--outlined"><?php echo $release["type_romaji"] ?: $release["type_name"]; ?></span>
																<?php
															}
														?>
													</span>
													
													<?php
														if($release["romaji"] || $release["press_romaji"] || $release["type_romaji"]) {
															?>
																<br />
																<span class="any--jp any--weaken" itemprop="alternateName">
																	<?php
																		echo $release["name"]." ".$release["press_name"]." ".$release["type_name"];
																	?>
																</span>
															<?php
														}
													?>
												</div>
												<span class="any--ja any--hidden">
													<?php
														echo $release["name"];
														
														if($release["press_name"]) {
															?>
																<span class="any--weaken a--outlined"><?php echo $release["press_name"]; ?></span>
															<?php
														}
														
														if($release["type_name"]) {
															?>
																<span class="any--weaken a--outlined"><?php echo $release["type_name"]; ?></span>
															<?php
														}
													?>
												</span>
											</div>
											<div class="input__row">
												<div class="input__group data__item" data-year="<?php echo substr($release["date_occurred"], 0, 4); ?>" data-date="<?php echo $release["date_occurred"]; ?>">
													<div>
														<h5>
															Release date
														</h5>
														<?php
															if(strlen($release["date_occurred"]) > 0) {
																foreach(explode("-", $release["date_occurred"]) as $d => $date_chunk) {
																	echo '<a class="a--inherit" href="https://vk.gy/search/releases/?start_date='.$prev_date_chunk.$date_chunk.'#result">'.$date_chunk.'</a>';
																	echo $d < 2 ? "-" : null;
																	$prev_date_chunk .= $date_chunk."-";
																}
															}
														?>
													</div>
												</div>
												<?php
													foreach(["price", "upc", "medium", "format"] as $data_section) {
														if(!empty($release[$data_section])) {
															?>
																<div class="input__group data__item">
																	<div>
																		<h5>
																			<?php echo str_replace('upc', 'catalog num', $data_section); ?>
																		</h5>
																		<?php
																			if($data_section === "upc") {
																				preg_match("/"."^([^ -]+)(.*)$"."/", $release["upc"], $upc_match);
																				
																				if($upc_match[1]) {
																					echo '<a class="a--inherit" href="/search/releases/?upc='.$upc_match[1].'#result">'.$upc_match[1].'</a>';
																					echo $upc_match[2];
																				}
																			}
																			elseif($data_section === "medium") {
																				if(is_array($release["medium"]) && !empty($release["medium"])) {
																					foreach($release["medium"] as $m => $medium) {
																						echo '<a class="a--inherit" href="/search/releases/?medium='.$medium['friendly'].'#result">'.lang($medium['romaji'] ?: $medium['name'], $medium['name'], 'hidden').'</a>';
																						echo $m < (count($release["medium"]) - 1) ? ", " : null;
																					}
																				}
																			}
																			elseif($data_section === 'format') {
																				if(strlen($release['format_name'])) {
																					echo '<a class="a--inherit" href="/search/releases/?format='.$release['format'][0]['friendly'].'#result">'.lang($release['format_romaji'] ?: $release['format_name'], $release['format_name'], 'hidden').'</a>';
																				}
																				else {
																					if(is_array($release['format']) && !empty($release['format'])) {
																						foreach($release['format'] as $f => $format) {
																							echo '<a class="a--inherit" href="/search/releases/?format='.$format['friendly'].'#result">'.lang($format['romaji'] ?: $format['name'], $format['name'], 'hidden').'</a>';
																							echo $f < (count($release['format']) - 1) ? ', ' : null;
																						}
																					}
																				}
																			}
																			else {
																				echo is_array($release[$data_section]) ? implode(", ", array_filter($release[$data_section])) : $release[$data_section];
																			}
																		?>
																	</div>
																</div>
															<?php
														}
													}
												?>
												
											</div>
											
											<div class="input__row">
												<div class="input__group data__item">
													<label class="input__label">Lists</label>
													
													<input class="list__choice input__choice" id="release-owned" type="checkbox" <?= $release['is_owned'] ? 'checked' : null; ?> />
													<label class="input__checkbox" data-list-id="-1" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="release-owned">
														<span class="symbol__checkbox--unchecked" data-role="status">own</span>
													</label>
													
													<input class="list__choice input__choice" id="release-wanted" type="checkbox" <?= $release['is_wanted'] ? 'checked' : null; ?> />
													<label class="input__checkbox" data-list-id="-2" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="release-wanted">
														<span class="symbol__checkbox--unchecked" data-role="status">want</span>
													</label>
													
													<input class="list__choice input__choice" id="release-sold" type="checkbox" <?= $release['is_for_sale'] ? 'checked' : null; ?> />
													<label class="input__checkbox" data-list-id="-3" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="release-sold">
														<span class="symbol__checkbox--unchecked" data-role="status">sell</span>
													</label>
													
													<span style="top: -5px;">
													<?= render_lists_dropdown([ 'item_id' => $release['id'], 'item_type' => 'release' ]); ?>
													</span>
													
												</div>
												
												<div class="collect__result text text--outlined text--notice symbol__help"></div>
											</div>
											<ul class="input__row">
												<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><span class="any--hidden" itemprop="ratingValue"><?php echo $release["rating"]; ?></span><span class="any--hidden" itemprop="reviewCount">1</span></span>
												
												<div class="input__group data__item" data-year="<?php echo substr($release["date_occurred"], 0, 4); ?>" data-date="<?php echo $release["date_occurred"]; ?>">
													<div>
														<h5>
															Avg rating
														</h5>
														<?php
															for($i = 1; $i <= 5; $i++) {
																$class  = "symbol__star--";
																$class .= $i <= round($release["rating"]) ? "full" : "empty";
																?><span class="rate__item <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>" style="font-size:1.5rem;"></span><?php
															}
														?>
													</div>
												</div>
												<div class="input__group data__item" data-year="<?php echo substr($release["date_occurred"], 0, 4); ?>" data-date="<?php echo $release["date_occurred"]; ?>">
													<div>
														<h5>
															Your rating
														</h5>
														<?php
															for($i = 1; $i <= 5; $i++) {
																$class  = "symbol__star--";
																$class .= $i <= round($release["user_rating"]) ? "full" : "empty";
																?><a class="rate__item rate__link <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>" href="" style="font-size:1.5rem;"></a><?php
															}
														?>
													</div>
												</div>
											</ul>
											
											<div class="input__row">
												<div class="data__item">
													<h5>
														Buy
													</h5>
													
													<a class="release__buy" href="<?= tracking_link( 'amazon', $release['artist']['name'].' '.$release['name'], 'release page' ); ?>" rel="nofollow" target="_blank">
														<img src="/releases/amazon.png" style="height:1rem;opacity:1;bottom:-2px;" /> Search Amazon<sup>JP</sup>
													</a>
													&nbsp;
													<a class="release__buy" href="<?= tracking_link( 'cdjapan', [ $release['quick_name'], $release['upc'] ], 'release page' ); ?>" target="_blank">
														<img src="/releases/cdj.gif" style="height:1rem;opacity:1;" /> <?= $release["upc"] ? 'Buy at' : 'Search'; ?> CDJapan
													</a>
													&nbsp;
													<a class="release__buy" href="<?= tracking_link( 'rarezhut', ( $release['artist']['romaji'] ?: $release['artist']['name'] ).' '.$release['name'], 'release page' ); ?>" target="_blank">
														<img src="/releases/rh.gif" style="height:1rem;opacity:1;" /> Search RarezHut
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<div>
									<table class="text release__tracklist">
										<?php
											include_once('../php/function-render_component.php');
											
											ob_start();
												?>
													<tr class="release__disc">
														<th class="h4" colspan="3"><span class="track__symbol symbol__release"></span>{disc_name}</th>
													</tr>
												<?php
											$template_disc = ob_get_clean();
											
											ob_start();
												?>
													<tr class="release__section">
														<th class="h4" colspan="3"><span class="track__symbol symbol__section"></span>{section_name}</th>
													</tr>
												<?php
											$template_section = ob_get_clean();
											
											ob_start();
												?>
													<tr class="release__track {track_class}">
														<td class="track__num any--weaken">{track_num}.</td>
														<td class="track__artist {artist_class}"><a class="artist artist--no-symbol track--no-wrap" data-name="{artist_official_name}" data-quickname="{artist_quick_name}" href="/releases/{artist_friendly}/">{artist_name}</a></td>
														<td class="track__name" data-track="{track_official_name}">{track_name}</td>
													</tr>
												<?php
											$template_track = ob_get_clean();
											
											if(is_array($release['tracklist']['discs']) && !empty($release['tracklist']['discs'])) {
												foreach($release['tracklist']['discs'] as $disc_num => $disc) {
													
													// Show disc title
													if(count($release['tracklist']['discs']) > 1) {
														echo render_component($template_disc, [
															'disc_name' => strlen($disc['disc_name']) ? lang($disc['disc_romaji'], $disc['disc_name'], 'conditional_div') : 'Disc '.$disc_num
														]);
													}
													
													if(is_array($disc['sections']) && !empty($disc['sections'])) {
														foreach($disc['sections'] as $section_num => $section) {
															
															// Show section title
															if(count($disc['sections']) > 1) {
																echo render_component($template_section, [
																	'section_name' => strlen($section['section_name']) ? lang($section['section_romaji'], $section['section_name'], 'conditional_div') : 'Section '.$section_num
																]);
															}
															
															if(is_array($section['tracks']) && !empty($section['tracks'])) {
																foreach($section['tracks'] as $track_num => $track) {
																	
																	// If only track and name is "contents unknown", set class to hide track numbering
																	$track_class = $track['name'] === '(contents unknown)' && count($section['tracks']) === 1 ? 'track--hide-number' : null;
																	
																	// Save official name
																	$track_official_name = $track['name'];
																	
																	// If omnibus-like or has display name, show artist name, otherwise show nothing
																	$artist_official_name = $track['artist']['display_name'] ?: $track['artist']['name'];
																	$artist_name = null;
																	if(strlen($track['artist']['display_name']) || $track['artist']['id'] != $release['artist']['id']) {
																		$artist_name = strlen($track['artist']['display_name']) ? lang($track['artist']['display_romaji'], $track['artist']['display_name'], 'conditional_div') : lang($track['artist']['romaji'], $track['artist']['name'], 'conditional_div');
																	}
																	
																	// Wrap (notes) in track names with spans so they can be styled
																	// First flip note array and work backward
																	// And if (notes) provided in Japanese name, but not romaji, copy+paste
																	if(is_array($track['notes']) && !empty($track['notes'])) {
																		$track['notes'] = array_reverse($track['notes']);
																		
																		foreach($track['notes'] as $note) {
																			if(strlen($track['romaji']) && !strlen($note['romaji'])) {
																				$note['romaji'] = $note['name'];
																			}
																			
																			foreach(['name', 'romaji'] as $key) {
																				if(strlen($track[$key])) {
																					$note[$key] = '<span class="any__note"><span class="track__parenth">(</span>'.$note[$key].'<span class="track__parenth">)</span></span>';
																					
																					if(is_numeric($note[$key.'_offset']) && is_numeric($note[$key.'_length'])) {
																						$track[$key] = substr_replace($track[$key], $note[$key], $note[$key.'_offset'], $note[$key.'_length']);
																					}
																					else {
																						$track[$key] .= $note[$key];
																					}
																				}
																			}
																		}
																	}
																	
																	echo render_component($template_track, [
																		'track_class' => $track_class,
																		'track_num' => $track_num,
																		'artist_official_name' => $artist_official_name,
																		'artist_name' => $artist_name,
																		'artist_quick_name' => $track['artist']['display_name'] ? ($track['artist']['display_romaji'] ?: $track['artist']['display_name']) : ($track['artist']['romaji'] ?: $track['artist']['name']),
																		'artist_friendly' => strlen($track['artist']['friendly']) ? $track['artist']['friendly'] : $release['artist']['friendly'],
																		'artist_class' => !strlen($artist_name) ? 'track--no-artist' : null,
																		'track_official_name' => $track_official_name,
																		'track_name' => lang($track['romaji'], $track['name'], 'conditional_div')
																	]);
																}
															}
														}
													}
												}
											}
											else {
												?>
													<tr><td class="symbol__error any--weaken-color" colspan="3">An error occurred. Please refresh.</td></tr>
												<?php
											}
										?>
									</table>
									
									<?php
										if(!empty($release["notes"])) {
											?>
												<h3>
													Notes
												</h3>
												<div class="text text--outlined">
													<ul>
														<?php
															if(is_array($release["notes"])) {
																foreach($release["notes"] as $note) {
																	?>
																		<li>
																			<?php
																				echo $markdown_parser->parse_markdown($note, true);
																			?>
																		</li>
																	<?php
																}
															}
														?>
													</ul>
												</div>
											<?php
										}

										if(is_array($release["credits"])) {
											?>
												<h3>
													Credits
												</h3>
												<div class="text text--outlined">
													<table>
														<?php
															foreach($release["credits"] as $credit) {
																?>
																	<tr>
																		<?php
																			if(!empty($credit["title"])) {
																				$title_max_width = str_replace(["\r", "\n"], "", $credit["title"]);
																				$title_max_width = html_entity_decode($title_max_width);
																				$title_max_width = mb_strlen($title_max_width, "utf-8");
																				$title_max_width = strpos($credit["title"], "&#") !== false ? $title_max_width * 2 : $title_max_width;
																				?>
																					<td class="credits__title-container">
																						<span class="any__note credits__title" style="width: <?php echo $title_max_width; ?>ch;"><?php echo $credit["title"]; ?></span>
																					</td>
																				<?php
																			}
																		?>
																		<td class="credits__credit" colspan="<?php echo !empty($credit["title"]) ? "1" : "2"; ?>">
																			<?php
																				echo $credit["credit"] ?: "&nbsp;";
																			?>
																		</td>
																	</tr>
																<?php
															}
														?>
													</table>
												</div>
											<?php
										}
									?>
								</div>

								<?php
									include('../comments/partial-comments.php');
									render_default_comment_section('release', $release['id'], $release['comments'], $markdown_parser);
								?>
							</div>
							<div>
								<table class="text text--outlined any--weaken marketing__container">
									<tr class="marketing__row flex">
										<td class="marketing__cell">
											<h5>
												Sales venue
											</h5>
											<?php
												if(is_array($release['venue_limitation']) && !empty($release['venue_limitation'])) {
													foreach($release['venue_limitation'] as $v => $venue) {
														echo '<a href="/search/releases/?venue_limitation='.$venue['friendly'].'#result">'.lang($venue['romaji'] ?: $venue['name'], $venue['name'], 'hidden').'</a>';
														echo $v < (count($release['venue_limitation']) - 1) ? ', ' : null;
													}
												}
												else {
													echo '<a href="/search/releases/?venue_limitation=everywhere#result">'.lang('everywhere', '全国ショップ', 'hidden').'</a>';
												}
											?>
										</td>
										<?php
											if(is_array($release['press_limitation_name']) && !empty($release["press_limitation_name"])) {
												?>
													<td class="marketing__cell">
														<h5>
															Press limitation
														</h5>
														<?php
															foreach($release['press_limitation_name'] as $p => $press) {
																echo '<a href="/search/releases/?press_limitation_name='.$press['friendly'].'#result">'.lang($press['romaji'] ?: $press['name'], $press['name'], 'hidden').'</a>';
																echo $v < (count($release['press_limitation_name']) - 1) ? ', ' : null;
															}
														?>
													</td>
												<?php
											}
											if(!empty($release["press_limitation_num"])) {
												?>
													<td class="marketing__cell">
														<h5>
															Units produced
														</h5>
														<?php echo $release["press_limitation_num"]; ?>
													</td>
												<?php
											}
										?>
									</tr>
									<?php
										if(!empty($release["label"]) || !empty($release["publisher"]) || !empty($release["distributor"])) {
											?>
												<tr class="marketing__row flex">
													<?php
														foreach(["label", "publisher", "distributor"] as $company_type) {
															if(!empty($release[$company_type])) {
																?>
																	<td class="marketing__cell">
																		<h5>
																			<?php echo $company_type; ?>
																		</h5>
																		<?php
																			foreach($release[$company_type] as $company) {
																				?>
																					<a class="a--inherit" href="/labels/<?php echo $company["friendly"]; ?>/"><?php echo $company["quick_name"].($company["name"] !== $company["quick_name"] ? " (".$company["name"].")" : ""); ?></a>
																				<?php
																			}
																		?>
																	</td>
																<?php
															}
														}
													?>
												</tr>
											<?php
										}
										if(!empty($release["marketer"]) || !empty($release["manufacturer"]) || !empty($release["organizer"])) {
											?>
												<tr class="marketing__row flex">
													<?php
														foreach(["marketer", "manufacturer", "organizer"] as $company_type) {
															if(!empty($release[$company_type])) {
																?>
																	<td class="marketing__cell">
																		<h5>
																			<?php echo $company_type; ?>
																		</h5>
																		<?php
																			foreach($release[$company_type] as $company) {
																				?>
																					<a class="a--inherit" href="/labels/<?php echo $company["friendly"]; ?>/"><?php echo $company["quick_name"].($company["name"] !== $company["quick_name"] ? " (".$company["name"].")" : ""); ?></a>
																				<?php
																			}
																		?>
																	</td>
																<?php
															}
														}
													?>
												</tr>
											<?php
										}
									?>
								</table>
								
								<?php
									if($_SESSION["is_signed_in"] && is_numeric($_SESSION["user_id"])) {
										$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
										$stmt_check = $pdo->prepare($sql_check);
										$stmt_check->execute([ $_SESSION["user_id"] ]);
										$is_vip = $stmt_check->fetchColumn();
									}
									
									$item_type = 'release';
									include('../tags/partial-tags.php');
									include('../tags/partial-add.php');
								?>
								
								<h3>
									Contributors and updates
								</h3>
								<div class="text text--outlined">
									<h5>
										Last updated
									</h5>
									<?php echo substr($release['date_edited'], 0, 10); ?>
									<span class="any--weaken-color"><?php echo substr($release['date_edited'], 11, 8); ?></span>

									<hr />

									<ul>
										<?php
											
											// Grab user IDs of latest updates
											$sql_editors = 'SELECT user_id, MAX(date_occurred) AS date_occurred FROM edits_releases WHERE release_id=? GROUP BY user_id ORDER BY date_occurred DESC';
											$stmt_editors = $pdo->prepare($sql_editors);
											$stmt_editors->execute([ $release['id'] ]);
											$rslt_editors = $stmt_editors->fetchAll();
					
											$num_editors = is_array($rslt_editors) ? count($rslt_editors) : 0;

											if($rslt_editors) {
												for($i=0; $i<$num_editors; $i++) {
													
													// Get user info
													$rslt_editors[$i]['user'] = $access_user->access_user([ 'id' => $rslt_editors[$i]['user_id'], 'get' => 'name' ]);
													
													?>
														<li>
															<a class="user" data-icon="<?= $rslt_editors[$i]['user']['icon']; ?>" data-is-vip="<?= $rslt_editors[$i]['user']['is_vip']; ?>" href="<?= $rslt_editors[$i]['user']['url']; ?>"><?= $rslt_editors[$i]['user']['username']; ?></a>
														</li>
													<?php
												}
											}
										?>
									</ul>
								</div>

								<h3>
									User collections
								</h3>
								<div class="text text--outlined">
									<?php
										if((is_array($rslt_collections) && !empty($rslt_collections)) || (is_array($rslt_wants) && !empty($rslt_wants))) {
											if(is_array($rslt_collections) && !empty($rslt_collections)) {
												?>
													<ul>
														<li>
															<h5>
																Owned by
															</h5>
														</li>
														<?php
															foreach($rslt_collections as $collection_key => $collection) {
																?>
																	<li>
																		<a class="user" data-icon="<?= $rslt_collections[$collection_key]['user']['icon']; ?>" data-is-vip="<?= $rslt_collections[$collection_key]['user']['is_vip']; ?>" href="<?= $rslt_collections[$collection_key]['user']['url']; ?>"><?= $rslt_collections[$collection_key]['user']['username']; ?></a>
																	</li>
																<?php
															}
														?>
													</ul>
												<?php
											}

											if(is_array($rslt_wants) && !empty($rslt_wants)) {
												?>
													<ul>
														<li>
															<h5>
																Wanted by
															</h5>
														</li>
														<?php
															foreach($rslt_wants as $want) {
																$want['user'] = $access_user->access_user([ 'id' => $want['user_id'], 'get' => 'name' ]);
																?>
																	<li>
																		<a class="user" data-icon="<?= $want['user']['icon']; ?>" data-is-vip="<?= $want['user']['is_vip']; ?>" href="<?= $want['user']['url']; ?>"><?= $want['user']['username']; ?></a>
																	</li>
																<?php
															}
														?>
													</ul>
												<?php
											}
										}
										else {
											?>
												<span class="symbol__error"></span> <span class="any--weaken">No users own this release</span>
											<?php
										}
									?>
								</div>

								<h3>
									For sale by
								</h3>
								<div class="text text--outlined">
									<ul>
										<?php
											if($is_for_sale) {
												foreach($rslt_collections as $collection) {
													if($collection["is_for_sale"]) {
														?>
															<li>
																<a class="user" data-icon="<?= $collection['user']['icon']; ?>" data-is-vip="<?= $collection['user']['is_vip']; ?>" href="<?= $collection['user']['url']; ?>"><?= $collection['user']['username']; ?></a>
															</li>
														<?php
													}
												}
											}
										?>
										<li>
											<a href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q=<?php echo $release["upc"] ? str_replace(["-000", "-00", "-0"], "-", $release["upc"]) : str_replace(" ", "+", $release["quick_name"]); ?>" target="_blank">Search at CDJapan</a>
										</li>
									</ul>
								</div>

								<?php
									if(is_array($release["images"]) && !empty($release['images'])) {
										?>
											<h3>
												Related images
											</h3>
											<div class="text text--outlined release__images">
												<?php
													foreach($release["images"] as $image) {
															?>
																<a href="/images/<?php echo $image["id"].($image["friendly"] ? "-".$image["friendly"] : null).".".$image["extension"]; ?>" target="_blank">
																	<img alt="<?php echo $image["description"]; ?>" class="release__thumbnail" src="/images/<?php echo $image["id"].($image["friendly"] ? "-".$image["friendly"] : null).".thumbnail.".$image["extension"]; ?>" />
																</a>
															<?php
													}
												?>
											</div>
										<?php
									}
								?>

								<?php
									$text = [
										$release["cover"] ? "https://vk.gy".$release["cover"] : null,
										"",
										$release["artist"]["romaji"] ? $release["artist"]["romaji"]." (".$release["artist"]["name"].")" : $release["artist"]["name"],
										"",
										$release["romaji"] ? $release["romaji"]." (".$release["name"].")" : $release["name"],
										$release["press_name"] ? ($release["press_romaji"] ? $release["press_romaji"]." (".$release["press_name"].")" : $release["press_name"]) : null,
										$release["type_name"] ? ($release["type_romaji"] ? $release["type_romaji"]." (".$release["type_name"].")" : $release["type_name"]) : null,
										$release["date_occurred"],
										"",
										"[Tracklist]"
									];

									if(is_array($release["tracklist"]["discs"])) {
										foreach($release["tracklist"]["discs"] as $disc) {
											$i = 0;
											$text[] = $disc["disc_romaji"] ? $disc["disc_romaji"]." (".$disc["disc_name"].")\n" : ($disc["disc_name"] ? $disc["disc_name"]."\n" : null);

											foreach($disc["sections"] as $section) {
												foreach($section["tracks"] as $track) {
													$i++;

													$tmp_track = ($i < 10 ? "0" : null).$i.". ";

													if($track["artist"]["id"] != $release["artist"]["id"] || ($track["artist"]["id"] === $release["artist"]["id"] && $track["artist"]["display_name"] && $track["artist"]["display_name"] != $release["artist"]["name"])) {
														$tmp_track .= "[";
														$tmp_track .= $track["artist"]["display_romaji"] ? $track["artist"]["display_romaji"]." (".$track["artist"]["display_name"].")" : ($track["artist"]["display_name"] ?: ($track["artist"]["romaji"] ? $track["artist"]["romaji"]." (".$track["artist"]["name"].")" : $track["artist"]["name"]));
														$tmp_track .= "] ";
													}

													$tmp_track .= ($track["romaji"] ? $track["romaji"]." (".$track["name"].")" : $track["name"]);

													$text[] = $tmp_track;
												}
											}
										}
									}

									$text[] = "";
									$text[] = "[Info]";
									$text[] = "https://vk.gy/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/";
								?>

								<h3>
									Copy release information
								</h3>
								<div class="text text--outlined">
									<?php
										if($_SESSION["is_signed_in"]) {
											?>
												<div class="input__row">
													<div class="input__group any--flex-grow">
														<textarea class="input__textarea any--flex-grow" id="release__share" style="height: 2rem;"><?php foreach($text as $i => $t) { echo $t.($i !== (count($text) - 1) && $t !== null ? "\r\n" : null); } ?></textarea>
													</div>
												</div>
												<div class="input__row">
													<div class="input__group any--flex-grow">
														<button class="release__share-link any--flex-grow">Copy to clipboard</a>
													</div>
												</div>
											<?php
										}
										else {
											echo '<span class="symbol__error">Please <a class="a--inherit" href="/account/">sign in</a> to easily share release information.';
										}
									?>
								</div>

							</div>
						</div>
					<?php
				}
				else {
					?>
						<div class="col c1">
							<div>
								<div class="text text--outlined text--error symbol__vip">
									Sorry, this article has been removed.
								</div>
							</div>
						</div>
					<?php
				}
			?>
		<?php
	}
?>