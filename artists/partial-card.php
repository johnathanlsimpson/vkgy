<?php
	style('../artists/style-partial-card.css');
	
	$name = sanitize($artist['name']);
	$romaji = sanitize($artist['romaji']);
	$friendly = sanitize($artist['friendly']);
	
	$image_url = '/artists/'.$friendly.'/main.medium.jpg';
	$image_url = image_exists($image_url, $this->pdo) ? $image_url : null;
?>

<div class="text text--compact">
	<a class="card__edit any--weaken-size symbol__edit <?= !$_SESSION['is_admin'] ? 'any--hidden' : null; ?>" href="<?= '/artists/'.$friendly.'/edit/'; ?>">Edit</a>
	<a class="card__name" href="<?= '/artists/'.$friendly.'/'; ?>"><span class="symbol__artist"></span><?= $romaji ? lang($romaji, $name, 'div') : $name; ?></a>
	
	<div class="card__image lazy" data-src="<?= $image_url; ?>">
		<ul class="card__nav ul--inline">
			<li><a class="symbol__artist" href="<?= '/artists/'.$friendly.'/'; ?>"><?= lang('Band', 'バンド', 'hidden'); ?></a></li>
			<li><a class="symbol__release" href="<?= '/releases/'.$friendly.'/'; ?>"><?= lang('Music', 'リリース', 'hidden'); ?></a></li>
			<li><a class="symbol__news" href="<?= '/news/artist/'.$friendly.'/'; ?>"><?= lang('News', 'ニュース', 'hidden'); ?></a></li>
		</ul>
	</div>
</div>