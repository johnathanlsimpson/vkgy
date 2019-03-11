<?php

if(is_array($badge) && !empty($badge) && strlen($badge['name']) && strlen($badge['friendly'])) {
	style([
		'../badges/style-partial-badge.css',
		'../badges/style-badges.css'
	]);
	
	$badge_class = 'badge--'.$badge['friendly'].($badge['level'] ? ' badge--'.$badge['level'] : null);
	$badge_html_id = 'badge-'.$badge['friendly'];

	if($badge['is_unseen']) {
		?>
			<input class="any--hidden badge__input" id="<?php echo $badge_html_id; ?>" type="checkbox" checked />
		<?php
	}

	?>
		<div class="text text--outlined text--compact h5 badge__container <?php echo $badge_class; ?>" data-name="<?php echo $badge['name']; ?>">
			<span class="badge__deco-a"></span>
			<span class="badge__deco-b"></span>
			<?php
				if($badge['is_unseen']) {
					?>
						<label class="badge__label" for="<?php echo $badge_html_id; ?>"></label>
					<?php
				}
			?>
		</div>
	<?php

	if(strlen($badge['description'])) {
		?>
			<div class="any--weaken badge__description">
				<h5 class="badge__title"><?php echo $badge['name']; ?></h5>
				<h5 class="badge__level"></h5>
				<?php echo $badge['description']; ?>
			</div>
		<?php
	}
}