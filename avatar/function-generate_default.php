<?php
include_once("../avatar/class-avatar.php");
include_once("../avatar/avatar-definitions.php");

$current_avatar = [
	'lips' => [
		'base' => [
			'shapes' => [ 'classic' => '' ],
			'colors' => [ 'nude' => '' ],
		],
	],
	'hair' => [
		'left' => [
			'shapes' => [ 'classic' => '' ],
			'colors' => [ 'brown-medium' => '' ],
		],
		'right' => [
			'shapes' => [ 'classic' => '' ],
			'colors' => [ 'brown-medium' => '' ],
		],
	],
	'bangs' => [
		'base' => [
			'shapes' => [ 'split' => '' ],
			'colors' => [ 'brown-medium' => '' ],
		],
	],
	'eyebrow' => [
		'shape' => [
			'shapes' => [ 'stub' => '' ],
			'colors' => [ 'brown-medium' => '' ],
		],
	],
	'eye' => [
		'shape' => [
			'shapes' => [ ($_POST["register_avatar"] === 'gecko' ? 'gecko' : 'bat') => '' ],
		],
		'accent' => [
			'colors' => [ 'red-medium' => '' ],
		],
	],
];

foreach($avatar_layers as $layer_name => $parts) {
	foreach($parts as $part_name => $part_attributes) {
		if($part_attributes["shape_is_selectable"]) {
			foreach($part_attributes["shapes"] as $shape_name => $shape_path) {
				$id = "{$layer_name}__{$part_name}--{$shape_name}";
				$name = "{$layer_name}__{$part_name}";
				$value = $shape_name;
				$checked = (isset($current_avatar[$layer_name][$part_name]["shapes"][$shape_name]) ? "checked" : (!$current_avatar[$layer_name][$part_name]["shapes"] && $shape_name === array_keys($part_attributes["shapes"])[0] ? "checked" : null));
				
				if($checked) {
					$avatar_result[] = '"'.$layer_name.'__'.$part_name.'":"'.$shape_name.'"';
				}
			}
		}

		if($part_attributes["color_is_selectable"]) {
			foreach($part_attributes["colors"] as $color_name => $color_value) {
				$id = "{$layer_name}__{$part_name}--{$color_name}";
				$name = "{$layer_name}__{$part_name}-color";
				$value = $color_name;
				$checked = (isset($current_avatar[$layer_name][$part_name]["colors"][$color_name]) ? "checked" : (!$current_avatar[$layer_name][$part_name]["colors"] && $color_name === array_keys($part_attributes["colors"])[0] ? "checked" : null));
				
				if($checked) {
					$avatar_result[] = '"'.$layer_name.'__'.$part_name.'-color":"'.$color_name.'"';
				}
			}
		}
	}
}

$avatar_options = '{'.implode(',', $avatar_result).'}';

include('../avatar/function-edit.php');