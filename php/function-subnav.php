<?php

// Take array of text/url pairs and add to navigation arrays
function subnav($input_array, $nav_type = 'section', $signed_in_only = false) {
	global $section_nav, $interact_nav, $directional_nav;

	if(!empty($input_array)) {
		foreach($input_array as $key => $value) {
			if(strlen($key) && !empty($value)) {
				if(is_array($value)) {
					$tmp_subnavs[] = [
						'text' => $value['text'],
						'url' => $value['url'],
						'position' => $value['position'],
						'signed_in_only' => $signed_in_only ? true : false,
					];
				}
				else {
					$tmp_subnavs[] = [
						'text' => $key,
						'url' => $value,
						'signed_in_only' => $signed_in_only ? true : false,
					];
				}
			}
		}
		
		
		if(is_array($tmp_subnavs) && !empty($tmp_subnavs)) {
			if($nav_type === 'section') {
				$section_nav = array_merge( ( is_array($section_nav) ? $section_nav : [] ), $tmp_subnavs );
			}
			elseif($nav_type === 'interact') {
				$interact_nav = array_merge( ( is_array($interact_nav) ? $interact_nav : [] ), $tmp_subnavs );
			}
			elseif($nav_type === 'directional') {
				$directional_nav = array_merge( ( is_array($directional_nav) ? $directional_nav : [] ), $tmp_subnavs );
			}
		}
	}
}