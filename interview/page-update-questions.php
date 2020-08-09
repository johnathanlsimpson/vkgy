<?php

script([
	'/interview/script-page-add.js',
]);

subnav([
	lang('Add interview', 'インタビューを追加', 'hidden') => '/interview/add/',
]);

$page_header = lang('Interviews', 'インタビュー', 'div');

$page_title = 'Add interview (インタビューを追加します)';

?>


<form action="/interview/function-add.php" class="col c1" enctype="multipart/form-data" method="post" name="add-interview">
	
	<h2>
		<?= lang('Add interview', 'インタビューを追加', 'div'); ?>
	</h2>
	
	<?php
		for($i=0; $i<3; $i++) {
			?>
				<div class="col c3">
					<?php
						for($x=0; $x<3; $x++) {
							?>
								<div>
									<div class="text">
										
										<!-- Name -->
										<div class="input__row">
											<div class="input__group">
												<label class="input__label"><?= lang('Name', 'タイトル', 'hidden'); ?></label>
												<input class="input" name="name[]" placeholder="name" value="" />
												<input class="input--secondary" name="romaji[]" placeholder="(romaji)" value="" />
											</div>
										</div>
										
									</div>
								</div>
							<?php
						}
					?>
				</div>
			<?php
		}
	?>
	
	<div class="text text--docked">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				<button class="any--flex-grow" name="submit" type="submit">
					<?= lang('Add magazines', '雑誌を追加', 'hidden'); ?>
				</button>
				<span data-role="status"></span>
			</div>
		</div>
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
	</div>
	
</form>