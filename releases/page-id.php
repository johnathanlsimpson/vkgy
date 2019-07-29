<?php
	include_once("../php/class-parse_markdown.php");
	$markdown_parser = new parse_markdown($pdo);


	if(!empty($release)) {
		include_once("../releases/head.php");
		
		$release['images'] = is_array($release['images']) ? $release['images'] : [];
		
		if(!empty($release['images']) && is_numeric($release['image_id'])) {
			$release['image'] = $release['images'][$release['image_id']];
			
			unset($release['images'][$release['image_id']]);
			
			$release['images'] = array_values($release['images']);
		}
		
		background("/artists/".$release["artist"]["friendly"]."/main.large.jpg");
		
		style("/releases/style-page-id.css");
		
		script([
			"/scripts/script-rateAlbum.js",
			"/releases/script-page-id.js"
		]);
			
		breadcrumbs([
			$release["artist"]["quick_name"] => "/releases/".$release["artist"]["friendly"]."/",
			$release["quick_name"] => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/"
		]);
		
		if($_SESSION["loggedIn"]) {
			subnav([
				"Edit" => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/edit/"
			]);
		}
		
		$pageTitle = $release["quick_name"]." - ".$release["artist"]["quick_name"];
		
		$artist = $release['artist'];
		include('../artists/head.php');
		
		subnav([
			'Edit release' => '/releases/'.$release['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/edit/',
		], 'interact', true);
		
		if(is_array($release["prev_next"]) && !empty($release["prev_next"])) {
			foreach($release["prev_next"] as $link) {
				subnav([
					[
						'text' => $link['quick_name'],
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
													//$release["format"] = $release["format_romaji"] ? $release["format_romaji"]." (".$release["format_name"].")" : $release["format_name"] ?: $release["format"];
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
												<div class="input__group">
													<label class="collect input__checkbox-label <?php echo $release["is_owned"] ? "input__checkbox-label--selected symbol__checked" : "symbol__unchecked"; ?>" data-action="own" data-id="<?php echo $release["id"]; ?>">I own this</label>
												</div>
												<div class="input__group">
													<label class="collect input__checkbox-label <?php echo $release["is_wanted"] ? "input__checkbox-label--selected symbol__checked" : "symbol__unchecked"; ?>" data-action="want" data-id="<?php echo $release["id"]; ?>">I want this</label>
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
																?><span class="rate__item <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>"></span><?php
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
																?><a class="rate__item rate__link <?php echo $class; ?>" data-release_id="<?php echo $release["id"]; ?>" data-score="<?php echo $i; ?>" href=""></a><?php
															}
														?>
													</div>
												</div>
											</ul>

											<div class="input__row">
												<div class="input__group">
													<a class="a--outlined a--padded any--weaken-size" href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q=<?php echo $release["upc"] ? str_replace(["-000", "-00", "-0"], "-", $release["upc"]) : str_replace(" ", "+", $release["quick_name"]); ?>" target="_blank"><?php echo $release["upc"] ? "Buy at CDJapan" : "Search at CDJapan"; ?></a>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div>
									<h3>
										Tracklist
									</h3>
									<div class="text">
										<div class="any--flex release__tracklist-container">
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
																						<td class="any--weaken track__num"><?php echo $track["track_num"]."."; $n++; ?></td>
																						<td class="track__artist any--weaken"><!--
																				--><?php
																								if($track["artist"]["id"] != $release["artist"]["id"]) {
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
																						<td class="track__name" data-track="<?php echo $track["name"]; ?>">
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

																								echo $track["romaji"] ?: $track["name"];

																								if(!empty($track["romaji"])) {
																									?>
																										<div class="any--jp any--weaken">
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
										</div>
									</div>

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
									if($_SESSION["loggedIn"] && is_numeric($_SESSION["userID"])) {
										$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
										$stmt_check = $pdo->prepare($sql_check);
										$stmt_check->execute([ $_SESSION["userID"] ]);
										$is_vip = $stmt_check->fetchColumn();
									}

								?>
								<h3>
									Tags <sup class="any--weaken">&beta;</sup>
								</h3>
								<div class="text text--outlined">
									<?php
										if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
											foreach($rslt_curr_tags as $tag) {
												echo '<a class="any__tag any__tag--selected" href="/search/releases/?tag='.$tag["friendly"].'#result" style="display: inline-block;">'.$tag["name"].' ('.$tag["num_times_tagged"].')'.'</a> ';
											}

											echo '<hr />';
										}

										echo '<h5>Add tags</h5>';

										if($_SESSION["loggedIn"]) {
											if(is_array($rslt_tags) && !empty($rslt_tags)) {
												foreach($rslt_tags as $tag) {
													$is_selected = is_array($rslt_user_tags) && !empty($rslt_user_tags) && in_array($tag["id"], $rslt_user_tags);
													echo '<label data-id="'.$release["id"].'" data-tag_id="'.$tag["id"].'" class="release__tag symbol__tag any__tag '.($is_selected ? "any__tag--selected" : null).'" style="display: inline-block;">'.$tag["name"].'</label> ';
												}
											}
										}
										else {
											echo '<span class="symbol__error"><a class="a--inherit" href="/account/">Sign in</a> to add tags.';
										}

										if($_SESSION["admin"] > 0 && $needs_admin_tags) {
											echo '<hr />';
											echo '<h5>Remove admin tags</h5>';

											if(is_array($rslt_tags) && !empty($rslt_tags)) {
												foreach($rslt_tags as $tag) {
													if($tag["is_admin_tag"] && in_array($tag["id"], $rslt_curr_tag_ids)) {
														echo '<label data-action="delete" data-id="'.$release["id"].'" data-tag_id="'.$tag["id"].'" class="release__tag symbol__tag any__tag any__tag--selected" style="display: inline-block;">'.$tag["name"].'</label> ';
													}
												}
											}
										}
									?>
								</div>

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
											$sql_editors = 'SELECT users.username FROM edits_releases LEFT JOIN users ON users.id=edits_releases.user_id WHERE edits_releases.release_id=? GROUP BY users.username ORDER BY edits_releases.date_occurred DESC';
											$stmt_editors = $pdo->prepare($sql_editors);
											$stmt_editors->execute([ $release['id'] ]);
											$rslt_editors = $stmt_editors->fetchAll();
											$num_editors = count($rslt_editors);

											if(is_array($rslt_editors) && !empty($rslt_editors)) {
												for($i=0; $i<$num_editors; $i++) {
													?>
														<li>
															<a class="user" href="<?php echo '/users/'.$rslt_editors[$i]['username'].'/'; ?>"><?php echo $rslt_editors[$i]['username']; ?></a>
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
										$sql_is_for_sale = 'SELECT 1 FROM releases_collections WHERE release_id=? AND is_for_sale=? LIMIT 1';
										$stmt_is_for_sale = $pdo->prepare($sql_is_for_sale);
										$stmt_is_for_sale->execute([ $release['id'], 1 ]);
										$is_for_sale = $stmt_is_for_sale->fetchColumn();

										$sql_collections = "SELECT users.username, releases_collections.is_for_sale FROM releases_collections LEFT JOIN users ON users.id=releases_collections.user_id WHERE releases_collections.release_id=? ORDER BY users.username ASC";
										$stmt_collections = $pdo->prepare($sql_collections);
										$stmt_collections->execute([ $release["id"] ]);
										$rslt_collections = $stmt_collections->fetchAll();

										$sql_wants = "SELECT users.username FROM releases_wants LEFT JOIN users ON users.id=releases_wants.user_id WHERE releases_wants.release_id=? ORDER BY users.username ASC";
										$stmt_wants = $pdo->prepare($sql_wants);
										$stmt_wants->execute([$release["id"]]);
										$rslt_wants = $stmt_wants->fetchAll();

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
															foreach($rslt_collections as $collection) {
																?>
																	<li>
																		<a class="user" href="/users/<?php echo $collection["username"]; ?>/"><?php echo $collection["username"]; ?></a>
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
																?>
																	<li>
																		<a class="user" href="/users/<?php echo $want["username"]; ?>/"><?php echo $want["username"]; ?></a>
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
																<a class="user" href="/users/<?php echo $collection["username"]; ?>/"><?php echo $collection["username"]; ?></a>
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
										if($_SESSION["loggedIn"]) {
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