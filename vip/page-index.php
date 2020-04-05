<div class="col c1">
	<div>
		<h1>
			VIP section
		</h1>
	</div>
</div>

<?php
	if(!$is_vip) {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--notice">
						Sorry, <?php echo $_SESSION["is_signed_in"] ? '<a class="user" href="/users/'.$_SESSION["username"].'/">'.$_SESSION["username"].'</a>,' : null; ?> the VIP section is limited to members who <a href="https://patreon/vkgy/" target="_blank">support vkgy at Patreon</a> for <span class="any__note">$5+ / month</span>.<br /><br />
						
						The first 7 VIP members will receive a free vkgy logo sticker!<br /><br />
						
						<a class="a--padded a--outlined" href="https://patreon.com/vkgy" target="_blank">Support vkgy at Patreon</a>
						<a class="a--padded any--weaken-color" href="/support/">Other ways to support</a>
					</div>
				</div>
			</div>
		<?php
	}
?>

<div class="col c3-ABB">
	<div class="vip__sidebar">
		<?php include("../vip/page-sidebar.php"); ?>
	</div>
	<div>
		<h2>
			<div class="h5">
				Last updated <?php echo substr($dev_line, 0, 4)."-".substr($dev_line, 4, 2)."-".substr($dev_line, 6, 2); ?>
			</div>
			<a class="a--inherit" href="/vip/development/">Development and suggestions</a>
		</h2>
		<div class="text">
			<ul>
				<li>
					<h5>
						2018-03-01
						by
						<a class="user a--inherit" href="/users/inartistic/">inartistic</a>
						<span class="any__note">stickied</span>
					</h5>
					
					Have suggestions? Need help? Want to read about the latest site changes? Check this post.<br /><br />
					
					<?php
						if($is_vip) {
							?>
								<span class="any--weaken-color">Last updated: <?php echo substr($dev_line, 0, 4)."-".substr($dev_line, 4, 2)."-".substr($dev_line, 6, 2); ?>.</span><br /><br />
								
								<a class="a--padded a--outlined <?php echo !$is_vip ? "any--weaken-color" : null; ?>" href="/vip/development/#comments">
									<?php echo ($entry["comment_count"] ? "read ".$entry["comment_count"]." comment".($entry["comment_count"] === 1 ? null : "s") : "comment on this"); ?>
								</a>
							<?php
						}
						else {
							?>
								<div class="symbol__error">
									Sorry, this content is only available to VIP members.
								</div>
							<?php
						}
					?>
				</li>
			</ul>
		</div>
		
		<h2>
			VIP forum
		</h2>
		<div class="text">
			<ul>
				<?php
					if($is_vip) {
						foreach($rslt_entries as $key => $entry) {
							if($key < 10) {
								?>
									<li>
										<h5>
											<?php echo substr($entry["date_occurred"], 0, 10); ?>
											by
											<a class="user a--inherit" href="/users/<?php echo $entry["username"]; ?>/"><?php echo $entry["username"]; ?></a>
										</h5>
										<h3>
											<a class="" href="/vip/<?php echo $entry["friendly"]; ?>/"><?php echo $entry["title"]; ?></a>
										</h3>
										
										<a class="a--padded a--outlined" href="/vip/<?php echo $entry["friendly"]; ?>/#comments">
											<?php echo $entry["comment_count"] ? "read ".$entry["comment_count"]." comment".($entry["comment_count"] === 1 ? null : "s") : "comment on this"; ?>
										</a>
									</li>
								<?php
							}
						}
					}
					else {
						?>
							<div class="symbol__error">
								Sorry, this content is only available to VIP members.
							</div>
						<?php
					}
				?>
			</ul>
		</div>
	</div>
</div>