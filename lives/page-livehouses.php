<?php
	style('../lives/style-page-livehouses.css');
?>

<div class="col c1">
	<div>
		<h2>
			<div class="any--en">
				Livehouse list
			</div>
			<div class="any--jp any--weaken">
				<?php echo sanitize('ライブハウス一覧'); ?>
			</div>
		</h2>
	</div>
</div>

<?php
	if(is_array($rslt_livehouses) && !empty($rslt_livehouses)) {
		?>
			<div class="col c1">
				<div>
					<div class="text">
						<table>
							<thead class="list__header">
								<th>
									ID
								</th>
								<th>
									<div class="any--en">
										Area
									</div>
									<div class="any--jp any--weaken">
										<?php echo sanitize('エリア'); ?>
									</div>
								</th>
								<th>
									<div class="any--en">
										Name
									</div>
									<div class="any--jp any--weaken">
										<?php echo sanitize('名'); ?>
									</div>
								</th>
								<th>
									<div class="any--en">
										Capacity
									</div>
									<div class="any--jp any--weaken">
										<?php echo sanitize('キャパ'); ?>
									</div>
								</th>
							</thead>
							<tbody>
								<?php
									for($i=0; $i<$num_livehouses; $i++) {
										?>
											<tr>
												<td class="any--weaken">
													<?php echo $rslt_livehouses[$i]['id']; ?>
												</td>
												<td>
													<?php echo $rslt_livehouses[$i]['area_romaji'] ? '<span class="any--en">'.$rslt_livehouses[$i]['area_romaji'].'</span> <span class="any--jp any--weaken">'.$rslt_livehouses[$i]['area_name'].'</span>' : ($rslt_livehouses[$i]['area_name'] ?: '<span class="any--weaken">?</span>'); ?>
												</td>
												<td>
													<div class="any--flex">
														<div>
															<?php
																if($rslt_livehouses[$i]['romaji']) {
																	?>
																		<div class="any--en"><a class="symbol__company" href="/lives/livehouses/<?php echo $rslt_livehouses[$i]['id']; ?>/edit/"><?php echo $rslt_livehouses[$i]['romaji']; ?></a></div>
																		<div class="any--jp any--weaken"><?php echo $rslt_livehouses[$i]['name']; ?></div>
																	<?php
																}
																else {
																	?>
																		<a class="symbol__company" href="/lives/livehouses/<?php echo $rslt_livehouses[$i]['id']; ?>/edit/"><?php echo $rslt_livehouses[$i]['name']; ?></a>
																	<?php
																}
															?>
														</div>
														&nbsp;
														<span class="any__note list__friendly"><?php echo $rslt_livehouses[$i]['friendly']; ?></span>
													</div>
													<?php echo $rslt_livehouses[$i]['nicknames'] ? '<div class="list__nicknames any--weaken-color symbol__help">Nicknames: '.$rslt_livehouses[$i]['nicknames'].'</div>' : null; ?>
												</td>
												<td>
													<?php echo $rslt_livehouses[$i]['capacity'] ?: '<span class="any--weaken">?</span>'; ?>
												</td>
											</tr>
										<?php
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php
	}
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error symbol__error">
						Sorry, something went wrong.
					</div>
				</div>
			</div>
		<?php
	}
?>