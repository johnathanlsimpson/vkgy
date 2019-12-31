<?php
	class avatar {
		private $all_avatar_options;
		private $selected_avatar_options;
		private $filtered_avatar_options;
		private $options;
		
		function __construct($input_all_options, $input_selected_options = null, $options = null) {
			$this->options = $options;
			$this->all_avatar_options = $input_all_options;
			$this->selected_avatar_options = $this->format_selected_options($input_selected_options);
			$this->selected_avatar_options = $this->validate_selected_options($this->all_avatar_options, $this->selected_avatar_options);
			$this->filtered_avatar_options = $this->filter_options($this->all_avatar_options, $this->selected_avatar_options);
		}
		
		// Takes a JSON list of avatar selections, transforms to array, and cleans up the list
		private function format_selected_options($input) {
			if(!is_array($input)) {
				if(substr($input, 0, 1) === "{") {
					$current_avatar_options = json_decode($input, true);
				}
			}
			else {
				$current_avatar_options = $input;
			}
			
			if(is_array($current_avatar_options) && !empty($current_avatar_options)) {
				foreach($current_avatar_options as $key => $selected_option) {
					$key = explode("__", $key);
					$group_name = $key[0];
					$layer_name = $key[1];
					
					if(substr($layer_name, -6) === "-color") {
						$layer_name = substr($layer_name, 0, -6);
						$is_color = true;
					}
					elseif(substr($layer_name, -9) === "-position") {
						$layer_name = substr($layer_name, 0, -9);
						$is_position = true;
					}
					
					if($is_color) {
						$reconstructed_avatar_options[$group_name][$layer_name]["colors"][$selected_option] = "";
					}
					elseif($is_position) {
						$reconstructed_avatar_options[$group_name][$layer_name]['positions'][$selected_option] = '';
					}
					else {
						$reconstructed_avatar_options[$group_name][$layer_name]["shapes"][$selected_option] = "";
					}
					
					unset($is_color, $is_position);
				}
			}
			
			return $reconstructed_avatar_options;
		}
		
		// Takes an array of cleaned avatar options and verifies them (e.g. if someone gets a VIP part in the DB, but is non-VIP, remove it from rendering)
		private function validate_selected_options($all_groups, $filtered_groups) {
			if(is_array($filtered_groups) && !empty($filtered_groups)) {
				foreach($filtered_groups as $group_name => $layers) {
						
					foreach($layers as $layer_name => $layer) {
						
						
						foreach($layer as $attribute_name => $attribute) {
							if($attribute_name === "shapes" || $attribute_name === "colors" || $attribute_name === 'positions') {
								foreach($attribute as $selection_name => $selection) {
									$possible_selection = $all_groups[$group_name][$layer_name][$attribute_name][$selection_name];
									
									if(
										is_array($possible_selection)
										&&
										$possible_selection["is_vip"]
										&&
										!$this->options["is_vip"]
									) {
										unset($filtered_groups[$group_name][$layer_name][$attribute_name][$selection_name]);
									}
								}
							}
						}
					}
				}
			}
			
			// Make sure eye directions match
			if($filtered_groups['eye']['iris-left']['positions']) {
				$left_position = reset(array_keys($filtered_groups['eye']['iris-left']['positions']));
				$right_position = $left_position;
				
				$filtered_groups['eye']['iris-right']['positions'] = [ $right_position => '' ];
			}
			
			//echo '<pre>'.print_r($filtered_groups, true).'</pre>';
			
			return $filtered_groups;
		}
		
		// Given verified list of avatar options, pull those options from an SVG containing all options
		private function filter_options($all_groups, $filtered_groups) {
			if(is_array($filtered_groups) && !empty($filtered_groups) && is_array($all_groups)) {
				foreach($all_groups as $group_name => $layers) {
					foreach($layers as $layer_name => $layer) {
						
						foreach($layer as $attribute_name => $attribute) {
							if($attribute_name === "shapes" || $attribute_name === "colors" || $attribute_name === 'positions') {
								foreach($attribute as $selection_name => $selection) {
									$extends_part = $all_groups[$group_name][$layer_name]["extends_part"];
									
									if($attribute_name === "shapes") {
										if($extends_part) {
											if(isset($filtered_groups[$group_name][$extends_part][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
										else {
											if(isset($filtered_groups[$group_name][$layer_name][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
									}
									elseif($attribute_name === "colors") {
										if(isset($filtered_groups[$group_name][$layer_name][$attribute_name][$selection_name])) {
											$keep_selection = true;
										}
									}
									elseif($attribute_name === 'positions') {
										if($extends_part) {
											if(isset($filtered_groups[$group_name][$extends_part][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
										else {
											if(isset($filtered_groups[$group_name][$layer_name][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
									}
									
									if(!$keep_selection) {
										unset($all_groups[$group_name][$layer_name][$attribute_name][$selection_name]);
									}
									
									unset($keep_selection);
								}
							}
						}
						
					}
				}
			}
			
			//echo '<pre>'.print_r($all_groups, true).'</pre>';
			
			return $all_groups;
		}
		
		public  function get_avatar_paths($show_fallbacks = false) {
			if(is_array($this->filtered_avatar_options) && !empty($this->filtered_avatar_options)) {
				
				foreach($this->filtered_avatar_options as $group_name => $layers) {
					$num_layers = $layers["attributes"]["is_mirrored"] ? 2 : 1;
					
					for($i=0; $i<$num_layers; $i++) {
						$class = "{$group_name} avatar--".($i ? "right" : "left");
						
						$g_elem = '';
						$g_elem_start = '<g class="'.$class.'" '.($i ? 'transform="scale(-1,1) translate(-600,0)"' : null).' >';
						
						foreach($layers as $layer_name => $layer) {
							if($layer_name !== "attributes") {
								
								ob_start();
								foreach($layer["shapes"] as $shape_option_name => $shape_option) {
									if(is_array($shape_option)) {
										if(!$layer["extends_part"] || isset($this->filtered_avatar_options[$group_name][$layer["extends_part"]]["shapes"][$shape_option_name])) {
											$class  = "{$group_name}__{$layer_name} ";
											$class .= $group_name."__".($layer["extends_part"] ?: $layer_name)."--".$shape_option_name;
											$class .= $layer['attributes']['is_mirror'] ? ' avatar--right' : null;
											$g_class = "{$group_name}__{$layer_name} ";
											
											$g_transform = ($shape_option["transform"] ?: null).($layer['attributes']['is_mirror'] ? ' scale(-1,1) translate(-600,0)' : null);
											//$clip_path = ($shape_option["clip_path"] ? 'url(#'.$shape_option["clip_path"].')' : null);
											
											// Movement
											$position = $this->filtered_avatar_options[$group_name][$layer_name]['positions'];
											$position = is_array($position) ? reset($position) : null;
											$path_transform = $position ? ' translate('.$position.') ' : null;
											
											// If clipped by another layer, apply that layer's clip path
											if($layer['attributes']['clipped_by']) {
												list($clipping_group, $clipping_layer) = explode('__', $layer['attributes']['clipped_by']);
												$clipping_shape_option = reset( array_keys( $this->filtered_avatar_options[$clipping_group][$clipping_layer]['shapes'] ) );
												$g_clip_path = $layer['attributes']['clipped_by'].'--'.$clipping_shape_option;
												$g_clip_path = 'url(#'.$g_clip_path.'.path)';
											}
											else {
												$g_clip_path = null;
											}
											
											// If layer has fill setting, use that; or if has colors selection, use those; or if extends another layer, use that layer's colors
											// Then make sure that we're choosing first available color, rather than array
											$color = $shape_option['fill'] ?: $this->filtered_avatar_options[$group_name][$layer_name]['colors'] ?: $this->filtered_avatar_options[$group_name][$layer['extends_part']]['colors'];
											$color = is_array($color) ? reset($color) : ($color ?: null);
											$color = is_array($color) ? $color['color'] : $color;
											
											// Set fill using color found above (unless custom path is used, which may have fill baked in)
											$fill = !$shape_option['custom'] || strpos($shape_option['custom'], 'fill=') === 0 ? 'fill="'.$color.'"' : null;
											//$fill = $shape_strpos($shape_option['custom'], 'fill=') === false ? ''
											
											// If layer does not have any colors set, but extends another layer, grab colors from other layer
											//if($layer['extends_part'] && !isset($this->filtered_avatar_options[$group_name][$layer_name]["colors"])) {
												//$color = $shape_option["fill"] ?: $this->filtered_avatar_options[$group_name][$layer['extends_part']]["colors"].'YYYY';
											//}
											//else {
												//$color = $shape_option["fill"] ?: $this->filtered_avatar_options[$group_name][$layer_name]["colors"].'ZZZZ';
											//}
											//$color = is_array($color) ? reset($color) : ($color ?: null);
											//$fill  = $show_fallbacks && is_array($color) && $color['fallback'] ? 'fill="'.$color['fallback'].'"' : null;
											//$color = is_array($color) ? $color["color"] : $color;
											//$fill  = $fill ?: (strpos($shape_option["custom"], "fill=") === false ? 'fill="'.$color.'"' : null);
											
											// Set stroke if specified, otherwise make sure there's none
											$stroke = $shape_option['stroke'] ? 'stroke="'.$shape_option['stroke'].'" stroke-width="2"' : 'stroke="none"';
											
											if($shape_option["custom"]) {
												echo '<'.$shape_option["custom"].' class="'.$class.' '.$shape_option["class"].'" clip-path="'.$clip_path.'" '.$fill.' />';
											}
											else {
												?>
													<g class="<?= $class; ?>" clip-path="<?= $g_clip_path; ?>" transform="<?= $g_transform; ?>">
														<path class="<?= $shape_option["class"]; ?>" d="<?= $shape_option['path']; ?>" <?= $fill; ?> <?= $stroke; ?> transform="<?= $path_transform; ?>" />
													</g>
												<?php
											}
										}
									}
								}
								$tmp_elem = ob_get_clean();
								
								$after_layer = strlen($layer['after_layer']) ? $layer['after_layer'] : null;
								
								if($after_layer) {
									$queued_g_elems[] = ['elem' => $g_elem_start.$tmp_elem.'</g>', 'after_layer' => $after_layer];
								}
								else {
									$g_elem .= $tmp_elem;
								}
							}
						}
						
						$g_elem = $g_elem_start.$g_elem.'</g>';
						$g_elems[$group_name][] = $g_elem;
					}
				}
				
				if(is_array($g_elems) && !empty($g_elems) && is_array($queued_g_elems) && !empty($queued_g_elems)) {
					foreach($queued_g_elems as $g_elem) {
						$group_name = $g_elem['after_layer'];
						$g_elems[$group_name][] = $g_elem['elem'];
					}
				}
				
				if(is_array($g_elems) && !empty($g_elems)) {
					foreach($g_elems as $layer) {
						foreach($layer as $elem) {
							$output .= $elem;
						}
					}
				}
			}
			
			return($output);
		}
		
		public  function get_all_options() {
			return $this->all_avatar_options;
		}
		
		public  function get_selected_options() {
			return $this->selected_avatar_options;
		}
		
		public  function get_filtered_options() {
			return $this->filtered_avatar_options;
		}
	}
?>