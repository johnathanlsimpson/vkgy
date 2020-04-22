<a class="artist__main-image-link <?php echo $artist['image'] ? '' : 'any--hidden'; ?>" href="<?php echo $artist['image']['url']; ?>">
	<img class="artist__main-image" alt="<?php echo $artist["quick_name"]; ?>" src="<?php echo str_replace('.', '.large.', $artist['image']['url']); ?>" />
</a>

<div class="data__container">
	<div class="data__item">
		<div>
			<h5>
				<?php echo lang('Type', 'タイプ', ['secondary_class' => 'any--hidden']); ?>
			</h5>
			<?php
				echo '<a href="/search/artists/&type='.$artist['type'].'">'.[
					lang('unknown', '不明', ['secondary_class' => 'any--hidden']),
					lang('band', 'バンド', ['secondary_class' => 'any--hidden']),
					lang('session', 'セッション', ['secondary_class' => 'any--hidden']),
					lang('alter-ego', '別名義バンド', ['secondary_class' => 'any--hidden']),
					lang('solo', 'ソロ', ['secondary_class' => 'any--hidden']),
					lang('special', '限定', ['secondary_class' => 'any--hidden']),
				][$artist["type"]].'</a>';
			?>
		</div>
	</div>
	<div class="data__item">
		<div>
			<h5>
				<?php echo lang('Status', '活動状況', ['secondary_class' => 'any--hidden']); ?>
			</h5>
			<?php
				echo '<a href="/search/artists/&active='.$artist['active'].'">'.[
					lang('unknown', '不明', ['secondary_class' => 'any--hidden']),
					lang('active', '現在活動', ['secondary_class' => 'any--hidden']),
					lang('disbanded', '解散', ['secondary_class' => 'any--hidden']),
					lang('paused', '休止', ['secondary_class' => 'any--hidden']),
					lang('semi-active', '時々活動', ['secondary_class' => 'any--hidden'])
				][$artist['active']].'</a>';
			?>
		</div>
	</div>
	<div class="data__item <?php echo (int)$artist['date_occurred'] || (int)$artist['date_ended'] ? '' : 'any--hidden'; ?>">
		<div>
			<h5>
				<?php echo lang('Active', '活動期間', ['secondary_class' => 'any--hidden']); ?>
			</h5>
			<?= $artist['date_occurred']; ?>
		</div>
	</div>
	<div class="data__item">
		<h5>
			<?php echo lang('Area', '地域', ['secondary_class' => 'any--hidden']); ?>
		</h5>
		<?php
			if(is_array($artist['areas']) && !empty($artist['areas'])) {
				$artist['areas'] = array_values(array_reverse($artist['areas']));
				
				foreach($artist['areas'] as $key => $area) {
					echo $key > 0 ? '<span class="symbol__previous"></span>' : null;
					echo '<a href="/search/artists/&area='.$area['friendly'].'">'.lang($area['romaji'], $area['name'], ['secondary_class' => 'any--hidden']).'</a>';
				}
			}
			else {
				if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
					foreach($rslt_curr_tags as $curr_tag) {
						if($curr_tag['friendly'] === 'foreign') {
							$artist_is_foreign = true;
							break;
						}
					}
				}
				
				if($artist_is_foreign) {
					echo '<a href="/search/artists/&area=overseas">'.lang('overseas', '海外', ['secondary_class' => 'any--hidden']).'</a>';
				}
				else {
					echo '<a href="/search/artists/&area=japan">'.lang('Japan', '日本', ['secondary_class' => 'any--hidden']).'</a>';
				}
			}
		?>
	</div>
	
	<?php
		if( $artist_is_non_visual || ( is_array($current_tags) && is_array($current_tags['subgenres']) && !empty($current_tags['subgenres']) ) ) {
			?>
				<div class="data__item">
					<h5>
						<?= lang('Subgenres', 'V系ジャンル', 'hidden'); ?>
					</h5>
					<?php
						$num_subgenres = count($current_tags['subgenres']);						
						foreach($current_tags['subgenres'] as $tag_key => $tag) {
							echo '<a href="/search/artists/?tags[]='.$tag['friendly'].'">'.str_replace( [' kei', '&#31995;' ], '', lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden') ).'</a>';
							echo $tag_key + 1 < $num_subgenres ? '<span class="any--weaken">,</span> ' : null;
						}
					?>
				</div>
			<?php
		}
	?>
	
	<div class="data__item <?php echo $artist['pronunciation'] ? null : 'any--hidden'; ?>">
		<h5>
			<?php echo lang('Pronunciation', '発音', ['secondary_class' => 'any--hidden']); ?>
		</h5>
		<?php echo $artist['pronunciation']; ?>
		<button class="symbol--standalone symbol__caret-right" data-pronunciation="<?php echo html_entity_decode($artist['pronunciation'], ENT_NOQUOTES, "UTF-8"); ?>" type="button"></button>
	</div>
	<div class="data__item <?php echo $artist['concept_name'] ? null : 'any--hidden'; ?>">
		<h5>
			<?php echo lang('Concept', 'コンセプト', ['secondary_class' => 'any--hidden']); ?>
		</h5>
		<?php echo lang(($artist['concept_romaji'] ?: $artist['concept_name']), $artist['concept_name'], ['secondary_class' => 'any--hidden']); ?>
	</div>
</div>

<div class="any--weaken artist__description"><?php echo $markdown_parser->parse_markdown($artist["description"], true); ?></div>