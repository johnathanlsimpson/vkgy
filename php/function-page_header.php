<?php
	function page_header($en, $ja, $supplemental = null) {
		global $page_header;
		
		if(strlen($en)) {
			$page_header = lang($en, $ja, [ 'container' => 'div' ]);
		}
		
		if(strlen($supplement)) {
			$GLOBALS['page_header_supplement'] = $supplemental;
		}
	}