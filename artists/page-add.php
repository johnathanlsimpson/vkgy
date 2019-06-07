<?php

if($_SESSION["admin"]) {
	script([
		"/artists/script-page-add.js"
	]);

	style([
		"/artists/style-page-add.css"
	]);
	
	$page_header = lang('Add artists', 'アーティストを追加する', [ 'container' => 'div' ]);
	
	subnav([
		lang('Artist list', 'アーティスト一覧', [ 'secondary_class' => 'any--hidden' ]) => '/artists/',
		lang('Search', 'サーチ', [ 'secondary_class' => 'any--hidden' ]) => '/search/artists/',
	]);
	
	?>
		<form action="" class="col c1 any--margin" enctype="multipart/form-data" method="post" name="form__add">
			<div class="col c2">
				<?php
					for($i = 0; $i < 12; $i++) {
						?>
							<div class="text">
								<div class="input__row">
									<div class="input__group any--flex-grow">
										<label class="input__label">
											Name
										</label>
										<input class="input any--flex-grow" name="name[<?php echo $i; ?>]" placeholder="name" />
										<input class="input--secondary" name="romaji[<?php echo $i; ?>]" placeholder="(romaji)" />
									</div>
								</div>
								<div class="input__row <?php echo $vkgy ? "any--hidden" : null; ?>">
									<div class="input__group">
										<label class="input__label">
											Affiliation
										</label>
										
										<input class="input__checkbox" id="<?php $y++; echo $y; ?>" name="affiliation[<?php echo $i; ?>]" type="radio" value="1" />
										<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>">signed</label>
										
										<input class="input__checkbox" id="<?php $y++; echo $y; ?>" name="affiliation[<?php echo $i; ?>]" type="radio" value="2" />
										<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>">related</label>
										
										<input class="input__checkbox" id="<?php $y++; echo $y; ?>" name="affiliation[<?php echo $i; ?>]" type="radio" value="3" checked />
										<label class="input__checkbox-label symbol__unchecked" for="<?php echo $y; ?>">unrelated</label>
									</div>
								</div>
							</div>
						<?php
					}
				?>
			</div>
			
			<div class="text text--docked">
				<div class="any--flex">
					<button class="any--flex-grow" type="submit">
						Add artists
					</button>
					<span data-role="status"></span>
				</div>
				<div class="text text--outlined text--notice add__result any--hidden" data-role="result"></div>
			</div>
		</form>
	<?php
	
	$documentation_page = 'add-artists';
	include('../documentation/index.php');
}