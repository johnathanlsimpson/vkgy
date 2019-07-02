<?php
	function standardize($input) {
		$input = mb_convert_kana($input, "aKV", "UTF-8");
		$input = str_replace(["˜", "∼", "～", "〜"], "~", $input);
		$input = str_replace("…", "・・・", $input);
		$input = str_replace(['　', "\t"], ' ', $input);
		$input = trim($input, " \t\0\x0B");
		
		/*if(substr_count($input, '"') === 2) {
			$input = preg_replace('/'.'"'.'/', '“', $input, 1);
			$input = preg_replace('/'.'"'.'/', '”', $input, 1);
		}
		
		if(substr_count($input, '\'') === 2) {
			$input = preg_replace('/'.'\''.'/', '‘', $input, 1);
			$input = preg_replace('/'.'\''.'/', '’', $input, 1);
		}*/
		
		return $input;
	}
	
	function sanitize($input, $modifier = NULL) {
		$input = standardize($input);
		
		$search = array("?", "\\", "<", ">", "\"", "'");
		$replace = array("&#63;", "&#92;", "&#60;", "&#62;", "&#34;", "&#39;");
		
		if($modifier != "allowhtml") {
			$input = str_replace($search, $replace, $input);
		}
		
		$input = mb_convert_encoding($input, "HTML-ENTITIES", "UTF-8");
		
		if($modifier != "allowslash") {
			if(@mysqli_ping()) {
				$input = mysqli_real_escape_string($GLOBALS["mysqli"], $input);
			}
		}
		
		return($input);
	}
?>