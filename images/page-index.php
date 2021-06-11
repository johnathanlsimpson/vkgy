<?php
	style([
		'/images/style-page-index.css',
	]);
	
	$search_page = (int) $_GET['page'] == $_GET['page'] && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
	$search_type = in_array(strtolower($_GET['type']), ['all', 'flyer', 'artist', 'release', 'vip']) ? strtolower($_GET['type']) : 'all';
	$search_order = in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';
	
	$limit_num = 25;
	$limit_images = (($search_page * $limit_num) - $limit_num).', '.$limit_num;
	
	$access_image = $access_image ?: new access_image($pdo);
	$rslt_images = $access_image->access_image([ 'type' => $search_type, 'get' => 'all', 'order' => 'images.id '.$search_order, 'limit' => $limit_images, 'show_queued' => false ]);
	
	if($search_type === 'all') {
		$sql_num_images = 'SELECT COUNT(*) FROM images';
	}
	if($search_type === 'artist') {
		$sql_num_images = 'SELECT COUNT(*) FROM images_artists';
	}
	if($search_type === 'flyer') {
		$sql_num_images = 'SELECT COUNT(*) FROM images WHERE description LIKE "%flyer%"';
	}
	if($search_type === 'release') {
		$sql_num_images = 'SELECT COUNT(*) FROM images_releases';
	}
	if($search_type === 'vip') {
		$sql_num_images = 'SELECT COUNT(*) FROM images WHERE is_exclusive=1';
	}
	$stmt_num_images = $pdo->prepare($sql_num_images);
	$stmt_num_images->execute();
	$num_images = $stmt_num_images->fetchColumn();
	$num_pages = ceil($num_images / $limit_num);
	
	subnav([
		[
			'text' => $search_page > 1 ? 'Page '.($search_page - 1) : 'Page 1',
			'url' => $search_page > 1 ? '/images/&type='.$search_type.'&order='.$search_order.'&page='.($search_page - 1) : null,
			'position' => 'left',
		],
		[
			'text' => 'Results '.(($search_page - 1) * $limit_num + 1).' to '.(($search_page - 1) * $limit_num + $limit_num),
			'position' => 'center',
		],
		[
			'text' => $search_page < $num_pages ? 'Page '.($search_page + 1) : 'Page '.$search_page,
			'url' => $search_page < $num_pages ? '/images/&type='.$search_type.'&order='.$search_order.'&page='.($search_page + 1) : null,
			'position' => 'right',
		],
	], 'directional');
?>

<div class="col c1">
	
	<div class="col c2 images__controls">
		<div>
			<a href="/images/&type=<?php echo $search_type; ?>&order=desc" class="input__radio symbol__triangle symbol--down <?php echo strtolower($search_order) === 'desc' ? 'input__radio--selected' : null; ?>">Date uploaded</a>
			<a href="/images/&type=<?php echo $search_type; ?>&order=asc" class="input__radio symbol__triangle symbol--up <?php echo strtolower($search_order) === 'asc' ? 'input__radio--selected' : null; ?>">Date uploaded</a>
		</div>
		<div>
			<a href="/images/&type=all&order=<?php echo strtolower($search_order); ?>" class="input__radio <?php echo $_GET['type'] === 'all' ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>">All</a>
			<a href="/images/&type=flyer&order=<?php echo strtolower($search_order); ?>" class="input__radio <?php echo $_GET['type'] === 'flyer' ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>">Flyer</a>
			<a href="/images/&type=artist&order=<?php echo strtolower($search_order); ?>" class="input__radio <?php echo $_GET['type'] === 'artist' ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>">Artist</a>
			<a href="/images/&type=release&order=<?php echo strtolower($search_order); ?>" class="input__radio <?php echo $_GET['type'] === 'release' ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>">Release</a>
			<a href="/images/&type=vip&order=<?php echo strtolower($search_order); ?>" class="input__radio <?php echo $_GET['type'] === 'vip' ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>">VIP</a>
		</div>
	</div>
	
	<div>
		<div class="text">
			<ul class="images__container">
				<?php
					foreach($rslt_images as $image) {
						?>
							<li class="image__wrapper">
								<a class="image__link" href="/images/<?php echo $image['id'].($image['friendly'] ? '-'.$image['friendly'] : null).'.'.$image['extension']; ?>">
									<img class="image__thumbnail lazy" data-src="/images/<?php echo $image['id'].'.thumbnail.'.$image['extension']; ?>" />
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
										<a class="user" data-icon="<?= $image['user']['icon']; ?>" data-is-vip="<?= $image['user']['is_vip']; ?>" href="<?= $image['user']['url']; ?>"><?= $image['user']['username']; ?></a>
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
													<span class="symbol__lock">VIP</span>
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