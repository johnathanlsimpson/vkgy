<?php
	include_once("../php/include.php");
	
	$access_artist = new access_artist($pdo);
	$access_blog = new access_blog($pdo);
	$markdown_parser = new parse_markdown($pdo);

	breadcrumbs([
		"News" => "/blog/"
	]);

	subnav([
		"Tags" => "/blog/tags/"
	]);

	subnav([
		"Add entry" => "/blog/add/"
	], true);
	
	
	
	// Entry
	if(!empty($_GET["entry"]) && !$_GET["action"]) {
		$entry = $access_blog->access_blog(["friendly" => sanitize($_GET["entry"]), "get" => "all"]);
		$entry["content"] = $markdown_parser->parse_markdown($entry["content"]);
		
		$sql_edit_history = 'SELECT edits_blog.date_occurred, users.username FROM edits_blog LEFT JOIN users ON users.id=edits_blog.user_id WHERE edits_blog.blog_id=? ORDER BY edits_blog.date_occurred DESC';
		$stmt_edit_history = $pdo->prepare($sql_edit_history);
		$stmt_edit_history->execute([ $entry['id'] ]);
		$entry['edit_history'] = $stmt_edit_history->fetchAll();
		$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];
		$entry['image'] = $entry['images'][$entry['image_id']];
		
		if(is_array($entry) && !empty($entry)) {
			$pageTitle = $entry["title"];
			
			breadcrumbs([$entry["title"] => "/blog/".$entry["friendly"]."/"]);
			
			subnav(["Edit entry" => "/blog/".$entry["friendly"]."/edit/"]);
			
			update_views("blog", $entry["id"], $pdo);
			
			include('../blog/page-entry.php');
		}
	}
	
	if(!$_GET['entry'] && !$_GET['action']) {
		$prev_next = $access_blog->get_prev_next([
			'artist' => $_GET['artist'],
			'tag' => $_GET['tag'],
			'page' => $_GET['page'] ?: 'latest',
			'get' => 'list',
		]);
		
		$entries = $access_blog->access_blog([
			'artist' => $_GET['artist'],
			'tag' => $_GET['tag'],
			'page' => $_GET['page'] ?: 'latest',
			'get' => 'list',
		]);
		$entries = is_array($entries) ? $entries : [];
		$num_entries = count($entries);
		
		if($_GET['artist']) {
			$artist = $access_artist->access_artist([ 'friendly' => friendly($_GET['artist']), 'get' => 'name' ]);
		}
		
		if($_GET['tag']) {
			$tag = $access_blog->access_tag([ 'friendly' => friendly($_GET['tag']) ]);
		}
		
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
			
		include("../blog/page-page.php");
	}
	
	
	
	/*if($_GET["tag"] || $_GET["artist"]) {
		
		if($_GET["tag"]) {
			$sql_tag = "SELECT id, tag FROM tags WHERE friendly=? LIMIT 1";
			$stmt_tag = $pdo->prepare($sql_tag);
			$stmt_tag->execute([sanitize($_GET["tag"])]);
			$tag = $stmt_tag->fetch();
			
			if(is_numeric($tag["id"])) {
				$entries = $access_blog->access_blog(["tag" => $tag["id"], "get" => "list"]);
			}
			else {
				$_GET["page"] = "latest";
				$error = "The requested tag doesn't exist. Showing latest entries instead.";
			}
		}
		elseif($_GET["artist"]) {
			$artist = $access_artist->access_artist(["friendly" => friendly($_GET["artist"]), "get" => "name"]);
			
			$entries = $access_blog->access_blog(["artist_id" => $artist["id"], "get" => "list"]);
		}
		
		if(is_array($entries)) {
			foreach($entries as $entry) {
				$entry["content"] = $markdown_parser->parse_markdown($entry["content"], true);
			}
			
			$pageTitle = "News tagged: ".$tag["tag"];
			
			include("../blog/page-tag.php");
		}
	}*/
	
	
	
	/*if($_GET["page"] === "latest" || is_numeric($_GET["page"])) {
		$entries = $access_blog->access_blog([ 'page' => $_GET['page'], 'get' => 'list' ]);
		$entries = is_array($entries) ? $entries : [];
		$num_entries = count($entries);
		
		if(is_array($entries) && !empty($entries)) {
			if($_GET["page"] === "latest") {
				$pageTitle = "Latest news";
				
				breadcrumbs([
					"Latest news" => "/blog/"
				]);
				
				$prev_next = $access_blog->get_prev_next(["page" => "latest"]);
			}
			else {
				$pageTitle = "News, page ".$_GET["page"];
				
				breadcrumbs([
					"Page ".$_GET["page"] => "/blog/".$_GET["page"]."/"
				]);
				
				$prev_next = $access_blog->get_prev_next(["page" => $_GET["page"]]);
			}
			
			include("../blog/page-page.php");
		}
	}*/
	
	
	if($_GET["action"] === "update") {
		if($_SESSION["loggedIn"]) {
			$sql_tags = "SELECT * FROM tags ORDER BY friendly ASC";
			$stmt_tags = $pdo->prepare($sql_tags);
			$stmt_tags->execute();
			$tags = $stmt_tags->fetchAll();
			
			// Edit entry
			if(!empty($_GET["entry"])) {
				$entry = $access_blog->access_blog(["friendly" => sanitize($_GET["entry"]), "get" => "all"]);
				
				$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];
				$entry['image'] = $entry['images'][$entry['image_id']];
				
				if(is_array($entry) && !empty($entry)) {
					$pageTitle = "Edit entry: ".$entry["title"];
					
					breadcrumbs([
						$entry["title"] => "/blog/".$entry["friendly"]."/",
						"Edit" => "/blog/".$entry["friendly"]."/edit/"
					]);
				}
			}
			
			// Add entry
			if(empty($entry)) {
				$pageTitle = "Add blog entry";
				
				breadcrumbs(["Add" => "/blog/add/"]);
			}
			
			include("../blog/page-update.php");
		}
	}
?>