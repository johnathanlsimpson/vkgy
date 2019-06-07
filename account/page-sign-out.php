<?php
	$login->sign_out();
	
	$page_header = lang('Sign out', 'サインアウト', ['container' => 'div']);
	
	if(!$_SESSION["loggedIn"]) {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--notice">
						Signed out.
						<meta http-equiv="refresh" content="0; url=<?php echo $_GET["request"] ? sanitize($_GET["request"]) : '/'; ?>">
					</div>
				</div>
			</div>
		<?php
	}
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error">
						An error occurred; you have not been signed out.
					</div>
				</div>
			</div>
		<?php
	}
?>