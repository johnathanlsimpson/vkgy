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
	
	<div class="avatar__column any--margin">
		<div class="text avatar__container">
			<svg version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="600px" height="600px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
				<?php
					$avatar = new avatar($avatar_layers);
					echo $avatar->get_avatar_paths();
				?>
			</svg>
		</div>
		<div class="any--flex" style="margin-top: 0.5rem;">
			<button class="any--flex-grow" type="submit">
				<?php echo lang('Save', '保存する', ['secondary_class' => 'any--hidden']); ?>
			</button>
			<span data-role="status"></span>
			<button class="symbol__random avatar__random" style="margin-left: 0.5rem;" type="button"><?php echo lang('Random', 'ランダム', ['secondary_class' => 'any--hidden']); ?></button>
		</div>
		<div class="text text--outlined text--notice avatar__result any--hidden" data-role="result"></div>
	</div>
	
	<div class="controls__column avatar__controls">
		
		<div class="tertiary-nav__container any--flex" style="background-color:hsl(var(--background--alt)); margin:2rem 0;">
			<a class="tertiary-nav__link tertiary-nav--active a--inherit a--padded" style="background-color:hsl(var(--background--alt));"><?php echo lang('Head', '頭', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Makeup', 'メイク', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Mouth', '口', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Jewelry', '宝飾', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Hairstyle', '髪型', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Bangs', '前髪', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Eyeshadow', 'アイシャドー', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Eyebrow', '眉', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Eye', '目', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Mask', 'マスク', ['secondary_class' => 'any--hidden']); ?></a>
			<a class="tertiary-nav__link a--inherit a--padded"><?php echo lang('Hat', '帽子', ['secondary_class' => 'any--hidden']); ?></a>
		</div>
		
		<?php
			/* Form controls */
			foreach($avatar_layers as $layer_name => $layer_parts) {
				if(!$layer_parts["attributes"]["is_hidden"]) {
					?>
						<h3 style="text-transform: capitalize;">
							<?php echo $layer_name; ?>
						</h3>
						<div class="text text--outlined">
							<ul>
							<?php
								foreach($layer_parts as $part_name => $part_attributes) {
									if($part_attributes["shape_is_selectable"]) {
										?>
											<li>
												<div class="input__row">
													<div class="input__group">
														<label class="input__label" style="height: calc(2rem + 50px);"><?php echo $part_attributes["description"]; ?> shape</label>
														<?php
															foreach($part_attributes["shapes"] as $shape_name => $shape) {
																$is_checked = (isset($current_avatar[$layer_name][$part_name]["shapes"][$shape_name]) ? true : (!$current_avatar[$layer_name][$part_name]["shapes"] && $shape_name === array_keys($part_attributes["shapes"])[0] ? true : false));
																$class = "input__checkbox-label symbol__unchecked ".(is_array($shape) && $shape["is_vip"] ? "avatar--vip" : null);
																$for = "{$layer_name}__{$part_name}--{$shape_name}";
																?>
																	<label class="<?php echo $class; ?>" for="<?php echo $for; ?>" style="height: 50px;width: 50px;">
																		
																		<svg version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
																			<?php
																				//$avatar = new avatar($avatar_layers);
																				//echo $avatar->get_avatar_paths();
																			?>
																			<?php
																				if(is_array($avatar_layers[$layer_name][$part_name]['shapes'][$shape_name])) {
																					echo '<path fill="black" d="'.$avatar_layers[$layer_name][$part_name]['shapes'][$shape_name]['path'].'"></path>';
																				}
																			?>
																		</svg>
																	</label>
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
											<li>
												<div class="input__row">
													<div class="input__group">
														<label class="input__label"><?php echo $part_attributes["description"]; ?> color</label>
														<?php
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
																	echo '<div style="width: 100%; flex-shrink: 1; height: 1px;"></div>';
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