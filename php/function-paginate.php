<?php

include_once('../php/include.php');

// Given current URL, try to spit one back out with updated page number
function paginate_url($page_num) {
	
	$new_url = '/'.$_GET['officialURL'];
	
	if(is_numeric($page_num)) {
		
		$get = $_GET;
		$official_url = $get['officialURL'];
		
		// Remove meta fields from URL
		unset($get[$official_url], $get['officialURL'], $get['view'], $get['headless']);
		
		// Assume remaining get vars are filters, so let's add page to that
		$get['page'] = $page_num;
		
		// Implode filters back into new url
		$new_url = '/'.$official_url;
		foreach($get as $get_key => $get_value) {
			$new_url .= '&'.$get_key.'='.$get_value;
		}
		
	}
	
	return $new_url;
	
}

// Given information about current page, spit out some helpers with navigation
function paginate($input = []) {
	
	// Set defaults
	$limit = is_numeric($input['limit']) ? $input['limit'] : 1;
	$num_items = is_numeric($input['num_items']) ? $input['num_items'] : 0;
	$num_pages = is_numeric($input['num_pages']) ? $input['num_pages'] : ( ceil( $num_items / $limit ) );
	$current_page = is_numeric($input['current_page']) ? $input['current_page'] : 1;
	$pages = [];
	
	// If requested page > num pages, clamp it to last possible page
	$current_page = $current_page <= $num_pages ? $current_page : $num_pages;
	
	// Do some calculations
	$remainder = $num_items % $limit;
	
	// Set some flags--we want nav to show different links depending on length and location
	$show_previous = $current_page > 1 ? true : false;
	$show_next = $current_page < $num_pages ? true : false;
	$show_ellipsis = $num_pages > 4 ? true : false;
	
	// Make sure at least one early page is hidden before showing start ellipsis
	if( $current_page >= 5) {
		$show_start_ellipsis = true;
	}
	
	// Make sure at least one late page is hidden before showing end ellipsis
	if( $current_page + 4 <= $num_pages ) {
		$show_end_ellipsis = true;
	}
	
	// Given the current page, we want to show a range of 5 pages around the requested page
	// But also always show first and last link, so up to 7 pages could be shown in total
	// We also want to *always* show the first page, so it should be like:
	// [*1* 2 3 4 5] ... 257
	// [1 *2* 3 4 5] ... 257
	// [1 2 *3* 4 5] ... 257
	// 1 [2 3 *4* 5 6] ... 257
	// 1 ... [3 4 *5* 6 7]  ... 257
	// 1 ... [251 252 *253* 254 255] ... 257
	// 1 ... [252 253 *254* 255 256] 257
	// 1 ... [253 254 *255* 256 257]
	// 1 ... [253 254 255 *256* 257]
	
	// Loop should start 2 pages back
	$loop_start = $current_page - 2;
	
	// If 2 pages back doesn't exist, clamp loop to page 1
	if( $loop_start < 1 ) {
		$loop_start = 1;
	}
	
	// Loop should show 4 additional pages after start, for a range of 5
	$loop_end = $loop_start + 4;
	
	// If loop start + 4 exceeds total pages, clamp to last page, and move range back if possible
	if( $loop_end > $num_pages ) { 
		$loop_end = $num_pages;
		$loop_start = $loop_end - 4 > 1 ? $loop_end - 4 : $loop_start;
	}
	
	// Now let's output an array with all the pages we need, always start with 1
	if( $loop_start > 1 ) {
		$pages[] = [ 'page_num' => 1 ];
	}
	
	// Go through loop and add other pages, if possible
	for($i=$loop_start; $i<=$loop_end; $i++) {
		$pages[] = [ 'page_num' => $i ];
	}
	
	// Add last page if necessary
	if( $loop_end < $num_pages ) {
		$pages[] = [ 'page_num' => $num_pages ];
	}
	
	// With pages array set up, loop through and get more info for each page
	foreach($pages as $page_key => $page) {
		
		$pages[$page_key]['url'] = paginate_url( $page['page_num'] );
		$pages[$page_key]['is_active'] = $page['page_num'] == $current_page;
		$pages[$page_key]['show_ellipsis_after'] = $page['page_num'] == 1 && $show_start_ellipsis;
		$pages[$page_key]['show_ellipsis_before'] = $page['page_num'] == $num_pages && $show_end_ellipsis;
		$pages[$page_key]['is_first'] = $page['page_num'] == 1;
		$pages[$page_key]['is_last'] = $page['page_num'] == $num_pages;
		
	}
	
	// Add 'previous' link to front
	array_unshift($pages, [
		'url' => paginate_url( $current_page > 1 ? $current_page - 1 : $current_page ),
		'is_previous' => true,
		'is_disabled' => $current_page > 1 ? false : true,
	]);
	
	// Add 'next' link to end
	$pages[] = [
		'url' => paginate_url( $current_page < $num_pages ? $current_page + 1 : $current_page ),
		'is_next' => true,
		'is_disabled' => $current_page < $num_pages ? false : true,
	];
	
	return $pages;
	
}

// Render links
function render_pagination($pagination) {
	
	ob_start();
	
	if( is_array($pagination) && !empty($pagination) ) {
		
		foreach($pagination as $page) {
			
			// Set classes for pagination links
			$page['classes'] = implode(' ', array_filter([
				($page['is_active']                                            ? 'a--outlined'          : null),
				($page['is_previous']                                          ? 'symbol__previous'     : null),
				($page['is_next']                                              ? 'symbol__next'         : null),
				($page['is_previous'] || $page['is_next']                      ? 'pagination__arrow'    : 'pagination__num'),
				($page['is_active']                                            ? 'pagination--active'   : null),
				($page['is_first']                                             ? 'pagination--first'    : null),
				($page['is_last']                                              ? 'pagination--last'     : null),
				($page['show_ellipsis_before'] || $page['show_ellipsis_after'] ? 'pagination--ellipsis' : null),
				($page['is_disabled']                                          ? 'pagination--disabled' : null),
				'a--padded',
				'pagination__link',
			]));
			
			// Render pagination links
			?>
				<a class="<?= $page['classes']; ?>" href="<?= $page['url']; ?>">
					<?= $page['is_previous'] ? '<span class="any--hidden">Previous page</span>' : null; ?>
					<?= $page['is_next'] ? '<span class="any--hidden">Next page</span>' : null; ?>
					<?= $page['page_num']; ?>
				</a>
			<?php
			
		}
		
	}
	
	$rendered_pagination = ob_get_clean();
	
	return $rendered_pagination;
	
}