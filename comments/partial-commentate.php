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
						<?php echo lang('Commented anonymously. Add a handle name? (To edit your VK avatar, sign in or <a href="/account/" target="_blank">register</a>.)', 'コメントは匿名で投稿されます。ハンドル名を追加しますか？ (V系アバターをカスタマイズように、サインイン・<a href="/account/" target="_blank">登録</a>する。)', 'div'); ?>
					</div>
					
					<!-- Set your handlename -->
					<div class="input__group commentate__name">
						<label class="input__label"><?php echo lang('Handle name', 'ハンドル名', 'hidden'); ?></label>
						<input class="any--flex-grow" name="name" placeholder="your name <?php echo sanitize('(ハンドル名)'); ?>" value="{name}" />
					</div>
					
					<!-- Sign in -->
					<div class="input__group commentate__sign-in">
						<label class="input__label"><?php echo lang('Username', 'ユーザ名', 'hidden'); ?></label>
						<input class="any--flex-grow" name="username" placeholder="username <?php echo sanitize('(ユーザ名)'); ?>" value="{name}" />
						<input class="commentate__password any--flex-grow input--secondary" name="password" placeholder="password <?php echo sanitize('(パスワード)'); ?>" type="password" value="{name}" />
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