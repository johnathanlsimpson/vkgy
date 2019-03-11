<?php
	include_once("../php/include.php");
	include_once("../php/class-parse_markdown.php");
	
	if(!empty($_POST["content"]) && is_numeric($_POST["artist"])) {
		$markdown_parser = new parse_markdown($pdo);
		$access_artist = new access_artist($pdo);
		
		$artist_bio = $_POST["content"];
		$artist_bio = $markdown_parser->validate_markdown($artist_bio);
		$artist_bio = $access_artist->validate_bio($_POST["artist"], $artist_bio);
		
		if(!empty($artist_bio)) {
			?>
				<ul class="ul--compact">
					<?php
						foreach($artist_bio as $bio) {
							?>
								<li>
									<span class="any--weaken"><?php echo $bio["date_occurred"]; ?></span>
									<?php
										
										$bio["type"] = array_filter(array_unique(explode('(', str_replace(')', "", $bio["type"]))));
										if(is_array($bio["type"])) {
											foreach($bio["type"] as $type) {
												?>
													<span class="any__tag"><?php echo $access_artist->artist_bio_types[$type]; ?></span>
												<?php
											}
										}
										
										echo $markdown_parser->parse_markdown($bio["content"]);
										
										echo $bio["note"];
									?>
								</li>
							<?php
						}
					?>
				</ul>
			<?php
		}
	}
	
	$pdo->connection = null;
?>