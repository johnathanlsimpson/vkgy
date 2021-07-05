<?php

// ========================================================
// Page setup
// ========================================================

// Artist view
if( is_array($artist) && !empty($artist) ) {
	
	include('../artists/head.php');
	
	subnav([
		lang('Add song', '曲を追加', 'hidden') => '/songs/add/'.$artist['friendly'].'/',
	], 'interact', true);
	
	if( is_array($song) && !empty($song) ) {
		
		if( $action == 'edit' ) {
			
			$active_page = '/songs/'.$artist['friendly'].'/';
			
		}
		else {
			
			subnav([
				lang('Edit song', '曲を追加', 'hidden') => $song['url'].'edit/',
			], 'interact', true);
			
		}
		
	}
	
}

else {
	
	subnav([
		lang('Add song', '曲を追加', 'hidden') => '/songs/add/',
	], 'interact', true);
	
}