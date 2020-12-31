<?php

include_once('../php/function-render_json_list.php');

script([
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-tribute.js',
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initTribute.js',
	'/scripts/script-initDelete.js',
	'/development/script-page-update.js'
]);

style([
	'/style/external/style-tribute.css',
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/development/style-page-update.css'
]);

$page_header = lang('Update entry', '記事を編集', 'div');

subnav([
	'Update' => '/development/add/',
]);

$active_page = '/development/add/';

// Only boss can edit dev posts
if( $_SESSION['is_boss'] ) {
	?>
		<form action="/about/function-update.php" class="col c1" enctype="multipart/form-data" method="post" name="form__update">
			
			<?php render_json_list('user'); ?>
			
			<div class="col c3-AAB">
				<div>
					<h2>
						Update <a class="a--inherit" href="<?= '/development/'.$entry['id']; ?>/"><?= $entry['title']; ?></a>
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
						
						<!-- Issue-specific -->
						<?php if($entry['is_issue']): ?>
							
							<input name="is_issue" value="1" hidden />
							
							<div class="input__row">
								
								<!-- Reported by -->
								<div class="input__group">
									<label class="input__label">Reported by</label>
									<select class="input" data-source="users" name="user_id" placeholder="username">
										<option value="1" selected>inartistic</option>
									</select>
								</div>
								
								<!-- Issue type -->
								<div class="input__group">
									<label class="input__label">Type</label>
									
									<select class="input" name="issue_type" placeholder="issue type">
										<?php foreach(['other', 'bug', 'feature'] as $value => $key): ?>
											<option value="<?= $value; ?>" <?= $entry['issue_type'] == $value ? 'selected' : null; ?> ><?= $key; ?></option>
										<?php endforeach; ?>
									</select>
									
								</div>
								
								<!-- Issue status -->
								<div class="input__group">
									<label class="input__label">Status</label>
									
									<label class="input__checkbox">
										<input class="input__choice" name="is_completed" type="checkbox" value="1" <?= $entry['is_completed'] ? 'checked' : null; ?> />
										<span class="symbol__unchecked">done?</span>
									</label>
									
								</div>
								
							</div>
						<?php endif; ?>
						
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
							<div class="update__preview"></div>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php
}