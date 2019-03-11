<?php
	function breadcrumbs($input_array) {
		global $breadcrumbs;
		if(!empty($input_array)) {
			foreach($input_array as $key => $value) {
				if(!empty($key) && !empty($value)) {
					$breadcrumbs[] = [
						"text" => $key,
						"url" => $value
					];
				}
			}
		}
	}
?>