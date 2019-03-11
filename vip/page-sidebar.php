<?php
	if($is_vip) {
		?>
			<h3>
				Welcome
			</h3>
			<div class="text text--outlined text--notice">
				Welcome to the VIP section, <a class="user" href="/users/<?php echo $_SESSION["username"]; ?>/"><?php echo $_SESSION["username"]; ?></a>! Thank you so much for supporting <a href="https://vk.gy/">vkgy</a>!<br /><br />
				
				Questions? Check the <a href="/support/">support vkgy</a> page, or comment on the <a href="/vip/development/">development post</a>.<br /><br />
				
				<a class="a--padded a--outlined" href="/support/">Support vkgy</a>
			</div>
		<?php
	}
?>

<h3 style="display: inline-block;">
	Latest VIP images
<a class="a--outlined a--padded" href="/vip/images/">More</a>
</h3>


<div class="text text--outlined <?php echo $is_vip ? "any__partial" : null; ?>">
	<?php
		if($is_vip) {
			?>
				<ul class="ul--inline any--flex images__container">
					<?php
						foreach($rslt_images_preview as $image) {
							?>
								<li class="images__item">
									<a class="images__link" href="<?php echo $image["url"]; ?>" style="background-image: url(<?php echo strtolower(str_replace(".", ".thumbnail.", $image["url"])); ?>);" target="_blank"></a>
								</li>
							<?php
						}
					?>
				</ul>
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
</div>

<h3>
	Latest VIP video
</h3>
<div class="text text--outlined">
	<?php
		if($is_vip) {
			$video = "zuMMRB8pn94";
			
			?>
				<iframe style="width: 100%; vertical-align: middle;" src="https://www.youtube.com/embed/<?php echo $video; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
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
</div>

<?php
	if($rslt_members) {
		?>
			<h3>
				VIP members
			</h3>
			<div class="text text--outlined">
				<ul>
					<?php
						foreach($rslt_members as $member) {
							?>
								<li>
									<a class="user" href="/users/<?php echo $member["username"]; ?>/"><?php echo $member["username"]; ?></a>
								</li>
							<?php
						}
					?>
				</ul>
			</div>
		<?php
	}
?>