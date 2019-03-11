<?php
	function style($style_urls) {
		if(!is_array($style_urls)) {
			$style_urls = [$style_urls];
		}
		
		foreach($style_urls as $style_url) {
			$style_url = strpos($style_url, "../") === 0 ? $style_url : "..".$style_url;
			
			if(file_exists($style_url)) {
				$GLOBALS["styles"][] = str_replace("../", "/", $style_url);
			}
		}
	}
	
	function display_styles($minimized = false) {
		$style_wrapper = '<link rel="stylesheet" href="*" media="none" onload="if(media!=\'all\')media=\'all\'"><noscript><link rel="stylesheet" href="*"></noscript>';
		
		$minimizer_stem = "/style/min/?b=style&amp;f=";
		
		if(is_array($GLOBALS["styles"])) {
			$GLOBALS['styles'] = array_unique($GLOBALS['styles']);
			
			foreach($GLOBALS["styles"] as $style) {
				$style = $style."?".date("Ymd", filemtime("..".$style));
				echo str_replace("*", ($minimized ? $minimizer_stem : "").$style, $style_wrapper)."\n";
			}
		}
	}
?>