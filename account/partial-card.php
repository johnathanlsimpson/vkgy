<?php
	style([
		'/account/style-partial-card.css'
	]);
?>

<div class="text user__card any--flex">
	
	<?= $_SESSION['username'] == $user['username'] ? '<a class="user__avatar-link" href="/account/edit-avatar/"><span class="user__avatar-text symbol__edit">'.lang('Edit avatar', 'アバター変更', 'hidden').'</span>' : null; ?>
	<svg class="user__avatar <?php echo $avatar_class; ?>" version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="0px" height="0px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
		<?php echo $user["avatar"]; ?>
	</svg>
	<?= $_SESSION['username'] == $user['username'] ? '</a>' : null; ?>
	
	<div class="user__card-data">
		<h1 class="user__username">
			<a class="a--inherit <?= 'symbol__user-'.$user['icon']; ?>" href="/users/<?php echo $user["username"]; ?>/"><?php echo $user["username"]; ?></a>
			<?php
				echo $user['is_admin'] === 28 ? '<span class="any__note symbol__star--full user__flair">Boss</span>' : null;
				echo $user['is_admin'] ? '<span class="any__note symbol__star--full user__flair">Editor</span>' : null;
				echo $user['is_vip'] ? '<span class="any__note symbol__star--full user__flair">VIP</span>' : null;
				echo $_SESSION['username'] == $user['username'] ? '<a class="symbol__edit" href="/account/" style="font-size:1rem;font-weight:normal;line-height:1;vertical-align:middle;">'.lang('Edit', '情報変更', 'hidden').'</a>' : null;
			?>
		</h1>
		
		<?php
			if($user['motto'] || $user['birthday'] || $user['pronouns'] || $user['website'] || $user['twitter'] || $user['mh'] || $user['facebook'] || $user['lastfm']) {
				?>
					<!-- User details -->
					<ul class="user__data data__container">
						<?php
							foreach(['birthday', 'pronouns', 'website', 'twitter', 'facebook', 'lastfm', 'mh'] as $field) {
								if(strlen($user[$field]) && !in_array($user[$field], ['0000-00-00', 'prefer not to say', 'custom'])) {
									?>
										<li class="data__item">
											<h5>
												<?= $field; ?>
											</h5>
											<?php
												switch($field) {
													case "member since":
														echo substr($user["date_added"], 0, 10);
														break;
													case "pronouns":
														echo $user[$field] ? sanitize($user[$field]) : 'prefer not to say';
														break;
													case "birthday":
														echo substr($user[$field], 0, 4).'-'.substr($user[$field], 5, 2).'-'.substr($user[$field], 8, 2);
														break;
													case "website":
														echo '<a class="a--inherit" href="'.$user[$field].'">'.preg_replace('/'.'(.*)\/$'.'/', '$1', preg_replace('/'.'^https?:\/\/(?:www.)?(.*?)'.'/', '$1', $user[$field])).'</a>';
														break;
													case "twitter":
														echo '<a class="a--inherit" href="https://twitter.com/'.$user[$field].'">@'.$user[$field].'</a>';
														break;
													case "facebook":
														echo '<a class="a--inherit" href="https://facebook.com/'.$user[$field].'">'.$user[$field].'</a>';
														break;
													case "lastfm":
														echo '<a class="a--inherit" href="https://last.fm/user/'.$user[$field].'">'.$user[$field].'</a>';
														break;
													case "mh":
														echo '<a class="a--inherit" href="https://www.monochrome-heaven.com/profile/'.$user[$field].'/">'.preg_replace('/'.'^\d+-(.*)$'.'/', '$1', $user[$field]).'</a>';
														break;
												}
											?>
										</li>
									<?php
								}
							}
						?>
					</ul>
				<?php
				
				if($user['motto']) {
					?>
						<div class="user__motto"><?php echo $user['motto']; ?></div>
					<?php
				}
			}
		?>
	</div>
	
</div>