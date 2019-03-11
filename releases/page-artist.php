<?php
	if(!empty($artist) && !empty($releases)) {
		
		background("/artists/".$artist["friendly"]."/main.large.jpg");
		
		style("/releases/style-page-artist.css");
		
		script([
			"/scripts/external/script-tinysort.js",
			"/scripts/script-rateAlbum.js",
			"/releases/script-page-artist.js"
		]);
		
		$pageTitle  = $artist["quick_name"]." discography";
		$pageTitle .= " | ".$artist["name"]."ディスコグラフィ";
		
		
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
					<?php
						$access_artist->artist_card(["quick_name" => $artist["quick_name"], "name" => $artist["name"], "romaji" => $artist["romaji"], "friendly" => $artist["friendly"]], true);
					?>
				</div>
				
				<div>
					<div class="any--flex release__control-container any--margin">
						<div>
							<a class="release__control input__checkbox-label input__checkbox-label--selected symbol__down-caret" data-sort="date" data-dir="desc" href="">Date</a>
							<a class="release__control input__checkbox-label symbol__up-caret" data-sort="name" data-dir="asc" href="">A-Z</a>
						</div>
						<div>
							<label class="release__control input__checkbox-label input__checkbox-label--selected" data-filter for="all">All</label>
							<label class="release__control input__checkbox-label" data-filter for="cd">CD</label>
							<label class="release__control input__checkbox-label" data-filter for="dvd">DVD</label>
							<label class="release__control input__checkbox-label" data-filter for="other">other</label>
						</div>
					</div>
					
					<input class="any--hidden" id="all" name="filter" value="all" type="radio" checked />
					<input class="any--hidden" id="cd" name="filter" value="cd" type="radio" />
					<input class="any--hidden" id="dvd" name="filter" value="dvd" type="radio" />
					<input class="any--hidden" id="other" name="filter" value="other" type="radio" />
					
					<?php
						foreach($releases as $release) {
							$release["is_omnibus"] = ($release["artist_id"] != $artist["id"] ? true : false);
							?>
								<div class="release__container" data-date="<?php echo $release["date_occurred"]; ?>" data-name="<?php echo $release["friendly"]; ?>" data-medium="<?php echo strtolower(implode(" ", is_array($release["medium"]) ? $release["medium"] : [])); ?>">
									<div class="h2 <?php echo !$release["is_omnibus"] ? "" : ""; ?>">
										<?php
											if($release["artist_id"] != $artist["id"] || ($release["artist"]["display_name"] && $release["artist"]["display_name"] != $artist["name"])) {
												?>
													<div class="h5" style="text-transform: none;">
														<a class="artist" href="/artists/<?php echo $release["artist"]["friendly"]; ?>/"><?php
															if(strlen($release["artist"]["display_romaji"])) {
																echo lang($release["artist"]["display_romaji"], $release["artist"]["display_name"], ['secondary_class' => 'any--weaken-color']);
															}
															elseif(strlen($release["artist"]["display_name"])) {
																echo $release["artist"]["display_name"];
															}
															elseif(strlen($release['artist']['romaji'])) {
																echo lang($release["artist"]["romaji"], $release["artist"]["name"], ['secondary_class' => 'any--weaken-color']);
															}
															else {
																echo $release["artist"]["quick_name"];
															}
														?></a>
													</div>
												<?php
											}
										?>
										<a href="/releases/<?php echo $artist["friendly"]."/".$release["id"]."/".$release["friendly"]; ?>/">
											<span class="any--en">
												<?php
													echo $release["romaji"] ?: $release["name"];
													
													if($release["press_name"]) {
														?>
															<span class="any--weaken-size a--outlined"><?php echo $release["press_romaji"] ?: $release["press_name"]; ?></span>
														<?php
													}
													
													if($release["type_name"]) {
														?>
															<span class="any--weaken-size a--outlined"><?php echo $release["type_romaji"] ?: $release["type_name"]; ?></span>
														<?php
													}
													
													if($release["romaji"] || $release["press_romaji"] || $release["type_romaji"]) {
														?>
															<br />
															<span class="any--jp any--weaken">
																<?php
																	echo $release["name"]." ".$release["press_name"]." ".$release["type_name"];
																?>
															</span>
														<?php
													}
												?>
											</span>
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
										</a>
									</div>
									<div class="text <?php echo $release["is_omnibus"] ? "text--outlined" : null; ?>">
										<div class="flex">
												<div class="any--flex-grow">
													<?php
														if(is_array($release["tracklist"]["discs"]) && !empty($release["tracklist"]["discs"])) {
															foreach($release["tracklist"]["discs"] as $disc_num => $disc) {
																$n = 1;
																?>
																	<table class="release__tracklist">
																		<?php
																			if($disc["disc_name"]) {
																				?>
																					<tr>
																						<th class="h4 release__disc" colspan="3">
																							<div class="any--flex">
																								<div class="symbol__release">&nbsp;</div>
																								<div>
																									<?php echo $disc["disc_romaji"] ?: $disc["disc_name"]; ?>
																									<div class="any--jp any--weaken">
																										<?php echo $disc["disc_romaji"] ? $disc["disc_name"] : null; ?>
																									</div>
																								</div>
																							</div>
																						</th>
																					</tr>
																				<?php
																			}
																			foreach($disc["sections"] as $section_num => $section) {
																				if($section["section_name"]) {
																					?>
																							<tr>
																								<th class="h4 release__section" colspan="3">
																									<div class="any--flex">
																										<div class="symbol__section">&nbsp;</div>
																										<div>
																											<?php echo $section["section_romaji"] ?: $section["section_name"]; ?>
																											<div class="any--jp any--weaken">
																												<?php echo $section["section_romaji"] ? $section["section_name"] : null; ?>
																											</div>
																										</div>
																									</div>
																								</th>
																							</tr>
																					<?php
																				}
																				foreach($section["tracks"] as $track) {
																					?>
																						<tr>
																							<td class="any--weaken release__track-num"><?php echo $track["track_num"]."."; $n++; ?></td>
																							<td class="track__artist any--weaken"><!--
																					--><?php
																									if(
																										$track["artist"]["id"] != $artist["id"]
																										||
																										(
																											$track["artist"]["display_name"] != $artist["name"]
																											&&
																											$track["artist"]["display_name"] != $release["artist"]["display_name"]
																											&&
																											!empty($track["artist"]["display_name"])
																										)
																									) {
																										?>
																											<a class="track__artist-link artist artist--no-symbol" href="/releases/<?php echo $track["artist"]["friendly"]; ?>/">
																										<?php
																										
																										echo $track["artist"]["display_romaji"] ?: ($track["artist"]["display_name"] ?: $track["artist"]["quick_name"]);
																										
																										if($track["artist"]["display_romaji"] || (!$track["artist"]["display_name"] && $track["artist"]["romaji"])) {
																											?>
																												<div class="any--jp any--weaken track__artist-romaji"><?php echo $track["artist"]["display_romaji"] ? $track["artist"]["display_name"] : (!$track["artist"]["display_name"] && $track["artist"]["romaji"] ? $track["artist"]["name"] : null); ?></div>
																											<?php
																										}
																										
																										?>
																											</a>
																										<?php
																									}
																								?><!--
																				--></td>
																							<td class="release__track-name">
																								<?php
																									if(!empty($track["notes"])) {
																										$track["notes"] = array_reverse($track["notes"]);
																										
																										foreach($track["notes"] as $note) {
																											if($track["romaji"]) {
																												$track["name"] = substr_replace($track["name"], ' ('.$note["name"].')', $note["name_offset"], $note["name_length"]);
																											}
																											else {
																												$track["name"] = substr_replace($track["name"], ' <span class="any__note">'.$note["name"].'</span>', $note["name_offset"], $note["name_length"]);
																											}
																											
																											if($track["romaji"] && $note["romaji"]) {
																												$track["romaji"] = substr_replace($track["romaji"], ' <span class="any__note">'.$note["romaji"].'</span>', $note["romaji_offset"], $note["romaji_length"]);
																											}
																										}
																									}
																									
																									echo '<span class="any--en">'.($track["romaji"] ?: $track["name"]).'</span>';
																									
																									echo '<span class="any--ja any--hidden">'.$track['name'].'</span>';
																									
																									if(!empty($track["romaji"])) {
																										?>
																											<div class="any--en any--weaken">
																												<?php
																													echo $track["name"];
																												?>
																											</div>
																										<?php
																									}
																								?>
																							</td>
																						</tr>
																					<?php
																				}
																			}
																		?>
																	</table>
																<?php
															}
														}
														else {
															?><div class="text--error symbol__error">Sorry, something went wrong. Please refresh the page and try again.</div><?php
														}
													?>
												</div>
												<div class="any--weaken release__data">
													<?php
														if($release["cover"]) {
															?>
																<a class="release__image-link" href="<?php echo $release["cover"]; ?>" target="_blank">
																	<img alt="<?php echo $release["artist"]["quick_name"]." - ".$release["quick_name"]; ?>" class="release__image" data-src="<?php echo preg_replace("/"."\.(\w+)$"."/", ".thumbnail.$1", $release["cover"]); ?>" />
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
																	?><span class="rate__item symbol--standalone <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>"></span><?php
																}
															?>
														</li>
														<li>
															<?php
																for($i = 1; $i <= 5; $i++) {
																	$class  = "symbol__star--";
																	$class .= $i <= round($release["user_rating"]) ? "full" : "empty";
																	?><a class="rate__item rate__link symbol--standalone <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>" href=""></a><?php
																}
															?>
														</li>
													</ul>
													
													<?php
														if($_SESSION["loggedIn"]) {
															?>
																<div class="a--padded collect__container">
																	<a class="collect collect__item any--flex any--flex-space-between <?php echo $release["is_owned"] ? "symbol__checked" : "symbol__unchecked"; ?>" data-action="own" data-id="<?php echo $release["id"]; ?>">Owned</a>
																	<a class="collect collect__item any--flex any--flex-space-between <?php echo $release["is_wanted"] ? "symbol__checked" : "symbol__unchecked"; ?>" data-action="want" data-id="<?php echo $release["id"]; ?>">Wanted</a>
																</div>
															<?php
														}
													?>
													
													<a class="a--outlined a--padded symbol__arrow-right-circled" href="/releases/<?php echo $artist["friendly"]."/".$release["id"]."/".$release["friendly"]; ?>/">Details</a>
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