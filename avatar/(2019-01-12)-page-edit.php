<?php
	include_once("../avatar/class-avatar.php");
	if($_SESSION['username'] === 'inartistic') {
		
	}
else {
	include_once("../avatar/avatar-options.php");
}
	include_once("../avatar/avatar-definitions.php");
	
	style([
		"/avatar/style-page-edit.css",
	]);
	
	script([
		"/avatar/script-page-edit.js",
	]);
?>

<form action="/avatar/function-edit.php" class="col c2" method="post" name="form__avatar">
	<div class="avatar__column any--margin">
		<h2>
			Edit avatar
		</h2>
		<div class="text avatar__container">
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
			
			<svg version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="600px" height="600px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
				<?php
					$avatar = new avatar($avatar_layers);
					echo $avatar->get_avatar_paths();
				?>
			</svg>
		</div>
		<div class="any--flex" style="margin-top: 0.5rem;">
			<button class="any--flex-grow" type="submit">
				Save avatar
			</button>
			<span data-role="status"></span>
		</div>
		<div class="text text--outlined text--notice avatar__result any--hidden" data-role="result"></div>
	</div>
	
	<div class="controls__column avatar__controls">
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
														<label class="input__label"><?php echo $part_attributes["description"]; ?> shape</label>
														<?php
															foreach($part_attributes["shapes"] as $shape_name => $shape) {
																$is_checked = (isset($current_avatar[$layer_name][$part_name]["shapes"][$shape_name]) ? true : (!$current_avatar[$layer_name][$part_name]["shapes"] && $shape_name === array_keys($part_attributes["shapes"])[0] ? true : false));
																$class = "input__checkbox-label ".(is_array($shape) && $shape["is_vip"] ? "symbol__unchecked avatar--vip" : "symbol__unchecked")." ".($is_checked ? "input__checkbox-label--selected symbol__checked" : null);
																$for = "{$layer_name}__{$part_name}--{$shape_name}";
																?>
																	<label class="<?php echo $class; ?>" for="<?php echo $for; ?>"><?php echo $shape_name; ?></label>
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
																$is_checked = (isset($current_avatar[$layer_name][$part_name]["colors"][$color_name]) ? true : (!$current_avatar[$layer_name][$part_name]["colors"] && $color_name === array_keys($part_attributes["colors"])[0] ? true : false));
																$class = "input__checkbox-label ".(is_array($color) && $color["is_vip"] ? "symbol__unchecked avatar--vip" : "symbol__unchecked")." ".($is_checked ? "input__checkbox-label--selected symbol__checked" : null);
																$for = "{$layer_name}__{$part_name}--{$color_name}";
																?>
																	<label class="<?php echo $class; ?>" for="<?php echo $for; ?>"><?php echo $color_name; ?></label>
																<?php
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
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ svg .'.$layer_name.' :not(.'.$layer_name.'__'.$part_name.'--'.$shape_name.') {';
							echo 'display:none;';
							echo '} '."\n";
						}
					}
					if(is_array($part_attributes["vip_shapes"])) {
						foreach($part_attributes["vip_shapes"] as $shape_name => $shape_path) {
							echo '#'.$layer_name.'__'.$part_name.'--'.$shape_name.':checked ~ svg .'.$layer_name.' :not(.'.$layer_name.'__'.$part_name.'--'.$shape_name.') {';
							echo 'display:none;';
							echo '} '."\n";
						}
					}
				}
				
				if($part_attributes["color_is_selectable"]) {
					foreach($part_attributes["colors"] as $color_name => $color) {
						$color_value = is_array($color) ? $color["color"] : $color;
						
						echo '#'.$layer_name.'__'.$part_name.'--'.$color_name.':checked ~ svg .'.$layer_name.' .'.$layer_name.'__'.$part_name.' {';
						echo 'fill: '.$color_value.'; ';
						echo 'stroke: '.$color_value.';';
						echo '} '."\n";
					}
				}
			}
		}
	?>
</style>