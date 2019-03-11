<?php
	function clean_values(&$value, $key) {
		$value = sanitize($value);
		
		foreach([
			"&#92;("     => "\\(",
			"&#92;)"     => "\\)",
			"\'"         => "&#39;",
			"&#92;&#39;" => "&#39;",
			"'"          => "&#39;",
			"&#65374;"   => "~",
			"&#65378;"   => "&#12300;",
			"&#65379;"   => "&#12301;",
			"&#12288;"   => " ",
		] as $search => $replace) {
			$value = str_replace($search, $replace, $value);
		}
		
		$value = trim($value);
		$value = preg_replace('/'.'\s+'.'/', ' ', $value);
		$value = strlen($value) > 0 ? $value : null;
	}
?>