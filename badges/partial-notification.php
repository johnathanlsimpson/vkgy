<?php

if($_SESSION['loggedIn'] && strlen($rendered_badge)) {
	style('../badges/style-partial-notice.css');
	
	?>
		<div class="col c1">
			<div>
				<div class="text text--outlined text--notice" style="text-align: center;">
					<?php echo $rendered_badge; ?>
					
					<p>
						You unlocked a new badge! <a class="symbol__next" href="/users/<?php echo $_SESSION['username']; ?>/">View all your badges</a> (or disable notifications) at <a class="a--inherited" href="/users/<?php echo $_SESSION['username']; ?>/">your profile</a>.
					</p>
				</div>
			</div>
		</div>
	<?php
}