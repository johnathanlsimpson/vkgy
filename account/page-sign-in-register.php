<?php
	script([
		"/account/script-page-sign-in-register.js"
	]);
	
	style([
		"/account/style-page-sign-in-register.css"
	]);
	
	$page_header = lang('Account', 'アカウント', ['container' => 'div']);
?>

<div class="col c2 any--signed-out-only">
	<div>
		<h2>
			Sign in
		</h2>
		<form action="" class="text sign-in__container <?php echo $_SERVER['HTTP_REFERER'] ? 'sign-in--back' : 'sign-in--refresh'; ?>" enctype="multipart/form-data" method="post" name="form__sign-in">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<input class="any--flex-grow" name="username" pattern="^[\w- \.]{3,}$" placeholder="username" required />
				</div>
			</div>
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<input class="any--flex-grow" name="password" placeholder="password" type="password" required />
				</div>
			</div>
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" type="submit">
						Sign in
					</button>
					<span data-role="status"></span>
				</div>
			</div>
			<div class="text text--outlined text--notice" data-role="result"></div>
		</form>
		
		<h3>
			Register
		</h3>
		<form action="/account/function-register.php" class="text register__container register__section" enctype="multipart/form-data" method="post" name="register__form" autocomplete="off">
				<ul>
					<input class="any--hidden" id="register__radio--bat" name="register_avatar" type="radio" value="bat" checked />
					<input class="any--hidden" id="register__radio--gecko" name="register_avatar" type="radio" value="gecko" />
					
					<li>
						<div class="input__row">
							<div class="input__group">
								<label class="input__label">Username</label>
							</div>
							<input class="any--flex-grow" name="register_username" pattern="[A-z0-9-]+" placeholder="username (ユーザ名)" title="A-z, 0-9, -" />
						</div>
						
						<div class="any--weaken register__note">
							Usernames may contain: <strong>A-z</strong>, <strong>0-9</strong>, <strong>-</strong>. <span class="any--jp">（半角英字、数字、ハイフンを使用できます。）</span>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Password</label>
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
								<label class="input__label">Avatar eyes</label>
								<label class="input__checkbox-label symbol__unchecked register__bat" for="register__radio--bat">bat</label>
								<label class="input__checkbox-label symbol__unchecked register__gecko" for="register__radio--gecko">gecko</label>
							</div>
							<div class="input__group">
								<div class="register__face"></div>
							</div>
						</div>
						
						<div class="any--weaken register__note">
							The avatar can be further customized after joining. <span class="any--jp">（登録後、アバターをさらにカスタマイズすることができます。）</span>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow" data-role="submit-container">
								<button class="any--flex-grow" data-role="submit" name="register_submit" type="submit">Join vkgy (登録する)</button>
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
			Why register?
		</h3>
		<div class="text text--outlined">
			<ul class="ul--bulleted">
				<li>Edit your custom profile.</li>
				<li>Rate and collect releases, and add releases to your wishlist.</li>
				<li>Post to the community blog.</li>
				<li>Comment on blog posts and releases.</li>
				<li>Edit artist and release information.</li>
				<li>VIP users: access special section and view exclusive images in super high quality, without watermarks.</li>
			</ul>
		</div>
	</div>
</div>