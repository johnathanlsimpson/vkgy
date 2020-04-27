<?php
	script([
		'/tags/script-partial-tags.js',
	]);
	
	style([
		'/tags/style-partial-tags.css',
	]);
	
	if($_SESSION['can_approve_data']) {
		?>
			<div>
				<h3>
					<?= lang('Remove tags', 'タグを削除する', 'div'); ?>
				</h3>
				<ul class="text text--outlined">
					<?php
						
						// Show each possible tag for deletion
						if(is_array($current_tags) && !empty($current_tags)) {
							foreach($current_tags as $tag_type => $tags) {
								?>
									<li class="tag__wrapper">
										<h5>
											<?= $tag_type; ?>
										</h5>
										<?php
											foreach($tags as $tag) {
												
												// Make sure user is allowed to see tag
												if(
													!$tag['requires_permission']
													||
													($tag['requires_permission'] && $_SESSION[ $tag['requires_permission'] ])
												) {
													
													// Set tag class and display tag
													$tag_class  = 'any__tag any__tag--selected';
													?>
														<label class="<?= $tag_class; ?>" data-action="permanent_delete" data-id="<?= $item_id; ?>" data-tag-id="<?= $tag['id']; ?>" data-item-type="<?= $item_type; ?>" >
															<span class="symbol__trash" data-role="status" style="margin:0;">
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
						else {
							echo lang('No tags to remove.', 'タグ無し', 'hidden');
						}
						
					?>
				</ul>
			</div>
		<?php
	}
?>