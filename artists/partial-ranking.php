<?php
	$sql_owned = "SELECT COUNT(releases_collections.id) AS num_owners FROM releases RIGHT JOIN releases_collections ON releases_collections.release_id=releases.id WHERE releases.artist_id=?";
	$stmt_owned = $pdo->prepare($sql_owned);
	$stmt_owned->execute([ $artist["id"] ]);
	$rslt_owned = $stmt_owned->fetchColumn();

	$sql_wanted = "SELECT COUNT(releases_wants.id) AS num_owners FROM releases RIGHT JOIN releases_wants ON releases_wants.release_id=releases.id WHERE releases.artist_id=?";
	$stmt_wanted = $pdo->prepare($sql_wanted);
	$stmt_wanted->execute([ $artist["id"] ]);
	$rslt_wanted = $stmt_wanted->fetchColumn();

	$sql_num_releases = "SELECT COUNT(releases.id) AS num_releases FROM releases WHERE venue_limitation=? AND artist_id=?";
	$stmt_num_releases = $pdo->prepare($sql_num_releases);
	$stmt_num_releases->execute([ "available everywhere", $artist["id"] ]);
	$rslt_num_releases = $stmt_num_releases->fetchColumn();

	$sql_lives = '
		SELECT lives.date_occurred, lives.livehouse_id, lives_livehouses.capacity
		FROM lives_artists
		LEFT JOIN lives ON lives.id=lives_artists.live_id
		LEFT JOIN lives_livehouses ON lives_livehouses.id=lives.livehouse_id
		WHERE lives_artists.artist_id=? AND lives_livehouses.capacity IS NOT NULL
		ORDER BY lives.date_occurred ASC';
	$stmt_lives = $pdo->prepare($sql_lives);
	$stmt_lives->execute([ $artist["id"] ]);
	$rslt_lives = $stmt_lives->fetchAll();

	if(is_array($artist["history"]) && !empty($artist["history"])) {
		foreach($artist['history'] as $history_year) {
			foreach($history_year as $history_month) {
				foreach($history_month as $history_day) {
					foreach($history_day as $event) {
						if(
							!empty($event["date_occurred"])
							&&
							in_array('live', $event["type"])
							&&
							stripos($event["content"], "oneman") !== false
						) {
							$oneman_lives[$event["date_occurred"]] = "";
						}
					}
				}
			}
		}
	}

	if(is_array($rslt_lives) && !empty($rslt_lives)) {
		$num_lives = count($rslt_lives);

		for($i=0; $i<$num_lives; $i++) {
			$year = substr($rslt_lives[$i]["date_occurred"], 0, 4);
			$avg_capacity[$year] = [
				"capacity" => $avg_capacity[$year]["capacity"] + $rslt_lives[$i]["capacity"],
				"count" => $avg_capacity[$year]["count"] + 1
			];
			$total_avg_capacity = $total_avg_capacity + $rslt_lives[$i]["capacity"];

			if(isset($oneman_lives[$rslt_lives[$i]["date_occurred"]])) {
				if($rslt_lives[$i]["capacity"] > $highest_oneman_capacity) {
					$highest_oneman_capacity =  $rslt_lives[$i]["capacity"];
					$largest_oneman_venue_id = $rslt_lives[$i]["livehouse_id"];
				}
				$avg_oneman_capacity = $avg_oneman_capacity + $rslt_lives[$i]["capacity"];
			}
		}

		foreach($avg_capacity as $year => $capacity) {
			$avg_capacity[$year]["capacity"] = $capacity["capacity"] / $capacity["count"];
		}
		
		if(is_array($oneman_lives) && !empty($oneman_lives) && count($oneman_lives) > 0) {
			$avg_oneman_capacity = $avg_oneman_capacity / count($oneman_lives);
		}

		$total_avg_capacity = $total_avg_capacity / $num_lives;
		$num_years = count($avg_capacity);
	}

	$total_avg_capacity = $total_avg_capacity > 1800 ? 1800 : $total_avg_capacity;

	if(is_numeric($largest_oneman_venue_id)) {
		$sql_livehouse = "SELECT COALESCE(areas.romaji, areas.name) AS area, COALESCE(lives_livehouses.romaji, lives_livehouses.name) AS name FROM lives_livehouses LEFT JOIN areas ON areas.id=lives_livehouses.area_id WHERE lives_livehouses.id=? LIMIT 1";
		$stmt_livehouse = $pdo->prepare($sql_livehouse);
		$stmt_livehouse->execute([ $largest_oneman_venue_id ]);
		$rslt_livehouse = $stmt_livehouse->fetch();
	}

	// Display
	if($rslt_owned || $rslt_wanted || (is_array($rslt_lives) && !empty($rslt_lives))) {
		?>
			<h3>
				<?php echo lang('Popularity', '動員', ['container' => 'div']); ?>
			</h3>
			<div class="text text--outlined">
				<?php
					if($rslt_owned || $rslt_wanted || (is_array($rslt_lives) && !empty($rslt_lives))) {
						?>
							<ul>
								<?php
									if($rslt_owned) {
										?>
											<li>
												<h5>
													User-owned releases
												</h5>
												<?php echo $rslt_owned; ?>
											</li>
										<?php
									}

									if($highest_oneman_capacity) {
										?>
											<li>
												<h5>
													Largest oneman venue
												</h5>
												<?php echo $rslt_livehouse["area"]." ".$rslt_livehouse["name"]." (".["E", "D", "C", "B", "A", "S"][floor(($highest_oneman_capacity / 1800) * 5)].")"; ?>
											</li>
										<?php
									}

									if(is_array($rslt_lives) && !empty($rslt_lives)) {
										?>
											<li>
												<h5>
													Average venue rank
												</h5>
												<?php echo ["E", "D", "C", "B", "A", "S"][floor(($total_avg_capacity / 1800) * 5)]; ?>
											</li>
											<li>
												<h5>
													Venue rank over time
												</h5>
												<div class="rank__chart any--weaken">
													<div class="rank__graph any--flex">
														<?php
															foreach($avg_capacity as $year => $avg) {
																$h = ($avg["capacity"] / 1800) * 100;
																$w = ($avg["count"] / $num_lives) * 100;

																echo '<div class="rank__bar rank__bar--year" style="height: '.$h.'%; width: '.$w.'%;"></div>';
															}
														?>
													</div>
													<div class="rank__graph any--flex">
														<?php
															foreach($rslt_lives as $live) {
																if($live["capacity"] > 0) {
																	$h = ($live["capacity"] / 1800) * 100;
																	echo '<div class="rank__bar" style="height: '.$h.'%;" data-date="'.$live["date_occurred"].'" data-capacity="'.$live["capacity"].'"></div>';
																}
															}
														?>
													</div>
												</div>
											</li>
										<?php
									}
								?>
							</ul>
						<?php
					}
					else {
						?>
							<span class="symbol__error any--weaken">No data</span>
						<?php
					}
				?>
			</div>
		<?php
	}
?>