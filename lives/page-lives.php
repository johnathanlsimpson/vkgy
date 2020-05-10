<?php
	// Setup page
	include_once('../php/function-render_json_list.php');
	$access_live = new access_live($pdo);
	
	script([
		'/scripts/external/script-selectize.js',
		'/scripts/script-initSelectize.js',
		'/lives/script-page-lives.js',
	]);
	
	style([
		'/styles/style-selectize.css',
		'/lives/style-page-lives.css',
	]);
	
	// Get year options
	$sql_live_years = 'SELECT SUBSTRING(date_occurred, 1, 4) AS year FROM lives GROUP BY year ORDER BY year DESC';
	$stmt_live_years = $pdo->prepare($sql_live_years);
	$stmt_live_years->execute();
	$rslt_live_years = $stmt_live_years->fetchAll();
	
	render_json_list('year', $rslt_live_years);
	
	// Get area options
	$sql_areas = 'SELECT id, name, romaji, friendly FROM areas ORDER BY friendly ASC';
	$stmt_areas = $pdo->prepare($sql_areas);
	$stmt_areas->execute();
	$rslt_areas = $stmt_areas->fetchAll();
	
	render_json_list('area', $rslt_areas);
	
	// Get livehouse options
	$sql_livehouses = 'SELECT id, name, romaji, friendly FROM lives_livehouses ORDER BY friendly ASC';
	$stmt_livehouses = $pdo->prepare($sql_livehouses);
	$stmt_livehouses->execute();
	$rslt_livehouses = $stmt_livehouses->fetchAll();
	
	render_json_list('livehouse', $rslt_livehouses);
	
	// Setup search options
	$q = [];
	$result_limit = 100;
	if(is_numeric($_GET['id'])) {
		$q['id'] = sanitize($_GET['id']);
	}
	if(preg_match('/'.'[\d-]{4,10}'.'/', $_GET['date_occurred'])) {
		$q['date_occurred'] = sanitize($_GET['date_occurred']);
	}
	if($_GET['order'] === 'asc') {
		$q['order'] = 'lives.date_occurred ASC';
	}
	if(is_numeric($_GET['livehouse_id'])) {
		$q['livehouse_id'] = sanitize($_GET['livehouse_id']);
	}
	if(is_numeric($_GET['artist_id'])) {
		$q['artist_id'] = sanitize($_GET['artist_id']);
	}
	if(is_numeric($_GET['area_id'])) {
		$q['area_id'] = sanitize($_GET['area_id']);
	}
	if(is_numeric($_GET['page'])) {
		$q['limit'] = (($_GET['page'] * $result_limit) - $result_limit).', '.$result_limit;
	}
	
	// Get data
	$lives = $access_live->access_live(array_merge($q, [ 'get' => 'basics', 'keys' => 'date' ]));
	$num_lives = $access_live->access_live(array_merge($q, [ 'get' => 'count', 'limit' => null ])) ?: 0;
	
	// Set pages
	$current_page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
	$total_pages = ceil($num_lives / $result_limit);
	unset($q['limit'], $q['order']);
	
	// Set title and nav
	if(is_numeric($q['id']) && count($lives) === 1) {
		$page_header = lang('Live info', 'ライブ情報', ['container' => 'div']);
	}
	else {
		$page_header = lang('Lives list', 'ライブ一覧', ['container' => 'div']);
		
		$directional_nav[] = [
			'position' => 'left',
			'text' => 'Page '.($current_page > 1 ? $current_page - 1 : 1),
			'url' => $current_page > 1 ? '&'.http_build_query($q).'&page='.($current_page - 1) : null
		];
		$directional_nav[] = [
			'position' => 'center',
			'text' => 'Results '.(($current_page * $result_limit) - $result_limit + 1).'~'.($current_page < $total_pages ? $current_page * $result_limit : $num_lives - (($current_page * $result_limit) - $result_limit)),
		];
		$directional_nav[] = [
			'position' => 'right',
			'text' => 'Page '.($current_page < $total_pages ? $current_page + 1 : $current_page),
			'url' => $current_page < $total_pages ? '&'.http_build_query($q).'&page='.($current_page + 1) : null
		];
	}
?>

<div class="col c1 any--margin">
	<div>
		<?php
			if(is_array($q) && !empty($q)) {
				?>
					<div class="text text--outlined <?php echo is_array($lives) && !empty($lives) ? 'text--notice symbol__help' : 'symbol__error text--error'; ?>">
						<?php
							echo is_array($lives) && !empty($lives) ? 'Showing: ' : 'No results for: ';
							
							foreach($q as $key => $value) {
								echo '<code>';
								
								if($key === 'artist_id') {
									$artist = $access_artist->access_artist([ 'id' => $value, 'get' => 'name' ]);
									echo 'artist: ';
									echo '<a class="artist" href="/artists/'.$artist['friendly'].'/">'.lang(($artist['romaji'] ?: $artist['name']), $artist['name'], ['secondary_class' => 'any--hidden']).'</a>';
								}
								
								elseif($key === 'area_id') {
									$sql_area = 'SELECT name, romaji FROM areas WHERE id=? LIMIT 1';
									$stmt_area = $pdo->prepare($sql_area);
									$stmt_area->execute([ $value ]);
									$area = $stmt_area->fetch();
									echo 'area: ';
									echo lang(($area['romaji'] ?: $area['name']), $area['name'], ['secondary_class' => 'any--hidden']);
								}
								
								elseif($key === 'livehouse_id') {
									$sql_livehouse = 'SELECT name, romaji FROM lives_livehouses WHERE id=? LIMIT 1';
									$stmt_livehouse = $pdo->prepare($sql_livehouse);
									$stmt_livehouse->execute([ $value ]);
									$livehouse = $stmt_livehouse->fetch();
									echo 'livehouse: ';
									echo lang(($livehouse['romaji'] ?: $livehouse['name']), $livehouse['name'], ['secondary_class' => 'any--hidden']);
								}
								
								else {
									echo $key.': '.$value;
								}
								
								echo '</code>';
								echo $key != end(array_keys($q)) ? ', ' : null;
							}
							echo '&nbsp;&nbsp;&nbsp;<a class="symbol__arrow-right-circled" href="/lives/">'.lang('Show all lives', '全てのライブ', 'hidden').'</a>';
						?>
					</div>
				<?php
			}
			
			if(!is_numeric($q['id'])) {
				?>
					<div class="senary-nav__container any--margin">
						<div class="senary-nav__left">
							<a class="input__radio input__radio--selected <?php echo $_GET['order'] === 'asc' ? 'symbol__up-caret' : 'symbol__down-caret'; ?>" href="<?php echo '&order='.($_GET['order'] === 'asc' ? 'desc' : 'asc').'&'.http_build_query($q); ?>">Date</a>
						</div>
						
						<div class="senary-nav__center">
						</div>
						
						<div class="senary-nav__right">
							<select class="input" data-source="areas" name="area_id" placeholder="area" style="width: 20ch;">
								<option>any</option>
								<?php echo is_numeric($_GET['area_id']) ? '<option value="'.$_GET['area_id'].'" selected></option>' : null; ?>
							</select>
							<select class="input" data-source="livehouses" name="livehouse_id" placeholder="livehouse" style="width: 20ch;">
								<option>any</option>
								<?php echo is_numeric($_GET['livehouse_id']) ? '<option value="'.$_GET['livehouse_id'].'" selected></option>' : null; ?>
							</select>
							<select class="input" data-source="years" name="date_occurred" placeholder="year" style="width: 8ch;">
								<option>any</option>
								<?php echo is_numeric($_GET['date_occurred']) ? '<option value="'.$_GET['date_occurred'].'" selected>'.$_GET['date_occurred'].'</option>' : null; ?>
							</select>
						</div>
					</div>
				<?php
			}
			
			if(is_array($lives) && !empty($lives)) { foreach($lives as $year => $live_year) {
				foreach($live_year as $month => $live_month) {
					?>
						<h2 class="lives__month">
							<?php echo lang(($year.', '.date('F', strtotime($year.'-'.$month))), $year.'年'.$month.'月', ['container' => 'div']); ?>
						</h2>
						<div class="lives__container any--flex">
							<?php
								foreach($live_month as $day => $live_day) {
									?>
										<div class="lives__day any--weaken-color">
											<h4 class="lives__date symbol__next symbol--right">
												<a class="a--inherit" href="/lives/&amp;date_occurred=<?= $year.'-'.$month; ?>"><?= $month; ?></a>-<a class="a--inherit" href="/lives/&amp;date_occurred=<?= $year.'-'.$month.'-'.$day; ?>"><?= $day; ?></a>
											</h4>
											
											<?php
												foreach($live_day as $live) {
													?>
														<div class="lives__live text">
															
															<a class="a--inherit any--weaken-size" href="<?php echo '/lives/&area_id='.$live['area_id']; ?>"><?php echo lang(($live['area_romaji'] ?: $live['area_name']), $live['area_name'], ['secondary_class' => 'any--hidden']); ?></a>
															<a class="a--inherit" href="<?php echo '/lives/&livehouse_id='.$live['livehouse_id']; ?>"><?php echo lang(($live['livehouse_romaji'] ?: $live['livehouse_name']), $live['livehouse_name'], ['secondary_class' => 'any--hidden']); ?></a>
															
															<?php
																if(strlen($live['name'])) {
																	echo '<br />';
																	echo '<br />';
																	echo $live['romaji'] ? lang($live['romaji'], $live['name'], 'div') : $live['name'];
																	echo '<br />';
																}
															?>
															
															<ul class="lives__artists ul--inline">
																<?php
																	if(is_array($live['artists']) && !empty($live['artists'])) {
																		foreach($live['artists'] as $artist) {
																			?>
																				<li class="lives__artist">
																					<?php
																						if($artist['friendly']) {
																							?>
																								<a class="artist artist--no-symbol" data-friendly="<?php echo $artist['friendly']; ?>" data-name="<?php echo $artist['name']; ?>" data-quickname="<?php echo $artist['romaji'] ?: $artist['name']; ?>" href="<?php echo '/lives/&artist_id='.$artist['id']; ?>"><?php echo lang(($artist['romaji'] ?: $artist['name']), $artist['name'], ['secondary_class' => 'any--hidden']); ?></a>
																							<?php
																						}
																						else {
																							echo lang(($artist['romaji'] ?: $artist['name']), $artist['name'], ['secondary_class' => 'any--hidden']);
																						}
																					?>
																				</li>
																			<?php
																		}
																	}
																?>
															<?php
																if($_SESSION['can_add_data']) {
																	?>
																		<a class="symbol__edit a--inherit any--weaken-size" href="/lives/<?= $live['id']; ?>/edit/" style="margin-left:1ch;">Edit</a>
																	<?php
																}
															?>
															</ul>
														</div>
													<?php
												}
											?>
										</div>
									<?php
								}
							?>
						</div>
					<?php
				}
			} }
		?>
	</div>
</div>