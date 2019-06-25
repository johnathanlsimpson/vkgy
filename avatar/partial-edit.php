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
		foreach($avatar_layers as $layer_name => $parts) {
			foreach($parts as $part_name => $part_attributes) {
				if($part_attributes["shape_is_selectable"]) {
					foreach($part_attributes["shapes"] as $shape_name => $shape_path) {
						$id = "{$layer_name}__{$part_name}--{$shape_name}";
						$name = "{$layer_name}__{$part_name}";
						$value = $shape_name;
						$checked = (isset($current_avatar[$layer_name][$part_name]["shapes"][$shape_name]) ? "checked" : (!$current_avatar[$layer_name][$part_name]["shapes"] && $shape_name === array_keys($part_attributes["shapes"])[0] ? "checked" : null));
						
						?>
							<input class="input__checkbox any--hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="<?php echo $value; ?>" <?php echo $checked; ?> />
						<?php
					}
				}
				
				if($part_attributes["color_is_selectable"]) {
					foreach($part_attributes["colors"] as $color_name => $color_value) {
						$id = "{$layer_name}__{$part_name}--{$color_name}";
						$name = "{$layer_name}__{$part_name}-color";
						$value = $color_name;
						$checked = (isset($current_avatar[$layer_name][$part_name]["colors"][$color_name]) ? "checked" : (!$current_avatar[$layer_name][$part_name]["colors"] && $color_name === array_keys($part_attributes["colors"])[0] ? "checked" : null));
						
						?>
							<input class="input__checkbox any--hidden" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="<?php echo $value; ?>" <?php echo $checked; ?> />
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
		</div>
		
		<!-- Error box -->
		<div class="text text--outlined text--notice avatar__result any--hidden" data-role="result"></div>
		
	</div>
	
	<!-- 'Edit avatar': right side -->
	<div class="controls__column avatar__controls">
		
		<div class="avatar__nav tertiary-nav__container any--flex any--margin">
			<?php
				foreach($avatar_layers as $layer_name => $layer) {
					if(is_array($layer) && !empty($layer)) {
					?>
						<label class="tertiary-nav__link <?php echo $layer_name === 'head' ? 'tertiary-nav--active' : null; ?> a--inherit a--padded" for="<?php echo 'avatar__show-'.$layer_name; ?>"><?php echo lang($layer_name, $layer['attributes']['ja'] ?: $layer_name, ['secondary_class' => 'any--hidden']); ?></label>
					<?php
					}
				}
			?>
		</div>
		
		<?php
			/* Form controls */
			foreach($avatar_layers as $layer_name => $layer_parts) {
				if(is_array($layer_parts) && !empty($layer_parts)) {
				if(!$layer_parts["attributes"]["is_hidden"]) {
					?>
						<input class="avatar__show any--hidden" form="fake-form" id="<?php echo 'avatar__show-'.$layer_name; ?>" name="avatar__show[]" type="radio" <?php echo $layer_name === 'head' ? 'checked' : null; ?> />
						<div class="avatar__group text text--outlined">
							<ul>
							<?php
								foreach($layer_parts as $part_name => $part_attributes) {
									if($part_attributes["shape_is_selectable"]) {
										?>
											<li class="avatar__shape">
												<div class="input__row">
													<div class="input__group">
														<?php
															if(is_array($part_attributes["description"])) {
																echo '<label class="input__label">';
																echo lang($part_attributes['description']['en'], $part_attributes['description']['ja'], [ 'secondary_class' => 'any--hidden' ]);
																echo '</label>';
															}
															
															foreach($part_attributes["shapes"] as $shape_name => $shape) {
																$is_checked = (isset($current_avatar[$layer_name][$part_name]["shapes"][$shape_name]) ? true : (!$current_avatar[$layer_name][$part_name]["shapes"] && $shape_name === array_keys($part_attributes["shapes"])[0] ? true : false));
																$class = "input__checkbox-label symbol__unchecked ".(is_array($shape) && $shape["is_vip"] ? "avatar--vip" : null);
																$for = "{$layer_name}__{$part_name}--{$shape_name}";
																
																$image_url = '/avatar/images/'.$layer_name.'-'.$part_name.'-'.$shape_name.'.gif';
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
									if($part_attributes["color_is_selectable"]) {
										?>
											<li class="avatar__color">
												<div class="input__row">
													<div class="input__group">
														<?php
															if(is_array($part_attributes["description"])) {
																echo '<label class="input__label">';
																echo lang($part_attributes['description']['en'].' color', $part_attributes['description']['ja'].'カラー', [ 'secondary_class' => 'any--hidden' ]);
																echo '</label>';
															}
															
															foreach($part_attributes["colors"] as $color_name => $color) {
																if($color) {
																	$is_checked = (isset($current_avatar[$layer_name][$part_name]["colors"][$color_name]) ? true : (!$current_avatar[$layer_name][$part_name]["colors"] && $color_name === array_keys($part_attributes["colors"])[0] ? true : false));
																	$class = "input__checkbox-label avatar__color-label symbol__unchecked ".(is_array($color) && $color["is_vip"] ? "avatar--vip" : null);
																	$for = "{$layer_name}__{$part_name}--{$color_name}";
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
		foreach($avatar_layers as $layer_name => $parts) {
			foreach($parts as $part_name => $part_attributes) {
				if($part_attributes["shape_is_selectable"]) {
					if(is_array($part_attributes["shapes"])) {
						foreach($part_attributes["shapes"] as $shape_name => $shape_path) {
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__column .'.$layer_name.' [class*='.$layer_name.'__'.$part_name.']:not(.'.$layer_name.'__'.$part_name.'--'.$shape_name.') {';
								echo 'display:none;';
							echo '} '."\n";
							
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$shape_name.'] {';
								echo 'color:hsl(var(--text));';
							echo '} '."\n";
							
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$shape_name.']::before {';
								echo 'clip-path:url(#symbol__checked);';
								echo 'opacity:1;';
							echo '} '."\n";
						}
					}
					if(is_array($part_attributes["vip_shapes"])) {
						foreach($part_attributes["vip_shapes"] as $shape_name => $shape_path) {
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__column .'.$layer_name.' [class*='.$layer_name.'__'.$part_name.']:not(.'.$layer_name.'__'.$part_name.'--'.$shape_name.') {';
								echo 'display:none;';
							echo '} '."\n";
							
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$shape_name.'] {';
								echo 'color:hsl(var(--text));';
							echo '} '."\n";
							
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$shape_name.']::before {';
								echo 'clip-path:url(#symbol__checked);';
								echo 'opacity:1;';
							echo '} '."\n";
						}
					}
				}
				
				if($part_attributes["color_is_selectable"]) {
					foreach($part_attributes["colors"] as $color_name => $color) {
						$color_value = is_array($color) ? $color["color"] : $color;
						
						echo '#'.$layer_name.'__'.$part_name.'--'.$color_name.':checked ~ .avatar__column .'.$layer_name.' .'.$layer_name.'__'.$part_name.' {';
							echo 'fill: '.$color_value.'; ';
							echo 'stroke: '.$color_value.';';
						echo '} '."\n";
						
						echo '#'.$layer_name.'__'.$part_name.'--'.$color_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$color_name.'] {';
							echo 'color:hsl(var(--text));';
						echo '} '."\n";
						
						echo '#'.$layer_name.'__'.$part_name.'--'.$color_name.':checked ~ .avatar__controls [for='.$layer_name.'__'.$part_name.'--'.$color_name.']::before {';
							echo 'clip-path:url(#symbol__checked);';
							echo 'opacity:1;';
						echo '} '."\n";
						
					}
				}
				elseif($part_attributes['extends_color']) {
					foreach($parts[$part_attributes['extends_color']]["colors"] as $color_name => $color) {
						$color_value = is_array($color) ? $color["color"] : $color;
						
						echo '#'.$layer_name.'__'.$part_attributes['extends_color'].'--'.$color_name.':checked ~ .avatar__column .'.$layer_name.' .'.$layer_name.'__'.$part_name.' {';
							echo 'fill: '.$color_value.'; ';
							echo 'stroke: '.$color_value.';';
						echo '} '."\n";
					}
				}
			}
		}
	?>
</style>

<script>
	var randButton = document.getElementsByClassName('avatar__random')[0];
	
	randButton.addEventListener('click', randomizeAvatar);
	
	function randomizeAvatar() {
		var radioElems = document.querySelectorAll('[name=form__avatar] .input__checkbox');
		var elemName;
		var prevName;
		
		for(var i=0; i<radioElems.length; i++) {
			elemName = radioElems[i].getAttribute('name');
			
			if(prevName != elemName) {
				selectedElems = document.querySelectorAll('[name=' + elemName + ']');
				randNum = Math.floor(Math.random() * Math.floor(selectedElems.length));
				
				for(var n=0; n<selectedElems.length; n++) {
					selectedElems[n].checked = false;
				}
				
				selectedElems[randNum].checked = true;
			}
			
			prevName = elemName;
		}
	}
</script>