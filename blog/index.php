<?php
// Setup
include_once("../php/include.php");

$access_artist = new access_artist($pdo);
$access_blog = new access_blog($pdo);
$markdown_parser = new parse_markdown($pdo);

$page_header = lang('Visual kei news', 'V系ニュース', ['container' => 'div']);

include('../blog/head.php');

breadcrumbs([
	"News" => "/blog/"
]);

subnav([
	"Add entry" => "/blog/add/"
], 'interact', true);

// Check VIP status
$sql_vip_check = 'SELECT 1 FROM users WHERE id=? AND is_vip=? LIMIT 1';
$stmt_vip_check = $pdo->prepare($sql_vip_check);
$stmt_vip_check->execute([ $_SESSION['user_id'], 1 ]);
$is_vip = $stmt_vip_check->fetchColumn();

// View: entry
if(!empty($_GET["entry"]) && !$_GET["action"]) {
	$entry = $access_blog->access_blog([ 'friendly' => sanitize($_GET['entry']), 'get' => 'all', 'show_queued' => true ]);
	
	if(
		!$entry['is_queued']
		||
		($entry['is_queued'] && strlen($entry['token']) && $_GET['preview'] === $entry['token'])
		||
		$_SESSION['user_id'] === $entry['user_id']
		||
		$_SESSION['can_access_drafts']
	) {
		$is_allowed = true;
	}
	
	if($is_allowed && is_array($entry) && !empty($entry)) {
		$entry['references'] = $markdown_parser->get_reference_data($entry['content']);
		$entry['content'] = $markdown_parser->parse_markdown($entry['content']);
		$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];
		$entry['image'] = $entry['images'][$entry['image_id']];
		
		$page_title = $entry['title'];
		
		breadcrumbs([$entry["title"] => "/blog/".$entry["friendly"]."/"]);
		
		subnav(["Edit article" => "/blog/".$entry["friendly"]."/edit/"], 'interact', true);
		
		update_views("blog", $entry["id"], $pdo);
		
		if(is_array($entry['tags']) && !empty($entry['tags'])) {
			foreach($entry['tags'] as $tag) {
				if($tag['friendly'] === 'interview') {
					$entry_is_feature = true;
					break;
				}
			}
		}
		
		if($entry_is_feature) {
			include('../blog/page-interview.php');
		}
		else {
			include('../blog/page-entry.php');
		}
	}
	else {
		$error = 'Sorry, the requested entry doesn\'t exist, or is restricted. Showing latest news instead.';
		
		// If user doesn't have permission to view article, let's still show the title and SNS image so we don't screw up Twitter preview later
		if( is_array($entry) && is_array($entry['images']) && !empty($entry['images']) && ( is_numeric($entry['image_id']) || is_numeric($entry['sns_image_id']) ) ) {
			
			// If special image set for SNS, user that
			if(is_numeric($entry['sns_image_id'])) {
				$entry['image'] = $entry['images'][$entry['sns_image_id']];
			}
			
			// Otherwise use header image
			else {
				$entry['image'] = $entry['images'][$entry['image_id']];
			}
			
			// Set page image
			$page_image = "https://vk.gy".str_replace('.', '.large.', $entry['image']['url']);
			
		}
		
		unset($_GET['entry']);
	}
}

// View: update
if($_GET["action"] === "update") {
	
	if(!empty($_GET['entry'])) {
		// Get entry
		$entry = $access_blog->access_blog([ 'friendly' => sanitize($_GET['entry']), 'get' => 'all', 'show_queued' => true ]);
		
		if(is_array($entry) && !empty($entry)) {
			$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];
			$entry['image'] = $entry['images'][$entry['image_id']];
		}
		else {
			$error = 'Sorry, that entry couldn\'t be found. Showing <em>add entry</em> instead.';
		}
	}
	
	// Check if allowed
	if($_SESSION['is_signed_in']) {
		if(
			empty($entry)
			||
			$_SESSION['user_id'] === $entry['user_id']
			||
			!$entry['is_queued'] && $_SESSION['can_add_data']
			||
			$entry['is_queued'] && $_SESSION['can_access_drafts']
		) {
			$is_allowed = true;
		}
		elseif(is_array($entry['tags']) && !empty($entry['tagss'])) {
			foreach($entry['tags'] as $tag) {
				if($tag['friendly'] === 'auto-generated') {
					$is_allowed = true;
				}
			}
		}
	}
	
	// Get list of tags
	$sql_tags = "SELECT * FROM tags ORDER BY friendly ASC";
	$stmt_tags = $pdo->prepare($sql_tags);
	$stmt_tags->execute();
	$tags = $stmt_tags->fetchAll();
	
	// Get queued entries
	$queued_entries = $access_blog->access_blog([
		'queued' => true,
		'get' => 'list',
		'show_queued' => true,
	]);
	$num_queued_entries = is_array($queued_entries) ? count($queued_entries) : 0;
	
	if($is_allowed) {
		if(is_array($entry) && !empty($entry)) {
			$page_title = 'Edit: '.$entry["title"];
			
			breadcrumbs([
				$entry["title"] => "/blog/".$entry["friendly"]."/",
				'Edit article' => "/blog/".$entry["friendly"]."/edit/"
			]);
			
			// Set flags
			$entry['is_published'] = $entry['is_queued'] ? 0 : 1;
			
		}
		else {
			$page_title = 'Add article';
			
			// Set flags
			$entry['is_queued'] = 1;
			$entry['is_published'] = 0;
			
			breadcrumbs([ 'Add article' => '/blog/add/' ]);
		}
		
		include("../blog/page-update.php");
	}
	else {
		$error = 'Sorry, the requested entry doesn\'t exist, or is restricted. Showing latest news instead.';
		unset($_GET['entry'], $_GET['action']);
	}
}

// View: entries
if(!$_GET['entry'] && !$_GET['action']) {
	
	// Get prev/next links
	$prev_next = $access_blog->get_prev_next([
		'artist' => $_GET['artist'],
		'tag' => $_GET['tag'],
		'page' => $_GET['page'] ?: 'latest',
		'get' => 'list',
	]);
	
	// Get entries
	$entries = $access_blog->access_blog([
		'artist' => $_GET['artist'],
		'tag' => $_GET['tag'],
		'page' => $_GET['page'] ?: 'latest',
		'get' => 'list',
		'limit' => $_GET['view'] === 'interviews' ? 40 : null
	]);
	$entries = is_array($entries) ? $entries : [];
	$num_entries = count($entries);
	
	// Get queued entries
	$queued_entries = $access_blog->access_blog([
		'queued' => true,
		'get' => 'list',
		'show_queued' => true,
	]);
	$num_queued_entries = is_array($queued_entries) ? count($queued_entries) : 0;
	
	// Allow user to see only appropriate queued entries
	for($i=0; $i<$num_queued_entries; $i++) {
		if($queued_entries[$i]['user_id'] != $_SESSION['user_id'] && !$is_vip) {
			unset($queued_entries[$i]);
		}
	}
	
	// If showing artist/tag view, get info
	if($_GET['artist']) {
		$artist = $access_artist->access_artist([ 'friendly' => friendly($_GET['artist']), 'get' => 'name' ]);
	}
	if($_GET['tag']) {
		$tag = $access_blog->access_tag([ 'friendly' => friendly($_GET['tag']) ]);
	}
	
	// Display page num or 'latest'
	if($_GET['page']) {
		$pageTitle = "News, page ".$_GET["page"];
		
		breadcrumbs([
			"Page ".$_GET["page"] => "/blog/".$_GET["page"]."/"
		]);
	}
	else {
		$page_title = 'Latest news';
		
		breadcrumbs([
			"Latest news" => "/blog/"
		]);
	}
	
	// View
	if($_GET['view'] === 'interviews') {
		include('page-interviews.php');
	}
	else {
		include("../blog/page-page.php");
	}
}