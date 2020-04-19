<?php
	function script($script_urls, $insert_location = "bottom") {
		if(!is_array($script_urls)) {
			$script_urls = [$script_urls];
		}
		
		foreach($script_urls as $script_url) {
			$script_url = strpos($script_url, "../") === 0 ? $script_url : "..".$script_url;
			
			if(file_exists($script_url) && in_array($insert_location, ["top", "bottom"])) {
				$GLOBALS["scripts"][] = [
					"location" => $insert_location,
					"url" => str_replace("../", "/", $script_url)
				];
			}
		}
	}
	
	function display_scripts($insert_location, $minimized = false) {
		$script_wrapper = '<script defer language="javascript" src="*"></script>';
		$minimizer_stem = "/scripts/min/?b=scripts&amp;f=";
		
		if(is_array($GLOBALS["scripts"]) && in_array($insert_location, ["top", "bottom"])) {
			foreach($GLOBALS["scripts"] as $script) {
				if($script["location"] === $insert_location) {
					echo str_replace("*", ($minimized ? $minimizer_stem : "").$script["url"]."?".date('YmdHis', filemtime("..".$script["url"])), $script_wrapper)."\n";
				}
			}
		}
	}
?>