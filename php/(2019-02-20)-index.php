<?php

if($_SESSION['username'] === 'inartistic') {
	include('../php/page-index-inartistic.php');
}
else {
	include('../php/page-index.php');
}