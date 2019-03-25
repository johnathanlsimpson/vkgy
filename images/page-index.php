<?php
	style([
		'/images/style-page-index.css',
	]);
	
	$search_page = (int) $_GET['page'] == $_GET['page'] && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
	$search_type = in_array(strtolower($_GET['type']), ['all', 'flyer', 'artist', 'release', 'vip']) ? strtolower($_GET['type']) : 'all';
	$search_order = in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';
	
	$limit_num = 25;
	$limit_images = (($search_page * $limit_num) - $limit_num).', '.$limit_num;
	$where_images = [
		'all' => null,
		'flyer' => 'images.description LIKE \'%flyer%\'',
		'artist' => 'images.release_id IS NULL',
		'release' => 'images.release_id IS NOT NULL',
		'vip' => 'images.is_exclusive=1',
	];
	$sql_images = "
		SELECT
			result.*,
			users.username,
			artists.name AS artist_name,
			artists.romaji AS artist_romaji,
			artists.friendly AS artist_friendly,
			CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(releases.press_romaji, releases.press_name, ''), COALESCE(releases.type_romaji, releases.type_name, '')) AS release_romaji,
			CONCAT_WS(' ', COALESCE(releases.name, ''), COALESCE(releases.press_name, ''), COALESCE(releases.type_name, '')) AS release_name,
			releases.friendly AS release_friendly,
			releases.id AS release_id
		FROM
		(
			SELECT images.*
			FROM images ".
			($where_images[$search_type] ? 'WHERE '.$where_images[$search_type] : null).' '.
			'ORDER BY images.date_added '.$search_order.' '.
			'LIMIT '.$limit_images."
		) result ".
		'LEFT JOIN users ON users.id=result.user_id '.
		"LEFT JOIN artists ON artists.id REGEXP CONCAT('^\(', result.artist_id, '\)$') ".
		"LEFT JOIN releases ON releases.id REGEXP CONCAT('^\(', result.release_id, '\)$') ";
	$stmt_images = $pdo->prepare($sql_images);
	$stmt_images->execute();
	$rslt_images = $stmt_images->fetchAll();
	
	$sql_num_images = 'SELECT COUNT(*) FROM images '.($where_images[$search_type] ? 'WHERE '.$where_images[$search_type] : null);
	$stmt_num_images = $pdo->prepare($sql_num_images);
	$stmt_num_images->execute();
	$num_images = $stmt_num_images->fetchColumn();
	$num_pages = ceil($num_images / $limit_num);
?>

<div class="col c1">
	<div>
		<h2>
			<?php echo lang('Images list', '画像一覧', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h2>
	</div>
	
	<div class="col c2 images__controls">
		<div>
			<a href="/images/&type=<?php echo $search_type; ?>&order=desc" class="input__checkbox-label symbol__down-caret <?php echo strtolower($search_order) === 'desc' ? 'input__checkbox-label--selected' : null; ?>">Date uploaded</a>
			<a href="/images/&type=<?php echo $search_type; ?>&order=asc" class="input__checkbox-label symbol__up-caret <?php echo strtolower($search_order) === 'asc' ? 'input__checkbox-label--selected' : null; ?>">Date uploaded</a>
		</div>
		<div>
			<a href="/images/&type=all&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'all' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">All</a>
			<a href="/images/&type=flyer&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'flyer' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Flyer</a>
			<a href="/images/&type=artist&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'artist' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Artist</a>
			<a href="/images/&type=release&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'release' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">Release</a>
			<a href="/images/&type=vip&order=<?php echo strtolower($search_order); ?>" class="input__checkbox-label <?php echo $_GET['type'] === 'vip' ? 'symbol__checked input__checkbox-label--selected' : 'symbol__unchecked'; ?>">VIP</a>
		</div>
	</div>
	
	<div class="col c3 any--weaken-color images__controls">
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
		<div>
			Results <?php echo (($search_page - 1) * $limit_num + 1).' to '.(($search_page - 1) * $limit_num + $limit_num); ?>
		</div>
		<div>
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
			<ul class="images__container">
				<?php
					foreach($rslt_images as $image) {
						?>
							<li class="image__wrapper">
								<a class="image__link" href="https://vk.gy/images/<?php echo $image['id'].($image['friendly'] ? '-'.$image['friendly'] : null).'.'.$image['extension']; ?>">
									<img class="image__thumbnail lazy" data-src="https://vk.gy/images/<?php echo $image['id'].'.thumbnail.'.$image['extension']; ?>" />
								</a>
								
								<div class="data__container image__data">
									<div class="data__item">
										<h5>
											Uploaded
										</h5>
										<?php echo substr($image['date_added'], 0, 10).' <span class="any--weaken-color">'.substr($image['date_added'], 11).'</span>'; ?>
									</div>
									
									<div class="data__item">
										<h5>
											Uploaded by
										</h5>
										<a class="user" href="<?php echo '/users/'.$image['username'].'/'; ?>"><?php echo $image['username']; ?></a>
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
										if($image['release_name'] && $image['release_friendly']) {
											?>
												<div class="data__item">
													<h5>
														Release
													</h5>
													<a href="<?php echo '/releases/'.$image['artist_friendly'].'/'.$image['release_id'].'/'.$image['release_friendly'].'/'; ?>">
														<?php echo lang(($image['release_romaji'] ?: $image['release_name']), $image['release_name'], ['container' => 'span', 'secondary_class' => 'any--hidden']); ?>
													</a>
												</div>
											<?php
										}
										if($image['description']) {
											?>
												<div class="data__item any--weaken-color">
													<h5>
														Description
													</h5>
													<?php echo $image['description']; ?>
												</div>
											<?php
										}
										if($image['is_exclusive']) {
											?>
												<div class="data__item any--weaken-color image__vip">
													<span class="symbol__vip">VIP</span>
													<a href="https://patreon.com/vkgy" target="_blank">Become VIP</a> for high-res, unwatermarked version.
												</div>
											<?php
										}
									?>
								</div>
							</li>
						<?php
					}
				?>
			</ul>
		</div>
	</div>
</div>