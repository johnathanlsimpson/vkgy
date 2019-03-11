<?php
	$requested_page = explode("&", urldecode($_SERVER["QUERY_STRING"]))[0];
	
	if(!empty($requested_page)) {
		if(substr($requested_page, -1) === "/") {
			if(substr_count($requested_page, "/") === 1) {
				$requested_page .= "index";
			}
			else {
				$requested_page = substr($requested_page, 0, (strlen($requested_page) - 1));
			}
		}
		
		$requested_page = "../".$requested_page.".php";
		
		if(!file_exists($requested_page)) {
			$requested_page = "../404/index.php";
		}
	}

	if(!$requested_page) {
		$requested_page = "../main/index.php";
	}
?>