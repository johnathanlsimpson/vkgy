<?php
	function subnav($input_array, $signed_in_only = false) {
		global $subnavs;
		if(!empty($input_array)) {
			foreach($input_array as $key => $value) {
				if(!empty($key) && !empty($value)) {
					$tmp_subnavs[] = [
						"text" => $key,
						"url" => $value,
						"signed_in_only" => $signed_in_only ? true : false
					];
				}
			}
			
			if(is_array($tmp_subnavs) && !empty($tmp_subnavs)) {
				$subnavs[] = $tmp_subnavs;
			}
		}
	}
?>