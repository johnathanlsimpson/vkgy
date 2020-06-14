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
								echo '<div class="h5">'.$tag_type.'</div>';
								
								$num_of_type = count($current_tags[$tag_type]);
								foreach($current_tags[$tag_type] as $tag_key => $tag) {
									if($tag_type === 'subgenres') {
										?>
											<div class="tag__container tag--subgenre">
												<a class="text <?= 'tag--'.$tag['friendly']; ?>" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
													<span class="tag__thumbnail lazy"></span>
													<span class="tag__name">
														<?= $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'div') : $tag['name']; ?>
													</span>
												</a>
												<?php
													echo '
														<span class="tag__voting any--weaken-color">
															
															<label class="tag__vote tag__upvote" data-vote="upvote" data-id="'.$item_id.'" data-tag-id="'.$tag['id'].'" data-item-type="'.$item_type.'">
																<input class="input__choice" type="checkbox" '.(is_array($user_upvotes) && in_array($tag['id'], $user_upvotes) ? 'checked' : null).' />
																<span class="tag__status symbol__up-caret symbol--standalone"></span>
															</label>
															
															<span class="tag__num any--weaken-size" data-tag-id="'.$tag['id'].'" data-num-tags="'.$tag['num_upvotes'].'"></span>
															
															<label class="tag__vote tag__status tag__downvote" data-vote="downvote" data-id="'.$item_id.'" data-tag-id="'.$tag['id'].'" data-item-type="'.$item_type.'">
																<input class="input__choice" type="checkbox" '.(is_array($user_downvotes) && in_array($tag['id'], $user_downvotes) ? 'checked' : null).' />
																<span class="symbol__down-caret symbol--standalone"></span>
															</label>
															
														</span>
													';
												?>
											</div>
										<?php
									}
									else {
										?>
											<span class="tag__container">
												<a class="" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
													<?= lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden'); ?>
												</a>
												<?php
													echo '
														<span class="tag__voting any--weaken-color">
															
															<label class="tag__vote tag__upvote" data-vote="upvote" data-id="'.$item_id.'" data-tag-id="'.$tag['id'].'" data-item-type="'.$item_type.'">
																<input class="input__choice" type="checkbox" '.(is_array($user_upvotes) && in_array($tag['id'], $user_upvotes) ? 'checked' : null).' />
																<span class="tag__status symbol__up-caret symbol--standalone"></span>
															</label>
															
															<span class="tag__num any--weaken-size" data-tag-id="'.$tag['id'].'" data-num-tags="'.$tag['num_upvotes'].'"></span>
															
															<label class="tag__vote tag__status tag__downvote" data-vote="downvote" data-id="'.$item_id.'" data-tag-id="'.$tag['id'].'" data-item-type="'.$item_type.'">
																<input class="input__choice" type="checkbox" '.(is_array($user_downvotes) && in_array($tag['id'], $user_downvotes) ? 'checked' : null).' />
																<span class="symbol__down-caret symbol--standalone"></span>
															</label>
															
														</span>
													';
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
		}
		else {
			echo lang('No tags', 'タグ無し', 'hidden');
		}
	?>
</ul>

<style>
	.tag--subgenre {
		align-items: stretch;
		margin-bottom: 1rem;
	}
	.tag--subgenre .text {
		display: flex;
		flex: 1;
		margin-bottom: 0;
	}
	
	.tag__voting {
		border: 1px solid hsl(var(--background));
		border-radius: 3px;
		display: inline-flex;
		line-height: 1.5rem;
		text-align: center;
	}
	.tag--subgenre .tag__voting {
		flex-direction: column;
	}
	.tag__num {
		padding: 0 5px;
	}
	.tag__num::before {
		content: attr(data-num-tags);
	}
	.tag__num[data-num-tags^="-"] {
		color: red;
	}
	.tag__upvote, .tag__downvote {
		color: hsl(var(--background--bold));
		cursor: pointer;
		padding: 0 5px;
		position: initial;
	}
	.tag__upvote:hover, .tag__downvote:hover {
		color: hsl(var(--text));
	}
	.tag__upvote:hover::after, .tag__downvote:hover::after {
		background: linear-gradient(var(--vote-dir), var(--vote-bg), transparent);
		bottom: 0;
		content: "";
		display: block;
		left: 0;
		pointer-events: none;
		position: absolute;
		right: 0;
		top: 0;
	}
	.tag__upvote:hover {
		--vote-bg: rgba(0,255,0,0.1);
		--vote-dir: to right;
	}
	.tag__downvote:hover {
		--vote-bg: rgba(255,0,0,0.1);
		--vote-dir: to left;
	}
	.tag--subgenre .tag__upvote:hover {
		--vote-dir: to bottom;
	}
	.tag--subgenre .tag__downvote:hover {
		--vote-dir: to top;
	}
</style>

<h3>
	<?= lang('Add tags', 'タグする', 'div'); ?>
</h3>
<input class="obscure__input" id="obscure-tags" type="checkbox"  >
<ul class="text text--outlined obscure__container obscure--height" <?= $_SESSION['is_signed_in'] ? 'style="min-height:16rem;"' : null; ?> >
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
												
												?>
													<label class="tag__vote any__tag input__radio" data-vote="upvote" data-id="<?= $item_id; ?>" data-tag-id="<?= $tag['id']; ?>" data-item-type="<?= $item_type; ?>">
														<input class="input__choice" type="checkbox" <?= in_array($tag['id'], $user_upvotes) ? 'checked' : null; ?> />
														<span class="symbol__tag tag__status">
															<?= str_replace( [' kei', '&#31995;' ], '', lang($tag['romaji'] ?: $tag['name'], $tag['name'], 'hidden') ); ?>
														</span>
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
	<label class="input__button obscure__button" for="obscure-comments">Show more</label>
</ul>