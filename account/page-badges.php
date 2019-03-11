<?php
	if($_SESSION['username'] === 'inartistic') {
		$access_badge = new access_badge($pdo);
		$user['badges'] = $access_badge->access_badge([ 'get' => 'badge', 'user_id' => $user['id'] ]);
		?>
			<div class="col c1">
				<div>
					<h2>
						<div class="any--en">
							Badges <sup class="any--weaken">&beta;</sup>
						</div>
						<div class="any--jp any--weaken">
							<?php echo sanitize('バッジ'); ?> <sup class="any--weaken">&beta;</sup>
						</div>
					</h2>
					
					<div class="user__badges">
						<?php
							if(is_array($user['badges']) && !empty($user['badges'])) {
								foreach($user['badges'] as $badge) {
									echo $access_badge->render_badge($badge);
								}
							}
							else {
								echo '<span class="any--weaken-color symbol__error">This user hasn\'t won any badges yet.</span>';
							}
						?>
					</div>
				</div>
			</div>
		<?php
	}
?>

<style>
	.user__badges .badge__description {
		display: none;
	}
	.user__badges {
		display: flex;
		flex-wrap: wrap;
		margin-bottom: 2rem;
	}
	.user__badges .badge__container {
		margin-bottom: 1rem;
	}
</style>