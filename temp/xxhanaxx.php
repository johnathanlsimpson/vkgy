<?php
if($_SESSION['is_admin']) {
	$a = 'http://web.archive.org/web/1/';
	$b = 'http://mykit.jp/nikki/nikkimiru.php3?';
	
	for($c=2001; $c<2010; $c++) {
		for($d=1; $d<13; $d++) {
			$e = $a.$b.'yy='.$c.'&mm='.$d.'&id=xxhanaxx';
			echo '<a href="'.$e.'">'.$e.'</a>'.'<br />';
		}
		echo '<hr />';
	}
}