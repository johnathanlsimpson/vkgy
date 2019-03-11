<?php
	if(is_array($label) && !empty($label)) {
		?>
			<div class="col c1">
				<div>
					<h1>
						<a class="symbol__company a--inherit" href="/labels/<?php echo $label["friendly"]; ?>/"><?php echo $label["quick_name"]; ?></a>
						label profile
					</h1>
					
					<div class="text text--outlined">
						<a class="symbol__company" href="/labels/<?php echo $label["friendly"]; ?>/"><?php echo $label["quick_name"]; ?></a>
						<div class="any--weaken-color"><?php echo $label["romaji"] ? $label["name"] : null; ?></div>
						
						<p class="any--weaken-color">
							<?php echo $label["date_started"].($label["date_started"] || $label["date_ended"] ? " ~ " : null).$label["date_ended"]; ?>
						</p>
						
						<?php
							if($label["description"]) {
								?>
									<p>
										<?php echo $label["description"]; ?>
									</p>
								<?php
							}
							
							if(is_array($label["president"]) || is_array($label["parent_label"])) {
								?>
									<ul class="ul--inline">
										<?php
											if(is_array($label["parent_label"])) {
												?>
													<li>
														<h5>
															Parent company
														</h5>
														<span class="any__note">
															<a class="a--inherit symbol__company" href="/labels/<?php echo $label["parent_label"]["friendly"]; ?>/"><?php echo $label["parent_label"]["quick_name"]; ?></a>
														</span>
														<span class="any--weaken-jp"><?php echo $label["parent_label"]["romaji"] ? ' ('.$label["parent_label"]["name"].')' : null; ?></span>
													</li>
												<?php
											}
											
											if(is_array($label["sublabels"]) && !empty($label["sublabels"])) {
												?>
													<li>
														<h5>
															Sublabel<?php echo count($label["sublabels"]) === 1 ? null : "s"; ?>
														</h5>
														<?php
															for($i = 0; $i < count($label["sublabels"]); $i++) {
																?>
																	<span class="any__note">
																		<a class="a--inherit symbol__company" href="/labels/<?php echo $label["sublabels"][$i]["friendly"]; ?>/"><?php echo $label["sublabels"][$i]["quick_name"]; ?></a>
																	</span>
																	<span class="any--weaken-jp"><?php echo $label["sublabels"][$i]["romaji"] ? ' ('.$label["sublabels"][$i]["name"].')' : null; ?></span>
																<?php
																echo $i + 1 != count($label["sublabels"]) ? ", " : null;
															}
														?>
													</li>
												<?php
											}
											
											if(is_array($label["president"])) {
												?>
													<li>
														<h5>
															President
														</h5>
														<?php
															if(!empty($label["president"]["friendly"])) {
																?>
																	<a class="a--inherit" href="/musicians/<?php echo $label["president"]["id"]."/".$label["president"]["friendly"]; ?>/"><?php echo $label["president"]["quick_name"]; ?></a>
																	<span class="any--weaken-jp"><?php echo $label["president"]["romaji"] ? ' ('.$label["president"]["name"].')' : null; ?></span>
																<?php
															}
															else {
																echo $label["president"]["romaji"] ? $label["president"]["romaji"]." (".$label["president"]["name"].")" : $label["president"]["name"];
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
						if(!empty($label["official_links"])) {
							$label["official_links"] = explode("\n", $label["official_links"]);
							
							if(is_array($label["official_links"]) && !empty($label["official_links"])) {
								?>
									<div class="col c1">
										<div>
											<h2>
												Official links
											</h2>
											<div class="any--weaken text text--outlined">
												<ul class="ul--bulleted">
													<?php
														foreach($label["official_links"] as $url) {
															$url = preg_replace("/"."^\s*(.+)\s*$"."/", "$1", $url);
															?>
																<li>
																	<a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a>
																	<a class="a--inherit" href="http://web.archive.org/web/*/<?php echo $url; ?>" target="_blank">(archived)</a>
																</li>
															<?php
														}
													?>
												</ul>
											</div>
										</div>
									</div>
								<?php
							}
						}
					?>
					
					<?php
						if(is_array($label["artists"]) && !empty($label["artists"])) {
							?>
								<h2>
									Artist list
								</h2>
								<div class="text">
									<ul>
										<?php
											foreach($label["artists"] as $key => $artist) {
												?>
													<li>
														<a class="artist" href="/artists/<?php echo $artist["friendly"]; ?>/">
															<?php echo $artist["quick_name"]; ?>
															<span class="any--weaken-size"><?php echo $artist["romaji"] ? "(".$artist["name"].")" : null; ?></span>
														</a>
													</li>
												<?php
											}
										?>
									</ul>
								</div>
							<?php
						}
					?>
					
					
					
					<?php
						if(is_array($label["releases"])) {
							?>
								<h2>
									Release history
								</h2>
								<div class="text text--outlined">
									<table>
										<?php
											foreach($label["releases"] as $release) {
												?>
													<tr>
														<td class="any--no-wrap any--weaken-color"><?php echo $release["date_occurred"]; ?></td>
														<td>
															<a class="artist" href="/artists/<?php echo $release["artist"]["friendly"]; ?>/"><?php echo $release["artist"]["quick_name"]; ?></a>
														</td>
														<td>
															<a class="symbol__release" href="/releases/<?php echo $release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]; ?>/"><?php echo $release["quick_name"]; ?></a>
														</td>
														<td class="any--no-wrap any--align-right any--weaken-color"><?php echo $release["upc"]; ?></td>
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
			</div>
		<?php
	}
?>