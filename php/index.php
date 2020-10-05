<?php

if($headless) {
	echo $page_contents;
}
else {
	include('../php/page-index.php');
}