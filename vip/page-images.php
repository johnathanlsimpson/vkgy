<?php
?>

<div class="col c1">
	<div>
		<h1>
			VIP section
		</h1>
	</div>
</div>

<div class="col c3-ABB">
	<div class="vip__sidebar">
		<?php include("../vip/page-sidebar.php"); ?>
	</div>
	<div>
		<h2>
			VIP images
		</h2>
		
		<div class="text">
			<?php
				if($is_vip) {
					?>
						<table>
							<tr>
								<td class="images__item">Image</td>
								<td class="images__item">Artist(s)</td>
								<td class="images__item">Description</td>
								<td class="images__item">Uploaded</td>
								<td class="images__item">User</td>
							</tr>
							
							<?php
								for($i=0; $i<count($rslt_images); $i++) {
									?>
										<tr>
											<td class="images__item">
												<a class="images__link lazy" href="<?php echo $rslt_images[$i]["url"]; ?>" style="background-image: url(<?php echo strtolower(str_replace(".", ".thumbnail.", $rslt_images[$i]["url"])); ?>);" target="_blank"></a>
											</td>
											<td class="images__item">
												<?php
													foreach($rslt_images[$i]["artists"] as $artist) {
														?>
															<a class="artist" href="/artists/<?php echo $artist["friendly"]; ?>/"><?php echo $artist["quick_name"]; ?></a>
														<?php
													}
												?>
											</td>
											<td class="images__item">
												<?php echo $rslt_images[$i]["description"] ?: "(no description)"; ?>
											</td>
											<td class="images__item any--weaken">
												<?php echo substr($rslt_images[$i]["date_added"], 0, 10); ?>
											</td>
											<td class="images__item any--weaken">
												<a class="user a--inherit" href="/users/<?php echo $rslt_images[$i]["username"]; ?>/"><?php echo $rslt_images[$i]["username"]; ?></a>
											</td>
										</tr>
									<?php
								}
							?>
						</table>
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
	</div>
</div>

<style>
	.images__item {
		white-space: nowrap;
		width: auto;
	}
	.images__item:nth-of-type(3) {
		white-space: auto;
		width: 100%;
	}
	.images__link {
		background-position: center;
		background-size: cover;
		display: block;
		padding: 2rem;
		text-align: center;
	}
	.images__link:hover {
		opacity: 0.75;
	}
	@media(min-width: 800px) {
		.vip__sidebar {
			order: 2;
		}
	}
</style>