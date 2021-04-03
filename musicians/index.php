<?php

// ============================================
// Setup
// ============================================

include_once('../php/class-tag.php');

$access_musician = new access_musician($pdo);
$access_image = new access_image($pdo);
$access_tag = new tag($pdo);

$musician_id = is_numeric($_GET['id']) ? $_GET['id'] : null;
$musician_is_removed = false;

$action = sanitize($_GET['action']);
$action = in_array( $action, [ 'add', 'edit' ] ) ? $action : null;

include_once('../musicians/head.php');

// ============================================
// Get data
// ============================================

// Get musician data
if( is_numeric($musician_id) ) {
	
	// Main data
	$musician = $access_musician->access_musician([ 'id' => $musician_id, 'get' => 'all' ]);
	
	// Additional data
	if( is_array($musician) && !empty($musician) ) {
		
		// Get tags
		$tags = $access_tag->access_tag([ 'item_type' => 'musician', 'item_id' => $musician_id, 'get' => 'all', 'separate' => true ]);
		
		// Loop through tags and set some flags
		if( is_array($tags) && !empty($tags) && is_array($tags['tagged']) ) {
			foreach($tags['tagged'] as $tag_type => $tagged_tags) {
				foreach($tagged_tags as $tag) {
					
					// Set flags
					if($tag['friendly'] === 'exclusive') {
						$musician_is_exclusive = true;
					}
					else if($tag['friendly'] === 'removed') {
						$musician_is_removed = true;
					}
					
				}
			}
		}
		
		// Get musician's images
		$images = $access_image->access_image([ 'musician_id' => $musician_id, 'get' => 'all' ]);
		
	}
	
}

// ============================================
// Decide which template to display and set errors
// ============================================

// Add musician
if( $action === 'add' ) {
	
	if( $_SESSION['can_add_data'] ) {
		$template = 'add';
	}
	else {
		$error = 'Sorry, you don\'t have permission to add musicians.';
	}
	
}

// Edit musician
if( $action === 'edit' ) {
	
	if( $_SESSION['can_add_data'] ) {
		
		if( is_array($musician) && !empty($musician) ) {
			$template = 'edit';
		}
		else {
			$error = 'Sorry, that musician doesn\'t exist. Showing musician search instead.';
		}
		
	}
	else {
		$error = 'Sorry, you don\'t have permission to edit musicians.';
	}
	
}

// View musician
if( !$action && is_numeric($musician_id) ) {
	
	if( is_array($musician) && !empty($musician) ) {
		
		if( !$musician_is_removed || ( $musician_is_removed && $_SESSION['can_approve_data'] ) ) {
			
			$template = 'musician';
			
			if( $musician_is_removed ) {
				$error = 'This musician has been removed from the public. Please don\'t share any information on this page.';
			}
			
		}
		else {
			$error = 'Sorry, that musician has been removed.';
		}
		
	}
	else {
		$error = 'Sorry, that musician doesn\'t exist. Showing musician search instead.';
	}
	
}

// Index (default template)
if( !$template ) {
	$template = 'index';
}

// ============================================
// Show template
// ============================================

// Add musician
if( $template === 'add' ) {
	
	$page_title = 'Add musician | ミュージシャン追加';
	
	breadcrumbs([
		lang('Add musician', 'ミュージシャン追加', 'hidden') => '/musicians/add/'
	]);
	
	include('../musicians/page-add.php');
	
}

// Edit musician
if( $template === 'edit' ) {
	
	$page_title = 'Edit '.($musician['romaji'] ?: $musician['name']).' | '.$musician['name'].'を編集する';
	
	$page_header = lang('Edit musician', 'メンバーを編集する', 'div');
	
	breadcrumbs([
		lang('Edit musician', 'ミュージシャンを編集する', 'hidden') => '/musicians/'.$musician['id'].'/edit/'
	]);
	
	subnav([
		lang('Edit musician', 'メンバーを編集する', 'hidden') => '/musicians/'.$musician['id'].'/edit/'
	]);
	
	include('../musicians/page-edit.php');
	
}

// Show musician
if( $template === 'musician' ) {
	
	subnav([
		lang('Profile', 'プロフィール', 'hidden') => '/musicians/'.$musician['id'].'/'.$musician['friendly'].'/'
	]);
	
	if( $_SESSION['can_add_data'] ) {
		subnav([
			lang('Edit musician', 'ミュージシャンを編集する', 'hidden') => '/musicians/'.$musician['id'].'/edit/'
		], 'interact', true);
	}
	
	// Page image
	if(is_array($images) && !empty($images)) {
		$page_image = 'https://vk.gy/images/'.$images[0]['id'].($images[0]['friendly'] ? '-'.$images[0]['friendly'] : null).'.'.$images[0]['extension'];
	}
	
	$page_header = lang( ($musician['romaji'] ?: $musician['name']).' profile', $musician['name'].'のプロフィール', 'div' );
	
	$page_title = ($musician['romaji'] ?: $musician['name']).' profile | '.$musician['name'].'のプロフィール';
	
	include('../musicians/page-musician.php');
	
}

// Index (default view)
if( $template === 'index' ) {
	
	$active_page = '/musicians/';
	
	include('../musicians/page-index.php');
	
}