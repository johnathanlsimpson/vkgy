<?php
	style('../artists/style-partial-card.css');
	
	$name = sanitize($artist['name']);
	$romaji = sanitize($artist['romaji']);
	$friendly = sanitize($artist['friendly']);
	
	$image_url = '/artists/'.$friendly.'/main.medium.jpg';
	$image_url = image_exists($image_url, $this->pdo) ? $image_url : null;
?>

<div class="card text text--compact">
	<a class="card__name a--inherit" href="<?= '/artists/'.$friendly.'/'; ?>"><span class="symbol__artist"></span><?= $romaji ? lang($romaji, $name, 'div') : $name; ?></a>
	
	<div class="card__image lazy" data-src="<?= $image_url; ?>">
		<ul class="card__nav ul--inline any--weaken-size">
			<li><a class="symbol__release" href="<?= '/releases/'.$friendly.'/'; ?>"><?= lang('Music', 'リリース', 'hidden'); ?></a></li>
			<li><a class="symbol__news" href="<?= '/blog/artist/'.$friendly.'/'; ?>"><?= lang('News', 'ニュース', 'hidden'); ?></a></li>
			<?php
				if($_SESSION['is_admin']) {
					?>
						<li><a class="symbol__edit" href="<?= '/artists/'.$friendly.'/edit/'; ?>"><?= lang('Edit', '編集', 'hidden'); ?></a></li>
					<?php
				}
			?>
		</ul>
	</div>
</div>