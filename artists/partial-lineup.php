<input class="lineup--compact" id="lineup--compact" type="checkbox" hidden />

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
					
					?>
						<div class="ul">
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
														echo '<span class="any__note">...</span>';
													}
													
												}
												else {
												
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
											
											echo $c < $num_history_periods - 1 ? ' <span class="lineup__arrow symbol__next">&rarr;</span> ' : null;
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