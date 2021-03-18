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
	<?= lang('Current tags', 'タグ', 'div'); ?>
</h3>
<ul class="text text--outlined tags--active">
	<?php
		if(is_array($tags['tagged']) && !empty($tags['tagged'])) {
			foreach($tags['tagged'] as $tag_type => $tagged_tags) {
				?>
					<li class="tags__wrapper">
						
						<div class="h5"><?= $tag_type; ?></div>
						
						<?php
							foreach($tagged_tags as $tag_key => $tag) {
								if($tag_type === 'scenes') {
									?>
										<div class="tag__container tag--scene">
											
											<a class="text <?= 'tag--'.$tag['friendly']; ?>" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
												<span class="tag__thumbnail lazy"></span>
												<span class="tag__name">
													<?= $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'div') : $tag['name']; ?>
												</span>
											</a>
											
											<?php
												// Only mods can pin/hide
												if( $_SESSION['can_approve_data'] ) {
													
													echo '<div class="tag__moderation--vertical">';
													
													// All moderation tags can be pinned
													?><label class="tag__moderation tag--pin input__checkbox" data-direction="pin" data-items-tags-id="<?= $tag['items_tags_id']; ?>" data-item-type="<?= $item_type.'s_tags'; ?>">
														<input class="tag__pin input__choice" type="checkbox" <?= $tag['mod_score'] > 0 ? 'checked' : null; ?> />
														<span class="symbol__pin tag__status"></span>
													</label><?php
													
													// Don't show hide button if tag can only be added by moderators
													if( $tag['requires_permission'] != 'can_approve_data' ) {
														?><label class="tag__moderation tag--hide input__checkbox" data-direction="hide" data-items-tags-id="<?= $tag['items_tags_id']; ?>" data-item-type="<?= $item_type.'s_tags'; ?>">
															<input class="tag__hide input__choice" type="checkbox" <?= $tag['mod_score'] < 0 ? 'checked' : null; ?> />
															<span class="symbol__hide tag__status"></span>
														</label><?php
													}
													
													echo '</div>';
													
												}
												
												if( is_numeric($tag['score']) && $tag['mod_score'] != 1 ) {
													echo render_component($vote_template, [
														'direction_class' => 'vote--vertical',
														'item_id' => $tag['items_tags_id'],
														'item_type' => $item_type.'s_tags',
														'upvote_is_checked' => $tag['user_score'] > 0 ? 'checked' : null,
														'downvote_is_checked' => $tag['user_score'] < 0 ? 'checked' : null,
														'score' => $tag['score'],
													]);
												}
											?>
											
										</div>
									<?php
								}
								else {
									?>
										<span class="tag__container">
											
											<a href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
												<?= lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden'); ?>
											</a>
											
											<?php
												// Only mods can pin/hide
												if( $_SESSION['can_approve_data'] ) {
													
													// All moderation tags can be pinned
													?><label class="tag__moderation tag--pin input__checkbox" data-direction="pin" data-items-tags-id="<?= $tag['items_tags_id']; ?>" data-item-type="<?= $item_type.'s_tags'; ?>">
														<input class="tag__pin input__choice" type="checkbox" <?= $tag['mod_score'] > 0 ? 'checked' : null; ?> />
														<span class="symbol__pin tag__status"></span>
													</label><?php
													
													// Don't show hide button if tag can only be added by moderators
													if( $tag['requires_permission'] != 'can_approve_data' ) {
														?><label class="tag__moderation tag--hide input__checkbox" data-direction="hide" data-items-tags-id="<?= $tag['items_tags_id']; ?>" data-item-type="<?= $item_type.'s_tags'; ?>">
															<input class="tag__hide input__choice" type="checkbox" <?= $tag['mod_score'] < 0 ? 'checked' : null; ?> />
															<span class="symbol__hide tag__status"></span>
														</label><?php
													}
													
												}
												
												// Only signed in users may vote, and only on tags that allow it
												if( $_SESSION['is_signed_in'] && $tag['is_votable'] ) {
													
													echo render_component($vote_template, [
														'item_id' => $tag['items_tags_id'],
														'item_type' => $item_type.'s_tags',
														'upvote_is_checked' => $tag['user_score'] > 0 ? 'checked' : null,
														'downvote_is_checked' => $tag['user_score'] < 0 ? 'checked' : null,
														'score' => $tag['score'],
													]);
													
												}
											?>
											
										</span>
									<?php
								}
							}
						?>
					</li>
				<?php
			}
		}
		else {
			echo lang('No tags yet', 'タグ無し', 'hidden');
		}
	?>
</ul>