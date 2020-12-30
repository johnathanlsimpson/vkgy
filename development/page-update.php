<?php

script([
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-tribute.js',
	'/scripts/script-initTribute.js',
	'/scripts/script-initDelete.js',
	'/about/script-page-update.js'
]);

style([
	'/style/external/style-tribute.css',
	'/about/style-page-update.css'
]);

$page_header = lang('Update entry', '記事を編集', 'div');

subnav([
	'Update' => '/about/development/add/',
]);

$active_page = '/about/development/add/';

// Only boss can edit dev posts
if( !$_SESSION['is_boss'] ) {
	?>
		<form action="/about/function-update.php" class="col c1" enctype="multipart/form-data" method="post" name="form__update">
			
			<div class="col c3-AAB">
				<div>
					<h2>
						Update <a class="a--inherit" href="<?= '/about/development/'.$entry['id']; ?>/"><?= $entry['title']; ?></a>
					</h2>
					<div class="text">
						<input name="id" type="hidden" value="<?= $entry['id']; ?>" />

						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Title</label>
								<input class="any--flex-grow" name="title" placeholder="title" value="<?= $entry['title']; ?>" />
								<input class="input--secondary" name="friendly" placeholder="friendly" value="<?= $entry['friendly']; ?>" />
							</div>
						</div>

						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Entry</label>
								<textarea class="autosize input__textarea any--flex-grow any--tributable" data-is-previewed="true" name="content" placeholder="your entry here"><?= $entry['content']; ?></textarea>
							</div>
						</div>

						<div class="input__row">
							<div class="input__group any--flex-grow" data-role="submit-container">
								<button class="any--flex-grow" name="submit" type="submit">
									Submit
								</button>
								<span data-role="status"></span>
								<button class="symbol__trash" data-role="delete" name="delete" type="button" style="margin-left:1ch;"></button>
							</div>

							<div class="input__group any--flex-grow any--flex any--hidden" data-role="edit-container">
								<a class="a--padded a--outlined any--flex-grow any--align-center" data-get="url" data-get-into="href" href="">View entry</a>
								<a class="a--padded" data-get="edit_url" data-get-into="href" data-role="edit">Edit</a>
							</div>
						</div>

						<div class="text text--outlined text--notice update__result" data-role="result"></div>
					</div>
				</div>

				<div>
					<div>
						<h3>
							Preview entry
							<span class="update__preview-status"></span>
						</h3>
						<div class="text text--outlined">
							<!--<div class="update__image-container"><img alt="" class="update__image" src="<?php echo $entry["image"]; ?>" /></div>-->
							<div class="update__preview"></div>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php
}