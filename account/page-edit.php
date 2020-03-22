<?php

	$user['username'] = $_SESSION['username'];
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
	
	$page_header = lang('Change account settings', 'アカウント', 'div');
	
	if($_SESSION["is_signed_in"] && is_numeric($_SESSION["userID"])) {
		$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $_SESSION["userID"] ]);
		$is_vip = $stmt_check->fetchColumn();
	}
	
	if(is_array($user) && !empty($user)) {
		$user['fan_since'] = is_numeric($user['fan_since']) ? $user['fan_since'] : date('Y');
		$max_fan_since = date('Y');
		$mid_fan_since = $max_fan_since - 15;
		$min_fan_since = $max_fan_since - 30;
		?>
			<span class="any--hidden" data-contains="artists" hidden><?php echo json_encode($artist_list); unset($artist_list); ?></span>
			
			<form action="/accounts/function-edit.php" class="col c1 any--margin" enctype="multipart/form-data" method="post" name="form__edit">
				<div class="col c3-AAB">
					<div>
						
						<h3>
							<?= lang('Profile options', '会員情報', 'div'); ?>
						</h3>
						<ul class="text">
								<li class="input__row">
									<div class="input__group any--flex-grow">
										<label class="input__label">Name</label>
										<input class="any--flex-grow" name="name" placeholder="name" value="<?php echo $user["name"]; ?>" />
									</div>
									<div class="input__group">
										<label class="input__label">Birthday</label>
										<input data-inputmask="'alias': '####-##-##'" max-length="10" name="birthday" placeholder="yyyy-mm-dd" size="10" value="<?php echo $user["birthday"]; ?>" />
									</div>
							</li>
							<li class="input__row">
								<div class="input__group any--flex-grow" style="flex-wrap:wrap;">
									<label class="input__label">VK fan since</label>
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
											<label class="input__label">pronouns</label>
											<select class="input" name="pronouns" placeholder="select pronouns">
												<option value="prefer not to say" <?= $user['pronouns'] === 'prefer not to say' ? 'selected' : null; ?>>prefer not to say</option>
												<option value="she/her" <?= $user['pronouns'] === 'she/her' ? 'selected' : null; ?>>she/her</option>
												<option value="he/him" <?= $user['pronouns'] == 'he/him' ? 'selected' : null; ?>>he/him</option>
												<option value="they/them" <?= $user['pronouns'] === 'they/them' ? 'selected' : null; ?>>they/them</option>
												<option value="custom" <?= !in_array($user['pronouns'], ['prefer not to say', 'she/her', 'he/him', 'they/them']) ? 'selected' : null; ?>>custom</option>
											</select>
											<input class="input input--secondary any--hidden" name="custom_pronouns" placeholder="your pronouns" value="<?= !in_array($user['pronouns'], ['prefer not to say', 'she/her', 'he/him', 'they/them', 'custom']) ? $user['pronouns'] : null; ?>" />
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label">Motto</label>
											<input class="any--flex-grow" name="motto" placeholder="motto" value="<?php echo $user["motto"]; ?>" />
										</div>
									</div>
								</li>
							</ul>
						
						<h3>
							<?= lang('Socials', 'ウエブサイト', 'div'); ?>
						</h3>
						
						<ul class="text">
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Website</label>
									<input class="any--flex-grow" name="website" placeholder="https://yoursite.com" value="<?= $user['website']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Twitter username</label>
									<span class="social__prefix">@</span>
									<input class="any--flex-grow" name="twitter" placeholder="username" style="padding-left:calc(0rem + 3ch);" value="<?= $user['twitter']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Facebook username</label>
									<span class="social__prefix">fb.com/</span>
									<input class="any--flex-grow" name="facebook" placeholder="username" style="padding-left:calc(0rem + 8ch);" value="<?= $user['facebook']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">last.fm username</label>
									<span class="social__prefix">last.fm/user/</span>
									<input class="any--flex-grow" name="lastfm" placeholder="username" style="padding-left:calc(0rem + 12ch);" value="<?= $user['lastfm']; ?>" />
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Monochrome Heaven username</label>
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
							<?= lang('Site customization', '表示', 'div'); ?>
						</h3>
						<ul class="text">
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= lang('Site theme', '背景画像', 'hidden'); ?></label>
											
											<input class="input__checkbox any--hidden" id="site_theme_0" name="site_theme" type="radio" value="0" <?php echo $user['site_theme'] == 0 ? 'checked' : null; ?> />
											<label class="input__checkbox-label symbol__unchecked " for="site_theme_0">default</label>
											
											<input class="input__checkbox any--hidden" id="site_theme_1" name="site_theme" type="radio" value="1" <?php echo $user['site_theme'] == 1 ? 'checked' : null; ?> />
											<label class="input__checkbox-label symbol__unchecked " for="site_theme_1">dark</label>
										</div>
									</div>
								</li>
								<li>
									<div class="input__row">
										<div class="input__group">
											<label class="input__label"><?= lang('Username style', 'アイコン', 'hidden'); ?></label>
											
											<?php
												if($user['is_vip']) {
													?>
														<input class="input__checkbox any--hidden" id="icon_0" name="icon" type="radio" value="0" <?= $user['icon'] === 'crown' ? 'checked' : null; ?> />
														<label class="input__checkbox-label symbol__unchecked " for="icon_0"><span class="symbol__user-crown symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														
														<input class="input__checkbox any--hidden" id="icon_1" name="icon" type="radio" value="1" <?= $user['icon'] === 'heart' ? 'checked' : null; ?> />
														<label class="input__checkbox-label symbol__unchecked " for="icon_1"><span class="symbol__user-heart symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														
														<input class="input__checkbox any--hidden" id="icon_2" name="icon" type="radio" value="2" <?= $user['icon'] === 'star' ? 'checked' : null; ?> />
														<label class="input__checkbox-label symbol__unchecked " for="icon_2"><span class="symbol__user-star symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														
														<input class="input__checkbox any--hidden" id="icon_3" name="icon" type="radio" value="3" <?= $user['icon'] === 'flower' ? 'checked' : null; ?> />
														<label class="input__checkbox-label symbol__unchecked " for="icon_3"><span class="symbol__user-flower symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
													<?php
												}
												else {
													?>
														<label class="input__checkbox-label symbol__checked input__checkbox-label--selected" for="icon_0"><span class="symbol__user-crown symbol--standalone" style="margin: 0 0.25ch 0 0.5ch;"></span><?= $user['username']; ?></label>
														<label class="input__checkbox-label" style="background:none;"><span class="symbol__user-heart symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
														<label class="input__checkbox-label" style="background:none;"><span class="symbol__user-star symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
														<label class="input__checkbox-label" style="background:none;"><span class="symbol__user-flower symbol--standalone" style="margin: 0 0.25ch 0 0;"></span><?= $user['username']; ?></label>
													<?php
												}
											?>
										</div>
									</div>
									
									<?= $user['is_vip'] ? '<span class="symbol__vip">This feature can be accessed after becoming a <a href="https://patreon.com/vkgy" target="_blank">VIP member</a>.</span>' : null; ?>
								</li>
						</ul>
					</div>
					
					<div>
						<h3>
							Email address
						</h3>
						<div class="text text--outlined <?= strlen($user['email']) ? null : 'text--error'; ?> ">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Email</label>
									<input class="any--flex-grow" name="email" placeholder="email" type="email" value="<?= $user["email"]; ?>" />
								</div>
							</div>
							
							<?= strlen($user['email']) ? null : '<div class="symbol__error" style="margin-top:1rem;">Your password cannot be recovered if you don\'t have an email address listed.</div>'; ?>
						</div>
						
						<h3>
							Change username
						</h3>
						<div class="text text--outlined">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">New username</label>
									<input autocomplete="off" class="any--flex-grow" name="new_username" placeholder="new username" />
								</div>
							</div>
						</div>
						
						<h3>
							Change password
						</h3>
						<div class="text text--outlined">
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Current password</label>
									<input autocomplete="off" class="any--flex-grow" name="current_password" placeholder="current password" type="password" />
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">New password</label>
									<input autocomplete="new-password" class="any--flex-grow" name="new_password_1" placeholder="new password" type="password" />
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">New password (confirm)</label>
									<input autocomplete="new-password" class="any--flex-grow" name="new_password_2" placeholder="new password (confirm)" type="password" />
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="text text--docked">
					
					<div class="any--flex" data-role="submit-container">
						<button class="any--flex-grow" name="submit" type="submit">
							Save settings
						</button>
						<span data-role="status"></span>
					</div>
					
					<div class="any--flex any--hidden" data-role="edit-container">
						<a class="any--align-center a--outlined a--padded any--flex-grow symbol__user" href="<?= '/users/'.$_SESSION['username'].'/'; ?>">View profile</a>
						<a class="add__edit any--weaken-color a--outlined a--padded symbol__edit" data-role="edit" href="/account/" style="margin-left:1rem;">Edit again</a>
					</div>
					
					<div class="edit__result text text--outlined text--notice" data-role="result"></div>
				</div>
			</form>
			
			<div class="col c1">
				<div>
					<h2>
						Avatars and banners
					</h2>
					<ul class="text">
						<li>
							Copy any URL below and paste it into your forum signature to display your account's latest stats, or your latest avatar.<br /><br />
							<div class="symbol__help any--weaken">If you manually save and upload the image, it will not auto-update. The image will also not auto-update on certain services such as Twitter, even if you copy+paste the link as instructed.</div>
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
						Use vkgy with Mp3tag
					</h2>
					<input class="obscure__input" id="obscure-mp3tag" type="checkbox" checked="">
					<div class="text obscure__container">
						<div class="obscure__item">
							You can now tag your music files using the vkgy database. This will automatically remove incorrect tags, add cover art, add romaji, and more.
						</div>
						<div class="obscure__item">
							<a class="a--outlined a--padded" href="/tag-source/">Download Mp3tag script</a>
						</div>
						<div class="obscure__item">
							<hr />
							<h3 class="obscure__item">
								Basic usage
							</h3>
						</div>
						<div class="obscure__item">
							<ol>
								<li>Download <a href="https://mp3tag.de/en/" target="_blank">Mp3tag</a>.</li>
								<li>Download the .src file above.</li>
								<li>Move the .src file to %APPDATA%\Mp3tag\data\sources.</li>
								<li>Open your music files in Mp3tag and select them.</li>
								<li>Go to Tag Sources > vkgy.</li>
								<li>Use the search popup to find the artist and/or album. (You can search like this: &ldquo;artist|album&rdquo;, &ldquo;artist&rdquo;, or &ldquo;|album&rdquo;.)</li>
							</ol>
						</div>
						<div class="obscure__item">
							<hr />
						</div>
						<div class="obscure__item">
							<h3>
								Notes
							</h3>
						</div>
						<div class="obscure__item">
							<ul class="ul--bulleted">
								<li>Searches work with Japanese and/or romaji.</li>
								<li>To search all releases by an artist, just ommit the &ldquo;|album&rdquo; portion in the search bar.</li>
								<li>To search by album name only, leave the artist portion blank and format your search like &ldquo;|album&rdquo;.</li>
								<li>Try leaving out press/type when search by album name.</li>
							</ul>
						</div>
						
						<label class="input__button obscure__button" for="obscure-mp3tag">Detailed instructions</label>
					</div>
					
					<h2>
						Download collection list
					</h2>
					<div class="text">
						Below, you can download a tab-separated <code>.csv</code> list of the items you own, or items you're selling. (These files work best when imported into Google Docs.)
						
						<br />
						
						<a class="a--outlined a--padded" href="/users/<?php echo $_SESSION["username"]; ?>/&action=download">Download collection</a> <a class="a--padded" href="/users/<?php echo $_SESSION["username"]; ?>/&action=download&limit=selling">Download selling list</a>
					</div>
				</div>
			</div>
		<?php
	}
?>