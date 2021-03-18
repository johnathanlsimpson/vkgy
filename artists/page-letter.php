<?php
	include('../php/function-render_component.php');
	$markdown_parser = new parse_markdown($pdo);
	
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
	
	subnav([
		lang('Artist list', 'アーティスト一覧', [ 'secondary_class' => 'any--hidden' ]) => '/artists/',
		lang('Search', 'サーチ', [ 'secondary_class' => 'any--hidden' ]) => '/search/artists/',
	]);
	
	$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ-";
	
	$page_header = lang('Visual kei artist list', 'ビジュアル系アーティストの一覧', [ 'container' => 'div' ]);
?>

<div class="col c1">
	<?php
		if($error) {
			?>
				<div class="text text--outlined text--error symbol__error">
					<?php echo $error; ?>
				</div>
			<?php
		}
	?>
	
	<?php if($_GET['letter'] === 'a') { ?>
<div class="col c1">
	<div class="genre__wrapper">
		
		<h2>
			<?= lang('VK subgenres', 'V系ジャンル', 'div'); ?>
		</h2>
		<ul class="genre__container any--margin">
			
			<li class="genre__item text" style="background-image:url(/test/subgenre-kote.png);">
				
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=kote-kei#result">
					
					<div class="genre__title h2">
						<?= lang('kote kei', 'コテ系', 'div'); ?>
					</div>
					
					<div class="genre__description">
						A classic subgenre unique to vkei, born in the late 90s. Typically speedy and aggressive music coupled with dark, surreal, horror visuals.
					</div>
					
				</a>
				
				<div class="genre__examples any--weaken">
					
					<a class="genre__example" href="/artists/due-le-quartz/" style="background-image:url(/artists/due-le-quartz/main.small.jpg);">Dué le quartz</a>
					
					<a class="genre__example" href="/artists/la-veil-mizeria/" style="background-image:url(/artists/la-veil-mizeria/main.small.jpg);">La'veil MizeriA</a>
					
					<a class="genre__example" href="/artists/phantasmagoria/" style="background-image:url(/artists/phantasmagoria/main.small.jpg);">Phantasmagoria</a>
					
					<a class="genre__example" href="/artists/verxina/" style="background-image:url(/artists/verxina/main.small.jpg);"><?= lang('VERXINA', 'ヴィルシーナ', 'hidden'); ?></a>
					
					<a class="genre__example" href="/artists/virge/" style="background-image:url(/artists/virge/main.small.jpg);"><?= lang('Virge', 'ヴァージュ', 'hidden'); ?></a>
					
				</div>
				
			</li>
			
			<li class="genre__item genre__kira text" style="background-image:url(/test/subgenre-kirakira.png);">
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=kirakira-kei#result">
					<div class="genre__title h2">
						<?= lang('kirakira', 'キラキラ系', 'div'); ?>
					</div>
				</a>
			</li>
			
			<li class="genre__item genre__menhera text" style="background-image:url(/test/subgenre-menhera.png);">
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=menhera-kei#result">
					<div class="genre__title h2">
						<?= lang('menhera', 'メンヘラ系', 'div'); ?>
					</div>
				</a>
			</li>
			
			<li class="genre__item genre__sofubi text" style="background-image:url(/test/subgenre-sofubi.png);">
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=soft-visual#result">
					<div class="genre__title h2">
						<?= lang('soft visual', 'ソフビ系', 'div'); ?>
					</div>
				</a>
			</li>
			
			<li class="genre__item genre__tanbi text" style="background-image:url(/test/subgenre-tanbi.png);">
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=tanbi-kei#result">
					<div class="genre__title h2">
						<?= lang('tanbi', '耽美系', 'div'); ?>
					</div>
				</a>
			</li>
			
			<li class="genre__item genre__wafuu text" style="background-image:url(/test/subgenre-wafuu.png);">
				<a class="genre__link" href="https://vk.gy/search/artists/?tags[]=wafuu#result">
					<div class="genre__title h2">
						<?= lang('wafuu', '和風系', 'div'); ?>
					</div>
				</a>
			</li>
			
			<li class="genre__item">
				<a class="genre__link genre__more a--outlined a--padded" href="/search/artists/">
					<div class="genre__title h2">
						<?= lang('search', 'サーチ', 'div'); ?>
					</div>
				</a>
			</li>
			
		</ul>
		
		<div class="symbol__help any--weaken-color" style="margin: -2rem 0 3rem 0;">
			Illustrations by <a href="https://www.instagram.com/darrylpyon/" target="_blank">@darrylpyon</a>
		</div>
		
	</div>
</div>

<style>
	.genre__container {
		display: grid;
		grid-gap: 1rem;
		grid-template-columns: repeat(4, minmax(0, 25%));
		grid-auto-rows: 175px;
	}
	@media(max-width:1199.99px) {
		.genre__container {
			grid-template-columns: repeat(3, minmax(0, 33%));
		}
	}
	@media(max-width:599.99px) {
		.genre__container {
			grid-template-columns: repeat(2, minmax(0, 50%));
		}
	}
	
	.genre__item {
		background-position: center 35%;
		background-size: 140% auto;
		border: none;
		display: flex;
		flex-direction: column;
		margin: 0;
		padding: 0;
	}
	.genre__item:first-of-type {
		background-color: #8c6cdd;
		grid-column: 1 / 3;
		grid-row: 1 / 4;
	}
	.genre__wafuu { background-color: #6a8950; }
	.genre__tanbi { background-color: #3648a2; }
	.genre__menhera { background-color: #e05a54; }
	.genre__sofubi { background-color: #b5b3c1; }
	.genre__kira { background-color: #ffc55c; }
	.genre__link {
		align-items: stretch;
		background: linear-gradient( hsla(var(--background--bold), 0.0) 20%, hsla(var(--background--bold), 0.9) 90% );
		display: flex;
		flex: 1;
		flex-direction: column;
		padding: 1rem 1rem 0 1rem;
		transition: opacity 0.1s ease-in-out;
	}
	.genre__link:not(.genre__more), .genre__link:not(.genre__more):hover, .genre__link:not(.genre__more) .any--weaken {
		color: hsl(var(--text));
	}
	.genre__link:not(.genre__more):hover, .genre__link:hover + .genre__examples {
		animation: bubble 1.0s forwards;
		animation-delay: 0.0s;
	}
	@keyframes bubble {
		0% { opacity: 1; }
		100% { opacity: 0; }
	}
	.genre__more {
		background: none;
	}
	.genre__more .genre__title {
		color: inherit;
	}
	
	.genre__title {
		line-height: 1;
		margin: auto 0 1rem 0;
		padding: 0;
	}
	#language-ja:checked~* .genre__title .any--ja {
		font-weight: bold;
	}
	.genre__title::before {
		display: none;
	}
	.genre__examples {
		background: hsla(var(--background--bold), 0.9);
		display: flex;
		justify-content: space-between;
		padding: 1rem;
	}
	.genre__example {
		background-size: cover;
		border-radius: 50%;
		color: transparent;
		display: inline-flex;
		height: 100px;
		overflow: hidden;
		width: 100px;
	}
	.genre__example:hover {
		color: transparent;
		opacity: 0.75;
	}
	@media(max-width:949.99px) {
		.genre__example:nth-of-type(n + 5) {
			display: none;
		}
	}
	@media(max-width:749.99px) {
		.genre__example:nth-of-type(n + 4) {
			display: none;
		}
	}
	@media(max-width:349.99px) {
		.genre__example:nth-of-type(n + 3) {
			display: none;
		}
	}
</style>
	<?php } ?>
	
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
	
	<div class="col c4-AAAB">
		<ul class="text any--flex" style="flex-wrap: wrap;" data-lazyload-artists data-letter="<?php echo $str[$i]; ?>">
			<?php
				for($n=0; $n<$num_artists; $n++) {
					$artist_image_url = '/artists/'.$artist_list[$n]['friendly'].'/main.medium.jpg';
					$artist_image_exists = image_exists($artist_image_url, $pdo, true) ?: null;
					
					?>
						<li class="list__item any--flex">
							
							<a class="list__visual <?= !$artist_image_exists ? 'list--no-image' : null; ?> any--weaken" href="<?= '/artists/'.$artist_list[$n]['friendly'].'/'; ?>">
								<?php
									if($artist_image_exists) {
										?>
											<img class="list__image lazy" data-src="<?= $artist_image_url; ?>" style="height: <?= $artist_image_exists['ratio'] * 150; ?>px; width: 150px;" />
										<?php
									}
								?>
							</a>
							
							<div class="list__details any--weaken-color any--flex-grow">
								
								<div class="list__pronunciation any--weaken" style="float:right;">
									<?=
										($artist_list[$n]['pronunciation']).
										($artist_list[$n]['pronunciation'] && $artist_list[$n]['needs_hint'] ? '<br />' : null).
										($artist_list[$n]['needs_hint'] ? $artist_list[$n]['friendly'] : null);
									?>
								</div>
								<div>
									<?= lang(
										'<a href="/artists/'.$artist_list[$n]['friendly'].'/">'.($artist_list[$n]["romaji"] ?: $artist_list[$n]["name"]).($artist_list[$n]['romaji'] ? '<div class="any--weaken">'.$artist_list[$n]['name'].'</div>' : null).'</a>',
										'<a href="/artists/'.$artist_list[$n]['friendly'].'/">'.$artist_list[$n]['name'].'</a>',
										'hidden'
									); ?>
								</div>
								
								<?php
									$description_limit = 15;
									$description = $artist_list[$n]['description'];
									$description = $markdown_parser->parse_markdown($description);
									$description = strip_tags($description);
									$description = explode(' ', $description);
									$description = count($description) > 15 ? implode(' ', array_slice($description, 0, 15)).'...' : implode(' ', $description);
									
									echo '<div class="list__description">'.$description.'</div>';
									
									$tag_names = array_unique(array_filter(explode(',', $artist_list[$n]['tag_names'])));
									$tag_romajis = explode(',', $artist_list[$n]['tag_romajis']);
									$tag_friendlys = explode(',', $artist_list[$n]['tag_friendlys']);
								?>
								<div class="list__tags">
									<?php
										if(is_array($tag_names) && !empty($tag_names)) {
											foreach($tag_names as $tag_key => $tag_name) {
												?>
													<a class="list__tag a--inherit symbol__tag" href=""><?= lang($tag_romajis[$tag_key], $tag_name, 'hidden'); ?></a>
												<?php
											}
										}
									?>
								</div>
							</div>
						</li>
					<?php
				}
			?>
		</ul>
		<div>
			<?php
				$sql_new_artists = 'SELECT artists.name, artists.romaji, artists.friendly, COALESCE(artists.romaji, artists.name) AS quick_name, artists.id FROM artists_bio LEFT JOIN artists ON artists.id=artists_bio.artist_id WHERE artists_bio.type LIKE CONCAT("%", ?, "%") ORDER BY artists_bio.date_occurred DESC LIMIT 10';
				$stmt_new_artists = $pdo->prepare($sql_new_artists);
				$stmt_new_artists->execute([ '(10)' ]);
				$new_artists = $stmt_new_artists->fetchAll();
				
				if(is_array($new_artists) && !empty($new_artists)) {
					shuffle($new_artists);
					$new_artists = array_slice($new_artists, 0, 1);
				}
				
				$sql_added_artists = 'SELECT name, romaji, friendly FROM artists ORDER BY id DESC LIMIT 5';
				$stmt_added_artists = $pdo->prepare($sql_added_artists);
				$stmt_added_artists->execute();
				$recently_added_artists = $stmt_added_artists->fetchAll();
				
				$sql_artist_edits = 'SELECT artists.name, artists.romaji, artists.friendly, users.username, edits_artists.date_occurred FROM edits_artists INNER JOIN (SELECT MAX(id) AS id FROM edits_artists GROUP BY artist_id ORDER BY id DESC LIMIT 30) max_edit_artist_ids ON edits_artists.id=max_edit_artist_ids.id LEFT JOIN artists ON artists.id=edits_artists.artist_id LEFT JOIN users ON users.id=edits_artists.user_id';
				$stmt_artist_edits = $pdo->prepare($sql_artist_edits);
				$stmt_artist_edits->execute();
				$artist_edits = $stmt_artist_edits->fetchAll();
				
				$sql_video = 'SELECT artists.name, artists.romaji, artists.friendly, videos.youtube_id FROM videos LEFT JOIN artists ON artists.id=videos.artist_id ORDER BY videos.date_occurred DESC LIMIT 1';
				$stmt_video = $pdo->prepare($sql_video);
				$stmt_video->execute();
				$recent_video = $stmt_video->fetch();
				
				if(is_array($new_artists) && !empty($new_artists)) {
					?>
						<h3>
							<?= lang('New band', '新盤', 'div'); ?>
						</h3>
					<?php
					
					foreach($new_artists as $new_artist) {
						?>
							<div class="card--small"><?= $access_artist->artist_card($new_artist); ?></div>
						<?php
					}
				}
				
				if(is_array($recent_video) && !empty($recent_video)) {
					?>
						<h3>
							<div class="h5">
								<a class="artist a--inherit" href="<?= '/artists/'.$recent_video['friendly'].'/'; ?>"><?= lang($recent_video['romaji'] ?: $recent_video['name'], $recent_video['name'], 'hidden'); ?></a>
							</div>
							<?= lang('Latest video', '最近の動画', 'div'); ?>
						</h3>
						<div class="any--margin">
							<a class="video__thumbnail lazy any--margin" data-id="<?= $recent_video['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$recent_video['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$recent_video['youtube_id']; ?>" target="_blank"></a>
						</div>
					<?php
				}
				
				if(is_array($recently_added_artists) && !empty($recently_added_artists)) {
					?>
						<h3>
							<?= lang('Recently added', '最近追加した', 'div'); ?>
						</h3>
						<ul class="text text--outlined">
							<?php
								foreach($recently_added_artists as $recent_artist) {
									?>
										<li>
											<a class="artist" href="/artists/<?= $recent_artist['friendly']; ?>/"><?= lang($recent_artist['romaji'] ?: $recent_artist['name'], $recent_artist['name'], 'hidden'); ?></a>
										</li>
									<?php
								}
							?>
						</ul>
					<?php
				}
				
				if(is_array($artist_edits) && !empty($artist_edits)) {
					?>
						<h3>
							<?= lang('Recent edits', '最近の更新', 'div'); ?>
						</h3>
						<ul class="text text--outlined">
							<?php
								foreach($artist_edits as $artist_edit) {
									?>
										<li>
											<a class="artist" href="/artists/<?= $artist_edit['friendly']; ?>/"><?= lang($artist_edit['romaji'] ?: $artist_edit['name'], $artist_edit['name'], 'hidden'); ?></a>
											<div class="h5">
												<?= $artist_edit['date_occurred'].' by <a class="user a--inherit" href="/users/'.$artist_edit['username'].'/">'.$artist_edit['username'].'</a>'; ?>
											</div>
										</li>
									<?php
								}
							?>
						</ul>
					<?php
				}
			?>
		</div>
	</div>
</div>