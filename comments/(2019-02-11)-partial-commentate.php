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
						<label class="input__label">Comment</label>
						<textarea class="commentate__content input__textarea any--flex-grow" name="content" placeholder="your comment...&#10;<?php echo sanitize('あなたのコメント...'); ?>">{content}</textarea>
					</div>
				</div>
				
				<!-- This section shows when you're not signed in -->
				<div class="input__row commentate__anonymous">
					<div class="text text--compact text--outlined text--notice symbol__error commentate__notice">Your comment was posted anonymously. Would you like to add a handle name, or sign in?
						<div class="any--jp any--weaken-color"><?php echo sanitize('コメントは匿名で投稿されます。 サインインしますか、それともハンドル名を追加しますか?'); ?></div>
					</div>
					
					<!-- Set your handlename -->
					<div class="input__group commentate__name">
						<label class="input__label">Handle name</label>
						<input class="any--flex-grow" name="name" placeholder="your name <?php echo sanitize('(ハンドル名)'); ?>" value="{name}" />
					</div>
					
					<!-- Sign in -->
					<div class="input__group commentate__sign-in">
						<label class="input__label">Username</label>
						<input class="any--flex-grow" name="username" placeholder="username <?php echo sanitize('(ユーザ名)'); ?>" value="{name}" />
						<input class="commentate__password any--flex-grow input--secondary" name="password" placeholder="password <?php echo sanitize('(パスワード)'); ?>" type="password" value="{name}" />
					</div>
				</div>
				
				<!-- Submit area -->
				<div class="input__row">
					<div class="input__group any--flex any--flex-grow">
						<button class="input__button any--flex-grow comment__submit" name="submit" type="submit">{button_text} comment <?php echo sanitize('(コメントする)'); ?></button>
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