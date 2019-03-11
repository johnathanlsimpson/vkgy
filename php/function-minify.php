<?php
	$path = 'external';
	require_once $path . '/minify/src/Minify.php';
	require_once $path . '/minify/src/CSS.php';
	require_once $path . '/minify/src/JS.php';
	require_once $path . '/minify/src/Exception.php';
	require_once $path . '/minify/src/Exceptions/BasicException.php';
	require_once $path . '/minify/src/Exceptions/FileImportException.php';
	require_once $path . '/minify/src/Exceptions/IOException.php';
	require_once $path . '/path-converter/src/ConverterInterface.php';
	require_once $path . '/path-converter/src/Converter.php';
	use MatthiasMullie\Minify;
	
	$css_regex = "[A-z0-9\.\-]+\.css";
	$date_regex = "[0-9]{8}";
	
	if(!empty($_GET["css_file"]) && preg_match("/".$css_regex."/", $_GET["css_file"])) {
		$minifier = new Minify\CSS("../".$_GET["css_file"]);
header("Content-type: text/css; charset: UTF-8");
		echo $minifier->minify();
	}
?>