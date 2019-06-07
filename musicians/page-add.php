<?php
	script([
		"/scripts/external/script-autosize.js",
		"/scripts/external/script-selectize.js",
		"/scripts/script-initSelectize.js",
		"/musicians/script-page-add.js"
	]);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/musicians/style-page-add.css"
	]);
	
	$page_header = lang('Add musician', 'ミュージシャンを追加する', ['container' => 'div']);
	
	subnav([
		lang('Add musician', 'ミュージシャン追加', ['secondary_class' => 'any--hidden']) => '/musicians/add/',
	]);
	
	if($_SESSION["admin"]) {
		?>
			<div class="col c1 any--margin">
				<form action="/musicians/function-add.php" enctype="multipart/form-data" method="post" name="form__add">
					<?php
						for($i = 0; $i < 7; $i++) {
							?>
								<div class="text">
									<div class="input__row">
										<div class="input__group any--flex-grow">
											<label class="input__label">Name</label>
											<input class="input any--flex-grow" name="name[]" placeholder="name" />
											<input class="input--secondary" name="romaji[]" placeholder="(romaji)" />
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label">Position</label>
											<select class="input" name="position[]">
												<option></option>
												<option value="1">vocals</option>
												<option value="2">guitar</option>
												<option value="3">bass</option>
												<option value="4">drums</option>
												<option value="5">keys</option>
												<option value="0">other/unknown</option>
											</select>
										</div>
									</div>
									
									<div class="input__row">
										<div class="input__group any--flex-grow">
											<label class="input__label">Band history</label>
											<textarea class="autosize input__textarea any--flex-grow" name="history[]" placeholder="band history"></textarea>
										</div>
									</div>
								</div>
							<?php
						}
					?>
					
					<div class="text text--docked">
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<button class="any--flex-grow" type="submit">
									Add musicians
								</button>
								<span data-role="status"></span>
							</div>
						</div>
						<div class="add__result text text--outlined text--notice any--hidden" data-role="result"></div>
					</div>
				</form>
			</div>
		<?php
		
		$documentation_page = 'musicians';
		include('../documentation/index.php');
	}
?>