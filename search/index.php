<?php
	include_once("../search/head.php");
	
	$search_type = in_array($_GET["search_type"], ["all", "releases", "artists", "musicians"]) ? $_GET["search_type"] : "all";
	
	if($search_type === "all") {
		script([
			"/search/script-page-index.js"
		]);
		
		style([
			"/search/style-page-index.css"
		]);
		
		$access_blog = new access_blog($pdo);
		$access_release = new access_release($pdo);
		$access_artist = new access_artist($pdo);
		$access_label = new access_label($pdo);
		$access_musician = new access_musician($pdo);
		$markdown_parser = new parse_markdown($pdo);
		
		$pageTitle = "Search";
		
		if(!isset($search["q"])) {
			$request = str_replace("/search/?", "", $_SERVER["REQUEST_URI"]);
			parse_str($request, $search);
			$search = array_filter($search);
		}
		
		if(!empty($search["q"])) {
			$q = sanitize($search["q"]);
			
			$results["artists"] = $access_artist->access_artist([ "get" => "name", "fuzzy" => true, "name" => $q ]);
			$results["labels"] = $access_label->access_label([ "get" => "name", "name" => $q ]);
			$results["musicians"] = $access_musician->access_musician([ "name" => $q, "get" => "name", "limit" => 10 ]);
			$results["musicians"] = is_array($results["musicians"]) ? array_values($results["musicians"]) : $results["musicians"];
			
			if(strlen($q) > 2) {
				$results["releases"] = $access_release->access_release(["release_name" => $q, "get" => "list", "limit" => 25 ]);
				$results["releases"] = is_array($results["releases"]) ? array_values($results["releases"]) : $results["releases"];
				$results["posts"] = $access_blog->access_blog(["get" => "list", "content" => $q, "limit" => 25 ]);
				
				for($i=0; $i<count($results["posts"]); $i++) {
					$results["posts"][$i]["content"] = $markdown_parser->parse_markdown($results["posts"][$i]["content"]);
				}
			}
		}
		
		include("../search/page-index.php");
	}
	elseif($search_type === "releases") {
		include("../search/page-releases.php");
	}
	elseif($search_type === "musicians") {
		include("../search/page-musicians.php");
	}
	elseif($search_type === "artists") {
		include("../search/page-artists.php");
	}
?>