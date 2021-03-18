<?php

include_once('../votes/function-render_vote.php');

script([
	'/tags/script-partial-tags.js',
]);

style([
	'/tags/style-partial-tags.css',
]);

?>

<h3>
	<?= lang('Add tags', 'タグする', 'div'); ?>
</h3>

<ul class="text text--outlined obscure__container obscure--height" <?= $_SESSION['is_signed_in'] && isset($artist) ? 'style="min-height:16rem;"' : null; ?> >
	<?php
		if( $_SESSION['is_signed_in'] && is_array($tags['untagged']) && !empty($tags['untagged']) ) {
				foreach($tags['untagged'] as $tag_type => $untagged_tags) {
					
					// Make sure user is allowed to see this type of tag
					if($tag_type != 'admin' || $_SESSION['can_add_data']) {
						?>
							<li class="tags__wrapper any--weaken">
								
								<div class="h5"><?= $tag_type; ?></div>
								
								<?php
									foreach($untagged_tags as $tag_key => $tag) {
										
										// Make sure user is allowed to see tag
										if(
											!$tag['requires_permission']
											||
											($tag['requires_permission'] && $_SESSION[ $tag['requires_permission'] ])
										) {
											
											?>
												<label class="tag__label input__checkbox" data-vote="upvote" data-id="<?= $item_id; ?>" data-tag-id="<?= $tag['id']; ?>" data-item-type="<?= $item_type; ?>">
													<input class="tag__checkbox input__choice" type="checkbox" <?= $tag['user_score'] == 1 ? 'checked' : null; ?> />
													<span class="symbol__plus tag__status"><?= str_replace( [' kei', '&#31995;' ], '', lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden') ); ?></span>
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
		else {
			echo lang('<a href="/account/">Sign in</a> to add tags.', 'タグするには<a href="">サインイン</a>してください。', 'hidden');
		}
	?>
	<label class="input__button obscure__button" for="obscure-comments">Show more</label>
</ul>