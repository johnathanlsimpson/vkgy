<?php

include('../php/class-views.php');

// New instance
$views = new views($pdo);

// Every 24 hours, aggregate that past day's views into current week
$views->archive('artist', 'daily');

// Every Sunday, save past week's views in permanent monhtly archive
// then move weekly views down and reset to 0 for upcoming week
if(date('D') === 'Sun') {
	
	// Montly archive
	$views->archive('artist', 'monthly');
	
	// Weekly archive
	$views->archive('artist', 'weekly');
	
}