<?php
	include_once("../avatar/class-avatar.php");
	include_once("../avatar/avatar-definitions.php");
	
	style([
		"/avatar/style-page-edit.css",
	]);
	
	script([
		"/avatar/script-page-edit.js",
	]);
?>

<h2>
	<?php echo lang('VK avatar', 'V系アバター', ['container' => 'div']); ?>
</h2>

<!-- 'Edit avatar' container -->
<form action="/avatar/function-edit.php" class="col c2" method="post" name="form__avatar">
	<?php
		/* Inputs */
		foreach($avatar_layers as $group_name => $layers) {
			foreach($layers as $layer_name => $layer) {
				if($layer["shape_is_selectable"]) {
					foreach($layer["shapes"] as $shape_name => $shape_path) {
						$id = "{$group_name}__{$layer_name}--{$shape_name}";
						$name = "{$group_name}__{$layer_name}";
						$value = $shape_name;
						$checked = (isset($current_avatar[$group_name][$layer_name]["shapes"][$shape_name]) ? "checked" : (!$current_avatar[$group_name][$layer_name]["shapes"] && $shape_name === array_keys($layer["shapes"])[0] ? "checked" : null));
						
						?>
							<input class="input__checkbox any--hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="<?php echo $value; ?>" <?php echo $checked; ?> />
						<?php
					}
				}
				
				if($layer["color_is_selectable"]) {
					foreach($layer["colors"] as $color_name => $color_value) {
						$id = "{$group_name}__{$layer_name}--{$color_name}";
						$name = "{$group_name}__{$layer_name}-color";
						$value = $color_name;
						$checked = (isset($current_avatar[$group_name][$layer_name]["colors"][$color_name]) ? "checked" : (!$current_avatar[$group_name][$layer_name]["colors"] && $color_name === array_keys($layer["colors"])[0] ? "checked" : null));
						
						?>
							<input class="input__checkbox any--hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="<?php echo $value; ?>" <?php echo $checked; ?> />
						<?php
					}
				}
				
				if($layer['position_is_selectable']) {
					foreach($layer['positions'] as $position_name => $position) {
						$id = "{$group_name}__{$layer_name}--{$position_name}";
						$name = "{$group_name}__{$layer_name}-position";
						$value = $position_name;
						$checked = (isset($current_avatar[$group_name][$layer_name]['positions'][$position_name]) ? 'checked' : (!$current_avatar[$group_name][$layer_name]['positions'] && $position_name === array_keys($layer['positions'])[0] ? 'checked' : null));
						
						?>
							<input class="input__checkbox any--hidden" id="<?= $id; ?>" name="<?= $name; ?>" type="radio" value="<?= $value; ?>" <?= $checked; ?> />
						<?php
					}
				}
			}
		}
	?>
	
	<!-- 'Edit avatar': left side -->
	<div class="avatar__column any--margin">
		
		<!-- Avatar preview -->
		<div class="text avatar__container">
			<svg version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="600px" height="600px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
				<?php
					$avatar = new avatar($avatar_layers);
					echo $avatar->get_avatar_paths();
				?>
			</svg>
		</div>
		
		<!-- Save button -->
		<div class="avatar__buttons any--flex">
			<button class="avatar__save" type="submit"><?php echo lang('Save', '保存する', ['secondary_class' => 'any--hidden']); ?></button>
			<span data-role="status"></span>
			<button class="symbol__random avatar__random" type="button"><?php echo lang('Random', 'ランダム', ['secondary_class' => 'any--hidden']); ?></button>
			<button class="symbol__oldest avatar__reset" type="button"><?php echo lang('Reset', 'リセット', ['secondary_class' => 'any--hidden']); ?></button>
		</div>
		
		<!-- Error box -->
		<div class="text text--outlined text--notice avatar__result any--hidden" data-role="result"></div>
		
	</div>
	
	<!-- 'Edit avatar': right side -->
	<div class="controls__column avatar__controls">
		
		<div class="avatar__nav tertiary-nav__container any--flex any--margin">
			<?php
				foreach($avatar_layers as $group_name => $layer) {
					if(is_array($layer) && !empty($layer)) {
					?>
						<label class="tertiary-nav__link <?php echo $group_name === 'head' ? 'tertiary-nav--active' : null; ?> a--inherit a--padded" for="<?php echo 'avatar__show-'.$group_name; ?>"><?php echo lang($group_name, $layer['attributes']['ja'] ?: $group_name, ['secondary_class' => 'any--hidden']); ?></label>
					<?php
					}
				}
			?>
		</div>
		
		<?php
			/* Form controls */
			foreach($avatar_layers as $group_name => $layer_parts) {
				if(is_array($layer_parts) && !empty($layer_parts)) {
				if(!$layer_parts["attributes"]["is_hidden"]) {
					?>
						<input class="avatar__show any--hidden" form="fake-form" id="<?php echo 'avatar__show-'.$group_name; ?>" name="avatar__show[]" type="radio" <?php echo $group_name === 'head' ? 'checked' : null; ?> />
						<div class="avatar__group text text--outlined">
							<ul>
							<?php
								
								// Set a counter that increments only when a new 'shape selectable' layer is encountered, so we can add a border on top
								$i = 0;
								
								foreach($layer_parts as $layer_name => $layer) {
									if($layer["shape_is_selectable"] && count($layer['shapes']) > 1) {
										echo $i++ ? '</ul><ul>' : null;
										?>
											<li class="avatar__shape">
												<div class="input__row">
													<div class="input__group">
														<?php
															if(is_array($layer["description"])) {
																echo '<label class="input__label">';
																echo lang($layer['description']['en'], $layer['description']['ja'], [ 'secondary_class' => 'any--hidden' ]);
																echo '</label>';
															}
															
															foreach($layer["shapes"] as $shape_name => $shape) {
																$is_checked = (isset($current_avatar[$group_name][$layer_name]["shapes"][$shape_name]) ? true : (!$current_avatar[$group_name][$layer_name]["shapes"] && $shape_name === array_keys($layer["shapes"])[0] ? true : false));
																$class = "input__checkbox-label symbol__unchecked ".(is_array($shape) && $shape["is_vip"] ? "avatar--vip" : null);
																$for = "{$group_name}__{$layer_name}--{$shape_name}";
																
																$image_url = '/avatar/images/'.$group_name.'-'.$layer_name.'-'.$shape_name.'.gif';
																$style = file_exists('..'.$image_url) ? 'background-image:url('.$image_url.');' : null;
																?>
																	<label class="<?php echo $class; ?>" for="<?php echo $for; ?>" style="height: 50px; width: 50px; background-size: 50px 50px; background-position: center; padding: 0.25rem; <?php echo $style; ?>"></label>
																<?php
															}
														?>
													</div>
												</div>
											</li>
										<?php
									}
									if($layer["color_is_selectable"]) {
										?>
											<li class="avatar__color">
												<div class="input__row">
													<div class="input__group">
														<?php
															if(is_array($layer["description"])) {
																echo '<label class="input__label">';
																echo lang($layer['description']['en'].' color', $layer['description']['ja'].'カラー', [ 'secondary_class' => 'any--hidden' ]);
																echo '</label>';
															}
															
															foreach($layer["colors"] as $color_name => $color) {
																if($color) {
																	$is_checked = (isset($current_avatar[$group_name][$layer_name]["colors"][$color_name]) ? true : (!$current_avatar[$group_name][$layer_name]["colors"] && $color_name === array_keys($layer["colors"])[0] ? true : false));
																	$class = "input__checkbox-label avatar__color-label symbol__unchecked ".(is_array($color) && $color["is_vip"] ? "avatar--vip" : null);
																	$for = "{$group_name}__{$layer_name}--{$color_name}";
																	$background_css = is_array($color) ? ($color['form_background'] ? 'background: '.$color['form_background'].';' : 'background-color: '.$color['color'].';') : 'background-color: '.$color.';';
																	?>
																		<label class="<?php echo $class; ?>" for="<?php echo $for; ?>" style="<?php echo $background_css; ?>">&nbsp;</label>
																	<?php
																}
																else {
																	echo '<div class="avatar__clear"></div>';
																}
															}
														?>
													</div>
												</div>
											</li>
										<?php
									}
									if($layer['position_is_selectable'] && $layer_name != 'iris-right') {
										?>
											<li class="avatar__position">
												<div class="input__row">
													<div class="input__group">
														<?php
															if(is_array($layer['description'])) {
																echo '<label class="input__label">';
																echo lang($layer['description']['en'].' position', $layer['description']['ja'].'', 'hidden');
																echo '</label>';
															}
															
															foreach($layer['positions'] as $position_name => $position) {
																$class = 'input__checkbox-label avatar__position-label symbol__unchecked '.(is_array($position) && $position['is_vip'] ? 'avatar--vip' : null);
																$for = "{$group_name}__{$layer_name}--{$position_name}";
																echo '<label class="'.$class.'" for="'.$for.'">'.$position_name.'</label>';
															}
														?>
													</div>
												</div>
											</li>
										<?php
									}
								}
							?>
							</ul>
						</div>
					<?php
				}
				}
			}
		?>
	</div>
</form>

<style>
	#obscure-avatar:checked + .obscure__container .obscure__item:nth-of-type(n + 2) {
		display: none;
	}
	<?php
		/*
		** This CSS *only* controls which layers are visible/clipped/colored in the editor, and does not control the rendered PNG
		*/
		foreach($avatar_layers as $group_name => $layers) {
			foreach($layers as $layer_name => $layer) {
				if($layer["shape_is_selectable"]) {
					if(is_array($layer["shapes"])) {
						foreach($layer["shapes"] as $shape_name => $shape_path) {
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__column .'.$group_name.' [class*='.$group_name.'__'.$layer_name.']:not(.'.$group_name.'__'.$layer_name.'--'.$shape_name.') {';
								echo 'display:none;';
							echo '} '."\n";
							
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$shape_name.'] {';
								echo 'color:hsl(var(--text));';
							echo '} '."\n";
							
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$shape_name.']::before {';
								echo 'clip-path:url(#symbol__checked);';
								echo 'opacity:1;';
							echo '} '."\n";
						}
					}
					if(is_array($layer["vip_shapes"])) {
						foreach($layer["vip_shapes"] as $shape_name => $shape_path) {
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__column .'.$group_name.' [class*='.$group_name.'__'.$layer_name.']:not(.'.$group_name.'__'.$layer_name.'--'.$shape_name.') {';
								echo 'display:none;';
							echo '} '."\n";
							
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$shape_name.'] {';
								echo 'color:hsl(var(--text));';
							echo '} '."\n";
							
							echo '#'.$group_name.'__'.$layer_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$shape_name.']::before {';
								echo 'clip-path:url(#symbol__checked);';
								echo 'opacity:1;';
							echo '} '."\n";
						}
					}
				}
				
				if($layer['attributes']['clipped_by']) {
					list($clipping_group, $clipping_layer) = explode('__', $layer['attributes']['clipped_by']);
					
					if($avatar_layers[$clipping_group][$clipping_layer]['extends_part']) {
						$clipping_layer = $avatar_layers[$clipping_group][$clipping_layer]['extends_part'];
					}
					
					foreach(array_keys( $avatar_layers[$clipping_group][$clipping_layer]['shapes'] ) as $clipping_shape_option) {
						$clip_path = $layer['attributes']['clipped_by'].'--'.$clipping_shape_option;
						$clip_path = 'url(#'.$clip_path.'.path)';
						
						echo '#'.$clipping_group.'__'.$clipping_layer.'--'.$clipping_shape_option.':checked ~ .avatar__column '.'.'.$group_name.'__'.$layer_name.' {';
							echo 'clip-path:'.$clip_path.';';
							echo 'filter:none;';
						echo '} '."\n";
					}
				}
				
				// Positions
				if($layer['position_is_selectable']) {
					foreach($layer['positions'] as $position_name => $position) {
						
						// Add pixel values to position
						$position = preg_replace('/'.'(\d+)'.'/', '$1px', $position);
						
						// If iris, make sure left-hand transform affects right-hand as well
						if($layer_name === 'iris-left' || $layer_name === 'iris-right') {
							if($layer_name === 'iris-left') {
								if($layers['iris-left']['positions'][$position_name] != $layers['iris-right']['positions'][$position_name]) {
									echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' * {';
										echo 'transform: translate('.$position.');';
									echo '} '."\n";
									echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.str_replace('-left', '-right', $layer_name).' * {';
										echo 'transform: translate('.preg_replace('/'.'(\d+)'.'/', '$1px', $layers['iris-right']['positions'][$position_name]).');';
									echo '} '."\n";
								}
								else {
									echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' *,';
									echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.str_replace('-left', '-right', $layer_name).' * {';
										echo 'transform: translate('.$position.');';
									echo '} '."\n";
								}
							}
						}
						
						// Otherwise, set transform as normal
						else {
							echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' * {';
								echo 'transform: translate('.$position.');';
							echo '} '."\n";
						}
						
						// Make label appear checked for selected position
						echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$position_name.'] {';
							echo 'color:hsl(var(--text));';
						echo '} '."\n";
						
						echo '#'.$group_name.'__'.$layer_name.'--'.$position_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$position_name.']::before {';
							echo 'clip-path:url(#symbol__checked);';
							echo 'opacity:1;';
						echo '} '."\n";
						
					}
				}
				
				// Apply transforms to part which gets its transform values from another part
				elseif($layer['extends_part']) {
					$extended_part = $layer['extends_part'];
					
					// If extended part has positions
					if($layers[$extended_part]['positions']) {
						
						// Loop through each extended part's positions and set up CSS
						foreach($layers[$extended_part]['positions'] as $position_name => $position) {
							
							// Add pixel values to position
							$position = preg_replace('/'.'(\d+)'.'/', '$1px', $position);
							
							// If extended part is iris, we have to make sure that the right-hand values are *oposite* the left-hand values
							if(strpos($extended_part, 'iris') === 0) {
								if($position_name === 'left') {
									$reflected_position = preg_replace('/'.'(\d+)'.'/', '$1px', $layers[$extended_part]['positions']['right']);
								}
								elseif($position_name === 'right') {
									$reflected_position = preg_replace('/'.'(\d+)'.'/', '$1px', $layers[$extended_part]['positions']['left']);
								}
								else {
									$reflected_position = null;
								}
							}
							
							if(strpos($layer_name, 'pupil') === 0) {
								if($layer_name === 'pupil-left') {
									if($reflected_position) {
										echo '#'.$group_name.'__'.$extended_part.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' * {';
											echo 'transform: translate('.$position.');';
										echo '} '."\n";
										echo '#'.$group_name.'__'.$extended_part.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.str_replace('-left', '-right', $layer_name).' * {';
											echo 'transform: translate('.$reflected_position.');';
										echo '} '."\n";
									}
									else {
										echo '#'.$group_name.'__'.$extended_part.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' *,';
										echo '#'.$group_name.'__'.$extended_part.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.str_replace('-left', '-right', $layer_name).' * {';
											echo 'transform: translate('.$position.');';
										echo '} '."\n";
									}
								}
							}
							
							// Otherwise, set transform as normal
							else {
								echo '#'.$group_name.'__'.$extended_part.'--'.$position_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' * {';
									echo 'transform: translate('.$position.');';
								echo '} '."\n";
							}
							
						}
					}
				}
				
				if($layer["color_is_selectable"]) {
					foreach($layer["colors"] as $color_name => $color) {
						$color_value = is_array($color) ? $color["color"] : $color;
						
						echo '#'.$group_name.'__'.$layer_name.'--'.$color_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' * {';
							echo 'fill: '.$color_value.'; ';
							echo 'stroke: '.$color_value.';';
						echo '} '."\n";
						
						echo '#'.$group_name.'__'.$layer_name.'--'.$color_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$color_name.'] {';
							echo 'color:hsl(var(--text));';
						echo '} '."\n";
						
						echo '#'.$group_name.'__'.$layer_name.'--'.$color_name.':checked ~ .avatar__controls [for='.$group_name.'__'.$layer_name.'--'.$color_name.']::before {';
							echo 'clip-path:url(#symbol__checked);';
							echo 'opacity:1;';
						echo '} '."\n";
						
					}
				}
				elseif($layer['extends_color']) {
					foreach($layers[$layer['extends_color']]["colors"] as $color_name => $color) {
						$color_value = is_array($color) ? $color["color"] : $color;
						
						echo '#'.$group_name.'__'.$layer['extends_color'].'--'.$color_name.':checked ~ .avatar__column .'.$group_name.' .'.$group_name.'__'.$layer_name.' {';
							echo 'fill: '.$color_value.'; ';
							echo 'stroke: '.$color_value.';';
						echo '} '."\n";
					}
				}
			}
		}
	?>
</style>