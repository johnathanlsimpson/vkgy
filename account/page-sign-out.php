<?php
	$login->sign_out();
	
	if(!$_SESSION["loggedIn"]) {
		?>
			<div class="col c1">
				<div>
					<h1>
						Account services
					</h1>
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
					<h1>
						Account services
					</h1>
					<div class="text text--outlined text--error">
						An error occurred; you have not been signed out.
					</div>
				</div>
			</div>
		<?php
	}
?>