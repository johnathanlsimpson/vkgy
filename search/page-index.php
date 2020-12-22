<?php
foreach(['artists', 'labels', 'musicians', 'posts', 'releases'] as $key) {
	if(!is_array($results[$key])) {
		$results[$key] = [];
	}
}
?>

<div class="col c1">
	<div>
		<h2>
			Search vkgy
		</h2>
		
		<form action="/search/" enctype="multipart/form-data" method="get" name="form__search">
			<div class="text">
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--flex-grow" name="q" placeholder="search for..." value="<?php echo $q; ?>" />
					</div>
				</div>
				
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<button class="any--flex-grow" type="submit">
							Submit search
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	if(strlen($q) > 0) {
		?>
			<div class="col c1">
				<div>
					<h2>
						Results for <code><?php echo $q; ?></code>
					</h2>
				</div>
			</div>
		<?php
	}
?>

<?php
	if(strlen($q)) {
		if(empty(array_filter($results))) {
			?>
				<div class="col c1" id="result">
					<div>
						<div class="text text--outlined text--error">
							<p class="symbol__error">Sorry, no results were found for &ldquo;<?php echo $q; ?>&rdquo;.</p>
							<p>Try an <a href="/search/artists/">advanced artist search</a>, <a href="/search/releases/">advanced release search</a>, or <a href="/search/musicians/">advanced musician search</a>.</p>
						</div>
					</div>
				</div>
			<?php
		}
		else {
			?>
				<div class="col c4-ABBB">
					<div>
						<h3 style="margin-bottom: 2rem;">
							<span class="symbol__artist"></span>
							Artists
						</h3>
						<input class="obscure__input" id="obscure-artists" type="checkbox" <?php echo is_array($results['artists']) && count($results["artists"]) > 10 ? 'checked' : null; ?> />
						<div class="obscure__container obscure--faint">
							<?php
								if(is_array($results['artists']) && count($results["artists"]) > 0) {
									$result = $results["artists"];
									?>
										<?php
												for($i = 0; $i < count($result); $i++) {
													echo '<div class="card--small lazy obscure__item" style="margin-top: -2rem;">';
													echo $access_artist->artist_card($result[$i]);
													echo '</div>';
												}
											?>
									<?php
								}
								else {
									echo '<div class="text text--outlined" style="margin-top: -2rem;">No artists found. Try an <a href="/search/artists/">advanced artist search</a>.</div>';
								}
							?>
							<label class="input__button obscure__button" for="obscure-artists">Show <?php echo is_array($result) ? count($result) - 3 : 0; ?> more</label>
						</div>
					</div>
					
					<div>
						<div class="col c2">
							<div>
								<h3>
									<span class="symbol__company"></span>
									Labels
								</h3>
								<input class="obscure__input" id="obscure-labels" type="checkbox" <?php echo count($results["labels"]) > 10 ? 'checked' : null; ?> />
								<div class="text text--outlined obscure__container obscure--faint">
									<?php
										if(is_array($results['labels']) && count($results["labels"]) > 0) {
											$result = $results["labels"];
											?>
												<ul>
													<?php
														for($i=0; $i<count($result); $i++) {
															?>
																<li class="obscure__item">
																	<?php echo '<a class="symbol__company" href="/labels/'.$result[$i]["friendly"].'/">'.$result[$i]["quick_name"].'</a>'; ?>
																</li>
															<?php
														}
													?>
												</ul>
											<?php
										}
										else {
											echo 'No labels found.';
										}
									?>
									<label class="input__button obscure__button" for="obscure-labels">Show <?php echo is_array($result) ? count($result) - 3 : 0; ?> more</label>
								</div>
							</div>
							
							<div>
								<h3>
									<span class="symbol__musician"></span>
									Musicians
								</h3>
								<div class="text text--outlined">
									<?php
										if(is_array($results['musicians']) && count($results["musicians"]) > 0) {
											$result = $results["musicians"];
											?>
												<ul class="ul ul--inline">
													<?php
														for($i=0; $i<count($result); $i++) {
															?>
																<li>
																	<?php echo '<a class="symbol__musician" href="/musicians/'.$result[$i]["id"].'/'.$result[$i]["friendly"].'/">'.$result[$i]["quick_name"].'</a>'.($result[$i]["romaji"] ? ' <span class="any--weaken-color">('.$result[$i]["name"].')</span>' : null); ?>
																</li>
															<?php
														}
													?>
												</ul>
											<?php
										}
										else {
											echo 'No musicians found. Try an <a href="/search/musicians/">advanced musician search</a>.';
										}
									?>
								</div>
							</div>
						</div>
						
						<div>
							<h3>
								<span class="symbol__release"></span>
								Releases
							</h3>
							<input class="obscure__input" id="obscure-release" name="obscure-release" type="checkbox" <?php echo count($results["releases"]) > 10 ? 'checked' : null; ?> />
							<div class="text obscure__container">
								<?php
									if(is_array($results['releases']) && count($results["releases"]) > 0) {
										$result = $results["releases"];
										?>
											<ul class="ul">
												<?php
													for($i=0; $i<count($result) && $i < 25; $i++) {
														?>
															<li class="obscure__item">
																<?php
																	echo '<h5>'.$result[$i]["date_occurred"].'</h5>';
																	echo '<a class="symbol__release" href="/releases/'.$result[$i]["artist"]["friendly"].'/'.$result[$i]["id"].'/'.$result[$i]["friendly"].'/">'.$result[$i]["quick_name"].'</a>';
																	echo '<span class="any--weaken-color"> <a class="a--inherit artist" href="/artists/'.$result[$i]["artist"]["friendly"].'/">'.$result[$i]["artist"]["quick_name"];
																	echo '</a>';
																	echo ($result[$i]["artist"]["romaji"] ? ' <span class="any--weaken">('.$result[$i]["artist"]["name"].')</a>' : null);
																	echo '</span>';
																?>
															</li>
														<?php
													}
												?>
											</ul>
										<?php
									}
									else {
										echo 'No releases found. Try an <a href="/search/releases/">advanced release search</a>.';
									}
								?>
								<label class="obscure__button input__button" for="obscure-release">Show <?php echo is_array($result) ? count($result) - 3 : 0; ?> more</label>
							</div>
						</div>
						
						<div>
							<h3>
								Blog posts
							</h3>
							<input class="obscure__input" id="obscure-posts" type="checkbox" <?php echo count($results["posts"]) > 10 ? 'checked' : null; ?> />
							<div class="text obscure__container">
								<?php
									if(is_array($results['posts']) && count($results["posts"]) > 0) {
										$result = $results["posts"];
										?>
											<ul class="ul">
												<?php
													for($i=0; $i<count($result) && $i < 25; $i++) {
														?>
															<li class="obscure__item">
																<?php
																	echo '<h5>'.substr($result[$i]["date_occurred"], 0, 10).'</h5>';
																	echo '<a href="/blog/'.$result[$i]["friendly"].'/">'.$result[$i]["title"].'</a>';
																	echo '<div class="any--weaken">'.strip_tags($result[$i]["content"]).'</div>';
																?>
															</li>
														<?php
													}
												?>
											</ul>
											<label class="input__button obscure__button" for="obscure-posts">Show <?php echo is_array($result) ? count($result) - 3 : 0; ?> more</label>
										<?php
									}
									else {
										echo 'No blog posts found.';
									}
								?>
							</div>
						</div>
					</div>
				</div>
			<?php
		}
	}
?>