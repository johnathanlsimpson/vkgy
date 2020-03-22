<?php
	breadcrumbs([
		"Support vkgy" => "/support/"
	]);
	
	subnav([
		"Support vkgy" => "/support/",
		"View Patreon" => "https://patreon.com/vkgy",
		"Buy us a coffee" => "https://ko-fi.com/vkgyofficial",
		"VIP forum" => "/vip/",
	]);
	
	$pageTitle = "Support vkgy at Patreon to become VIP";
	
	background("../support/patreon-back.png");
?>

<div class="col c1">
	<div>
		<h1>
			Support vkgy
		</h1>
	</div>
</div>

<div class="col c3-AAB">
	<div>
		<h2>
			<div class="h5">
				Become a VIP member
			</div>
			Donate via Patreon and become VIP
		</h2>
		<div class="text">
			Want to support vkgy? Please consider making a small monthly donation via <a href="https://patreon.com/vkgy" target="_blank">Patreon</a>! Donate <span class="any__note">$5+ / month</span> or more to upgrade your account to VIP status. Check the side bar for reward details.<br /><br />
			
			The first 7 VIP members will receive a free vkgy logo sticker!<br /><br />
			
			<a class="a--outlined a--padded" href="https://patreon.com/vkgy" target="_blank">Support vkgy at Patreon</a>
			<a class="a--padded any--weaken-color" href="https://ko-fi.com/vkgyofficial" target="_blank">Or buy us a coffee</a>
		</div>

		<h3>
			Other ways to support
		</h3>
		<div class="text text--outlined">
			<ul class="ul--bulleted">
				<li>Use our <a href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.media_format=&f=all&q=">CDJapan affiliate link</a>.</li>
				<li><a href="https://ko-fi.com/vkgyofficial" target="_blank">Buy us a coffee</a> (payments through PayPal).</li>
				<li>Contribute to the news section or database (<a href="mailto:johnathan.l.simpson@gmail.com">contact me</a> for permission.)</li>
				<li>Like/subscribe on <a href="https://twitter.com/vkgy_" target="_blank">Twitter</a>, <a href="https://facebook.com/vkgyofficial" target="_blank">Facebook</a>, and <a href="https://youtube.com/c/vkgyofficial" target="_blank">YouTube</a>.</li>
				<li>Comment on a post somewhere on the site!</li>
			</ul>
		</div>

		<h3 style="display: inline-block;">
			VIP terms and details
		</h3>

		<input class="any--hidden any__partial-input" id="show-all-images" type="radio" />
		<label class="any__partial-label input__button" for="show-all-images">
			Show all
		</label>

		<div class="text text--outlined any__partial">
			<ol>
				<h4 style="margin-bottom: 1rem;">
					Activating VIP status
				</h4>
				<li>VIP status will be granted after the first payment of $5 or more has processed, and will last until the next payment period (about one month).</li>
				<li>VIP status will be granted to the <a href="https://vk.gy/">vkygy</a> account that matches the username or email address used on <a href="https://patreon.com/vkgy">Patreon</a>. If your <a href="https://patreon.com/vkgy">Patreon</a> username/address differ from <a href="https://vk.gy/">vkgy</a>, please <a href="">contact me</a> with the correct vkgy username.</li>
				<li>Members who have just gained VIP status will need to sign out and sign back in for it to take effect. VIP status is granted manually, so please <a href="">contact me</a> if you have any issues.</li>
			</ol>

			<ol>
				<h4 style="margin-bottom: 1rem;">
					About VIP images/scans
				</h4>
				<li>Only VIP members can view VIP-exclusive images at full resolution without watermarks. All other users are limited to smaller, watermarked versions of those images.</li>
				<li>Any member with admin rights may upload images and mark them as VIP-exclusive, and may view their <em>own</em> uploads at full resolution and without watermarks, regardless of their VIP status.</li>
				<li>Image watermarks include &ldquo;vkgy&rdquo; and the username of the uploader.</li>
			</ol>

			<ol>
				<h4 style="margin-bottom: 1rem;">
					VIP mini-forum
				</h4>
				<li>For the first month after becoming a VIP member, only that month's posts will be visible to the member. All previous posts will unlock after the second month's payment has processed.</li>
				<li>Any user of the site is free to make suggestions for features/changes, but those made via the VIP mini-forum will be prioritized. However, there is no guarantee that suggested features/changes will come to fruition.</li>
			</ol>
		</div>

		<h3>
			Questions?
		</h3>
		<div class="text text--outlined">
			Have questions or issues? <a href="mailto:johnathan.l.simpson@gmail.com">Contact me</a> for help.
		</div>

		<!--<h3>
			Current and past supporters
		</h3>
		<div class="text">
			<ul>
				<li><a class="user" href="/users/inartistic/">inartistic</a></li>
			</ul>
		</div>-->
	</div>
	<div>
		<?php
			$sql_check = "SELECT is_vip, vip_since FROM users WHERE id=? LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $_SESSION["user_id"] ]);
			$rslt_check = $stmt_check->fetch();

			if(is_array($rslt_check) && !empty($rslt_check)) {
				if($rslt_check["is_vip"] == 1) {
					$is_vip = true;
				}
			}

			if($is_vip) {
				?>
					<!--<div class="text text--outlined text--notice">
						<a class="user" href="/users/<?php echo $_SESSION["username"]; ?>/"><?php echo $_SESSION["username"]; ?></a>, thank you so much for supporting the site! You can access the VIP section below.<br /><br />

						<a class="a--padded a--outlined" href="/vip/">VIP section</a>
					</div>-->
				<?php
			}
		?>

		<h2>
			<div class="h5">
				Patreon rewards
			</div>
			VIP membership <span class="any__note">$5+ / month</span>
		</h2>
		<div class="text">
			<?php include("page-rewards.php"); ?>
		</div>

		<h2>
			<div class="h5">
				Patreon rewards
			</div>
			Advertise <span class="any__note">$15+ / month</span>
		</h2>
		<div class="text text--outlined">
			<ul class="ul--bulleted">
				<li>Banner and link on main page</li>
				<li>All rewards from the <span class="any__note">$5+ / month</span> tier</li>
			</ul>
			<ul>
				<li>After choosing this tier, I will contact you on Patreon with details. <a href="mailto:johnathan.l.simpson@gmail.com">Contact me</a> for questions.</li>
			</ul>
			<br /><br />

			<a class="a--outlined a--padded any--weaken-color" href="https://patreon.com/vkgy" target="_blank">Support vkgy at Patreon</a>
			<a class="a--padded any--weaken-color" href="https://ko-fi.com/vkgyofficial" target="_blank">Or buy us a coffee</a>
		</div>

		<h2>
			<div class="h5">
				Patreon rewards
			</div>
			Thank you <span class="any__note">$1+ / month</span>
		</h2>
		<div class="text text--outlined">
			<ul class="ul--bulleted">
				<li>Featured in thanks section</li>
				<li>My gratitude!</li>
			</ul>
			<br /><br />

			<a class="a--outlined a--padded any--weaken-color" href="https://patreon.com/vkgy" target="_blank">Support vkgy at Patreon</a>
			<a class="a--padded any--weaken-color" href="https://ko-fi.com/vkgyofficial" target="_blank">Or buy us a coffee</a>
		</div>
	</div>
</div>