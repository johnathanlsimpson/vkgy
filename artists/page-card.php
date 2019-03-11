<?php
	style("../artists/style-page-card.css");
	
	if(isset($artist) && is_array($artist) && !empty($artist)) {
		array_walk_recursive($artist, function(&$value) { $value = sanitize($value); });
		?>
			<div class="text card any__obscure any__obscure--faint" style="background-image: url(/artists/<?php echo $artist["friendly"]; ?>/main.large.jpg);">
				<div class="card__inner">
					<span class="card__name">
						<a class="a--inherit artist h1 card__name-link" href="/artists/<?php echo $artist["friendly"]; ?>/" data-name="<?php echo $artist["name"]; ?>"><?php echo $artist["quick_name"]; ?></a>
						
						<?php
							if($artist["romaji"]) {
								?>
									<span class="any--weaken">(<?php echo $artist["name"]; ?>)</span>
								<?php
							}
						?>
					</span>
					<span class="card__links">
						<a class="card__link a--outlined a--padded symbol__release" href="/releases/<?php echo $artist["friendly"]; ?>/" style="vertical-align: middle;">Discography</a>
						<a class="card__link a--outlined a--padded symbol__artist" href="/artists/<?php echo $artist["friendly"]; ?>/" style="vertical-align: middle;">Profile</a>
						<?php
							if($_SESSION["admin"]) {
								?>
									<a class="card__link a--outlined a--padded symbol__edit" href="/artists/<?php echo $artist["friendly"]; ?>/edit/" style="vertical-align: middle;">Edit artist</a>
								<?php
							}
						?>
						<a class="card__link a--outlined a--padded symbol__news" href="/blog/artist/<?php echo $artist["friendly"]; ?>/" style="vertical-align: middle;">News</a>
					</span>
				</div>
			</div>
		<?php
	}
?>