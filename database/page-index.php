<?php
	$pageTitle = "Database";
	$access_artist = new access_artist($pdo);
?>

<div class="col c1">
	<div>
		<h1>
			<span class="symbol__database"></span> Database
		</h1>
	</div>
</div>

<?php
	if(is_array($database) && !empty($database)) {
		$database_sections = ["0" => "artists", "3" => "releases", "1" => "labels", "2" => "musicians"];
		?>
			<div class="col c2">
				<?php
					foreach($database_sections as $database_key => $database_section) {
						?>
							<div>
								<h2>
									Recently updated <a class="a--inherit symbol__<?php echo $database_section === "labels" ? "company" : substr($database_section, 0, -1); ?>" href="/<?php echo $database_section; ?>/"><?php echo $database_section; ?></a>
								</h2>
								<div class="text">
									<ul>
										<?php
											for($i = ($database_key * 20); $i < (($database_key * 20) + 20); $i++) {
												?>
													<li>
														<?php
															if($database_section === "releases") {
																?>
																	<div class="any--weaken-color">
																		<a class="artist" href="<?php echo $database[$i]["artist_url"]; ?>"><?php echo $database[$i]["artist_quick_name"]; ?></a>
																	</div>
																<?php
															}
														?>
														
														<a class="symbol__<?php echo $database_section === "labels" ? "company" : substr($database_section, 0, -1); ?>" href="<?php echo $database[$i]["url"]; ?>"><?php echo $database[$i]["quick_name"]; ?></a>
														
														<div class="h5 any--no-wrap">
															<?php echo $database[$i]["date_edited"]; ?> by 
															<a class="a--inherit symbol__user" href="/users/<?php echo $database[$i]["username"]; ?>/"><?php echo $database[$i]["username"]; ?></a>
														</div>
													</li>
												<?php
											}
										?>
									</ul>
								</div>
							</div>
						<?php
					}
				?>
			</div>
			
			<div class="col c1">
				<?php include("../search/page-releases.php"); ?>
			</div>
		<?php
	}
?>