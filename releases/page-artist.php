<?php
	if(!empty($artist) && !empty($releases)) {
		
		background("/artists/".$artist["friendly"]."/main.large.jpg");
		
		style('/releases/style-page-artist.css');
		style('/releases/style-partial-tracklist.css');
		
		script([
			'/scripts/external/script-tinysort.js',
			'/scripts/script-rateAlbum.js',
			'/lists/script-list.js',
			'/releases/script-page-artist.js'
		]);
		
		$pageTitle  = $artist["quick_name"]." discography";
		$pageTitle .= " | ".$artist["name"]."ディスコグラフィ";

$page_description =
	'Visual kei band '.$artist['quick_name'].' full discography and album list. '.
	'ビジュアル系バンド「'.$artist['name'].'」のディスコグラフィ・アルバム・作品の一覧';
		
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
					
					<input class="any--hidden symbol--orphan-a" id="all" name="filter" value="all" type="radio" checked />
					<input class="any--hidden symbol--orphan-b" id="cd" name="filter" value="cd" type="radio" />
					<input class="any--hidden symbol--orphan-c" id="dvd" name="filter" value="dvd" type="radio" />
					<input class="any--hidden symbol--orphan-d" id="other" name="filter" value="other" type="radio" />
					
					<div class="any--flex release__control-container any--margin symbol--parent">
						<div>
							<a class="release__control input__radio input__radio--selected symbol__triangle symbol--down" data-sort="date" data-dir="desc" href="">Date</a>
							<a class="release__control input__radio symbol__triangle symbol--up" data-sort="name" data-dir="asc" href="">A-Z</a>
						</div>
						<div>
							<label class="release__control input__radio symbol--orphan-a input__radio--selected" data-filter for="all"><span class="symbol__unchecked"><?= lang('all', '全て', 'hidden'); ?></span></label>
							<label class="release__control input__radio symbol--orphan-b" data-filter for="cd"><span class="symbol__unchecked">CD</span></label>
							<label class="release__control input__radio symbol--orphan-c" data-filter for="dvd"><span class="symbol__unchecked"><?= lang('video', '映像', 'hidden'); ?></span></label>
							<label class="release__control input__radio symbol--orphan-d" data-filter for="other"><span class="symbol__unchecked"><?= lang('others', 'その他', 'hidden'); ?></span></label>
						</div>
					</div>
					
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
								
								<div class="text flex <?= $release_is_omnibus ? 'text--outlined' : null; ?>" style="max-width:100%;">
									<table class="release__tracklist " style="width:calc(100% - 100px - 1rem);">
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
															if( count($disc['sections']) > 1 && strlen($section['section_name']) ) {
																echo render_component($template_section, [
																	'section_name' => lang($section['section_romaji'], $section['section_name'], 'conditional_div'),
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
												if($_SESSION["is_signed_in"]) {
													?>
														<div class="release__lists">
															<label class="input__checkbox" data-list-id="-1" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="<?= 'release-owned-'.$release['id']; ?>">
																<input class="list__choice input__choice" id="<?= 'release-owned-'.$release['id']; ?>" type="checkbox" <?= $release['is_owned'] ? 'checked' : null; ?> />
																<span class="symbol__unchecked" data-role="status" style="margin-left:0;">own</span>
															</label>
															
															<label class="input__checkbox" data-list-id="-2" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="<?= 'release-wanted-'.$release['id']; ?>">
																<input class="list__choice input__choice" id="<?= 'release-wanted-'.$release['id']; ?>" type="checkbox" <?= $release['is_wanted'] ? 'checked' : null; ?> />
																<span class="symbol__unchecked" data-role="status" style="margin-left:0;">want</span>
															</label>
															
															<label class="input__checkbox" data-list-id="-3" data-item-id="<?= $release['id']; ?>" data-item-type="release" for="<?= 'release-sold-'.$release['id']; ?>">
																<input class="list__choice input__choice" id="<?= 'release-sold-'.$release['id']; ?>" type="checkbox" <?= $release['is_for_sale'] ? 'checked' : null; ?> />
																<span class="symbol__unchecked" data-role="status" style="margin-left:0;">sell</span>
															</label>
														</div>
													<?php
												}
											?>
											<div style="text-align:left;">
												<a class="release__buy" href="<?= tracking_link( 'cdjapan', [ $release['quick_name'], $release['upc'] ], 'discography page' ); ?>" target="_blank">
													<img src="/releases/cdj.gif" style="height:1.25rem;vertical-align:middle;" />
												</a>
												&nbsp;
												<a class="release__buy" href="<?= tracking_link( 'rarezhut', ( $release['artist']['romaji'] ?: $release['artist']['name'] ).' '.$release['name'], 'discography page' ); ?>" target="_blank">
													<img src="/releases/rh.gif" style="height:1rem;vertical-align:middle;" />
												</a>
											</div>
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