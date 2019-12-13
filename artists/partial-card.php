<?php
	style('../artists/style-partial-card.css');
	
	$name = sanitize($artist['name']);
	$romaji = sanitize($artist['romaji']);
	$friendly = sanitize($artist['friendly']);
	$display_name = sanitize($artist['display_name']);
	$display_romaji = sanitize($artist['display_romaji']);
	
	$image_url = '/artists/'.$friendly.'/main.medium.jpg';
	$image_url = image_exists($image_url, $this->pdo) ? $image_url : null;
?>

<div class="card text text--compact lazy" data-src="<?= $image_url; ?>">
	<span class="card__name">
		<a class="a--inherit" href="<?= '/artists/'.$friendly.'/'; ?>">
			<span class="symbol__artist"></span>
			<?php
				if(strlen($display_name)) {
					echo lang(
						'<div>'.($display_romaji ?: $display_name).'</div>'.($display_romaji ? '<div class="any--weaken">'.$display_name.'</div>' : null),
						'<div>'.$display_name.'</div>',
						'hidden'
					);
				}
				else {
					echo lang(
						'<div>'.($romaji ?: $name).'</div>'.($romaji ? '<div class="any--weaken">'.$name.'</div>' : null),
						'<div>'.$name.'</div>',
						'hidden'
					);
				}
			?>
		</a>
	</span>
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

<?php
	if($artist['is_alternate_name']) {
		?>
			<div class="text text--outlined card__note" style="">
				<div class="h5"><?= lang('alternate name', '別名', 'hidden'); ?></div>
				<span class="any--weaken"><a class="a--inherit" href="<?= '/artists/'.$friendly.'/'; ?>"><?= $romaji ? lang($romaji, $name, 'parentheses') : $name; ?></a></span>
			</div>
		<?php
	}
?>