<?php

// Take string of links, break into array, transform data
function format_artist_urls($urls) {
	
	if(is_array($urls) && !empty($urls)) {
		foreach($urls as $url_key => $url) {
			
			// Remove protocol
			$url['display_name'] = preg_replace('/'.'^((?:https?:)?(?:\/\/)?(?:www\.)?)'.'/', '', $url['content']);
			
			// Remove trailing slash
			$url['display_name'] = preg_replace('/'.'(\/)$'.'/', '', $url['display_name']);
			
			// Set platform
			foreach(['youtube', 'twitter', 'instagram'] as $platform) {
				if(strpos($url['content'], $platform) !== false) {
					
					// If platform, only need username
					$url['display_name'] = str_replace($platform.'.com/', '', $url['display_name']);
					
					// Set platform
					$urls[$url_key]['platform'] = $platform;
					
					break;
				}
			}
			
			// Add display name
			$urls[$url_key]['display_name'] = $url['display_name'];
		}
	}
	
	return $urls;
	
}