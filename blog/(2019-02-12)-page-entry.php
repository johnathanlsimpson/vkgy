<?php
	if(is_array($entry) && !empty($entry)) {
		$page_image = "https://vk.gy".$entry["image"];
		
		$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
		$stmt_twitter = $pdo->prepare($sql_twitter);
		$stmt_twitter->execute([$entry["username"]]);
		$rslt_twitter = $stmt_twitter->fetchColumn();
		
		if(!empty($rslt_twitter) && preg_match("/"."^[A-z0-9_]+$"."/", $rslt_twitter)) {
			$page_creator = $rslt_twitter;
		}
		
		$page_description = preg_replace("/"."<.*?>"."/", "", strtok($entry["content"], "\n"))." (Continued…)";
		
		style(["/blog/style-page-entry.css"]);
		
		?>
			<div class="col c1">
				<div>
					<h1>
						News
					</h1>
				</div>
				<div class="any--flex any--margin">
					<?php
						if(is_array($entry["prev_next"])) {
							foreach($entry["prev_next"] as $prev_next) {
								?>
									<div class="any--flex-grow any--no-wrap <?php echo $prev_next["type"] === "next" ? "any--align-right" : null; ?>">
										<a class="" href="/blog/<?php echo $prev_next["friendly"]; ?>/">
											<?php echo $prev_next["type"] === "prev" ? '<span class="symbol__previous"></span>' : null; ?>
											<?php echo $prev_next["title"]; ?>
											<?php echo $prev_next["type"] === "next" ? '<span class="symbol__next"></span>' : null; ?>
										</a>
									</div>
								<?php
							}
						}
					?>
				</div>
			</div>
			
			<div class="col c4-AAAB">
				<div>
					<h2>
						<?php echo $entry["title"]; ?>
					</h2>
					<div class="text">
						<?php
							if($entry["image"]) {
								?>
									<div class="entry__image-container" style="background-image: url(<?php echo $entry["image"]; ?>);">
										<a class="entry__image-link" href="<?php echo $entry["image"]; ?>" target="_blank">
											<img alt="<?php echo $entry["title"]; ?>" class="entry__image" src="<?php echo $entry["image"]; ?>" />
										</a>
									</div>
								<?php
							}
						?>
						<div class="text--centered">
							<?php
								echo $entry["content"];
								
								if(is_array($entry["tags"]) && !empty($entry["tags"])) {
									foreach($entry["tags"] as $tag) {
										if($tag["friendly"] === "auto-generated") {
											echo '<hr />';
											echo '<p class="any--weaken-color symbol__error">';
											echo 'This post was automatically generated, and may be inaccurate. Any user may edit the post, until the &ldquo;auto generated&rdquo; tag is removed.';
											echo '</p>';
											break;
										}
									}
								}
							?>
						</div>
					</div>
				</div>
				
				<div>
					<h3>
						Data
					</h3>
					<div class="text text--outlined">
						<ul>
							<li>
								<h5>
									Published
								</h5>
								<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 0, 4); ?></a>-<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 5,2); ?></a>-<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 8,2); ?></a>
							</li>
							<li>
								<h5>
									Author
								</h5>
								<a class="user a--inherit" href="/users/<?php echo $entry["username"]; ?>/"><?php echo $entry["username"]; ?></a>
							</li>
							<?php
								if(is_array($entry["edit_history"]) && !empty($entry["edit_history"])) {
									?>
										<li>
											<h5>
												Edit history
											</h5>
											
											<input class="obscure__input" id="obscure-edits" type="checkbox" <?php echo count($entry["edit_history"]) > 3 ? 'checked' : null; ?> >
											<ul class="ul--compact obscure__container obscure--faint">
												<?php
													foreach($entry["edit_history"] as $edit) {
														?>
															<li class="any--weaken obscure__item" style="border: none;">
																<?php echo $edit["date_occurred"]; ?>
																by
																<a class="user a--inherit" href="/users/<?php echo $edit["username"]; ?>/"><?php echo $edit["username"]; ?></a>
															</li>
														<?php
													}
												?>
												<label class="input__button obscure__button" for="obscure-edits">Show <?php echo count($entry["edit_history"]) - 3; ?> more</label>
											</ul>
										</li>
									<?php
								}
								
								if(is_array($entry["tags"]) && !empty($entry["tags"])) {
									?>
										<li>
											<h5>
												Tags
											</h5>
											<?php
												foreach($entry["tags"] as $tag) {
													?>
														<a class="tag symbol__tag" href="/blog/tag/<?php echo $tag["friendly"]; ?>/"><?php echo $tag["name"]; ?></a>
													<?php
												}
											?>
										</li>
									<?php
								}
								
								if(is_array($entry["tags_artists"]) && !empty($entry["tags_artists"])) {
									?>
										<li>
											<h5>
												Tagged artists
											</h5>
											<?php
												foreach($entry["tags_artists"] as $tag) {
													?>
														<a class="tag symbol__tag" href="/blog/artist/<?php echo $tag["friendly"]; ?>/"><?php echo $tag["quick_name"]; ?></a>
													<?php
												}
											?>
										</li>
									<?php
								}
							?>
						</ul>
					</div>
					
					<?php
						if(is_array($entry["tags_artists"]) && !empty($entry["tags_artists"])) {
							?>
								<h3>
									Referenced artists
								</h3>
								<div class="card--small">
									<?php
										$i = 0;
										foreach($entry["tags_artists"] as $artist) {
											if(is_array($artist) && !empty($artist) && is_numeric($artist["id"]) && $i < 4) {
												$artist = $access_artist->access_artist(["id" => $artist["id"], "get" => "name"]);
												$access_artist->artist_card($artist);
												$i++;
											}
										}
									?>
								</div>
							<?php
						}
					?>
				</div>
			</div>
		<?php
		
		include('../comments/partial-comments.php');
		render_default_comment_section('blog', $entry['id'], $entry['comments'], $markdown_parser);
	}
?>