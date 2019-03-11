<?php
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/artists/style-page-letter.css",
	]);
	
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/script-initSelectize.js",
		"/artists/script-page-letter.js",
	]);
	
	$list_template = str_replace(["\t", "\r", "\n"], "", '
		<li class="artist-list__item" data-recent="{8}" data-is_exclusive="{9}">
			<div class="h5 artist-list__history">{1}</div>
			<a class="artist artist-list__link artist artist--no-symbol" data-name="{2}" data-quickname="{3}" href="{4}">
				<span class="artist-list__name">{5}</span> <span class="any__note artist-list__hint">{6}</span><br />
				<span class="artist-list__jp any--jp any--weaken">{7}</span>
			</a>
		</li>
	');
	
	$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ-";
?>

<div class="col c1">
	<div>
		<h1>
			Artist list
		</h1>
	</div>
	
	<?php
		if($error) {
			?>
				<div class="text text--outlined text--error symbol__error">
					<?php echo $error; ?>
				</div>
			<?php
		}
	?>
	
	<div class="any--flex any--margin controls__container">
		<div class="controls__letters">
			<?php
				for($i=0; $i < strlen($str); $i++) {
					?>
						<a class="a--padded <?php echo $str[$i] === strtoupper($_GET["letter"]) ? "a--outlined" : null; ?>" href="/artists/&letter=<?php echo strtolower($str[$i]); ?>"><?php echo $str[$i] === "-" ? "#" : $str[$i]; ?></a>
					<?php
				}
			?>
		</div>
		<div class="controls__search">
				<span data-contains="artists" hidden><?php echo json_encode($full_artist_list); ?></span>
				<select class="input" id="artist_jump" placeholder="jump to artist" data-source="artists">
					<option></option>
				</select>
		</div>
	</div>
	
	<div>
		<input class="any--hidden" id="all" name="filter" value="all" type="radio" checked />
		<input class="any--hidden" id="exclusive" name="filter" value="exclusive" type="radio" />
		<input class="any--hidden" id="recent" name="filter" value="recent" type="radio" />
		
		<div class="any--flex">
			<h2 class="controls__title">
				<?php echo $_GET["letter"] === '-' ? '#' : strtoupper($_GET["letter"]); ?>
			</h2>
			<div>
				<label class="controls__control input__checkbox-label input__checkbox-label--selected" data-filter for="all">All</label>
				<label class="controls__control input__checkbox-label" data-filter for="recent">Recent</label>
				<label class="controls__control input__checkbox-label" data-filter for="exclusive">Exclusive</label>
			</div>
		</div>
		<div class="text">
			<ul class="artist-list__container" data-lazyload-artists data-letter="<?php echo $str[$i]; ?>">
				<?php
					for($n=0; $n<$num_artists; $n++) {
						echo str_replace(
							[
								//"{1}",
								"{2}",
								"{3}",
								"{4}",
								"{5}",
								"{6}",
								"{7}",
								"{8}",
								"{9}",
							],
							[
								//substr($artist_list[$n]["edit_history"], 0, 10),
								$artist_list[$n]["name"],
								($artist_list[$n]["romaji"] ?: $artist_list[$n]["name"]),
								'/artists/'.$artist_list[$n]["friendly"].'/',
								($artist_list[$n]["romaji"] ?: $artist_list[$n]["name"]),
								($artist_list[$n]["needs_hint"] ? $artist_list[$n]["friendly"] : null),
								($artist_list[$n]["romaji"] ? $artist_list[$n]["name"] : null),
								($artist_list[$n]["edit_history"] > date("Y-m-d", strtotime("-1 month")) ? "true" : "false"),
								($artist_list[$n]["is_exclusive"] ? "1" : "0"),
							],
							$list_template
						);
					}
				?>
			</ul>
		</div>
	</div>
</div>