<?php
	include('../artists/head.php');
	
	script([
		"/scripts/external/script-autosize.js",
		"/scripts/external/script-selectize.js",
		'/scripts/external/script-tribute.js',
		
		"/scripts/script-showElem.js",
		"/scripts/script-initDelete.js",
		"/scripts/script-initSelectize.js",
		'/scripts/script-initTribute.js',
		
		"/artists/script-previewBio.js",
		"/artists/script-exclusive.js",
		"/artists/script-page-edit.js",
	]);
	
	style([
		'/style/external/style-tribute.css',
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/artists/style-page-edit.css"
	]);
	
	subnav([
		'Edit artist' => '/artists/'.$artist['friendly'].'/edit/',
	]);
	
	if($_SESSION["admin"] > 0) { 
			if(!empty($artist)) {
				$artist['images'] = is_array($artist['images']) ? array_values($artist['images']) : [];
				?>
					<form action="" class="col c1 any--margin" enctype="multipart/form-data" id="form__edit" method="post" name="form__edit">
						<?php
							include_once('../php/function-render_json_list.php');
							render_json_list('artist', null, null, true);
							render_json_list('label', null, null, true);
							render_json_list('musician', $artist['musicians'], null, true);
							render_json_list('release', $artist['id'], 'artist_id');
						?>
						
						<input id="form__changes" name="changes" type="hidden" />
						
						<div class="col c1">
							<div>
								<h2>Edit basic information</h2>
								<div class="text">
									<ul>
										<li>
											<div class="input__row">
												<div class="input__group">
													<label class="input__label">ID</label>
													<input class="edit__id" name="id" placeholder="ID" size="3" value="<?php echo $artist["id"]; ?>" readonly />
												</div>
												<div class="any--flex any--flex-grow input__group edit__name">
													<label class="input__label">Name</label>
													<input name="name" placeholder="name" value="<?php echo $artist["name"]; ?>" />
													
													<label class="input__label">Romaji</label>
													<input class="input--secondary" name="romaji" placeholder="(romaji)" value="<?php echo $artist["romaji"]; ?>" />
												</div>
												<div class="input__group">
													<label class="input__label">Friendly</label>
													<input name="friendly" placeholder="friendly name" value="<?php echo $artist["friendly"]; ?>" />
												</div>
											</div>
										</li>
										<li>
											<div class="input__row any--flex-space-between">
												<span class="input__group">
													<label class="input__label">Artist type</label>
													<?php
														$n = -1;
														foreach(["unknown", "band", "session", "alter-ego", "solo", "special project"] as $key) {
															$n++;
															?>
																<input class="input__checkbox" id="type<?php echo $key; ?>" name="type" type="radio" value="<?php echo $n; ?>" <?php echo $artist["type"] === "".$n ? "checked" : null; ?> />
																<label class="input__checkbox-label symbol__unchecked" for="type<?php echo $key; ?>"><?php echo $key; ?></label>
															<?php
														}
													?>
												</span>
											</div>
											
											<div class="input__row any--flex-space-between">
												<span class="input__group">
													<label class="input__label">Status</label>
													<?php
														$n = -1;
														foreach(["unknown", "active", "disbanded", "paused", "semi-active"] as $key) {
															$n++;
															?>
																<input class="input__checkbox" id="status<?php echo $key; ?>" name="active" type="radio" value="<?php echo $n; ?>" <?php echo $artist["active"] === "".$n ? "checked" : null; ?> />
																<label class="input__checkbox-label symbol__unchecked" for="status<?php echo $key; ?>"><?php echo $key; ?></label>
															<?php
														}
													?>
												</span>
												
												<button data-show="edit__status">
													Custom status
												</button>
												
												<div class="input__group edit__status any--hidden">
													<label class="input__label">Custom status name</label>
													<input class="input" name="status_name" placeholder="eg. &ldquo;sealed&rdquo;" value="<?php echo $artist["status_name"]; ?>" />
													<input class="input--secondary" name="status_romaji" placeholder="(romaji)" value="<?php echo $artist["status_romaji"]; ?>" />
												</div>
											</div>
										</li>
										<li>
											<div class="input__row">
												<span class="input__group any--flex-grow">
													<label class="input__label">Short description</label>
													<input name="description" placeholder="short description/bio" value="<?php echo $artist["description"]; ?>" />
												</span>
												<span class="input__group any--flex-grow">
													<label class="input__label">Official concept</label>
													<input class="input" name="concept_name" placeholder="eg. &ldquo;visual shock&rdquo;" value="<?php echo $artist["concept_name"]; ?>" />
													<input class="input--secondary" name="concept_romaji" placeholder="(romaji)" value="<?php echo $artist["concept_romaji"]; ?>" />
												</span>
											</div>
										</li>
										<li>
											<div class="input__row">
												<div class="input__group any--flex-grow">
													<label class="input__label">
														Label history
													</label>
													<textarea class="autoresize any--flex-grow input__textarea any--tributable" name="label_history" placeholder="(1)&#10;(1), (2)&#10;(3) (management only)"><?php echo $artist["label_history"]; ?></textarea>
												</div>
											</div>
										</li>
										<li>
											<div class="input__row">
												<div class="input__group any--flex-grow">
													<label class="input__label">
														Official links
													</label>
													<textarea class="autoresize any--flex-grow input__textarea" name="official_links" placeholder="http://officialwebsite.com/&#10;http://twitter.com/official_account/"><?php echo $artist["official_links"]; ?></textarea>
												</div>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						
						<div class="col c2">
							<div class="any--margin">
								<?php
									if(!empty($artist["prev_artist"])) {
										?>
											<a class="" href="/artists/<?php echo $artist["prev_artist"]["friendly"]; ?>/edit/">
												<span class="symbol__previous"></span>
												<?php echo $artist["prev_artist"]["quick_name"]; ?>
											</a>
										<?php
									}
								?>
							</div>
							<div class="any--margin any--align-right">
								<?php
									if(!empty($artist["next_artist"])) {
										?>
											<a class="" href="/artists/<?php echo $artist["next_artist"]["friendly"]; ?>/edit/">
												<?php echo $artist["next_artist"]["quick_name"]; ?>
												<span class="symbol__next"></span>
											</a>
										<?php
									}
								?>
							</div>
						</div>
						
						<input class="any--hidden obscure__input" id="obscure-bio" type="checkbox" checked />
						<div class="col c2 edit__biography obscure__container obscure--height obscure--faint">
							<div>
								<h2>
									Edit biography
								</h2>
								<div class="text">
									<div class="input__row">
										<div class="input__group any--flex-grow">
											<textarea class="autoresize input__textarea any--flex-grow any--tributable edit__history" name="bio"><?php
												if(is_array($artist["history"])) {
													$n = 0;
													foreach($artist["history"] as $history_line) {
														foreach($history_line["type"] as $key => $type_num) {
															$history_line["type"][$key] = $access_artist->artist_bio_types[$type_num];
														}
														$history_line["type"] = array_filter(array_unique($history_line["type"]));
														
														$history_string =
															$history_line["date_occurred"]." ".
															$history_line["content"]." -".
															implode(",", $history_line["type"]).
															($n + 1 < count($artist["history"]) ? "\n\n" : null);
														$history_strings[] = $history_string;
														echo $history_string;
														
														$n++;
													}
												}
											?></textarea>
											<textarea class="any--hidden" name="original_bio" hidden><?php echo is_array($history_strings) ? implode('', $history_strings) : $history_strings; unset($history_strings); ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<div>
								<h3>
									Preview
								</h3>
								<div class="text text--outlined edit__history-preview"></div>
							</div>
							<label class="input__button obscure__button" for="obscure-bio">Show section</label>
						</div>
						
						<div class="col c1">
							<div>
								<h2>
									Edit image gallery
								</h2>
								<?php
									include('../images/function-render_image_section.php');
									render_image_section($artist['images'], [
										'item_type' => 'artist',
										'item_id' => $artist['id'],
										'item_name' => $artist['quick_name'],
										'description' => $artist['quick_name'].' group photo',
										'id' => $artist['image_id'],
										'hide_blog' => true,
										'hide_labels' => true,
										'hide_markdown' => true,
									]);
								?>
							</div>
						</div>
						
						<div class="col c1">
							<div>
								<h2>
									Edit member information
								</h2>
								<?php
									if(is_array($artist["musicians"]) && !empty($artist["musicians"])) {
										usort($artist["musicians"], function($a, $b) {
											return strtolower($a["quick_name"]) <=> strtolower($b["quick_name"]);
										});
										
										$y = 0;
										foreach($artist["musicians"] as $musician) {
											$m = $musician["id"];
											?>
												<div class="edit__musician">
													<h3>
														<?php
															echo $musician["quick_name"].' ';
															echo '<span class="any--jp">';
															echo $musician["as_name"] && $musician["quick_name"] === $musician["as_romaji"] ? "(".$musician["as_name"].")" : null;
															echo !$musician["as_name"] && $musician["quick_name"] === $musician["romaji"] ? "(".$musician["name"].")" : null;
															echo '</span>';
														?>
													</h3>
													<div class="text">
														<ul>
															<li>
																<div class="input__row">
																	<div class="input__group any--flex-grow">
																		<label class="input__label">Alias in <?php echo $artist["quick_name"]; ?></label>
																		<input class="input" name="musicians[<?php echo $m; ?>][as_name]" placeholder="alias" value="<?php echo $musician["as_name"]; ?>" />
																		<input class="input--secondary" name="musicians[<?php echo $m; ?>][as_romaji]" placeholder="(romaji)" value="<?php echo $musician["as_romaji"]; ?>" />
																	</div>
																</div>
															</li>
															<li>
																<div class="input__row">
																	<div class="input__group">
																		<label class="input__label">Position in <?php echo $artist["quick_name"]; ?></label>
																		<?php
																			$n = 0;
																			foreach($access_artist->positions as $pos_num => $pos_name) {
																				$y++;
																				?>
																					<input class="input__checkbox" id="<?php echo $y; ?>" name="musicians[<?php echo $m; ?>][position]" type="radio" value="<?php echo $pos_num; ?>" <?php echo $musician["position"] == $pos_num ? "checked" : null; ?> />
																					<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>"><?php echo strtolower($pos_name); ?></label>
																				<?php
																			}
																		?>
																	</div>
																	
																	<div class="input__group any--flex-grow">
																		<button data-show="edit__position-credit">
																			Custom position
																		</button>
																		<label class="input__label any--hidden edit__position-credit">Position credited as</label>
																		<input class="input any--hidden edit__position-credit" name="musicians[<?php echo $m; ?>][position_name]" placeholder="position" value="<?php echo $musician["position_name"]; ?>" />
																		<input class="input--secondary any--hidden edit__position-credit" name="musicians[<?php echo $m; ?>][position_romaji]" placeholder="(romaji)" value="<?php echo $musician["position_romaji"]; ?>" />
																	</div>
																	
																	<div class="input__group">
																		<input class="input__checkbox" id="<?php $y++; echo $y; ?>" name="musicians[<?php echo $musician["id"]; ?>][to_end]" type="checkbox" value="1" <?php echo $musician["to_end"] ? "checked" : null; ?> />
																		<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>">Part of final lineup?</label>
																	</div>
																</div>
															</li>
														</ul>
														
														<ul>
															<h4>
																General details
															</h4>
															<li>
																<div class="input__row">
																	<div class="input__group">
																		<label class="input__label">ID</label>
																		<input name="musicians[<?php echo $m; ?>][id]" size="4" value="<?php echo $musician["id"]; ?>" readonly />
																	</div>
																	<div class="input__group any--flex-grow">
																		<label class="input__label">Name</label>
																		<input class="input any--flex-grow" name="musicians[<?php echo $m; ?>][name]" placeholder="name" value="<?php echo $musician["name"]; ?>" />
																		<input class="input--secondary" name="musicians[<?php echo $m; ?>][romaji]" placeholder="(romaji)" value="<?php echo $musician["romaji"]; ?>" />
																	</div>
																	<div class="input__group">
																		<label class="input__label">Friendly</label>
																		<input name="musicians[<?php echo $m; ?>][friendly]" placeholder="friendly name" value="<?php echo $musician["friendly"]; ?>" />
																	</div>
																	<div class="input__group">
																		<label class="input__checkbox-label symbol__trash symbol--standalone edit__delete-musician" data-id="<?php echo $musician["id"]; ?>"></label>
																	</div>
																</div>
															</li>
															
															<li>
																<div class="input__row" style="justify-content: space-between;">
																	<div class="input__group">
																		<label class="input__label">Usual position</label>
																		<?php
																			foreach($access_artist->positions as $pos_num => $pos_name) {
																				$y++;
																				?>
																					<input class="input__checkbox" id="<?php echo $y; ?>" name="musicians[<?php echo $m; ?>][usual_position]" type="radio" value="<?php echo $pos_num; ?>" <?php echo $musician["usual_position"] == $pos_num ? "checked" : null; ?> />
																					<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>"><?php echo strtolower($pos_name); ?></label>
																				<?php
																			}
																		?>
																	</div>
																</div>
															</li>
															<li>
															<div class="input__row">
																	<div class="input__group">
																		<label class="input__label">Gender</label>
																		<?php
																			$n = 0;
																			foreach(["male", "female", "other/unknown"] as $key) {
																				$n++;
																				$y++;
																				?>
																					<input class="input__checkbox" id="<?php echo $y; ?>" name="musicians[<?php echo $m; ?>][gender]" type="radio" value="<?php echo $n; ?>" <?php echo $musician["gender"] === "".$n ? "checked" : null; ?> />
																					<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>"><?php echo $key; ?></label>
																				<?php
																			}
																		?>
																	</div>
																	<div class="input__group">
																		<label class="input__label">Blood</label>
																		<input name="musicians[<?php echo $m; ?>][blood_type]" placeholder="eg. B" size="3" value="<?php echo $musician["blood_type"]; ?>" />
																	</div>
																	
																	<div class="input__group">
																		<label class="input__label">Birth date</label>
																		<input name="musicians[<?php echo $m; ?>][birth_date]" placeholder="yyyy-mm-dd" value="<?php echo $musician["birth_date"]; ?>" />
																	</div>
																	
																	<div class="input__group">
																		<label class="input__label">Home area</label>
																		<input name="musicians[<?php echo $m; ?>][birthplace]" placeholder="eg. Tokyo (東京)" value="<?php echo $musician["birthplace"]; ?>" />
																	</div>
																</div>
															</li>
															<li>
																<div class="input__row">
																	<div class="input__group any--flex-grow">
																		<label class="input__label">Band history</label>
																		<textarea class="autoresize input__textarea any--flex-grow any--tributable" name="musicians[<?php echo $m; ?>][history]" placeholder="(1)[Nega]&#10;(1) (support)"><?php echo $musician["raw_history"]; ?></textarea>
																	</div>
																</div>
															</li>
														</ul>
													</div>
												</div>
											<?php
										}
									}
									else {
										?>
											<div class="text text--outlined text--notice symbol__error">
												There are currently no musicians in the database enrolled in this band. <a href="/musicians/add/">Add musicians?</a>
											</div>
										<?php
									}
								?>
							</div>
						</div>
						
						<div class="text text--docked">
							<div class="input__row" data-role="submit-container">
								<div class="input__group any--flex-grow">
									<button class="any--flex-grow" data-role="submit" type="submit">
										Submit edits
									</button>
								</div>
								<div class="input__group">
									<a class="artist a--inherit a--padded any--weaken-size" data-get="artist_url" data-get-into="href" href="/artists/<?php echo $artist["friendly"]; ?>/">
										<span data-get="artist_quick_name"><?php echo $artist["quick_name"]; ?></span>
									</a>
								</div>
								<?php
									if($_SESSION["admin"]) {
										?>
											<div class="input__group">
												<label class="input__checkbox-label symbol__trash symbol--standalone" name="delete"></label>
											</div>
										<?php
									}
								?>
								<span data-role="status"></span>
							</div>
							
							<div class="edit__result text text--outlined text--notice any--hidden" data-role="result"></div>
						</div>
					</form>
				<?php
				
				$documentation_page = ['edit-artist', 'musicians'];
				include('../documentation/index.php');
			}
			else {
				?>
					<div class="col c1">
						<div>
							<div class="text text--outlined text--error symbol__error">
								Sorry, that artist doesn't exist. Showing artist list instead.
							</div>
						</div>
					</div>
				<?php
				include("../artists/page-index.php");
			}
	}
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error symbol__error">
						Sorry, only administrators may edit artist information.
					</div>
				</div>
			</div>
		<?php
	}
?>