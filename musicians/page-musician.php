<?php
	if(is_array($musician) && !empty($musician)) {
		
		style("../musicians/style-page-musician.css");
		
		?>
			<div class="col c1">
				<div>
					<h1>
						<a class="a--inherit symbol__musician" href="/musicians/<?php echo $musician["id"]."/".$musician["friendly"]; ?>/"><?php echo $musician["quick_name"]; ?></a> profile
					</h1>
					
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
																<a class="artist" href="/artists/<?php echo $band["friendly"]; ?>/"><?php echo $band["quick_name"]; ?></a>
																<?php echo $band["romaji"] ? " (".$band["name"].")" : null; ?>
															<?php
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
				</div>
			</div>
		<?php
	}
	else {
		include("../musicians/page-index.php");
	}
?>