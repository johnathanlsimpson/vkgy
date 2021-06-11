<?php

	include('head-user.php');
	
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/script-initSelectize.js",
		"/scripts/external/script-inputmask.js",
		"/account/script-page-edit.js"
	]);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/account/style-page-edit.css"
	]);
	
	$access_artist = new access_artist($pdo);
	
	$page_header = tr('Change account settings', ['ja'=>'アカウント', 'lang'=>true,'lang_args'=>'div']);
	
	if(is_array($user) && !empty($user)) {
		$user['fan_since'] = is_numeric($user['fan_since']) ? $user['fan_since'] : date('Y');
		$max_fan_since = date('Y');
		$mid_fan_since = $max_fan_since - 15;
		$min_fan_since = $max_fan_since - 30;
		?>
			<template class="any--hidden" data-contains="artists" hidden><?php echo json_encode($artist_list); unset($artist_list); ?></template>
			
			<form action="/accounts/function-edit.php" class="col c1 any--margin" enctype="multipart/form-data" method="post" name="form__edit">
				
				<?php if($_SESSION['can_edit_roles'] && $_SESSION['user_id'] != $user['id']) { ?>
				
				<!-- User card -->
				<?php include('partial-card.php'); ?>
				
				<!-- Moderation -->
				<div class="col <?= $_SESSION['can_edit_permissions'] ? 'c3-AAB' : 'c1'; ?>">
					
					<!-- User roles -->
					<div>
						
						<input name="id" value="<?= $user['id']; ?>" hidden />
						
						<h2>
							<?= tr('User roles', ['ja'=>'ユーザーの役割','lang'=>true,'lang_args'=>'div']); ?>
						</h2>
						
						<ul class="text">
							<li>
								<label class="input__checkbox">
									<input class="input__choice" name="is_vip" type="checkbox" value="1" <?= $user['is_vip'] ? 'checked' : null; ?> />
									<span class="symbol__unchecked">VIP</span>
								</label>
								<?= tr('Can access VIP-limited content.'); ?>
							</li>
							<li>
								<label class="input__checkbox">
									<input class="input__choice" name="is_editor" type="checkbox" value="1" <?= $user['is_editor'] ? 'checked' : null; ?> />
									<span class="symbol__unchecked"><?= tr('Editor'); ?></span>
								</label>
								<?= tr('Can add/edit data.'); ?>
							</li>
							<li>
								<label class="input__checkbox">
									<input class="input__choice" name="is_moderator" type="checkbox" value="1" <?= $user['is_moderator'] ? 'checked' : null; ?> />
									<span class="symbol__unchecked"><?= tr('Moderator'); ?></span>
								</label>
								<?= tr('Can approve/delete data and assign user roles.'); ?>
							</li>
						</ul>
					</div>
					
					<?php if($_SESSION['can_edit_permissions']) { ?>
					
					<!-- User permissions -->
					<div>
						
						<h3>
							<?= tr('Individual permissions', ['ja'=>'各パーミッション', 'lang'=>true,'lang_args'=>'div']); ?>
						</h3>
						
						<ul class="text text--outlined user__permissions">
							<?php foreach($access_user->permissions as $permission_group => $permissions): ?>
								<li class="input__row">
									<div class="input__group any--flex-grow">
										
										<label class="input__label"><?= $permission_group; ?></label>
										
										<?php foreach($permissions as $permission): ?>
											<label class="input__checkbox">
												<input class="input__choice" name="<?= $permission; ?>" type="checkbox" value="1" <?= $user[$permission] ? 'checked' : null; ?> />
												<span class="symbol__unchecked">
													<?= str_replace( ['can_', '_'], ['', ' '], $permission ); ?>
												</span>
											</label>
										<?php endforeach; ?>
										
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
						
					</div>
					
					<?php } ?>
					
				</div>
				
				<?php } ?>
				
				<style>
					.user__permissions .input__radio {
						margin-left: 0 !important;
						margin-right: 0.5rem;
					}
				</style>
				
				<?php if($_SESSION['user_id'] === $user['id']) { ?>
				
				<!-- Edit profile -->
				<div class="col c3-AAB">
					<div>
						
						<h3>
							<?= tr('Profile options', ['lang'=>true,'ja'=>'会員情報','lang_args'=>'div']); ?>
						</h3>
						<ul class="text">
								<li class="input__row">
									<div class="input__group any--flex-grow">
										<label class="input__label"><?= tr('Name', ['context'=>'Profile option']); ?></label>
										<input class="any--flex-grow" name="name" placeholder="<?= tr('name', ['context'=>'Profile option']); ?>" value="<?php echo $user["name"]; ?>" />
									</div>
									<div class="input__group">
										<label class="input__label"><?= tr('Birthday', ['context'=>'Profile option']); ?></label>
										<input data-inputmask="'alias': '####-##-##'" max-length="10" name="birthday" placeholder="yyyy-mm-dd" size="10" value="<?php echo $user["birthday"]; ?>" />
									</div>
							</li>
							<li class="input__row">
								<div class="input__group any--flex-grow" style="flex-wrap:wrap;">
									<label class="input__label"><?= tr('VK fan since', ['context'=>'Profile option (refers to a year)']); ?></label>
									<input class="fan-since__input" min="<?= $min_fan_since; ?>" max="<?= $max_fan_since; ?>" name="fan_since" step="1" type="range" value="<?= $user['fan_since']; ?>" />
									<div class="fan-since__labels">
										<span class="any__note fan-since__tooltip" style="<?= '--fan-since-min:'.$min_fan_since.'; --fan-since:'.$user['fan_since'].';'; ?>"><?= $user['fan_since']; ?></span> 
										<span class="any__note any--weaken-color fan-since__label" style="">~<?= $min_fan_since; ?></span>
										<span class="any__note any--weaken-color fan-since__label" style=""><?= $mid_fan_since; ?></span>
										<span class="any__note any--weaken-color fan-since__label" style=""><?= $max_fan_since; ?></span>
									</div>
								</div>
								</li>
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= tr('Pronouns', ['context'=>'Profile option']); ?></label>
											<select class="input" name="pronouns" placeholder="<?= tr('select pronouns', ['context'=>'Profile option']); ?>">
												<option value="prefer not to say" <?= $user['pronouns'] === 'prefer not to say' ? 'selected' : null; ?>><?= tr('prefer not to say', ['context'=>'Pronouns option']); ?></option>
												<option value="she/her" <?= $user['pronouns'] === 'she/her' ? 'selected' : null; ?>><?= tr('she/her', ['context'=>'Pronouns option']); ?></option>
												<option value="he/him" <?= $user['pronouns'] == 'he/him' ? 'selected' : null; ?>><?= tr('he/him', ['context'=>'Pronouns option']); ?></option>
												<option value="they/them" <?= $user['pronouns'] === 'they/them' ? 'selected' : null; ?>><?= tr('they/them', ['context'=>'Pronouns option']); ?></option>
												<option value="custom" <?= !in_array($user['pronouns'], ['prefer not to say', 'she/her', 'he/him', 'they/them']) ? 'selected' : null; ?>><?= tr('custom', ['context'=>'Pronouns option']); ?></option>
											</select>
											<input class="input input--secondary any--hidden" name="custom_pronouns" placeholder="<?= tr('your pronouns', ['context'=>'Profile option']); ?>" value="<?= !in_array($user['pronouns'], ['prefer not to say', 'she/her', 'he/him', 'they/them', 'custom']) ? $user['pronouns'] : null; ?>" />
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label"><?= tr('Motto', ['context'=>'Profile option']); ?></label>
											<input class="any--flex-grow" name="motto" placeholder="<?= tr('Motto', ['context'=>'Profile option']); ?>" value="<?php echo $user["motto"]; ?>" />
										</div>
									</div>
								</li>
							</ul>
						
						<h3>
							<?= tr('Social Media',['context'=>'Profile option','lang'=>true,'lang_args'=>'div']); ?>
						</h3>
						
						<ul class="text">
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('Website', ['context'=>'Profile option']); ?></label>
									<input class="any--flex-grow" name="website" placeholder="https://yoursite.com" value="<?= $user['website']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('Twitter username', ['context'=>'Profile option']); ?></label>
									<span class="social__prefix">@</span>
									<input class="any--flex-grow" name="twitter" placeholder="<?= tr('username', ['context'=>'Profile option']); ?>" style="padding-left:calc(0rem + 3ch);" value="<?= $user['twitter']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('Facebook username', ['context'=>'Profile option']); ?></label>
									<span class="social__prefix">fb.com/</span>
									<input class="any--flex-grow" name="facebook" placeholder="<?= tr('username', ['context'=>'Profile option']); ?>" style="padding-left:calc(0rem + 8ch);" value="<?= $user['facebook']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('last.fm username', ['context'=>'Profile option']); ?></label>
									<span class="social__prefix">last.fm/user/</span>
									<input class="any--flex-grow" name="lastfm" placeholder="<?= tr('username', ['context'=>'Profile option']); ?>" style="padding-left:calc(0rem + 12ch);" value="<?= $user['lastfm']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('Monochrome Heaven username', ['context'=>'Profile option']); ?></label>
									<span class="social__prefix">m-h.com/profile/</span>
									<input class="any--flex-grow" name="mh" placeholder="123-username" style="padding-left:calc(0rem + 15ch);" value="<?= $user['mh']; ?>" />
								</div>
							</li>
						</ul>
						
						<script>
							let socialElems = document.querySelectorAll('[class^="social__"] + input');
							
							socialElems.forEach((elem) => {
								let socialType = elem.name.substring(7);
								let socialPattern;
								let pastedValue;
								
								// Set proper padding
								let elemStyles = window.getComputedStyle(elem);
								let paddingLeft = elem.previousElementSibling.offsetWidth;
								elem.style.paddingLeft = 'calc(0.5rem + ' + paddingLeft + 'px + 5px)';
								
								elem.addEventListener('paste', (event) => {
									event.preventDefault();
									
									pastedValue = event.clipboardData || window.clipboardData;
									pastedValue = pastedValue.getData('text');
									
									if(socialType != 'website') {
										pastedValue = pastedValue.replace('@', '').replace(/\/$/, '');
										pastedValue = pastedValue.split('/');
										pastedValue = pastedValue[pastedValue.length - 1];
									}
									
									window.document.execCommand('insertText', false, pastedValue);
								});
							})
						</script>
						
						<h3>
							<?= tr('Site customization', ['context'=>'Profile option','ja'=>'表示', 'lang'=>true,'lang_args'=>'div']); ?>
						</h3>
						<ul class="text">
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= tr('Site theme', ['context'=>'Profile option','ja'=>'背景画像']); ?></label>
											
											<input class="input__choice any--hidden" id="site_theme_0" name="site_theme" type="radio" value="0" <?php echo $user['site_theme'] == 0 ? 'checked' : null; ?> />
											<label class="input__radio symbol__unchecked " for="site_theme_0"><?= tr('default', ['context'=>'site theme option']); ?></label>
											
											<input class="input__choice any--hidden" id="site_theme_1" name="site_theme" type="radio" value="1" <?php echo $user['site_theme'] == 1 ? 'checked' : null; ?> />
											<label class="input__radio symbol__unchecked " for="site_theme_1"><?= tr('dark', ['context'=>'site theme option']); ?></label>
										</div>
									</div>
								</li>
								
								<!-- Points system -->
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= tr('Point animation', ['context'=>'Profile option','ja'=>'ポイントをアニメイトします']); ?></label>
											
											<label class="input__radio">
												<input class="input__choice" name="site_point_animations" type="radio" value="1" <?= $_SESSION['site_point_animations'] ? 'checked' : null; ?> />
												<span class="symbol__unchecked"><?= tr('show', ['context'=>'option for point animations']); ?></span>
											</label>
											
											<input class="input__choice" id="hide-points" name="site_point_animations" type="radio" value="0" <?= !$_SESSION['site_point_animations'] ? 'checked' : null; ?> />
											<label class="input__radio symbol__unchecked" for="hide-points">
												<?= tr('hide', ['context'=>'option for point animations']); ?>
											</label>
											
											<span class="point__container point--example h5" href="/users/inartistic/" style="opacity:1;transform:none;display:inline-flex;align-items:center;height:2rem;">
												<span class="point__value">1</span>
												<span class="symbol__point point__symbol"></span>
											</span>
											
											<style>
												#hide-points:checked ~ .point--example {
													animation: none;
												}
												#hide-points:checked ~ .point--example::after {
													background-color: hsla(var(--background), 0.5);
													background-image: linear-gradient(to bottom right, transparent, transparent calc(50% - 3px), hsla(var(--text--secondary), 0.8) calc(50% - 2px), hsla(var(--text--secondary), 0.8) calc(50% + 1px), transparent calc(50% + 2px));
													bottom: 0;
													border-radius: inherit;
													content: "";
													left: 0;
													position: absolute;
													right: 0;
													top: 0;
												}
												.point--example {
													animation-name: fadePointFromHalf;
													animation-duration: 1s;
													animation-timing-function: ease-out;
												}
											</style>
											
										</div>
									</div>
								</li>
								
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= tr('Username icon', ['context'=>'Profile option','ja'=>'アイコン']); ?></label>
											
											<?php
												foreach([ 'crown', 'heart', 'star', 'flower', 'moon' ] as $icon_key => $icon_name) {
													
													if( $user['icon'] === $icon_name || $_SESSION['is_vip'] ) {
														?>
															<label class="input__radio" style="font-family:inherit;margin-right:0.5rem;">
															<input class="input__choice" name="icon" type="radio" value="<?= $icon_key; ?>" <?= $user['icon'] === $icon_name ? 'checked' : null; ?> />
															<span class="symbol__unchecked" style="align-items:center;display:flex;">
														<?php
													}
													else {
														echo '<span style="line-height:2rem;margin-right:0.5rem;">';
													}
													
													echo '<span class="symbol__user-'.$icon_name.' symbol--standalone" style="margin-right:5px;"></span>'.$user['username'];
													
													if( $user['icon'] === $icon_name || $_SESSION['is_vip'] ) {
														?>
															</span>
															</label>
														<?php
													}
													else {
														echo '</span>';
													}
													
												}
											?>
											
											<?php
												/*if($user['is_vip']) {
													?>
														<input class="input__choice any--hidden" id="icon_0" name="icon" type="radio" value="0" <?= $user['icon'] === 'crown' ? 'checked' : null; ?> />
														<label class="input__radio symbol__unchecked " for="icon_0"><span class="symbol__user-crown symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><a href=""><?= $user['username']; ?></a></label>
														
														<input class="input__choice any--hidden" id="icon_1" name="icon" type="radio" value="1" <?= $user['icon'] === 'heart' ? 'checked' : null; ?> />
														<label class="input__radio symbol__unchecked " for="icon_1" style="font-family:inherit;"><span class="symbol__user-heart symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														
														<input class="input__choice any--hidden" id="icon_2" name="icon" type="radio" value="2" <?= $user['icon'] === 'star' ? 'checked' : null; ?> />
														<label class="input__radio symbol__unchecked " for="icon_2"><span class="symbol__user-star symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														
														<input class="input__choice any--hidden" id="icon_3" name="icon" type="radio" value="3" <?= $user['icon'] === 'flower' ? 'checked' : null; ?> />
														<label class="input__radio symbol__unchecked " for="icon_3"><span class="symbol__user-flower symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
													<?php
												}
												else {
													?>
														<label class="input__radio symbol__checked input__radio--selected" for="icon_0"><span class="symbol__user-crown symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														<label class="input__radio" style="background:none;"><span class="symbol__user-heart symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
														<label class="input__radio" style="background:none;"><span class="symbol__user-star symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
														<label class="input__radio" style="background:none;"><span class="symbol__user-flower symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
													<?php
												}*/
											?>
										</div>
									</div>
									
									<?= !$_SESSION['is_vip'] ? '<span class="symbol__lock" style="display:block;margin-top:1rem;">'.tr('This feature is limited to VIP members.').'.</span>' : null; ?>
								</li>
						</ul>
					</div>
					
					<div>
						<h3>
							<?= tr('Email address'); ?>
						</h3>
						<div class="text text--outlined <?= strlen($user['email']) ? null : 'text--error'; ?> ">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= ('Email'); ?></label>
									<input class="any--flex-grow" name="email" placeholder="<?= ('Email'); ?>" type="email" value="<?= $user["email"]; ?>" />
								</div>
							</div>
							
							<?= strlen($user['email']) ? null : '<div class="symbol__error" style="margin-top:1rem;">'.tr('Your password cannot be recovered if you don\'t have an email address listed.').'</div>'; ?>
						</div>
						
						<h3>
							<?= tr('Change username'); ?>
						</h3>
						<div class="text text--outlined">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('New username'); ?></label>
									<input autocomplete="off" class="any--flex-grow" name="new_username" placeholder="<?= tr('New username'); ?>" />
								</div>
							</div>
						</div>
						
						<h3>
							<?= tr('Change password'); ?>
						</h3>
						<div class="text text--outlined">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('Current password'); ?></label>
									<input autocomplete="off" class="any--flex-grow" name="current_password" placeholder="<?= tr('Current password'); ?>" type="password" />
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('New password'); ?></label>
									<input autocomplete="new-password" class="any--flex-grow" name="new_password_1" placeholder="<?= tr('New password'); ?>" type="password" />
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label"><?= tr('New password (confirm)'); ?></label>
									<input autocomplete="new-password" class="any--flex-grow" name="new_password_2" placeholder="<?= tr('New password (confirm)'); ?>" type="password" />
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<?php } ?>
				
				<div class="text text--docked">
					
					<div class="any--flex" data-role="submit-container">
						<button class="any--flex-grow" name="submit" type="submit">
							<?= tr('Save settings'); ?>
						</button>
						<span data-role="status"></span>
					</div>
					
					<div class="any--flex any--hidden" data-role="edit-container">
						<a class="any--align-center a--outlined a--padded any--flex-grow symbol__user" href="<?= $user['url']; ?>"><?= tr('View profile'); ?></a>
						<a class="add__edit any--weaken-color a--outlined a--padded symbol__edit" data-role="edit" href="<?= $_SESSION['can_edit_roles'] && $_SESSION['username'] != $user['username'] ? $user['url'].'edit/' : '/account/'; ?>" style="margin-left:1rem;"><?= tr('Edit again'); ?></a>
					</div>
					
					<div class="edit__result text text--outlined text--notice" data-role="result"></div>
				</div>
			</form>
			
			<?php if($_SESSION['user_id'] === $user['id']) { ?>
			
			<!-- Tools -->
			<div class="col c1">
				<div>
					<h2>
						<?= tr('Avatars and banners'); ?>
					</h2>
					<ul class="text">
						<li>
							<?= tr('Copy the URL below and paste it into your forum signature to display a banner of your vkgy account.'); ?>
						</li>
						<li>
							<code>https://vk.gy/sig.jpg?<?= $_SESSION['username']; ?></code><br /><br />
							<img alt="signature banner - light" src="https://vk.gy/sig.jpg?<?= $_SESSION['username']; ?>" />
						</li>
						<li>
							<code>https://vk.gy/sig.jpg?<?= $_SESSION['username']; ?>&amp;dark</code><br /><br />
							<img alt="signature banner - light" src="https://vk.gy/sig.jpg?<?= $_SESSION['username']; ?>&amp;dark" />
						</li>
						<li>
							<code>https://vk.gy/av.jpg?<?= $_SESSION['username']; ?></code><br /><br />
							<img alt="avatar - light" src="https://vk.gy/av.jpg?<?= $_SESSION['username']; ?>" />
						</li>
						<li>
							<code>https://vk.gy/av.jpg?<?= $_SESSION['username']; ?>&amp;dark</code><br /><br />
							<img alt="avatar - light" src="https://vk.gy/av.jpg?<?= $_SESSION['username']; ?>&amp;dark" />
						</li>
					</ul>
					
					<h2>
						<?= tr('Use vkgy with Mp3tag'); ?>
					</h2>
					<input class="obscure__input" id="obscure-mp3tag" type="checkbox" checked="">
					<div class="text obscure__container">
						<div class="obscure__item">
							<?= tr('You can correctly tag your music files using the vkgy database in Mp3tag.'); ?>
						</div>
						<div class="obscure__item">
							<a class="a--outlined a--padded" href="/tag-source/"><?= tr('Download Mp3tag script'); ?></a>
						</div>
						<div class="obscure__item">
							<hr />
							<h3 class="obscure__item">
								<?= tr('How to use'); ?>
							</h3>
						</div>
						<div class="obscure__item">
							<ol>
								<li><?= tr('Download the {mp3tag} program.', [ 'replace' => [ 'mp3tag' => '<a href="https://mp3tag.de/en/" target="_blank">Mp3tag</a>' ] ]); ?></li>
								<li><?= tr('Download the .src file above.'); ?></li>
								<li><?= tr('Move the .src file to {folder_name}.', [ 'replace' => [ 'folder_name' => '%APPDATA%\Mp3tag\data\sources' ] ]); ?></li>
								<li><?= tr('Open your music files in Mp3tag and select them.'); ?></li>
								<li><?= tr('In the file menu, go to Tag Sources > vkgy.'); ?></li>
								<li><?= tr('Use the search popup to find the artist and/or album.'); ?></li>
							</ol>
						</div>
						
						<label class="input__button obscure__button" for="obscure-mp3tag"><?= tr('Detailed instructions'); ?></label>
					</div>
					
					<h2>
						<?= tr('Download collection list'); ?>
					</h2>
					<div class="text">
						<?= tr('You can download a tab-separated {file_type} list of the items you own.', [ 'replace' => [ 'file_type' => '<code>.csv</code>' ] ]); ?>
						
						<br /><br />
						
						<a class="a--outlined a--padded" href="/users/<?php echo $_SESSION["username"]; ?>/&action=download"><?= tr('Download collection list'); ?></a> <a class="a--padded" href="/users/<?php echo $_SESSION["username"]; ?>/&action=download&limit=selling"><?= tr('Download selling list'); ?></a>
					</div>
				</div>
			</div>
			
			<?php } ?>
		<?php
	}
?>