<?php

function get_cdj_preorders() {
	$cdj_js = file_get_contents('http://www.cdjapan.co.jp/aff/data/tp_visual_cd_sen_ure.js');
	$cdj_js = substr($cdj_js, 50, -1);
	
	if($cdj_js) {
		return json_decode($cdj_js, true);
	}
}