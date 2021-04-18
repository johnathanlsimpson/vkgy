<?php

include_once('../php/include.php');
include_once('../php/class-link.php');

$link = new link($pdo);

$artist_id = $_POST['artist_id'];
$urls = $_POST['add_links'];

if( $_SESSION['can_add_data'] ) {
	
	if( is_numeric($artist_id) ) {
		
		if( strlen($urls) ) {
			
			$urls = str_replace(["\r\n", "\r"], "\n", $urls);
			$urls = explode("\n", $urls);
			$urls = array_unique(array_filter($urls));
			
			if( is_array($urls) && !empty($urls) ) {
				
				// Loop through links and add
				foreach($urls as $url) {
					
					// Use class to add each link
					$link = $link->add_link( $url, $artist_id );
					
					// Return link info if successful, otherwise return error message
					if( is_array($link) && !empty($link) && $link['status'] === 'success' ) {
						
						$output = $link;
						$output['status'] = 'success';
						
					}
					else {
						
						$output['result'] = $link['result'];
						$output['status'] = 'error';
						
					}
					
				}
				
			}
			else {
				$output['result'] = 'No appropriate links found.';
			}
			
		}
		else {
			$output['result'] = 'No links supplied.';
		}
		
	}
	else {
		$output['result'] = 'No artist supplied.';
	}
	
}
else {
	$output['result'] = 'Sorry, you don\'t have permission to add links.';
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);