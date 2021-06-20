<?php

include('../php/class-views.php');

// New instance
$views = new views($pdo);

// For each item type, aggregate views
foreach( views::$allowed_items as $item_type ) {
	
	// Every 24 hours, aggregate that past day's views into current week
	$views->archive_views($item_type, 'daily');
	
	// Every Sunday, save past week's views in permanent monhtly archive
	// then move weekly views down and reset to 0 for upcoming week
	//if(date('D') === 'Sun') {
		
		// Montly archive--run this first since it relies on current week's views
		$views->archive_views($item_type, 'monthly');
		
		// Weekly archive
		$views->archive_views($item_type, 'weekly');
		
	//}
	
}