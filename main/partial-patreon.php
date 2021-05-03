<?php

$access_user = $access_user ?: new access_user($pdo);

style([
	'/main/style-partial-patreon.css',
]);

// Get VIP patrons
$patrons = $access_user->access_user([ 'is_vip' => true ]);

// Get non-VIP patrons
foreach([ 'redaudrey' ] as $non_vip_patron) {
	$patrons[] = $access_user->access_user([ 'username' => $non_vip_patron ]);
}

// Sort patrons
usort($patrons, function($a, $b) { return strtolower($a['username']) <=> strtolower($b['username']); });

// Make sure icons exist
$num_patrons = count($patrons);
for($i=0; $i<$num_patrons; $i++) {
	if(file_exists('..'.$patrons[$i]['avatar_url'])) {
		$patrons[$i]['avatar_url'] .= '?'.date( 'YmdHis', filemtime('..'.$patrons[$i]['avatar_url']) );
	}
	else {
		$patrons[$i]['avatar_url'] = '/usericons/avatar-anonymous.png';
	}
}

// Get dummy patrons to fill gaps in layout
$patron_columns = 3;
$patron_modulo = count($patrons) % $patron_columns;
while($patron_modulo) {
	$patrons[] = [ 'avatar_url' => '/usericons/avatar-anonymous.png' ];
	$patron_modulo = count($patrons) % $patron_columns;
}

?>
<!-- Background color -->
<div class="patreon__bg any--margin">
	<!-- Background slashes at top and bottom -->
	<div class="patreon__diagonals col c1 any--flex">
		<!-- Spacing helper to keep all content in center -->
		<div class="patreon__spacing col c4-ABBC">
			<!-- Empty space on side -->
			<div class="patreon__empty"></div>
			
			<!-- Patreon content starts here -->
			<div class="patreon__container col c3-AAB any--flex">
				
				<!-- Text on left side -->
				<div class="patreon__text">
					
					<h1 class="patreon__title">
						<?= lang('Thank you for supporting vkgy', 'サポーターの皆様のおかげです', 'div'); ?>
					</h1>
					
					<?php if($_SESSION['is_vip']): ?>
						<p class="patreon__p">
							<?= $access_user->render_username($_SESSION); ?>, thank you so much!<br />
							vkgy is possible because of <a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">Patreon</a> supporters like you.<br />
							<span style="font-size:1rem;">&ndash; <?= $access_user->render_username(['username' => 'inartistic']); ?></span>
						</p>
					<?php else: ?>
					<p class="patreon__p">
						vkgy is possible thanks to our <a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">Patreon</a> supporters!<br />Please consider joining them, for these benefits:
					</p>
					
					<ul class="patreon__list">
						<li>No ads</li>
						<li><span class="patreon__badge" >VIP</span> badge</li>
						<li>Early access to features</li>
						<li>Priority support</li>
						<li>Full-resolution images</li>
						<li>Exclusive Discord channel</li>
						<li>Dev blog</li>
						<li>Avatar items and colors</li>
						<li><a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">+ more</a></li>
					</ul>
					
					<a class="patreon__button a--inherit" href="https://patreon.com/vkgy" target="_blank">
						<span class=""></span>
						Become a Patron
					</a>
					<?php endif; ?>
					
				</div>
				
				<!-- Avatar wall on right side -->
				<div class="patreon__wall">
					<!-- Handles scrolling animation -->
					<div class="patreon__scroll">
						<?php
							// Display all avatars twice to give us room to repeat scroll
							for($i=0; $i<2; $i++) {
								foreach($patrons as $patron) {
									if($patron['username']) {
										?>
											<a class="patreon__patron" href="<?= $patron['url']; ?>">
												<img alt="<?= $patron['username']; ?>" class="patreon__avatar" src="<?= $patron['avatar_url']; ?>" />
												<span class="user patreon__username" data-icon="<?= $patron['icon']; ?>"><?= $patron['username']; ?></span>
											</a>
										<?php
									}
									else {
										?>
											<img class="patreon__avatar" src="<?= $patron['avatar_url']; ?>" />
										<?php
									}
								}
							}
						?>
					</div>
				</div>
				
			</div>
			<!-- End Patreon content -->
			
			<!-- Empty space on side -->
			<div class="patreon__empty"></div>
		</div>
	</div>
</div>