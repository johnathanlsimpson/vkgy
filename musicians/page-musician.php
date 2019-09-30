<?php
	if(is_array($musician) && !empty($musician)) {
		
		$page_header = '<a class="a--inherit symbol__musician" href="/musicians/'.$musician['id'].'/'.$musician['friendly'].'/">'.$musician['name'].'</a>';
		if($musician['romaji']) {
			$page_header = lang(
				'<a class="a--inherit symbol__musician" href="/musicians/'.$musician['id'].'/'.$musician['friendly'].'/">'.($musician['romaji'] ?: $musician['name']).'</a>',
				'<a class="a--inherit symbol__musician" href="/musicians/'.$musician['id'].'/'.$musician['friendly'].'/">'.$musician['name'].'</a>',
				['container' => 'div']
			);
		}
		
		subnav([
			lang('Profile', 'プロフィール', ['secondary_class' => 'any--hidden']) => '/musicians/'.$musician['id'].'/'.$musician['friendly'].'/'
		]);
		
		style("../musicians/style-page-musician.css");
		
		script([
			'/musicians/script-page-musician.js',
		]);
		
		// Hide article if removed, unless VIP user
		if($musician_is_removed <= $_SESSION['is_vip']) {
			if($musician_is_removed) {
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
				<div class="col c1">
					<div>

						<div class="text text--outlined">
							<a class="symbol__musician" href="/musicians/<?php echo $musician["id"]."/".$musician["friendly"]; ?>/"><?php echo $musician["quick_name"]; ?></a>
							<div class="any--weaken-color"><?php echo $musician["romaji"] ? "(".$musician["name"].")" : null; ?></div>

							<ul class="ul--inline musician__data">
								<?php
									if(is_numeric($musician["usual_position"])) {
										?>
											<li>
												<h5>
													Usual position
												</h5>
												<a class="a--inherit" href="/search/musicians/?position=<?php echo $musician["usual_position"]; ?>#result"><?php echo ["unknown", "vocals", "guitar", "bass", "drums", "keys/DJ", "other/unknown"][$musician["usual_position"]]; ?></a>
											</li>
										<?php
									}

									if($musician["aliases"]) {
										?>
											<li>
												<h5>
													Aliases
												</h5>
												<?php echo $musician["aliases"]; ?>
											</li>
										<?php
									}

									if($musician["blood_type"]) {
										?>
											<li>
												<h5>
													Blood
												</h5>
												<a class="a--inherit" href="/search/musicians/?blood_type=<?php echo in_array(strtolower($musician["blood_type"]), ["a", "ab", "b", "o"]) ? strtolower($musician["blood_type"]) : "other"; ?>#result"><?php echo $musician["blood_type"]; ?></a>
											</li>
										<?php
									}

									if($musician["birth_date"]) {
										?>
											<li>
												<h5>
													Birth date
												</h5>
												<a class="a--inherit" href="/search/musicians/?birth_date=<?php echo substr($musician["birth_date"], 0, 4); ?>#result"><?php echo substr($musician["birth_date"], 0, 4); ?></a>-<a class="a--inherit" href="/search/musicians/?birth_date=<?php echo substr($musician["birth_date"], 0, 7); ?>#result"><?php echo substr($musician["birth_date"], 5, 2); ?></a>-<a class="a--inherit" href="/search/musicians/?birth_date=<?php echo $musician["birth_date"]; ?>#result"><?php echo substr($musician["birth_date"], 8, 2); ?></a>
											</li>
										<?php
									}

									if($musician["gender"]) {
										?>
											<li>
												<h5>
													Gender
												</h5>
												<a class="a--inherit" href="/search/musicians/?gender=<?php echo $musician["gender"]; ?>#result"><?php echo ["unknown/other", "male", "female", "other"][$musician["gender"]]; ?></a>
											</li>
										<?php
									}
								?>
							</ul>
						</div>

						<?php
							if(is_array($musician["labels"])) {
								?>
									<h2>
										President of
									</h2>
									<div class="text text--outlined">
										<ul>
											<?php
												foreach($musician["labels"] as $label_key => $label) {
													?>
														<li>
															<a class="symbol__company" href="/labels/<?php echo $label["friendly"]; ?>/"><?php echo $label["romaji"] ?: $label["name"]; ?></a>
														</li>
													<?php
												}
											?>
										</ul>
									</div>
								<?php
							}
						?>

						<h2>
							Band history
						</h2>
						<div class="text">
							<ul>
								<?php
									if(is_array($musician["history"])) {
										foreach($musician["history"] as $period) {
											?>
												<li class="any--weaken-color">
													<?php
														foreach($period as $band_key => $band) {
															if(is_numeric($band["id"])) {
																?>
																	<a class="artist" href="/artists/<?php echo $band["friendly"]; ?>/">
																		<?php 
																			if(strlen($band['display_name']) || strlen($band['display_romaji'])) {
																				echo lang($band['display_romaji'] ?: $band['display_name'], $band['display_name'], 'hidden');
																			}
																			else {
																				echo lang($band['romaji'] ?: $band['name'], $band['name'], 'hidden');
																			}	
																		?>
																	</a>
																<?php
																
																if(strlen($band['display_romaji'])) {
																	echo lang('('.$band['display_name'].')', null, 'hidden');
																}
																elseif(strlen($band['romaji'])) {
																	echo lang('('.$band['name'].')', null, 'hidden');
																}
																
															}
															else {
																echo $band["romaji"] ?: $band["name"].($band["romaji"] ? " (".$band["name"].")" : null);
															}

															if(is_array($band["notes"])) {
																foreach($band["notes"] as $note) {
																	?>
																		<span class="any__note"><?php echo $note; ?></span>
																	<?php
																}
															}

															echo $band_key + 1 < count($period) ? ", " : null;
														}
													?>
												</li>
											<?php
										}
									}
								?>
							</ul>
						</div>

						<h3 class="h--nav">
							<span><?php echo lang('Tags', 'タグ', ['container' => 'div']); ?></span>
						</h3>
						<div class="text text--outlined">
							<?php
								if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
									foreach($rslt_curr_tags as $tag) {
										echo '<a class="symbol__tag" style="display: inline-block;" href="/search/musicians/?tags[]='.$tag["friendly"].'">'.
											lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).
											($tag["num_times_tagged"] > 0 ? ' <span class="any__note">×'.$tag["num_times_tagged"].'</span>' : null).
											'</a> &nbsp; ';
									}
									echo '<hr />';
								}

								echo '<h5>'.lang('Add tags', 'タグする', ['secondary_class' => 'any--hidden']).'</h5>';
								if($_SESSION["loggedIn"]) {
									if(is_array($rslt_tags) && !empty($rslt_tags)) {
										foreach($rslt_tags as $tag) {
											$is_selected = is_array($rslt_user_tags) && !empty($rslt_user_tags) && in_array($tag["id"], $rslt_user_tags);
											echo '<label data-id="'.$musician["id"].'" data-tag_id="'.$tag["id"].'" class="musician__tag any__tag symbol__tag '.($is_selected ? "any__tag--selected" : null).'" style="display: inline-block;">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</label> ';
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
												echo '<label data-action="delete" data-id="'.$musician["id"].'" data-tag_id="'.$tag["id"].'" class="musician__tag symbol__tag any__tag any__tag--selected" style="display: inline-block;">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</label> ';
											}
										}
									}
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
	}
	else {
		include("../musicians/page-index.php");
	}
?>