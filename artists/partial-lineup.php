<input class="lineup--compact" id="lineup--compact" type="checkbox" hidden />

<?php
include_once('../images/function-calculate_face_box.php');

// Kind of dumb but since our array of images is flat, let's make a map of image ids and their key in the array
if( is_array($artist['images']) && !empty($artist['images']) ) {
 foreach( $artist['images'] as $image_key => $image ) {
		
		$image_keys[ $image['id'] ] = $image_key;
		
	}
}

// Collect ids of all musicians, who don't have images, in a flat array
foreach( $artist['musicians'] as $group_key => $musicians_group ) {
	foreach( $musicians_group as $musician_key => $musician ) {
		if( !is_numeric($musician['image_id']) ) {
			
			// Do some hacky key saving so we can pop an image back onto the musician in the original array
			$key = $group_key.'-'.$musician_key;
			
			$musicians_without_images[$key] = $musician['id'];
			
		}
	}
}

// If we have some musicians without images, let's try to find some
if( is_array($musicians_without_images) && !empty($musicians_without_images) ) {
	
	$values_potential_musician_images = array_values( $musicians_without_images );
	$values_potential_musician_images[] = $artist['id'];
	
	$sql_potential_musician_images = '
		SELECT
			x.*,
			IF( x.image_content=2, 1, 0 ) AS is_musician,
			IF( x.face_boundaries IS NOT NULL, 1, 0 ) AS has_boundaries
		FROM (
			SELECT
				potential_images.image_id,
				potential_images.musician_id,
				potential_images.face_boundaries,
				images.image_content
			FROM (
				SELECT
					images_musicians.image_id,
					images_musicians.musician_id,
					images_musicians.face_boundaries
				FROM
					images_artists
				LEFT JOIN
					images_musicians
				ON
					images_musicians.image_id=images_artists.image_id
					AND
					images_musicians.musician_id IN ('.substr( str_repeat('?,', count($musicians_without_images)), 0, -1 ).')
				WHERE
					images_artists.artist_id=?
			) potential_images
			LEFT JOIN
				images 
			ON
				images.id=potential_images.image_id
			WHERE
				potential_images.image_id IS NOT NULL
			GROUP BY
				potential_images.image_id
		) x
		WHERE
			x.image_content=2
			OR
			( ( x.image_content=1 OR x.image_content=3 ) AND x.face_boundaries IS NOT NULL )
		ORDER BY
			is_musician ASC,
			has_boundaries ASC
	';
	$stmt_potential_musician_images = $pdo->prepare($sql_potential_musician_images);
	$stmt_potential_musician_images->execute( $values_potential_musician_images );
	$potential_musician_images = $stmt_potential_musician_images->fetchAll();
	
	// If we got some potential images, let's loop through them, choose the appropriate ones, and then optionally save them back to db
	if( is_array($potential_musician_images) && !empty($potential_musician_images) ) {
		foreach( $potential_musician_images as $potential_image ) {
			
			// Do some hacky stuff to get the musician's original spot in the artists array
			$key = array_search( $potential_image['musician_id'], $musicians_without_images );
			list( $group_key, $musician_key ) = explode('-', $key);
			
			$artist['musicians'][$group_key][$musician_key]['image_id'] = $potential_image['image_id'];
			
			// Save the results so we don't have to do this again
			$sql_save = 'UPDATE artists_musicians SET image_id=? WHERE artist_id=? AND musician_id=? LIMIT 1';
			$stmt_save = $pdo->prepare($sql_save);
			$stmt_save->execute([ $potential_image['image_id'], $artist['id'], $potential_image['musician_id'] ]);
			
		}
	}
	
}

foreach($artist["musicians"] as $musicians_type => $musicians) {
	
	$num_musicians_in_group = count($musicians);
	
	echo '<details class="any--margin" '.($musicians_type === 1 || $num_musicians_in_group === 1 ? 'open' : null).'>';
	
	?>
		<span id="<?php echo $musicians_type === 1 ? 'lineup' : ($musicians_type === 2 ? 'former' : 'staff'); ?>"></span>
		
		<summary class="h2 lineup__title <?php echo $musicians_type > 1 ? null : 'any--hidden'; ?>">
			<?php
				echo lang(
					($musicians_type === 1 ? 'Lineup' : ($musicians_type === 2 ? 'Former members' : 'Staff').($num_musicians_in_group > 1 ? ' <span class="any--weaken" style="float:none;clear:none;">('.$num_musicians_in_group.')</span>' : null) ),
					($musicians_type === 1 ? 'メンバー' : ($musicians_type === 2 ? '元メンバー' : 'スタッフ').($num_musicians_in_group > 1 ? ' <span class="any--weaken" style="float:none;clear:none;">('.$num_musicians_in_group.')</span>' : null) ),
					['container' => 'div', 'secondary_class' => 'any--weaken']
				);
			?>
		</summary>
		
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
					
					$musician_has_image = is_numeric( $musicians[$a]['image_id'] );
					
					?>
						<div class="ul" style="<?= $musician_has_image ? 'box-sizing: content-box; min-height: 100px; padding-left: calc(75px + 1rem);' : null; ?>" >
							
							<?php
								
								// Let's set the flag to false and make sure the image actually exists and is appropriate
								$musician_has_image = false;
								
								if( is_numeric($musicians[$a]['image_id']) ) {
									
									$image_key = $image_keys[ $musicians[$a]['image_id'] ];
									$image = $artist['images'][ $image_key ];
									
									// If the image is a musician image, we just show it how it is
									if( $image['image_content'] == 2 ) {
										
										$musician_has_image = true;
										$thumbnail = $image['thumbnail_url'];
										$face_styles = null;
										
									}
									
									// If image is a group image or flyer, where the musician is tagged in it, we have to make sure that musician has face boundaries
									elseif( $image['image_content'] == 1 || $image['image_content'] == 3 ) {
										
										if( is_array($image['musicians']) && !empty($image['musicians']) ) {
											
											foreach( $image['musicians'] as $image_musician ) {
												
												if( $image_musician['musician_id'] == $musicians[$a]['id'] && strlen($image_musician['face_boundaries']) ) {
													
													$face_boundaries = json_decode( $image_musician['face_boundaries'], true );
													$face_box = calculate_face_box([ 'image_height' => $image['height'], 'image_width' => $image['width'], 'face' => $face_boundaries, 'desired_width' => 75, 'desired_height' => 100 ]);
													
													$musician_has_image = true;
													$thumbnail = $image['small_url'];
													$face_styles = $face_box['css'];
													
												}
												
											}
											
										}
										
									}
									
								}
								
								// If musician still has image after checks, display it
								if( $musician_has_image ) {
									echo '<a class="musician__thumbnail lazy" data-src="'.$thumbnail.'" href="/'.$artist['friendly'].'/images/" style="'.$face_styles.'"></a>';
								}
								
								unset($musician_has_image, $thumbnail, $face_styles);
								
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
	
	echo '</details>';
	
}
?>