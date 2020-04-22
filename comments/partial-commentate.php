<!-- Template: Add/edit comment form -->
<template id="commentate-template">
	<?php
		ob_start();
		
		?>
			<form action="/comments/function-update.php" class="commentate__container" enctype="multipart/form-data" method="post" name="form__commentate">
				
				<!-- Hidden inputs -->
				<input name="comment_id" type="hidden" value="{comment_id}" placeholder="comment id" />
				<input name="item_id" type="hidden" value="{item_id}" placeholder="item id" />
				<input name="item_type" type="hidden" value="{item_type}" placeholder="item type" />
				<input name="thread_id" type="hidden" value="{thread_id}" placeholder="thread id" />
				<input class="comment__website" name="website" />
				<input class="comment__email" name="email" />
				
				<!-- Where you leave your comment -->
				<div class="input__row commentate__comment">
					<div class="input__group any--flex-grow">
						<label class="input__label"><?php echo lang('Comment', 'コメント', 'hidden'); ?></label>
						<textarea class="commentate__content input__textarea any--flex-grow autosize any--tributable" name="content" placeholder="your comment...&#10;<?php echo sanitize('あなたのコメント...'); ?>">{content}</textarea>
					</div>
				</div>
				
				<!-- This section shows when you're not signed in -->
				<div class="input__row commentate__anonymous">
					<div class="text text--compact text--outlined text--notice commentate__notice">
						<span class="symbol__error" style="float: left;"></span>
						<?= lang('Commented anonymously. Add a handle name, or register, below.', 'コメントは匿名で投稿されました。以下、ハンドルネームを追加するか、登録してください。', 'div'); ?>
					</div>
					
					<!-- Set your handlename -->
					<div class="input__group commentate__name">
						<label class="input__label"><?php echo lang('Handle name', 'ハンドル名', 'hidden'); ?></label>
						<input class="any--flex-grow" name="name" placeholder="your name <?php echo sanitize('(ハンドル名)'); ?>" value="{name}" />
					</div>
				</div>
				
				<!-- This section shows when you're not signed in -->
				<div class="input__row commentate__anonymous">
					
					<!-- Sign in -->
					<div class="input__group commentate__sign-in any--flex-grow">
						<label class="input__label"><?= lang('Sign in/Register', 'サインイン・登録', 'hidden'); ?></label>
						<input class="any--flex-grow" name="username" placeholder="username <?= sanitize('(ユーザ名)'); ?>" value="" />
					</div>
					<div class="input__group commentate__sign-in any--flex-grow">
						<input class="commentate__password any--flex-grow" name="password" placeholder="password <?= sanitize('(パスワード)'); ?>" type="password" value="" />
					</div>
					<div class="input__group commentate__sign-in">
						<label class="input__radio"><input class="input__choice" name="sign_in_type" type="radio" value="register" checked /><span class="symbol__unchecked">Register</span></label>
						<label class="input__radio"><input class="input__choice" name="sign_in_type" type="radio" value="sign-in" /><span class="symbol__unchecked">Sign In</span></label>
					</div>
					
				</div>
				
				<!-- Submit area -->
				<div class="input__row">
					<div class="input__group any--flex any--flex-grow">
						<button class="input__button any--flex-grow comment__submit" name="submit" type="submit"><?php echo lang('comment', 'コメントする', 'hidden'); ?></button>
						<span data-role="status"></span>
					</div>
				</div>
				<div class="comment__result text text--outlined text--error text--compact symbol__error" data-role="result"></div>
				
			</form>
		<?php
		
		$commentate_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $commentate_template);
	?>
</template>