<?php
	script([
		"/scripts/external/script-tinysort.js",
		"/account/script-page-users.js"
	]);
	
	style([
		"/account/style-page-users.css"
	]);
	
	subnav([
		lang('Member list', 'メンバー一覧', 'hidden') => '/users/',
	]);
	
	if(!$_SESSION['is_signed_in']) {
		subnav([
			lang('Register/Sign in', '登録・サインイン', 'hidden') => '/account/',
		]);
	}
	
	$page_header = lang('Member list', 'メンバー一覧', ['container' => 'div']);
?>

<div class="col c1">
	<div>
		
		<?php
			if($error) {
				?>
					<div class="text text--outlined text--error symbol__error">
						<?php echo $error; ?>
					</div>
				<?php
			}
		?>
		
		<input class="input__choice symbol--orphan-a" name="filter" id="all" type="radio" checked />
		<input class="input__choice symbol--orphan-b" name="filter" id="editor" type="radio" />
		<input class="input__choice symbol--orphan-c" name="filter" id="moderator" type="radio" />
		<input class="input__choice symbol--orphan-d" name="filter" id="vip" type="radio" />
		
		<div class="any--flex any--flex-space-between any--margin symbol--parent">
			<div>
				<a class="release__control input__radio input__radio--selected symbol__triangle symbol--down" data-sort="date" data-dir="desc" href="">join date</a>
				<a class="release__control input__radio symbol__triangle symbol--up" data-sort="username" data-dir="asc" href="">A-Z</a>
			</div>
			<div>
				<label class="release__control input__radio symbol--orphan-a" data-filter for="all"><span class="symbol__unchecked">all</span></label>
				<label class="release__control input__radio symbol--orphan-b" data-filter for="editor"><span class="symbol__unchecked">editor</span></label>
				<label class="release__control input__radio symbol--orphan-c" data-filter for="moderator"><span class="symbol__unchecked">moderator</span></label>
				<label class="release__control input__radio symbol--orphan-d" data-filter for="vip"><span class="symbol__unchecked">VIP</span></label>
			</div>
		</div>
		
		<style>
			.users__container {
				padding-top: 0;
			}
			.users__container table {
				display: block;
				padding-top: 0.5rem;
			}
			.users__container tbody {
				display: block;
			}
			.users__container tr:first-of-type {
				background: hsl(var(--background));
				position: sticky;
				top: 3rem;
				z-index: 1;
			}
			.users__container th {
				font-weight: normal;
				padding-bottom: 0.5rem;
				padding-top: 0.5rem;
				text-align: left;
			}
			.users__container tr {
				display: flex;
			}
			.users__container tr > :nth-of-type(1) {
				width: 12ch;
			}
			.users__container tr > :nth-of-type(2) {
				flex-grow: 1;
			}
			.users__container tr > :nth-of-type(3) {
				text-align: right;
				width: auto;
			}
		</style>
		
		<div class="text users__container">
			<table class="">
				<tbody>
					<tr>
						<th>Joined</th>
						<th>Username</th>
						<?= $_SESSION['can_edit_roles'] ? '<th>Roles</th>' : null; ?>
					</tr>
					<?php
						foreach($users as $user) {
							$letter = strtolower(substr($user["username"], 0, 1));
							$letter = preg_match("/"."[^a-z]"."/", $letter) ? "#" : $letter;
							
							?>
								<tr class="user__container" data-is-editor="<?= $user["is_editor"]; ?>" data-is-moderator="<?= $user['is_moderator']; ?>" data-is-vip="<?= $user["is_vip"]; ?>" data-date="<?= $user['id']; ?>" data-username="<?= $user["username"]; ?>">
									<td class="any--weaken-color user__date">
										<?= substr($user["date_added"], 0, 10); ?>
									</td>
									<td class="td--width-100">
										<a class="user" data-icon="<?= $user['icon']; ?>" href="<?= $user['url']; ?>"><?= $user['username']; ?></a>
										<?php
											echo $user['is_boss'] || $user['is_editor'] || $user['is_moderator'] || $user['is_vip'] ? '<br />' : null;
											if($user['is_boss']) {
												?>
													<span class="any__note symbol__star--full">Boss</span>
												<?php
											}
											if($user['is_editor']) {
												?>
													<span class="any__note symbol__star--full">Editor</span>
												<?php
											}
											if($user['is_moderator']) {
												?>
													<span class="any__note symbol__star--full">Moderator</span>
												<?php
											}
											if($user['is_vip']) {
												?>
													<span class="any__note symbol__star--full">VIP</span>
												<?php
											}
										?>
									</td>
									<?= $_SESSION['can_edit_roles'] ? '<td class="any--weaken-color"><a class="a--inherit symbol__edit" href="'.$user['url'].'edit/">Edit</a></td>' : null; ?>
								</tr>
							<?php
							
							$prev_letter = $letter;
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>