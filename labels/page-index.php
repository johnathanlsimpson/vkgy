<?php
	if(is_array($labels) && !empty($labels)) {
		?>
			<div class="col c1">
				<div>
					<h1>
						Labels
					</h1>
					<h2>
						Full label list
					</h2>
					<div class="text">
						<?php
							foreach($labels as $key => $label) {
								$char = substr($label["friendly"], 0, 1);
								$char = ctype_alpha($char) ? $char : "#";

								echo $key === count($labels) || ($char !== $prev_char && $char !== "1") ? "</ul>" : null;

								if($char != $prev_char) {
									?>
										<ul>
											<li>
												<h4>
													<?php echo strtoupper($char); ?>
												</h4>
											</li>
									<?php
								}

								?>
									<li>
										<a class="symbol__company" href="/labels/<?php echo $label["friendly"]; ?>/">
											<?php echo $label["quick_name"]; ?>
											<span class="any--weaken-size"><?php echo $label["romaji"] ? "(".$label["name"].")" : null; ?></span>
										</a>
									</li>
								<?php
								$prev_char = $char;
							}
						?>
					</div>
				</div>
			</div>
		<?php
	}
?>