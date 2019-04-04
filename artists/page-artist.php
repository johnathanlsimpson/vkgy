<?php
	if(is_array($artist) && !empty($artist)) {
		style([
			"../artists/style-page-artist.css"
		]);
		
		script([
			"/artists/script-page-artist.js"
		]);
		
		?>
			<div class="col c3 any--margin">
				<?php
					foreach($rslt_next as $rslt) {
						?>
							<div class="artist__<?php echo $rslt["type"]; ?>">
								<div class="h5"></div>
								<a class="symbol__<?php echo $rslt["type"]; echo $rslt["type"] === "next" ? " symbol--right" : null; ?>" href="/artists/<?php echo $rslt["friendly"]; ?>/"><?php echo $rslt["romaji"] ? $rslt["romaji"]." (".$rslt["name"].")" : $rslt["name"]; ?></a>
							</div>
						<?php
					}
					for($i=count($rslt_next); $i<3; $i++) {
						echo '<div><div class="h5"></div></div>';
					}
				?>
			</div>
			<div class="col c1">
				<div>
					<?php
						$access_artist->artist_card(array_merge($artist, ["musicians" => $artist["musicians"][1]]), true);
					?>
				</div>
			</div>
			
			<div class="col c4-ABBB">
				<div class="artist__nav">
					<ul class="ul--compact">
						<?php
							echo is_array($artist['musicians'][1]) ? '<li><a href="#lineup">'.lang('Lineup', 'メンバー', ['container' => 'div']).'</a></li>' : null;
							echo is_array($artist['musicians'][2]) ? '<li><a href="#former">'.lang('Former', '元メンバー', ['container' => 'div']).'</a></li>' : null;
							echo is_array($artist['musicians'][3]) ? '<li><a href="#staff">'.lang('Staff', 'スタッフ', ['container' => 'div']).'</a></li>' : null;
							echo is_array($artist['history']) ?      '<li><a href="#history">'.lang('History', '活動', ['container' => 'div']).'</a></li>' : null;
							echo is_array($artist['schedule']) ?     '<li><a href="#schedule">'.lang('Lives', 'ライブ', ['container' => 'div']).'</a></li>' : null;
							echo                                     '<li><a href="#comments">'.lang('Comment', 'コメント', ['container' => 'div']).'</a></li>';
						?>
					</ul>
				</div>
				
				<div class="artist__content" style="flex-grow: 1;">
					
					<div class="col c4-AAAB artist__top">
						<div class="artist__left">
							<div class="text text--outlined artist__details--first">
								<?php
									if($default_image) {
										?>
											<a class="artist__main-image-link" href="<?php echo $default_image; ?>">
												<img class="artist__main-image" alt="<?php echo $artist["quick_name"]; ?>" src="<?php echo $default_image; ?>" />
											</a>
										<?php
									}
								?>
								
								<div class="data__container">
									<div class="data__item">
										<div>
											<h5>
												<?php echo lang('Type', 'タイプ', ['secondary_class' => 'any--hidden']); ?>
											</h5>
											<?php echo ["unknown", "band", "session", "alter-ego", "solo", "special project"][$artist["type"]]; ?>
										</div>
									</div>
									<div class="data__item">
										<div>
											<h5>
												<?php echo lang('Status', '活動状況', ['secondary_class' => 'any--hidden']); ?>
											</h5>
											<?php echo ["unknown", "active", "disbanded", "paused", "semi-active"][$artist["active"]]; ?>
										</div>
									</div>
									<?php
										if(!empty($artist["date_occurred"]) || !empty($artist["date_ended"])) {
											?>
												<div class="data__item">
													<div>
														<h5>
															<?php echo lang('Active', '活動期間', ['secondary_class' => 'any--hidden']); ?>
														</h5>
														<?php echo str_replace("0000", "", substr($artist["date_occurred"], 0, 4)."~".substr($artist["date_ended"], 0, 4)); ?>
													</div>
												</div>
											<?php
										}
									?>
									<div class="data__item">
										<h5>
											<?php echo lang('Area', '地域', ['secondary_class' => 'any--hidden']); ?>
										</h5>
										<?php
											if(is_array($artist['areas']) && !empty($artist['areas'])) {
												foreach($artist['areas'] as $area) {
													echo lang($area['romaji'], $area['name'], ['secondary_class' => 'any--hidden']);
												}
											}
											else {
												echo lang('Japan', '日本', ['secondary_class' => 'any--hidden']);
											}
										?>
									</div>
								</div>
								
								<div class="any--weaken artist__description"><?php echo $markdown_parser->parse_markdown($artist["description"], true); ?></div>
							</div>
							
							<?php
								// Exclusive banner
								if($artist["is_exclusive"]) {
									?>
										<div class="text text--outlined any__obscure any__obscure--faint" style="background-image: url(/support/patreon-back.png); background-size: cover;">
											<div class="h5 symbol__star--full">vkgy exclusive</div>
											<div>
												This artist profile features exclusive information discovered by vkgy contributors!
											</div>
										</div>
									<?php
								}
								
								// Empty banner
								if(!$artist['musicians'] && !$artist['history']) {
									?>
										<div class="text text--outlined symbol__error">
											This artist doesn't have much information yet. Please comment below if you have any information.
										</div>
									<?php
								}
								
								// Lineup
								if(is_array($artist["musicians"]) && !empty($artist["musicians"])) {
									foreach($artist["musicians"] as $musicians_type => $musicians) {
										?>
											<span id="<?php echo $musicians_type === 1 ? 'lineup' : ($musicians_type === 2 ? 'former' : 'staff'); ?>"></span>
											<?php
												if($musicians_type > 1) {
													?>
														<h2>
															<?php
																echo lang(
																	($musicians_type === 1 ? 'Lineup' : ($musicians_type === 2 ? 'Former members' : 'Staff')),
																	($musicians_type === 1 ? 'メンバー' : ($musicians_type === 2 ? '元メンバー' : 'スタッフ')),
																	['container' => 'div', 'secondary_class' => 'any--weaken']
																);
															?>
														</h2>
													<?php
												}
											?>
											
											<div class="text <?php echo $musicians_type !== 1 ? "text--outlined" : null; ?>">
												<?php
													foreach($musicians as $musician) {
														?>
															<div class="ul">
																<h4>
																	<a class="a--inherit" href="/search/musicians/?position=<?php echo $musician["position"]; ?>#result"><?php echo $musician["position_name"]; ?></a>
																</h4>
																<h3>
																	<a class="a--inherit" href="/musicians/<?php echo $musician["id"]."/".$musician["friendly"]; ?>/"><?php echo $musician["quick_name"]; ?></a>
																	<span class="any--weaken-color"><?php echo $musician["romaji"] ? " (".$musician["name"].")" : null; ?></span>
																</h3>
																<div class="any--flex member__history">
																	<div class="lineup__container">
																		<?php
																			foreach($musician['history'] as $history_period => $history_chunk) {
																				?>
																					<ul class="ul--inline lineup__period any--weaken-color symbol__next <?php echo $history_class; ?> ">
																						<?php
																							foreach($history_chunk as $band) {
																								?>
																									<li class="lineup__artist-container">
																										<?php
																											if(!empty($band["url"])) {
																												?>
																													<a class="artist artist--no-symbol" href="<?php echo $band["url"]; ?>">
																														<?php echo $band["quick_name"]; ?>
																													</a>
																												<?php
																											}
																											
																											echo empty($band["url"]) ? $band["quick_name"] : null;
																											echo $band["romaji"] ? " (".$band["name"].")" : null;
																											
																											if(!empty($band["notes"]) && is_array($band["notes"])) {
																												foreach($band["notes"] as $note) {
																													?>
																														<span class="any__note">
																															<?php
																																echo $note;
																															?>
																														</span>
																													<?php
																												}
																											}
																										?>
																									</li>
																								<?php
																							}
																						?>
																					</ul>
																				<?php
																			}
																		?>
																	</div>
																	<?php
																		if(is_array($musician['sessions']) && !empty($musician['sessions'])) {
																			?>
																				<div class="lineup__sessions any--weaken-color">
																					<h5>
																						<?php echo lang('Sessions', 'セッション', ['secondary_class' => 'any--hidden']); ?>
																					</h5>
																					<?php
																						foreach($musician['sessions'] as $band) {
																							?>
																								<span class="lineup__session">
																									<?php
																										if(!empty($band["url"])) {
																											?>
																												<a class="artist artist--no-symbol a--inherit" href="<?php echo $band["url"]; ?>"><?php echo $band["quick_name"]; ?></a>
																											<?php
																										}
																										
																										echo empty($band["url"]) ? $band["quick_name"] : null;
																										echo $band["romaji"] ? " (".$band["name"].")" : null;
																										
																										if(!empty($band["notes"]) && is_array($band["notes"])) {
																											foreach($band["notes"] as $note) {
																												?>
																													<span class="any__note">
																														<?php
																															echo $note;
																														?>
																													</span>
																												<?php
																											}
																										}
																									?>
																								</span>
																							<?php
																						}
																					?>
																				</div>
																			<?php
																		}
																	?>
																</div>
															</div>
														<?php
													}
												?>
											</div>
										<?php
									}
								}
								
								// History
								if(!empty($artist["history"])) {
									?>
										<span id="history"></span>
										<h2>
											<?php echo lang('History', '活動', ['container' => 'div', 'secondary_class' => 'any--weaken']); ?>
										</h2>
										<div class="text bio__container">
											<?php
												$num_years = count($artist["history"]);
												for($i = 0; $i < $num_years; $i++) {
													$year = substr($artist["history"][$i][0][0]["date_occurred"], 0, 4);
													
													?>
														<ul>
															<h2>
																<?php echo $year === '0000' ? '????' : $year; ?>
															</h2>
															<?php
																$num_months = count($artist["history"][$i]);
																for($n = 0; $n < $num_months; $n++) {
																	
																	$num_days = count($artist["history"][$i][$n]);
																	for($m = 0; $m < $num_days; $m++) {
																		
																		$history_event = $artist['history'][$i][$n][$m];
																		$history_type = trim($history_event['type']);
																		
																		if($history_type === 'release' && is_array($history_event["content"])) {
																			
																			// Save base name of release
																			$release_name = $history_event["content"]["romaji"] ?: $history_event["content"]["name"];
																			$cdjapan_link =
																				'http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q='.
																				str_replace("-", "+", friendly($release_name));
																			
																			// Set content of first release in series to link to release
																			$history_event["content"] =
																				'<a class="symbol__release " href="'.$history_event["content"]["url"].'">'.
																				$history_event["content"]["quick_name"].
																				'</a>';
																			
																			// If multiple releases in one day, loop through rest
																			if(count($artist["history"][$i][$n]) > 1) {
																				$x = 1;
																				unset($stop);
																				while(!$stop) {
																					
																					// If next bio item that day is also a release...
																					if(is_array($artist["history"][$i][$n][$m + $x]["content"]) && strstr($artist["history"][$i][$n][$m + $x]["type"], " release ") !== false) {
																						
																						// ...and if that next release shares the same base name as the first release that day...
																						if($release_name === ($artist["history"][$i][$n][$m + $x]["content"]["romaji"] ?: $artist["history"][$i][$n][$m + $x]["content"]["name"])) {
																							
																							// ...then remove the base name from that nth release, showing only "type", and transform to a link
																							$artist["history"][$i][$n][$m + $x]["content"] = 
																								'<a class="" href="'.$artist["history"][$i][$n][$m + $x]["content"]["url"].'">'.
																								substr($artist["history"][$i][$n][$m + $x]["content"]["quick_name"], strlen($release_name)).
																								'</a>';
																								
																							// ...and combine the nth release with the first one, removing the nth release from the flow of the biography
																							$history_event["content"] .= 
																								' <span class="any--weaken">/</span> '.
																								$artist["history"][$i][$n][$m + $x]["content"];
																							
																							unset($artist["history"][$i][$n][$m + $x]);
																						}
																						
																						$x++;
																					}
																					else {
																						$stop = true;
																					}
																				}
																			}
																			
																			// Add CDJapan link back in afer adding multiple release types to base release
																			$history_event["content"] .=
																				' <a class="any__note a--inherit" href="'.$cdjapan_link.'">BUY</a>';
																		}
																		
																		if(strlen($history_event["content"]) > 0) {
																			?>
																				<li class="bio__item" data-item-num="<?php echo $m; ?>" data-item-type="<?php echo $history_event["type"]; ?>">
																					<h4 class="bio__date <?php echo $m ? 'bio__date--multiple' : null; ?>">
																						<?php echo $history_event["date_occurred"]; ?>
																					</h4>
																					<div class="bio__content any--weaken">
																						<?php
																							echo $history_type === 'lineup' ? '<h5></h5>' : null;
																							echo $history_type === 'schedule' ? '<span class="symbol__company" style="float:left;"></span>' : null;
																							echo str_replace('<ol>', '<ol class="ol--inline">', $history_event["content"]);
																						?>
																					</div>
																				</li>
																			<?php
																		}
																	}
																}
															?>
														</ul>
													<?php
												}
											?>
										</div>
									<?php
								}
								
								// Schedule
								if(is_array($artist['schedule']) && !empty($artist['schedule'])) {
									?>
										<span id="schedule"></span>
										<h2>
											<?php echo lang('Live history', 'ライブ一覧', ['container' => 'div', 'secondary_class' => 'any--weaken']); ?>
										</h2>
										<input class="obscure__input" id="obscure-lives" type="checkbox" <?php echo $num_lives > 3 ? 'checked' : null; ?> />
										<div class="text a obscure__container obscure--height">
											<?php
												foreach($artist['schedule'] as $year => $schedule_year) {
													?>
														<ul class="obscure__item ul--compact">
															<?php
																foreach($schedule_year as $day => $lives) {
																	?>
																		<li class="any--weaken">
																			<span class="h4"><?php echo $day; ?></span>
																			&nbsp;
																			<?php
																				foreach($lives as $live) {
																					echo str_replace(['<p>', '</p>'], '', $live['content']);
																				}
																			?>
																		</li>
																	<?php
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
							?>
						</div>
						
						<div class="artist__right">
							<div class="text text--outlined artist__details--second">
								<?php
									if($default_image) {
										?>
											<a class="artist__main-image-link" href="<?php echo str_replace(".large.", ".", $default_image); ?>">
												<img class="artist__main-image" alt="<?php echo $artist["quick_name"]; ?>" src="<?php echo $default_image; ?>" />
											</a>
										<?php
									}
								?>
								<div class="data__container">
									<div class="data__item">
										<div>
											<h5>
												<?php echo lang('Type', 'タイプ', ['secondary_class' => 'any--hidden']); ?>
											</h5>
											<?php echo ["unknown", "band", "session", "alter-ego", "solo", "special project"][$artist["type"]]; ?>
										</div>
									</div>
									<div class="data__item">
										<div>
											<h5>
												<?php echo lang('Status', '活動状況', ['secondary_class' => 'any--hidden']); ?>
											</h5>
											<?php echo ["unknown", "active", "disbanded", "paused", "semi-active"][$artist["active"]]; ?>
										</div>
									</div>
									<?php
										if(!empty($artist["date_occurred"]) || !empty($artist["date_ended"])) {
											?>
												<div class="data__item">
													<div>
														<h5>
															<?php echo lang('Active', '活動期間', ['secondary_class' => 'any--hidden']); ?>
														</h5>
														<?php echo str_replace("0000", "", substr($artist["date_occurred"], 0, 4)."~".substr($artist["date_ended"], 0, 4)); ?>
													</div>
												</div>
											<?php
										}
									?>
									<div class="data__item">
										<h5>
											<?php echo lang('Area', '地域', ['secondary_class' => 'any--hidden']); ?>
										</h5>
										<?php
											if(is_array($artist['areas']) && !empty($artist['areas'])) {
												foreach($artist['areas'] as $area) {
													echo lang($area['romaji'], $area['name'], ['secondary_class' => 'any--hidden']);
												}
											}
											else {
												echo lang('Japan', '日本', ['secondary_class' => 'any--hidden']);
											}
										?>
									</div>
								</div>
								
								<div class="any--weaken artist__description"><?php echo $markdown_parser->parse_markdown($artist["description"], true); ?></div>
							</div>
							
							<?php
								// Images
								if(is_array($artist["images"]) && !empty($artist["images"])) {
									if(count($artist["images"]) === 1 && image_exists($default_image, $pdo)) {
									}
									else {
										?>
											<div>
												<h3>
													<?php echo lang('Images', '画像', ['container' => 'div', 'secondary_class' => 'any--weaken']); ?>
												</h3>
												
												<input class="obscure__input" id="obscure-images" type="checkbox" <?php echo count($artist['images']) > 4 ? 'checked' : null; ?> >
												
												<div class="text text--outlined obscure__container obscure--faint">
													<ul class="ul--inline any--flex images__container">
														<?php
															foreach($artist["images"] as $image) {
																?>
																	<li class="images__item obscure__item">
																		<a class="images__link" href="<?php echo $image["url"]; ?>" style="background-image: url(<?php echo strtolower(str_replace(".", ".thumbnail.", $image["url"])); ?>);" target="_blank"></a>
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
								}
								
								// Label history
								if(is_array($artist["labels"]) && !empty($artist["labels"])) {
									?>	
										<h3>
											<?php echo lang('Label history', '所属レーベル', ['container' => 'div', 'secondary_class' => 'any--weaken']); ?>
										</h3>
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
																			<span class="label__comma any--weaken-color"> & </span>
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
								if(!empty($artist["official_links"])) {
									$artist["official_links"] = explode("\n", $artist["official_links"]);
									
									if(is_array($artist["official_links"]) && !empty($artist["official_links"])) {
										?>
											<div>
												<h3>
													<?php echo lang('Links', 'リンク', ['container' => 'div', 'secondary_class' => 'any--weaken']); ?>
												</h3>
												
												<input class="obscure__input" id="obscure-links" type="checkbox" <?php echo count($artist['official_links']) > 4 ? 'checked' : null; ?> >
												
												<div class="any--weaken text text--outlined obscure__container obscure--faint">
													<ul>
														<?php
															foreach($artist["official_links"] as $url) {
																$url = preg_replace("/"."^\s*(.+)\s*$"."/", "$1", $url);
																?>
																	<li class="obscure__item">
																		<a href="<?php echo $url; ?>" target="_blank"><?php echo str_replace(["https://www.", "http://www.", "https://", "http://"], "", $url); ?></a>
																		<a class="a--inherit" href="http://web.archive.org/web/*/<?php echo $url; ?>" target="_blank">(saved)</a>
																	</li>
																<?php
															}
														?>
													</ul>
													<label class="input__button obscure__button" for="obscure-links">Show all</label>
												</div>
											</div>
										<?php
									}
								}
								
								// Tags
								include("../artists/page-tags.php");
								
								// Popularity
								include("../artists/page-ranking.php");
							?>
						</div>
						
					</div>
					
					<div class="artist__bottom">
						<?php
							include('../comments/partial-comments.php');
							render_default_comment_section('artist', $artist['id'], $artist['comments'], $markdown_parser);
						
							if(is_array($artist["edit_history"]) && !empty($artist["edit_history"])) {
								?>
									<div>
										<h3>
											<?php echo lang('Edit history', '変更履歴', ['container' => 'div']); ?>
										</h3>
										
										<input class="obscure__input" id="show-edits" type="checkbox" <?php echo count($artist["edit_history"]) > 4 ? "checked" : null; ?> />
										
										<div class="text text--outlined obscure__container obscure--faint">
											<ul class="ul--compact">
												<?php
													for($i = 0; $i < count($artist["edit_history"]); $i++) {
														?>
															<li class="obscure__item">
																<span class="h4">
																	<?php
																		echo substr($artist["edit_history"][$i]["date_occurred"], 0, 10);
																	?>
																</span>
																<a class="user" href="/users/<?php echo $artist["edit_history"][$i]["username"]; ?>/">
																	<?php
																		echo $artist["edit_history"][$i]["username"];
																	?>
																</a>
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
									</div>
								<?php
							}
						?>
					</div>
				</div>
			</div>
			
							<style>.obscure__input:checked + .obscure__container.a { max-height: 8rem; overflow:hidden; }</style>
							<style>
								:target {
									content: "";
									display: block;
									margin-top: -4rem;
									padding-top: 4rem;
									position: relative;
								}
								/*h2:target::after {
									background: var(--attention--faint);
									background-clip: content-box;
									bottom: 1rem;
									content: "";
									display: inline-block;
									left: 0;
									padding-top: 4rem;
									position: absolute;
									top: 0;
									width: 3px;
								}*/
								tbody:not(:last-of-type) tr:last-of-type td {
									padding-bottom: 1.5rem;
								}
								td h3 {
									padding-bottom: 0;
								}
								.h--nav {
									align-items: center;
									display: flex;
									flex-wrap: wrap;
									/*justify-content: space-between;*/
									padding-right: 0;
								}
								.h--nav::before {
									align-self: bottom;
								}
								.h--nav :last-child {
									margin-left: auto;
								}
							</style>
			<style>
				.artist__nav {
					/*background: linear-gradient(to top, transparent, var(--background--faint) 1rem);*/
					margin-top: -1rem;
					padding: 1rem 0;
					position: -webkit-sticky;
					position: sticky;
					top: 3rem;
					z-index: 2;
				}
				@media(max-width:799.9px) {
					.artist__nav {
						background-image: linear-gradient(var(--background--faint), var(--background--faint));
						background-position: 0 -1rem;
						background-repeat: no-repeat;
						padding: 1rem;
						padding: 0.5rem 1rem 1rem 1rem;
						text-align: center;
					}
					.artist__nav::after {
						bottom: 0;
						box-shadow: inset 0 1.5rem 1rem -1rem var(--background--faint);
						content: "";
						display: block;
						height: 1rem;
						left: 0;
						position: absolute;
						right: 0;
					}
					.artist__nav ul {
						display: flex;
						flex-wrap: wrap;
						justify-content: space-between;
					}
					.artist__nav li {
						border: none;
						margin: 0;
						padding: 0;
					}
					.artist__nav [href*='former'], .artist__nav [href*='staff'], .artist__nav [href*='schedule'] {
						display: none;
					}
				}
				@media(min-width:800px) {
					.artist__nav.artist__nav {
						width: auto;
						margin-right: 3rem;
					}
				}
			</style>
		<?php
	}
?>