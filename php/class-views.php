<?php

include_once('../php/include.php');

class views {
	
	// Items we can record views for
	public static $allowed_items = [
		'artist',
		'blog',
		'video',
	];
	
	// Frequencies we can archive
	public static $allowed_time_periods = [
		'daily',
		'weekly',
		'monthly',
	];
	
	
	
	// ======================================================
	// Construct
	// ======================================================
	
	// Connect and set vars
	function __construct($pdo) {
		
		// Create PDO connection if not already provided
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		$this->pdo = $pdo;
		
	}
	
	
	
	// ======================================================
	// Add view
	// ======================================================

	// Add view
	public function add_view($item_type, $item_id) {
		
		// Set up item names
		$item_table     = $item_type.'s';
		$item_id_column = $item_type.'_id';
		
		// Set up views names
		$views_table    = 'views_'.$item_table.'_daily';
		
		// Make sure item is allowed
		if( strlen($item_type) && in_array($item_type, self::$allowed_items) ) {
			
			// Make sure item ID is specified
			if( is_numeric($item_id) ) {
				
				// Add the view
				$sql_view = 'INSERT INTO '.$views_table.' ('.$item_id_column.') VALUES (?) ON DUPLICATE KEY UPDATE num_views=num_views+1';
				$stmt_view = $this->pdo->prepare($sql_view);
				
				// Run query
				if($stmt_view->execute([ $item_id ])) {
					return true;
				}
				
			}
			
		}
		
	}
	
	
	
	// ======================================================
	// Aggregate views
	// ======================================================
	
	// Archive current views and restart table
	public function archive_views( $item_type, $time_period='daily' ) {
		
		// Set up item names
		$item_table          = $item_type.'s';
		$item_id_column      = $item_type.'_id';
		
		// Set source and destination periods based on type of archive we're running
		switch ($time_period) {
			
			case 'daily':
				$source_period      = 'daily';
				$destination_period = 'weekly';
				break;
				
			case 'weekly':
				$source_period      = 'weekly';
				$destination_period = 'weekly';
				break;
				
			case 'monthly':
				$source_period      = 'weekly';
				$destination_period = 'monthly';
				break;
			
		}
		
		// Set up views tables
		$source_table      = 'views_'.$item_table.'_'.$source_period;
		$destination_table = 'views_'.$item_table.'_'.$destination_period;
		
		// Month will be needed if performing monthly archive
		$date_occurred = date('Y-m').'-01';
		
		// Make sure time period is allowed
		if( in_array( $time_period, self::$allowed_time_periods ) ) {
			
			// Make sure item type provided and allowed
			if( strlen($item_type) && in_array($item_type, self::$allowed_items) ) {
				
				// From daily to weekly--daily views are summed and added to num_views (a.k.a. current week) column of weekly
				// Truncate the table to start fresh every day
				if($time_period === 'daily') {
					$sql_archive = "
						INSERT INTO $destination_table ($item_id_column, num_views)
						SELECT $item_id_column, num_views FROM $source_table
						ON DUPLICATE KEY UPDATE $destination_table.num_views = $destination_table.num_views + $source_table.num_views
					";
					$sql_delete = "TRUNCATE $source_table";
				}
				
				// From weekly to weekly--last week is summed into two weeks ago, this week is summed into last week, this week is reset to 0
				// We basically keep a rolling tally so we can see which artists have gained attention compared to their previous week's performance
				// (Used to delete any rows w/ 2 consecutive weeks of 0 views but that's v rare and the table can only be as large as artists anyway...)
				elseif($time_period === 'weekly') {
					$sql_archive = "
						UPDATE $source_table
						SET past_past_views = past_views, past_views = num_views, num_views = 0
					";
				}
				
				// From weekly to monthly
				elseif($time_period === 'monthly') {
					$sql_archive = "
						INSERT INTO $destination_table ($item_id_column, num_views, date_occurred)
						SELECT $item_id_column, num_views, '$date_occurred' AS date_occurred FROM $source_table
						ON DUPLICATE KEY UPDATE $destination_table.num_views = $destination_table.num_views + $source_table.num_views
					";
				}
				
				// Archive views
				if($sql_archive) {
					$stmt_archive = $this->pdo->prepare($sql_archive);
					$stmt_archive->execute();
				}
				
				// Wipe data as needed
				if($sql_delete) {
					$stmt_delete = $this->pdo->prepare($sql_delete);
					$stmt_delete->execute();
				}
				
			}
			
		}
		
	}
	
}