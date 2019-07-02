<!-- Bottom -->
<div class="artist__bottom">
	<?php
		// Comments
		include('../comments/partial-comments.php');
		render_default_comment_section('artist', $artist['id'], $artist['comments'], $markdown_parser);

		// Edit history
		if(is_array($artist["edit_history"]) && !empty($artist["edit_history"])) {
			?>
				<h3><?= lang('Edit history', '変更履歴', 'div'); ?></h3>
				<input class="obscure__input" id="show-edits" type="checkbox" <?php echo count($artist["edit_history"]) > 4 ? "checked" : null; ?> />
				<div class="text text--outlined obscure__container obscure--faint">
					<ul class="ul--compact">
						<?php
							for($i = 0; $i < count($artist["edit_history"]); $i++) {
								?>
									<li class="obscure__item">
										<span class="h4"><?= substr($artist["edit_history"][$i]["date_occurred"], 0, 10); ?></span>
										<a class="user" href="<?php echo '/users/'.$artist["edit_history"][$i]["username"].'/'; ?>"><?= $artist["edit_history"][$i]["username"]; ?></a>
										<?php
											foreach($artist['edit_history'][$i]['content'] as $change) {
												echo strlen($change) ? '<span class="symbol__edit any--weaken">'.$change.'</span> ' : null;
											}
										?>
									</li>
								<?php
							}
						?>
					</ul>
					<label class="input__button obscure__button" for="show-edits">
						Show all
					</label>
				</div>
			<?php
		}
	?>
</div>