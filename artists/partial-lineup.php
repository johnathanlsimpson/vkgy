<input class="lineup--compact" id="lineup--compact" type="checkbox" hidden />

<?php

$active_musician_ids = [];
$former_musician_ids = [];

// Collect IDs of active and former musicians (not staff)
foreach($artist['musicians'] as $musicians_type => $musicians) {
	if( $musicians_type == 1 || $musicians_type == 2 ) {
		foreach( $musicians as $musician ) {
			if( $musicians_type == 1 ) {
				$active_musician_ids[] = $musician['id'];
				$num_active_musicians++;
			}
			else {
				$former_musician_ids[] = $musician['id'];
			}
		}
	}
}

$release_images;

// Get images of musicians from their time with band
if( !empty($active_musician_ids) || !empty($former_musician_ids) ) {
	
	$values_musician_images = array_merge( $active_musician_ids, $former_musician_ids );
	$values_musician_images[] = $artist['id'];
	
	$sql_musician_images = '
	SELECT
		images.id,
		images.extension,
		ii.*
	FROM
		(
			SELECT
				i.image_id,
				i.musician_id,
				images_releases.release_id,
				releases.date_occurred
			FROM
				(
					SELECT
						images_musicians.image_id,
						images_musicians.musician_id
					FROM
						images_musicians
					WHERE
						images_musicians.musician_id IN ('.substr( str_repeat( '?, ', count($values_musician_images) - 1 ), 0, -2 ).')
				) i
			LEFT JOIN
				images_artists ON images_artists.image_id=i.image_id
			LEFT JOIN
				images_releases ON images_releases.image_id=i.image_id
			LEFT JOIN
				releases ON releases.id=images_releases.release_id AND images_releases.release_id IS NOT NULL
			WHERE
				images_artists.artist_id=?
		) ii
	LEFT JOIN
		images ON images.id=ii.image_id
	';
	
	$stmt_musician_images = $pdo->prepare( $sql_musician_images );
	$stmt_musician_images->execute( $values_musician_images );
	$musician_images = $stmt_musician_images->fetchAll();
	
	// Loop through, check for images with multiple musicians connected (a.k.a. group photos) and remove
	if( is_array($musician_images) && !empty($musician_images) ) {
		
		// Format as array of arrays so we can eliminate ones with > 1 entry
		foreach($musician_images as $musician_image_key => $musician_image) {
			
			$tmp_musician_images[ $musician_image['image_id'] ][] = $musician_image;
			
		}
		
		// Reset array
		$musician_images = [];
		
		// Loop through and save unique images, remove images with dupes (presumed group images)
		foreach($tmp_musician_images as $tmp_key => $tmp_images) {
			
			if( count($tmp_images) == 1 ) {
				$musician_images[] = $tmp_images[0];
			}
			
		}
		
		// Clean up temp images
		unset($tmp_musician_images);
		
	}
	
	// Go through remaining array, and build helper skelly around releases, so we can attempt to use same photoshoot
	if( is_array($musician_images) && !empty($musician_images) ) {
		foreach($musician_images as $musician_image_key => $musician_image) {
			
			if( is_numeric($musician_image['release_id']) ) {
				$release_images[ $musician_image['release_id'] ]['date_occurred'] = $musician_image['date_occurred'];
				$release_images[ $musician_image['release_id'] ]['images'][] = $musician_image;
				$release_images[ $musician_image['release_id'] ]['musician_ids'][] = $musician_image['musician_id'];
			}
			
		}
	}
	
	// If we have sets of images tied to a release, order them by release date, then check for latest one containing all current members
	if( is_array($release_images) && !empty($release_images) ) {
		
		usort($release_images, function($a, $b) {
			return $b['date_occurred'] <=> $a['date_occurred'];
		});
		
		// If num active musicians is less than or equal to musicians in this release,
		// *and* the number of ids in both arrays equals that number of active musicians,
		// then this release at least features all active musicians (and possibly some former ones),
		// which is the ideal scenario from which we want to show the musicians' images
		foreach($release_images as $release_key => $release) {
			
			$num_release_musicians = count($release['musician_ids']);
			if( $num_active_musicians <= $num_release_musicians && $num_active_musicians == count( array_intersect( $release['musician_ids'], $active_musician_ids ) ) ) {
				$preferred_release_key = $release_key;
			}
			
		}
		
	}
	
	// If we found a preferred release key, loop through its images and save them for later
	if( is_numeric($preferred_release_key) ) {
		foreach( $release_images[ $preferred_release_key ]['images'] as $image ) {
			
			$final_musician_images[ $image['musician_id'] ] = $image;
			
		}
	}
	
	// Then loop through remaining images and set whatever random image pops up for that musician
	foreach( $musician_images as $image ) {
		
		if( !isset($final_musician_images[ $image['musician_id'] ]) ) {
			
			$final_musician_images[ $image['musician_id'] ] = $image;
			
		}
		
	}
	
}

$musician_images = $final_musician_images;

?>

<?php
foreach($artist["musicians"] as $musicians_type => $musicians) {
	?>
		<span id="<?php echo $musicians_type === 1 ? 'lineup' : ($musicians_type === 2 ? 'former' : 'staff'); ?>"></span>
		
		<h2 class="<?php echo $musicians_type > 1 ? null : 'any--hidden'; ?>">
			<?php
				echo lang(
					($musicians_type === 1 ? 'Lineup' : ($musicians_type === 2 ? 'Former members' : 'Staff')),
					($musicians_type === 1 ? 'メンバー' : ($musicians_type === 2 ? '元メンバー' : 'スタッフ')),
					['container' => 'div', 'secondary_class' => 'any--weaken']
				);
			?>
		</h2>
		
		<div class="text lineup__wrapper <?php echo $musicians_type !== 1 ? "text--outlined" : null; ?>">
			<label class="lineup__compact input__checkbox symbol__unchecked <?= !$compact_button_shown ? '' : 'any--hidden'; $compact_button_shown = true; ?>" for="lineup--compact">
				<?php echo lang('Make compact?', '縮小する', ['secondary_class' => 'any--hidden']); ?>
			</label>
			
			<?php
				$num_musicians = count($musicians);
				for($a=0; $a<$num_musicians; $a++) {
					$position_name = $musicians[$a]['position_name'];
					$position_name = $position_name == 'roadie' ? lang('roadie', 'ローディー', [ 'secondary_class' => 'any--hidden' ]) : $position_name;
					$position_name = strpos($position_name, 'support') === 0 ? lang($position_name, str_replace('support ', 'サポート', $position_name), [ 'secondary_class' => 'any--hidden' ]) : $position_name;
					
					// Loop through bands one time to determine if we should show this musician for this band
					$hide_musician_from_lineup = false;
					$can_see_hidden_musician = $_SESSION['is_moderator'];
					
					foreach($musicians[$a]['history'] as $periods) {
						foreach($periods as $band) {
							if($band['id'] === $artist['id']) {
								if($band['is_hidden'] && !$can_see_hidden_musician) {
									$hide_musician_from_lineup = true;
								}
							}
						}
					}
					
					$musician_has_image = is_array($musician_images) && !empty($musician_images) && $musician_images[ $musicians[$a]['id'] ];
					
					?>
						<div class="ul" style="<?= $musician_has_image ? 'box-sizing: content-box; min-height: 100px; padding-left: calc(75px + 1rem);' : null; ?>" >
							
							<?php
								if( $musician_has_image ) {
									
									$image = $musician_images[ $musicians[$a]['id'] ];
									
									echo '<a href="/musicians/'.$musicians[$a]['id'].'/'.$musicians[$a]['friendly'].'/" style="left: 0; position: absolute;">';
									echo '<img src="/images/'.$image['id'].'.thumbnail.'.$image['extension'].'" style="height: 100px; object-fit: cover; object-position: center; width: 75px;" />';
									echo '</a>';
									
								}
							?>
							
							<h4>
								<a class="a--inherit" href="/search/musicians/?position=<?php echo $musicians[$a]["position"]; ?>#result"><?php echo $position_name; ?></a>
							</h4>
							<h3>
								
								<?= !$hide_musician_from_lineup || $can_see_hidden_musician ? '<a class="a--inherit" href="/musicians/'.$musicians[$a]['id'].'/'.$musicians[$a]['friendly'].'/">' : null; ?>
								<?= $musicians[$a]['romaji'] ? lang($musicians[$a]['romaji'], $musicians[$a]['name'], 'hidden') : $musicians[$a]['name']; ?>
								<?= !$hide_musician_from_lineup || $can_see_hidden_musician ? '</a>' : null; ?>
								
								<span class="any--weaken-color any--en"><?= $musicians[$a]['romaji'] ? '('.$musicians[$a]['name'].')' : null; ?></span>
							</h3>
							<div class="any--flex member__history">
								<div class="lineup__container any--weaken-color">
									<?php
										$musician_bands = [];
										
										$num_history_periods = count($musicians[$a]['history']);
										for($c=0; $c<$num_history_periods; $c++) {
											
											$num_period_chunks = count($musicians[$a]['history'][$c]);
											for($d=0; $d<$num_period_chunks; $d++) {
												$duplicate_identifier = ($musicians[$a]['history'][$c][$d]['url'] ?: 'no-url-'.$musicians[$a]['history'][$c][$d]['quick_name']);
												
												// Determine whether or not to show this particular band
												// If viewing the band, then the flag should be opposite so we can see this band's history but not the rest
												$hide_band_from_lineup = $musicians[$a]['history'][$c][0]['is_hidden'];
												$hide_band_from_lineup = $hide_musician_from_lineup ? !$hide_band_from_lineup : $hide_band_from_lineup;
												
												// If first band in a period is flagged as hidden, then that entire period should be hidden
												// But show for moderators
												if($hide_band_from_lineup && !$can_see_hidden_musician) {
													
													// Only show ellipsis once per period
													if($d === 0) {
														
														// Don't show ellipsis if it was preceded by one
														if(
															!$ellipsis_shown
														) {
															echo '<span class="any__note">...</span>';
														}
														
													}
													
													$ellipsis_shown = true;
													
												}
												else {
													
												$ellipsis_shown = false;
												
												if($musicians[$a]['history'][$c][$d]['is_session']) {
													$session_id = $musicians_type.'-'.$a.'-'.$c.'-'.$d;
													
													echo '<session data-for-session="'.$session_id.'"></session>';
													
													echo $d < $num_period_chunks - 1 ? ' , ' : null;
													
													$musicians[$a]['sessions'][] = array_merge($musicians[$a]['history'][$c][$d], [ 'session_id' => $session_id ]);
												}
												else {
													?>
														<span class="lineup__band <?php echo in_array($duplicate_identifier, $musician_bands) ? 'lineup--duplicate' : null; ?>" style="<?= $hide_band_from_lineup ? 'opacity:0.5;' : null; ?>">
															<?php
																if(!empty($musicians[$a]['history'][$c][$d]["url"])) {
																	?>
																		<a class="artist artist--no-symbol" href="<?php echo $musicians[$a]['history'][$c][$d]["url"]; ?>"><?php echo lang($musicians[$a]['history'][$c][$d]["quick_name"], $musicians[$a]['history'][$c][$d]['name'], ['secondary_class' => 'any--hidden']); ?></a>
																	<?php
																}
																
																echo empty($musicians[$a]['history'][$c][$d]["url"]) ? $musicians[$a]['history'][$c][$d]["quick_name"] : null;
																echo $musicians[$a]['history'][$c][$d]["romaji"] ? ' <span class="any--en">('.$musicians[$a]['history'][$c][$d]["name"].')</span>' : null;
																
																if(!empty($musicians[$a]['history'][$c][$d]["notes"]) && is_array($musicians[$a]['history'][$c][$d]["notes"])) {
																	foreach($musicians[$a]['history'][$c][$d]["notes"] as $note) {
																		$note = substr($note, 1, -1);
																		$note = $note == 'support' ? lang('support', 'サポート', [ 'secondary_class' => 'any--hidden' ]) : $note;
																		$note = $note == 'roadie' ? lang('roadie', 'ローディー', [ 'secondary_class' => 'any--hidden' ]) : $note;
																		$note = $note == 'retired' ? lang('retired', '引退', [ 'secondary_class' => 'any--hidden' ]) : $note;
																		$note = $note == 'deceased' ? lang('deceased', '死去', [ 'secondary_class' => 'any--hidden' ]) : $note;
																		
																		?>
																			<span class="any__note"><?php echo $note == '(support)' ? lang('support', 'サポート') : $note; ?></span>
																		<?php
																	}
																}
																
																echo $d < $num_period_chunks - 1 ? ' , ' : null;
															?>
														</span>
													<?php
													
													
													$musician_bands[] = $duplicate_identifier;
												}
												
												}
												
											}
											
											if(
												$c < $num_history_periods - 1
												&&
												(!$ellipsis_shown || ( $ellipsis_shown && $c === 0 ))
											) {
												echo ' <span class="lineup__arrow symbol__next">&rarr;</span> ';
											}
											
										}
									?>
								</div>
								
								<?php
									if(is_array($musicians[$a]['sessions']) && !empty($musicians[$a]['sessions'])) {
										?>
											<div class="lineup__sessions any--weaken-color">
												<h5>
													&#8251;<?php echo lang('Sessions', 'セッション', ['secondary_class' => 'any--hidden']); ?>
												</h5>
												<?php
													$num_sessions = count($musicians[$a]['sessions']);
													for($e=0; $e<$num_sessions; $e++) {
														?><session data-is-session="<?php echo $musicians[$a]['sessions'][$e]['session_id']; ?>"><?php
																	if(!empty($musicians[$a]['sessions'][$e]["url"])) {
																		?><a class="artist artist--no-symbol a--inherit" href="<?php echo $musicians[$a]['sessions'][$e]["url"]; ?>"><?php echo lang($musicians[$a]['sessions'][$e]["quick_name"], $musicians[$a]['sessions'][$e]['name'], ['secondary_class' => 'any--hidden']); ?></a><?php
																	}
																	
																	echo empty($musicians[$a]['sessions'][$e]["url"]) ? $musicians[$a]['sessions'][$e]["quick_name"] : null;
																	echo $musicians[$a]['sessions'][$e]["romaji"] ? ' <span class="any--en">('.$musicians[$a]['sessions'][$e]["name"].')</span>' : null;
																	
																	if(!empty($musicians[$a]['sessions'][$e]["notes"]) && is_array($musicians[$a]['sessions'][$e]["notes"])) {
																		foreach($musicians[$a]['sessions'][$e]["notes"] as $note) {
																			?>
																				<span class="any__note">
																					<?php
																						echo $note;
																					?>
																				</span><?php
																		}
																	}
																?></session><?php
														
														echo $e < $num_sessions - 1 ? ' , ' : null;
													}
												?>
											</div>
										<?php
									}
								?>
							</div>
						</div>
					<?php
				}
			?>
		</div>
	<?php
}
?>