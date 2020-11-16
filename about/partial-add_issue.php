<?php

script([
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-sortable.js',
	'/scripts/external/script-tribute.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initTribute.js',
	'/about/script-partial-add_issue.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/external/style-tribute.css',
	'/style/style-selectize.css',
]);

include_once('../php/function-render_json_list.php');

?>

<h2>
	<?= lang('Add issue', '問題を追加する', 'div'); ?>
</h2>

<form action="/about/function-update.php" class="text text--outlined" enctype="multipart/form-data" method="post" name="form__add-issue">
	
	<input name="is_issue" value="1" hidden />
	
	<div class="input__row">
		<div class="input__group any--flex-grow">
			
			<label class="input__label">Issue</label>
			<textarea class="input input__textarea any--flex-grow" name="title" placeholder="issue description"></textarea>
			
		</div>
	</div>
	
	<div class="input__row">
		<div class="input__group any--flex-grow">
			
			<?php render_json_list('user'); ?>
			
			<label class="input__label">Reported by</label>
			<select class="input" data-source="users" name="user_id" placeholder="username">
				
				<option></option>
				
			</select>
			
		</div>
		
		<div class="input__group">
		
			<label class="input__label">Type</label>
			<select class="input" name="type" placeholder="issue type">
				
				<option></option>
				<?php foreach(['other', 'bug', 'feature'] as $value => $key): ?>
					<option value="<?= $value; ?>"><?= $key; ?></option>
				<?php endforeach; ?>
				
			</select>
			
		</div>
		
		<div class="input__group">
			
			<button class="symbol__plus" data-role="submit" name="add_issue" type="submit">Add</button>
			<span data-role="status"></span>
			
		</div>
	</div>
	
	<div class="text text--outlined text--error" data-role="result" style="margin: 1rem 0 0 0;"></div>

</form>

<style>
	[data-role="result"]:empty {
		display: none;
	}
</style>