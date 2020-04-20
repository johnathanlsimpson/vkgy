<?php
	script([
		'/tags/script-partial-tags.js',
	]);
	
	style([
		'/tags/style-partial-tags.css',
	]);
?>

<h3>
	<?= lang('Tags', 'タグ', 'div'); ?>
</h3>
<ul class="text text--outlined">
	<?php
		if(is_array($current_tags) && !empty($current_tags)) {
			foreach($tag_types as $tag_type) {
				
				if(is_array($current_tags[$tag_type]) && !empty($current_tags[$tag_type])) {
					?>
						<li class="tag__wrapper">
							<?php
								$num_of_type = count($current_tags[$tag_type]);
								foreach($current_tags[$tag_type] as $tag_key => $tag) {
									if($tag_type === 'subgenres') {
										?>
											<a class="text tag__container tag--subgenre <?= 'tag--'.$tag['friendly']; ?>" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
												<span class="tag__thumbnail lazy"></span>
												<span class="tag__name">
													<?= $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'div') : $tag['name']; ?>
													<span class="any__note tag__num"><?= lang('×'.$tag['num_times_tagged'], $tag['num_times_tagged'].'回', 'hidden'); ?></span>
												</span>
											</a>
										<?php
									}
									else {
										?>
											<a class="tag__container symbol__tag" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
												<?= lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden'); ?>
												<span class="any__note tag__num"><?= lang('×'.$tag['num_times_tagged'], $tag['num_times_tagged'].'回', 'hidden'); ?></span>
												<?= $tag_key + 1 < $num_of_type ? '<span class="any--weaken">,</span> ' : null; ?>
											</a>
										<?php
									}
								}
							?>
						</li>
					<?php
				}
				
			}
		}
		else {
			echo lang('No tags', 'タグ無し', 'hidden');
		}
	?>
</ul>

<h3>
	<?= lang('Add tags', 'タグする', 'div'); ?>
</h3>
<ul class="text text--outlined">
	<?php
		if($_SESSION['is_signed_in']) {
			if(is_array($all_tags) && !empty($all_tags)) {
				foreach($tag_types as $tag_type) {
					
					// Only show section if that tag type is actually used
					if(is_array($all_tags[$tag_type]) && !empty($all_tags[$tag_type])) {
					
						// Make sure user is allowed to see this type of tag
						if($tag_type != 'admin' || $_SESSION['can_add_data']) {
							?>
								<li class="tag__wrapper">
									<h5>
										<?= $tag_type; ?>
									</h5>
									<?php
										foreach($all_tags[$tag_type] as $tag_key => $tag) {
											
											// Make sure user is allowed to see tag
											if(
												!$tag['requires_permission']
												||
												($tag['requires_permission'] && $_SESSION[ $tag['requires_permission'] ])
											) {
												
												// Set different classes depending on whether or not user has used this tag
												$tag_class  = 'any__tag symbol__tag';
												$tag_class .= in_array($tag['id'], $user_tags) ? ' any__tag--selected' : null;
												
												?>
													<label class="<?= $tag_class; ?>" data-id="<?= $item_id; ?>" data-tag-id="<?= $tag['id']; ?>" data-item-type="<?= $item_type; ?>">
														<?= str_replace( [' kei', '&#31995;' ], '', lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden') ); ?>
													</label>
												<?php
												
											}
										}
									?>
								</li>
							<?php
						}
					
					}
					
				}
			}
		}
		else {
			echo lang('<a href="">Sign in</a> to add tags.', 'タグするには<a href="">サインイン</a>してください。', 'hidden');
		}
	?>
</ul>