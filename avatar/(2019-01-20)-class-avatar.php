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
					$layer_name = $key[0];
					$part_name = $key[1];
					
					if(substr($part_name, -6) === "-color") {
						$part_name = substr($part_name, 0, -6);
						$is_color = true;
					}
					
					if($is_color) {
						$reconstructed_avatar_options[$layer_name][$part_name]["colors"][$selected_option] = "";
					}
					else {
						$reconstructed_avatar_options[$layer_name][$part_name]["shapes"][$selected_option] = "";
					}
					
					unset($is_color);
				}
			}
			
			return $reconstructed_avatar_options;
		}
		
		private function validate_selected_options($possible_avatar_layers, $filter_layers) {
			if(is_array($filter_layers) && !empty($filter_layers)) {
				foreach($filter_layers as $layer_name => $parts) {
					foreach($parts as $part_name => $part_attributes) {
						foreach($part_attributes as $attribute_name => $attribute) {
							if($attribute_name === "shapes" || $attribute_name === "colors") {
								foreach($attribute as $selection_name => $selection) {
									$possible_selection = $possible_avatar_layers[$layer_name][$part_name][$attribute_name][$selection_name];
									
									if(
										is_array($possible_selection)
										&&
										$possible_selection["is_vip"]
										&&
										!$this->options["is_vip"]
									) {
										unset($filter_layers[$layer_name][$part_name][$attribute_name][$selection_name]);
									}
								}
							}
						}
					}
				}
			}
			
			return $filter_layers;
		}
		
		private function filter_options($possible_avatar_layers, $filter_layers) {
			if(is_array($filter_layers) && !empty($filter_layers) && is_array($possible_avatar_layers)) {
				foreach($possible_avatar_layers as $layer_name => $parts) {
					foreach($parts as $part_name => $part_attributes) {
						
						foreach($part_attributes as $attribute_name => $attribute) {
							if($attribute_name === "shapes" || $attribute_name === "colors") {
								foreach($attribute as $selection_name => $selection) {
									$extends_part = $possible_avatar_layers[$layer_name][$part_name]["extends_part"];
									
									if($attribute_name === "shapes") {
										if($extends_part) {
											if(isset($filter_layers[$layer_name][$extends_part][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
										else {
											if(isset($filter_layers[$layer_name][$part_name][$attribute_name][$selection_name])) {
												$keep_selection = true;
											}
										}
									}
									elseif($attribute_name === "colors") {
										if(isset($filter_layers[$layer_name][$part_name][$attribute_name][$selection_name])) {
											$keep_selection = true;
										}
									}
									
									if(!$keep_selection) {
										unset($possible_avatar_layers[$layer_name][$part_name][$attribute_name][$selection_name]);
									}
									
									unset($keep_selection);
								}
							}
						}
						
					}
				}
			}
			
			return $possible_avatar_layers;
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
		
		public  function get_avatar_paths() {
			if(is_array($this->filtered_avatar_options) && !empty($this->filtered_avatar_options)) {
				
				foreach($this->filtered_avatar_options as $layer_name => $parts) {
					$num_layers = $parts["attributes"]["is_mirrored"] ? 2 : 1;
					
					for($i=0; $i<$num_layers; $i++) {
						$class = "{$layer_name} avatar--".($i ? "right" : "left");
						
						$g_elem = '';
						$g_elem_start = '<g class="'.$class.'" '.($i ? 'transform="scale(-1,1) translate(-600,0)"' : null).' >';
						
						foreach($parts as $part_name => $part_attributes) {
							if($part_name !== "attributes") {
								
								ob_start();
								foreach($part_attributes["shapes"] as $shape_name => $shape) {
									if(is_array($shape)) {
										if(!$part_attributes["extends_part"] || isset($this->filtered_avatar_options[$layer_name][$part_attributes["extends_part"]]["shapes"][$shape_name])) {
											$class  = "{$layer_name}__{$part_name} ";
											$class .= $layer_name."__".($part_attributes["extends_part"] ?: $part_name)."--".$shape_name;
											
											$transform = $shape["transform"] ?: null;
											$clip_path = ($shape["clip_path"] ? 'url(#'.$shape["clip_path"].')' : null);
											
											$color = $shape["fill"] ?: $this->filtered_avatar_options[$layer_name][$part_name]["colors"];
											$color = is_array($color) ? reset($color) : ($color ?: null);
											$color = is_array($color) ? $color["color"] : $color;
											
											if($shape["custom"]) {
												echo '<'.$shape["custom"].' class="'.$class.' '.$shape["class"].'" clip-path="'.$clip_path.'" '.(strpos($shape["custom"], "fill=") === false ? 'fill="'.$color.'"' : null).' />';
											}
											else {
												?>
													<path class="<?php echo $class.' '.$shape["class"]; ?>" clip-path="<?php echo $clip_path; ?>" d="<?php echo $shape["path"]; ?>" fill="<?php echo $color; ?>" transform="<?php echo $transform; ?>" />
												<?php
											}
										}
									}
								}
								$tmp_elem = ob_get_clean();
								
								$after_layer = strlen($part_attributes['after_layer']) ? $part_attributes['after_layer'] : null;
								
								if($after_layer) {
									$queued_g_elems[] = ['elem' => $g_elem_start.$tmp_elem.'</g>', 'after_layer' => $after_layer];
								}
								else {
									$g_elem .= $tmp_elem;
								}
							}
						}
						
						$g_elem = $g_elem_start.$g_elem.'</g>';
						$g_elems[$layer_name][] = $g_elem;
					}
				}
				
				if(is_array($g_elems) && !empty($g_elems) && is_array($queued_g_elems) && !empty($queued_g_elems)) {
					foreach($queued_g_elems as $g_elem) {
						$layer_name = $g_elem['after_layer'];
						$g_elems[$layer_name][] = $g_elem['elem'];
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
	}
?>