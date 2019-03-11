<?php
	$request = urldecode(substr($_SERVER["REQUEST_URI"], 1));
	$request = str_replace("/", " ", $request);
	$request = preg_replace("/"."\s$"."/", "", $request);
	
	breadcrumbs([
		"Page not found" => "/404/",
		"Search" => "/search/"
	]);
	
	$pageTitle = "Page not found";
?>

<div class="col c1">
	<div>
		<div class="text text--outlined text--notice symbol__error">
			Sorry, the requested page couldn't be found. <?php echo $request ? "Instead, here's a search for &ldquo;".sanitize($request)."&rdquo;" : "Try a search instead."; ?>
		</div>
	</div>
</div>

<?php
	if($request && $request !== "404 ") {
		$search["q"] = $request;
	}
	
	include("../search/page-index.php");
?>