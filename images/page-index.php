<?php
	$search_page = (int) $_GET['page'] == $_GET['page'] && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
	$search_type = in_array(strtolower($_GET['type']), ['all', 'flyer', 'artist', 'release', 'vip']) ? strtolower($_GET['type']) : 'all';
	$search_order = in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';
	
	$limit_num = 6;
	$limit_images = (($search_page * $limit_num) - $limit_num).', '.$limit_num;
	$where_images = [
		'all' => null,
		'flyer' => 'images.description LIKE \'%flyer%\'',
		'artist' => 'images.release_id IS NULL',
		'release' => 'images.release_id IS NOT NULL',
		'vip' => 'images.is_exclusive=1',
	];
	$sql_images = 
		'SELECT images.*, users.username, artists.name AS artist_name, artists.romaji AS artist_romaji, artists.friendly AS artist_friendly FROM images '.
		'LEFT JOIN users ON users.id=images.user_id '.
		"LEFT JOIN artists ON artists.id REGEXP CONCAT('^\(', images.artist_id, '\)$') ".
		($where_images[$search_type] ? 'WHERE '.$where_images[$search_type] : null).' '.
		'ORDER BY images.date_added '.$search_order.' '.
		'LIMIT '.$limit_images;
	$stmt_images = $pdo->prepare($sql_images);
	$stmt_images->execute();
	$rslt_images = $stmt_images->fetchAll();
	
	$sql_num_images = 'SELECT COUNT(*) FROM images '.($where_images[$search_type] ? 'WHERE '.$where_images[$search_type] : null);
	$stmt_num_images = $pdo->prepare($sql_num_images);
	$stmt_num_images->execute();
	$num_images = $stmt_num_images->fetchColumn();
	$num_pages = ceil($num_images / $limit_num);
	
	echo $sql_images;
	//echo $num_images.'*'.$num_pages;
	//echo '<pre>'.print_r($rslt_images, true).'</pre>';
?>

<div class="col c1 images__container">
	<div>
		<h2>
			<?php echo lang('Images list', 'イメージ一覧', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h2>
	</div>
	
	<div class="col c2" style="margin-bottom: 1rem;">
		<div>
			<a href="/images/&type=<?php echo $search_type; ?>&order=desc" class="input__checkbox-label symbol__down-caret <?php echo strtolower($search_order) === 'desc' ? 'input__checkbox-label--selected' : null; ?>">Date uploaded</a>
			<a href="/images/&type=<?php echo $search_type; ?>&order=asc" class="input__checkbox-label symbol__up-caret <?php echo strtolower($search_order) === 'asc' ? 'input__checkbox-label--selected' : null; ?>">Date uploaded</a>
		</div>
		
		<div style="text-align: right;">
			<a href="/images/&type=all&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'all' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">All</a>
			<a href="/images/&type=flyer&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'flyer' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Flyer</a>
			<a href="/images/&type=artist&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'artist' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Artist</a>
			<a href="/images/&type=release&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'release' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Release</a>
			<a href="/images/&type=vip&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'vip' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">VIP</a>
		</div>
	</div>
	
	<div class="col c3 any--weaken-color" style="margin-bottom: 1rem;">
		<div>
			<?php
				if($search_page > 1) {
					?>
						<a class="symbol__previous" href="<?php echo '/images/&type='.$search_type.'&order='.$search_order.'&page='.($search_page - 1); ?>">Page <?php echo ($search_page - 1); ?></a>
						&nbsp;
						<a class="symbol__oldest" href="<?php echo '/images/&type='.$search_type.'&order='.$search_order.'&page=1'; ?>">1</a>
					<?php
				}
				else {
					echo 'Page 1';
				}
			?>
		</div>
		<div style="text-align: center;">
			Results <?php echo (($search_page - 1) * $limit_num + 1).' to '.(($search_page - 1) * $limit_num + $limit_num); ?>
		</div>
		<div style="text-align: right;">
			<?php
				if($search_page < $num_pages) {
					?>
						<a href="<?php echo '/images/&type='.$search_type.'&order='.$search_order.'&page='.$num_pages; ?>"><?php echo $num_pages; ?> <span class="symbol__newest"></span></a>
						&nbsp;
						<a href="<?php echo '/images/&type='.$search_type.'&order='.$search_order.'&page='.($search_page + 1); ?>">Page <?php echo ($search_page + 1); ?> <span class="symbol__next"></span></a>
					<?php
				}
				else {
					echo 'Page '.$search_page;
				}
			?>
		</div>
	</div>
	
	<div>
		<div class="text">
			<ul>
				<?php
					foreach($rslt_images as $image) {
						?>
							<li>
								<a href="https://vk.gy/images/<?php echo $image['id'].($image['friendly'] ? '-'.$image['friendly'] : null).'.'.$image['extension']; ?>">
									<img src="https://vk.gy/images/<?php echo $image['id'].'.thumbnail.'.$image['extension']; ?>" style="vertical-align: middle;" />
								</a>
								
								<div class="data__item">
									<h5>
										Uploaded
									</h5>
									<?php echo substr($image['date_added'], 0, 10).' <span class="any--weaken-color">'.substr($image['date_added'], 11).'</span>'; ?>
								</div>
								
								<?php
									if($image['artist_name'] && $image['artist_friendly']) {
										?>
											<div class="data__item">
												<h5>
													Artist
												</h5>
												<a href="<?php echo '/artists/'.$image['artist_friendly'].'/'; ?>">
													<?php echo lang(($image['artist_romaji'] ?: $image['artist_name']), $image['artist_name'], ['container' => 'span', 'secondary_class' => 'any--hidden']); ?>
												</a>
											</div>
										<?php
									}
								?>
								<div class="data__item">
									<h5>
										Releases
									</h5>
									<?php echo $image['release_id']; ?>
								</div>
								<?php print_r($image); ?>
							</li>
						<?php
					}
				?>
			</ul>
		</div>
	</div>
</div>

<style>
	.images__container .input__checkbox-label {
		display: inline-block;
	}
</style>