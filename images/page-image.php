<?php
	include_once('../php/include.php');
	
	//if($_SESSION['username'] === 'inartistic') {
		//include_once('../php/class-access_image.php');
		//$access_image = $access_image ?: new access_image($pdo);
		//$access_image->get_image($_GET);
	//}
	//else {
		include_once("../images/function-get_image.php");
		get_image($_GET, $pdo);
	//}
?>