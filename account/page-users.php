<?php
	script([
		"/scripts/external/script-tinysort.js",
		"/account/script-page-users.js"
	]);
	
	style([
		"/account/style-page-users.css"
	]);
	
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
		
		<div class="any--flex any--flex-space-between any--margin">
			<div>
				<a class="release__control input__checkbox-label symbol__down-caret" data-sort="date" data-dir="desc" href="">Join date</a>
				<a class="release__control input__checkbox-label input__checkbox-label--selected symbol__up-caret" data-sort="username" data-dir="asc" href="">A-Z</a>
			</div>
			<div>
				<label class="release__control input__checkbox-label input__checkbox-label--selected" data-filter for="all">All</label>
				<label class="release__control input__checkbox-label" data-filter for="admin">Admin</label>
				<label class="release__control input__checkbox-label" data-filter for="vip">VIP</label>
			</div>
		</div>
		
		<input class="input__checkbox" name="filter" id="all" type="radio" />
		<input class="input__checkbox" name="filter" id="admin" type="radio" />
		<input class="input__checkbox" name="filter" id="vip" type="radio" />
		
		<div class="text">
			<table>
				<tbody>
					<?php
						foreach($users as $user) {
							$letter = strtolower(substr($user["username"], 0, 1));
							$letter = preg_match("/"."[^a-z]"."/", $letter) ? "#" : $letter;
							
							?>
								<tr class="user__container" <?= $user["is_admin"] ? "data-admin" : null; ?> <?= $user["is_vip"] ? "data-vip" : null; ?> data-date="<?= $user['id']; ?>" data-username="<?= $user["username"]; ?>">
									<td class="any--weaken-color any--no-wrap">
										<?php echo substr($user["date_added"], 0, 10); ?>
									</td>
									<td class="td--width-100">
										<a class="user" href="/users/<?php echo $user["username"]; ?>"><?php echo $user["username"]; ?></a>
										<?php
											if($user["id"] === "1") {
												?>
													<span class="any__note symbol__star--full">Founder</span>
												<?php
											}
											if($user["is_admin"]) {
												?>
													<span class="any__note symbol__star--full">Admin</span>
												<?php
											}
											if($user["is_vip"]) {
												?>
													<span class="any__note symbol__star--full">VIP</span>
												<?php
											}
										?>
									</td>
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