<?php
	script([
		"/scripts/script-signIn.js",
		"/account/script-page-sign-in-register.js"
	]);
	
	style([
		"/account/style-page-sign-in-register.css"
	]);
	
	$page_header = lang('Register', '登録', ['container' => 'div']);
?>

<div class="col c2 any--signed-out-only">
	<div>
		<h2>
			<?php echo lang('Sign in', 'サインイン', ['container' => 'div']); ?>
		</h2>
		<form action="" class="text sign-in__container <?php echo $_SERVER['HTTP_REFERER'] ? 'sign-in--back' : 'sign-in--refresh'; ?>" enctype="multipart/form-data" method="post" name="form__sign-in">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<input class="any--flex-grow" name="username" pattern="^[A-z0-9- \.]{3,}$" placeholder="username (ユーザー名)" required />
				</div>
			</div>
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<input class="any--flex-grow" name="password" placeholder="password (パスワード)" type="password" required />
				</div>
			</div>
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" type="submit">
						<?php echo lang('Sign in', 'サインイン', ['secondary_class' => 'any--hidden']); ?>
					</button>
					<span data-role="status"></span>
				</div>
			</div>
			<div class="text text--outlined text--notice" data-role="result"></div>
		</form>
		
		<h3>
			<?php echo lang('Join vkgy', '新規登録', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h3>
		<form action="/account/function-register.php" class="text register__container register__section" enctype="multipart/form-data" method="post" name="register__form" autocomplete="off">
				<ul>
					<input class="any--hidden" id="register__radio--bat" name="register_avatar" type="radio" value="bat" checked />
					<input class="any--hidden" id="register__radio--gecko" name="register_avatar" type="radio" value="gecko" />
					
					<li>
						<div class="input__row">
							<div class="input__group">
								<label class="input__label"><?php echo lang('Username', 'ユーザー名', ['secondary_class' => 'any--hidden']); ?></label>
							</div>
							<input class="any--flex-grow" name="register_username" pattern="[A-z0-9-]+" placeholder="username (ユーザ名)" title="A-z, 0-9, -" />
						</div>
						
						<div class="any--weaken register__note">
							<?php echo lang('Usernames may contain: <strong>A-z</strong>, <strong>0-9</strong>, <strong>-</strong>. ', '（半角英字、数字、ハイフンを使用できます。）', ['secondary_class' => 'any--weaken-color']); ?>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label"><?php echo lang('Password', 'パスワード', ['secondary_class' => 'any--hidden']); ?></label>
								<input class="any--flex-grow symbol__locked" name="register_password" placeholder="password (パスワード)" type="password" />
							</div>
							<div class="input__group">
								<input class="register__show any--hidden" id="register__show" type="checkbox" />
								<label class="input__checkbox-label symbol__unchecked register__show-label" onclick="togglePassword()" for="register__show">Show?</label>
							</div>
						</div>
					</li>
					
					<li class="register__avatar-container">
						<div class="input__row">
							<div class="input__group" style="align-self: center;">
								<label class="input__label"><?php echo lang('Avatar eyes', 'アバター', ['secondary_class' => 'any--hidden']); ?></label>
								<label class="input__checkbox-label symbol__unchecked register__bat" for="register__radio--bat"><?php echo lang('bat', 'メークⅠ', ['secondary_class' => 'any--hidden']); ?></label>
								<label class="input__checkbox-label symbol__unchecked register__gecko" for="register__radio--gecko"><?php echo lang('bat', 'メークⅡ', ['secondary_class' => 'any--hidden']); ?></label>
							</div>
							<div class="input__group">
								<div class="register__face"></div>
							</div>
						</div>
						
						<div class="any--weaken register__note">
							<?php echo lang('The avatar can be further customized after joining. ', '（登録後、アバターをさらにカスタマイズすることができます。）', ['secondary_class' => 'any--weaken-color']); ?>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow" data-role="submit-container">
								<button class="any--flex-grow" data-role="submit" name="register_submit" type="submit"><?php echo lang('Join vkgy', '登録する', ['secondary_class' => 'any--hidden']); ?></button>
								<span class="register__status" data-role="status"></span>
							</div>
						</div>
					</li>
				</ul>
				
				<div class="text text--outlined text--notice register__result" data-role="result"></div>
			</form>
	</div>
	
	<div>
		<h3>
			<?php echo lang('Why join?', '登録について', ['container' => 'div']); ?>
		</h3>
		<div class="text text--outlined">
			<ul class="ul--bulleted">
				<li><?php echo lang('Contribute information and news', '情報を追加する'); ?></li>
				<li><?php echo lang('Comment, review, rate, and tag', 'コメント・評価・レビュー'); ?></li>
				<li><?php echo lang('Customize your VK avatar', 'V系アバターを作る'); ?></li>
				<li><?php echo lang('Track items you own and want to own', 'コレクション・ほしい物リスト'); ?></li>
			</ul>
		</div>
	</div>
</div>