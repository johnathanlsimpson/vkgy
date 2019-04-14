<?php
	if($_SESSION["admin"]) {
		?>
			<form action="/lives/function-edit-livehouses.php" class="col c1 any--margin" enctype="multipart/form-data" method="post" name="form__update">
				<span data-contains="areas" hidden><?php echo json_encode($area_list); ?></span>
				<span data-contains="companies" hidden><?php echo json_encode($company_list); ?></span>
				<span data-contains="livehouses" hidden><?php echo json_encode($livehouse_list); ?></span>
				
				<div>
					<h2>
						<?php echo $edit_livehouses ? 'Edit' : 'Add'; ?> livehouses
					</h2>
				</div>
				
				<?php
					if($edit_livehouses) {
						?>
							<div class="col c3 any--margin">
								<div>
									<?php
										if($page_num > 1) {
											?>
												<a class="symbol__previous" href="/lives/livehouses/edit/page/<?php echo $page_num - 1; ?>/">Page <?php echo $page_num - 1; ?></a>
												<a class="a--padded symbol__oldest" href="/lives/livehouses/edit/page/1/"></a>
											<?php
										}
									?>
								</div>
								<div style="text-align: center;">
									Page <?php echo $page_num; ?>
								</div>
								<div style="text-align: right;">
									<?php
										if($page_num < $max_page) {
											?>
												<a class="a--padded" href="/lives/livehouses/edit/page/<?php echo $max_page; ?>/"><span class="symbol__newest"></span></a>
												<a href="/lives/livehouses/edit/page/<?php echo $page_num + 1; ?>/">Page <?php echo $page_num + 1; ?><span class="symbol__next"></span></a>
											<?php
										}
									?>
								</div>
							</div>
						<?php
					}
				?>
				
				<div>
					<?php
						for($i = 0; $i < ($num_livehouses ?: $limit_num); $i++) {
							?>
								<h3>
									<?php echo $add_livehouses ? 'Add livehouse' : ($rslt_livehouses[$i]["area_romaji"] ?: $rslt_livehouses[$i]["area_name"]).' '.($rslt_livehouses[$i]["romaji"] ?: $rslt_livehouses[$i]["name"]); ?>
								</h3>
								
								<input class="obscure__input" id="obscure-livehouse-<?php echo $i; ?>" type="checkbox" checked />
								<div class="text obscure__container">
									<div class="input__row li obscure__item">
										<div class="input__group">
											<label class="input__label">ID</label>
											<input name="id[]" value="<?php echo $rslt_livehouses[$i]["id"]; ?>" placeholder="id" size="1" readonly />
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label">Name</label>
											<input name="name[]" value="<?php echo $rslt_livehouses[$i]["name"]; ?>" placeholder="name" />
											<input class="input--secondary" name="romaji[]" value="<?php echo $rslt_livehouses[$i]["romaji"]; ?>" placeholder="romaji" />
										</div>
										<div class="input__group">
											<label class="input__label">Friendly</label>
											<input name="friendly[]" value="<?php echo $rslt_livehouses[$i]["friendly"]; ?>" placeholder="friendly" />
										</div>
									</div>
									
									<div class="input__row li obscure__item">
										<div class="input__group">
											<label class="input__label">Area</label>
											<select class="input" data-source="areas" name="area_id[]">
												<option value="<?php echo $rslt_livehouses[$i]["area_id"]; ?>" selected><?php echo $rslt_livehouses[$i]["area_romaji"] ? $rslt_livehouses[$i]["area_romaji"].' ('.$rslt_livehouses[$i]["area_name"].')' : $rslt_livehouses[$i]["area_name"]; ?></option>
											</select>
										</div>
										<div class="input__group">
											<label class="input__label">Capacity</label>
											<input name="capacity[]" size="8" value="<?php echo $rslt_livehouses[$i]["capacity"]; ?>" placeholder="cap." size="4" />
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label">Parent company</label>
											<select class="input" data-populate-on-click="true" data-source="companies" name="parent_id[]">
												<option value="<?php echo $rslt_livehouses[$i]["parent_id"]; ?>" selected><?php echo $rslt_livehouses[$i]["parent_romaji"] ? $rslt_livehouses[$i]["parent_romaji"].' ('.$rslt_livehouses[$i]["parent_name"].')' : $rslt_livehouses[$i]["parent_name"]; ?></option>
											</select>
										</div>
									</div>
									
									<?php
										if(strlen($rslt_livehouses[$i]["name"])) {
											?>
												<div class="input__row li obscure__item">
													<div class="input__group any--flex-grow">
														<label class="input__label">Nicknames</label>
														<?php
															foreach([
																$rslt_livehouses[$i]["romaji"] ?: null,
																$rslt_livehouses[$i]["romaji"] ? $rslt_livehouses[$i]["area_romaji"].' '.$rslt_livehouses[$i]["romaji"] : null,
																$rslt_livehouses[$i]["name"],
																$rslt_livehouses[$i]["area_name"].$rslt_livehouses[$i]["name"]
															] as $hint) {
																if(strlen($hint) > 0) {
																	echo '<span class="symbol__tag">'.strtolower($hint).'</span>&nbsp;';
																}
															}
														?>
													</div>
												</div>
											<?php
										}
									?>
									<div class="input__row <?php echo !strlen($rslt_livehouses[$i]["name"]) ? 'li' : null; ?> obscure__item">
										<div class="input__group any--flex-grow">
											<?php echo !strlen($rslt_livehouses[$i]["name"]) ? '<label class="input__label">Nicknames</label>' : null; ?> 
											<input name="nicknames[]" value="<?php echo $rslt_livehouses[$i]["nicknames"]; ?>" placeholder="nickname 1, nickname 2, ..." />
										</div>
									</div>
									<div class="input__row obscure__item li">
										<div class="any--weaken" style="margin-left: 0.5rem;">
											<span class="symbol__help"></span>  When updating artist schedules, you can use these nicknames to quickly add a livehouse. Nicknames are automatically generated, but you can manually add more above.<br />
											<span class="symbol__help"></span>  Nicknames are case insensitive, and also work when typed without spaces.<br />
											<span class="symbol__error"></span> Nicknames must be unique (no two livehouses can use the same nickname).
										</div>
									</div>
									
									<div class="input__row obscure__item li">
										<div class="input__group any--flex-grow">
											<label class="input__label">Changed name to</label>
											<select class="input" data-populate-on-click="true" data-source="livehouses" name="renamed_to[]">
												<option value="<?php echo $rslt_livehouses[$i]["renamed_to"]; ?>" selected><?php echo $rslt_livehouses[$i]["renamed_romaji"] ? $rslt_livehouses[$i]["renamed_romaji"].' ('.$rslt_livehouses[$i]["renamed_name"].')' : $rslt_livehouses[$i]["renamed_name"]; ?></option>
											</select>
										</div>
										<div class="input__group any--flex-grow">
											<label class="input__label">Merge data from</label>
											<select class="input" data-populate-on-click="true" data-source="livehouses" name="merge_with[]">
												<option value="<?php echo $rslt_livehouses[$i]["merge_with"]; ?>" selected></option>
											</select>
										</div>
									</div>
									<div class="input__row obscure__item">
										<div class="any--weaken" style="margin-left: 0.5rem;">
											<span class="symbol__help"></span> If a livehouse has changed its name, the &ldquo;changed name to&rdquo; field should link to the <em>newer</em> name.<br />
											<span class="symbol__help"></span> Duplicate livehouses may be merged above (do <em>not</em> use for name changes). The database will attempt to merge both livehouses into one. See documentation for more details.
										</div>
									</div>
									
									<label class="input__button obscure__button symbol__down-caret" for="obscure-livehouse-<?php echo $i; ?>">More options</label>
								</div>
							<?php
						}
					?>
				</div>
				
				<div class="col c3 any--margin">
					<div>
						<?php
							if($page_num > 1) {
								?>
									<a class="symbol__previous" href="/lives/livehouses/edit/page/<?php echo $page_num - 1; ?>/">Page <?php echo $page_num - 1; ?></a>
									<a class="a--padded symbol__oldest" href="/lives/livehouses/edit/page/1/"></a>
								<?php
							}
						?>
					</div>
					<div style="text-align: center;">
						Page <?php echo $page_num; ?>
					</div>
					<div style="text-align: right;">
						<?php
							if($page_num < $max_page) {
								?>
									<a class="a--padded" href="/lives/livehouses/edit/page/<?php echo $max_page; ?>/"><span class="symbol__newest"></span></a>
									<a href="/lives/livehouses/edit/page/<?php echo $page_num + 1; ?>/">Page <?php echo $page_num + 1; ?><span class="symbol__next"></span></a>
								<?php
							}
						?>
					</div>
				</div>
				
				<div class="text text--docked">
					<div class="any--flex">
						<button class="any--flex-grow" type="submit">
							Update livehouses
						</button>
						<span data-role="status"></span>
					</div>
					<div class="text text--outlined text--notice add__result any--hidden" data-role="result"></div>
				</div>
			</form>
		<?php
		
		$documentation_page = 'livehouses';
		include('../documentation/index.php');
	}
?>