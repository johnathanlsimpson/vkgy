<?php

// Take string of links, break into array, transform data
function format_artist_links($links_string) {
	$links = explode("\n", $links_string);
	$links = array_filter($links);
	
	foreach($links as $link_key => $link) {
		$url = trim($link);
		$domain = str_replace(['https://www.', 'http://www.', 'https://', 'http://'], '', $link);
		$links[$link_key] = [ 'url' => $url, 'domain' => $domain ];
	}
	
	return $links;
}