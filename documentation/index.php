<?php
	include('../documentation/function-render_documentation.php');

	if(strpos($_SERVER['REQUEST_URI'], '/documentation/') === 0) {
		include('../account/head.php');
		$page_header = lang('Documentation', 'ガイド', ['container' => 'div']);
	}
	
	if(!$markdown_parser) {
		$markdown_parser = new parse_markdown($pdo);
	}
	
	// Parse filename; if allowed, and exists, get content
	$documentation_pages = $documentation_page ?: $_GET['documentation_page'];
	$documentation_pages = $documentation_pages ? (is_array($documentation_pages) ? $documentation_pages : [ $documentation_pages ]) : null;
	
	if($documentation_pages) {
		foreach($documentation_pages as $documentation_page) {
			$documentation_url = '../documentation/partial-'.$documentation_page.'.php';
			
			if(preg_match('/'.'^[A-z0-9-]+$'.'/', $documentation_page) && file_exists($documentation_url)) {
				$documentation_contents[$documentation_page] = file_get_contents($documentation_url);
			}
		}
	}
	
	// Set title and breadcrumbs
	if($_GET['documentation_page']) {
		$documentation_page = $_GET['documentation_page'];
		$documentation_page = strtoupper(substr($documentation_page, 0, 1)).substr($documentation_page, 1);
		$documentation_page = str_replace('-', ' ', $documentation_page);
		
		breadcrumbs([
			'Documentation' => '/documentation/',
			$documentation_page => '/documentation/'.$_GET['documentation_page'].'/'
		]);
		
		$page_title = 'Documentation: '.$documentation_page;
	}

	// Pass content to page template, or else show index
	if($documentation_contents && is_array($documentation_contents) && !empty($documentation_contents)) {
		include('../documentation/page-documentation.php');
	}
	else {
		breadcrumbs([
			'Documentation' => '/documentation/',
		]);
		
		$page_title = 'Documentation';
		
		include('../documentation/page-index.php');
	}
?>