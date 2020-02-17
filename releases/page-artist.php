<?php
	if(!empty($artist) && !empty($releases)) {
		
		background("/artists/".$artist["friendly"]."/main.large.jpg");
		
		style('/releases/style-page-artist.css');
		style('/releases/style-partial-tracklist.css');
		
		script([
			"/scripts/external/script-tinysort.js",
			"/scripts/script-rateAlbum.js",
			"/releases/script-page-artist.js"
		]);
		
		$pageTitle  = $artist["quick_name"]." discography";
		$pageTitle .= " | ".$artist["name"]."ディスコグラフィ";
		
		include('../artists/head.php');
		
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
		
		?>
			<div class="col c1">
				
				<div>
					<div class="any--flex release__control-container any--margin">
						<div>
							<a class="release__control input__checkbox-label input__checkbox-label--selected symbol__down-caret" data-sort="date" data-dir="desc" href="">Date</a>
							<a class="release__control input__checkbox-label symbol__up-caret" data-sort="name" data-dir="asc" href="">A-Z</a>
						</div>
						<div>
							<label class="release__control input__checkbox-label input__checkbox-label--selected" data-filter for="all"><?= lang('all', '全て', 'hidden'); ?></label>
							<label class="release__control input__checkbox-label" data-filter for="cd">CD</label>
							<label class="release__control input__checkbox-label" data-filter for="dvd"><?= lang('video', '映像', 'hidden'); ?></label>
							<label class="release__control input__checkbox-label" data-filter for="other"><?= lang('others', 'その他', 'hidden'); ?></label>
						</div>
					</div>
					
					<input class="any--hidden" id="all" name="filter" value="all" type="radio" checked />
					<input class="any--hidden" id="cd" name="filter" value="cd" type="radio" />
					<input class="any--hidden" id="dvd" name="filter" value="dvd" type="radio" />
					<input class="any--hidden" id="other" name="filter" value="other" type="radio" />
					
					<?php
						foreach($releases as $release) {
							
							// Set array of media into one medium
							$release_medium = is_array($release['medium']) ? implode(' ', array_column($release['medium'], 'friendly')) : null;
							
							// Determine if artist needs name displayed, and if that's due to omnibus or pseudonym
							$release_is_omnibus = $release['artist_id'] != $artist['id'] ? true : false;
							$release_needs_artist = $release_is_omnibus || (strlen($release['artist']['display_name']) && $release['artist']['display_name'] != $artist['name']) ? true : false;
							$release_needs_as = strlen($release['artist']['display_name']) ? true : false;
							
							// If release needs artist name displayed (omnibus/pseudonym), format it correctly
							if($release_needs_artist) {
								if(strlen($release['artist']['display_name'])) {
									$release_artist_display = $release['artist']['display_romaji'] ? lang(  $release['artist']['display_romaji'], $release['artist']['display_name'], 'parentheses' ) : $release['artist']['display_name'];
								}
								else {
									$release_artist_display = $release['artist']['romaji'] ? lang( $release['artist']['romaji'], $release['artist']['name'], 'parentheses' ) : $release['artist']['name'];
									
									// If artist is omnibus, prevent "(omnibus) ((オムニバス))"
									if($release['artist']['friendly'] === 'omnibus') {
										$release_artist_display = str_replace( '<span class="any--en">(</span>(&#12458;&#12512;&#12491;&#12496;&#12473;)<span class="any--en">)</span>', '<span class="any--en"></span>(&#12458;&#12512;&#12491;&#12496;&#12473;)<span class="any--en"></span>', $release_artist_display );
									}
								}
							}
							
							// Set up release name display
							if($release['romaji'] || $release['press_romaji'] || $release['type_romaji']) {
								$release_display_romaji =
									($release['romaji'] ?: $release['name']).
									($release['press_name'] ? ' <span class="any--weaken a--outlined">'.($release['press_romaji'] ?: $release['press_name']).'</span>' : null ).
									($release['type_name'] ? ' <span class="any--weaken a--outlined">'.($release['type_romaji'] ?: $release['type_name']).'</span>' : null );
							}
							else {
								$release_display_romaji = null;
							}
							$release_display_name =
								($release['name']).
								($release['press_name'] ? ' <span class="any--weaken a--outlined">'.$release['press_name'].'</span>' : null ).
								($release['type_name'] ? ' <span class="any--weaken a--outlined">'.$release['type_name'].'</span>' : null );
							
							?>
							<div class="release__container" data-date="<?= $release['date_occurred']; ?>" data-name="<?= $release['friendly']; ?>" data-medium="<?= $media; ?>">
								<div class="h2">
									<?php
										if($release_needs_artist) {
											?>
												<div class="any--weaken release__pseudonym">
													<div class="h5"><?= $release_needs_as ? ''.lang('Released as', '名義', 'hidden').'' : null; ?></div>
													<a class="artist" href="<?= '/artists/'.$artist['friendly'].'/'; ?>"><?= $release_artist_display; ?></a>
												</div>
											<?php
										}
									?>
									<a href="<?= '/releases/'.$artist['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/'.($release['artist']['id'] != $artist['id'] ? '&prev_next_artist='.$artist['id'] : null); ?>">
										<?= $release_display_romaji ? lang($release_display_romaji, $release_display_name, 'div') : $release_display_name; ?>
									</a>
								</div>
								
								<div class="text flex <?= $release_is_omnibus ? 'text--outlined' : null; ?>">
									<table class="release__tracklist any--flex-grow">
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
																		'artist_quick_name' => $track['artist']['display_name'] ? ($track['artist']['display_romaji'] ?: $track['artist']['display_name']) : $track['artist']['romaji'] ?: $track['artist']['name'],
																		'artist_friendly' => $track['artist']['friendly'],
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
									<div class="any--weaken release__data">
											<?php
												if($release["image"]) {
													?>
														<a class="release__image-link" href="<?= $release["image"]['url']; ?>" target="_blank">
															<img alt="<?= $release["artist"]["quick_name"]." - ".$release["quick_name"]; ?>" class="release__image lazy" data-src="<?= preg_replace("/"."\.(\w+)$"."/", ".thumbnail.$1", $release["image"]['url']); ?>" />
														</a>
													<?php
												}
											?>
											<div class="a--padded a--outlined">
												<h5>
													Released
												</h5>
												<?php
													if(strlen($release["date_occurred"]) > 0) {
														foreach(explode("-", $release["date_occurred"]) as $d => $date_chunk) {
															echo '<a class="a--inherit" href="https://vk.gy/search/releases/?start_date='.$prev_date_chunk.$date_chunk.'#result">'.$date_chunk.'</a>';
															echo $d < 2 ? "-" : null;
															$prev_date_chunk .= $date_chunk."-";
														}
													}
													$prev_date_chunk = null;
												?>
											</div>
											<ul class="a--padded rate__container">
												<li>
													<?php
														for($i = 1; $i <= 5; $i++) {
															$class  = "symbol__star--";
															$class .= $i <= round($release["rating"]) ? "full" : "empty";
															?><span class="rate__item symbol--standalone <?= $class; ?>" data-release_id="<?= $release["id"]; ?>" data-score="<?= $i; ?>"></span><?php
														}
													?>
												</li>
												<li>
													<?php
														for($i = 1; $i <= 5; $i++) {
															$class  = "symbol__star--";
															$class .= $i <= round($release["user_rating"]) ? "full" : "empty";
															?><a class="rate__item rate__link symbol--standalone <?= $class; ?>" data-release_id="<?= $release["id"]; ?>" data-score="<?= $i; ?>" href=""></a><?php
														}
													?>
												</li>
											</ul>
											<?php
												if($_SESSION["loggedIn"]) {
													?>
														<div class="a--padded collect__container">
															<a class="collect collect__item any--flex any--flex-space-between <?= $release["is_owned"] ? "symbol__checked" : "symbol__unchecked"; ?>" data-action="own" data-id="<?= $release["id"]; ?>">Owned</a>
															<a class="collect collect__item any--flex any--flex-space-between <?= $release["is_wanted"] ? "symbol__checked" : "symbol__unchecked"; ?>" data-action="want" data-id="<?= $release["id"]; ?>">Wanted</a>
														</div>
													<?php
												}
											?>
											<a class="a--outlined a--padded symbol__arrow-right-circled" href="/releases/<?php echo $artist["friendly"]."/".$release["id"]."/".$release["friendly"]; ?>/">Details</a>
										</div>
								</div>
							</div>
						<?php
						}
					?>
				</div>
			</div>
		<?php
	}
?>