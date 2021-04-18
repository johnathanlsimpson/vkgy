<?php

include_once('../php/include.php');
include_once('../php/class-link.php');

$access_link = new link($pdo);

$allowed_actions = [ 'update', 'add', 'delete' ];

$action = friendly($_POST['action']);
$action = in_array( $action, $allowed_actions ) ? $action : 'update';

// =========================================================
// Add links
// =========================================================

if( $action === 'add' ) {
	
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
						$link_output = $access_link->add_link( $url, $artist_id );
						
						// Return link info if successful, otherwise return error message
						if( is_array($link_output) && !empty($link_output) && $link_output['status'] === 'success' ) {
							$output['links'][] = $link_output['link'];
							$output['status'] = 'success';
						}
						else {
							$output['result'] = $link_output['result'];
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
	
}

// =========================================================
// Delete link
// =========================================================

if( $action === 'delete' ) {
	
	if( $_SESSION['can_delete_data'] ) {
		
		$link_id = $_POST['link_id'];
		
		$delete_output = $access_link->delete_link( $link_id );
		
		if( is_array($delete_output) && !empty($delete_output) && $delete_output['status'] === 'success' ) {
			$output['status'] = 'success';
		}
		else {
			$output['result'] = $delete_output['result'];
		}
		
	}
	else {
		$output['result'] = 'Sorry, you don\'t have permission to delete links.';
	}
	
}

// =========================================================
// Update links
// =========================================================

if( $action === 'update' ) {
	
	if( $_SESSION['can_add_data'] ) {
		
		if( is_array($_POST['url_id']) && !empty($_POST['url_id']) ) {
			
			foreach( $_POST['url_id'] as $url_key => $id ) {
				
				// Get id
				$link['id'] = $id;
				
				// Get is_active; may or may not be sent so this one has a different key than the others
				$link['is_active'] = is_numeric($_POST['url_is_active'][$id]) ? 1 : 0;
				
				// Add rest of values to link
				foreach( [ 'content', 'type', 'musician_id' ] as $key ) {
					$link[ $key ] = $_POST[ 'url_'.$key ][ $url_key ];
				}
				
				// Try to update link
				$link_output = $access_link->update_link($link);
				
				// Make sure we have a url object
				if( is_array($link_output) && !empty($link_output) && $link_output['status'] === 'success' ) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = $link_output['result'];
				}
				
			}
			
		}
		else {
			$output['result'] = 'No urls were supplied.';
		}
		
	}
	else {
		$output['result'] = 'Sorry, you don\'t have permission to edit links.';
	}
	
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);