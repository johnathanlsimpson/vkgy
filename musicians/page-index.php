<?php
	script("/musicians/script-page-index.js");
	style("/musicians/style-page-index.css");
	
	echo str_replace(["\t", "\r", "\n"], "", '
		<div class="any--hidden">
			<li class="musician-list__item">
				<a class="musician musician-list__link musician musician--no-symbol" data-name="" href="">
					<span class="musician-list__name"></span> <span class="any__note musician-list__hint"></span><br />
					<span class="musician-list__jp any--jp any--weaken"></span>
				</a>
			</li>
		</div>
	');
	
	$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ-";
	$letter = strtoupper($_GET["letter"]) ?: "A";
	$letter = $letter === "ALL" || strstr($str, $letter) !==false ? $letter : "A";
	
	$page_header = lang('Musician list', 'ミュージシャン一覧', ['container' => 'div']);
?>

<div class="col c1">
	<div>
		<?php
			if($letter === "ALL") {
				for($i=0; $i < strlen($str); $i++) {
					?>
						<div class="any--margin" id="<?php echo $str[$i]; ?>">
							<?php
								for($n=0; $n < strlen($str); $n++) {
									?>
										<a class="a--padded <?php //echo $i === $n ? "a--outlined" : null; ?>" href="<?php echo "&letter=".$str[$n]; ?>"><?php echo $str[$n] === "-" ? "#" : $str[$n]; ?></a>
									<?php
								}
							?>
							<a class="a--padded a--outlined" href="&letter=all">All</a>
						</div>
						
						<h2>
							<?php echo $str[$i] === "-" ? "#" : $str[$i]; ?>
						</h2>
						<div class="text" >
							<ul class="musician-list__container" data-lazyload-musicians data-letter="<?php echo $str[$i]; ?>">
								<span class="loading"></span>
							</ul>
						</div>
					<?php
				}
			}
			else {
				?>
					<div class="any--margin" id="<?php echo $letter; ?>">
						<?php
							for($n=0; $n < strlen($str); $n++) {
								?>
									<a class="a--padded <?php echo $letter === $str[$n] ? "a--outlined" : null; ?>" href="<?php echo "&letter=".$str[$n]; ?>"><?php echo $str[$n] === "-" ? "#" : $str[$n]; ?></a>
								<?php
							}
						?>
						<a class="a--padded" href="&letter=all">All</a>
					</div>
					
					<h2>
						<?php echo $letter === "-" ? "#" : $letter; ?>
					</h2>
					<div class="text" >
						<ul class="musician-list__container" data-lazyload-musicians data-letter="<?php echo $letter; ?>">
							<span class="loading"></span>
						</ul>
					</div>
				<?php
			}
		?>
	</div>
</div>