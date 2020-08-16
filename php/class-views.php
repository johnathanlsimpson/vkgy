<?php

include_once('../php/include.php');

class views {
	
	public $allowed_item_types;
	
	
	
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
		
		// Set allowed item types and associated tables
		$this->allowed_item_types = [
			'artist' => 'artists',
			'blog'   => 'blog,'
		];
		
	}
	
	
	
	// ======================================================
	// Add view
	// ======================================================

	// Add view
	public function add($item_type=null, $item_id=null) {
		
		// Make sure item type is allowed
		$table_name = 'views_daily_'.$this->allowed_item_types[$item_type];
		$id_name = $item_type.'_id';
		
		if( strlen($item_type) && in_array($item_type, array_keys($this->allowed_item_types)) ) {
			
			// Make sure item ID is specified
			if( is_numeric($item_id) ) {
				
				// Add the view
				$sql_view = 'INSERT INTO '.$table_name.' ('.$id_name.') VALUES (?) ON DUPLICATE KEY UPDATE num_views=num_views+1';
				$stmt_view = $this->pdo->prepare($sql_view);
				$stmt_view = $this->pdo->prepare($sql_view);
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
	public function archive($item_type=null, $archive_type='daily') {
		
		// Set vars
		$allowed_archive_types = [ 'daily', 'weekly' ];
		$id_column_name = $item_type.'_id';
		$views_table_name = 'views_'.$archive_type.'_'.$this->allowed_item_types[$item_type];
		$archived_table_name = $archive_type === 'daily' ? 'views_weekly_'.$this->allowed_item_types[$item_type] : 'views_archived_'.$this->allowed_item_types[$item_type];
		
		// Make sure archive type is allowed
		if( in_array($archive_type, $allowed_archive_types) ) {
			
			// Make sure item type provided and allowed
			if( strlen($item_type) && in_array($item_type, array_keys($this->allowed_item_types)) ) {
				
				// Aggregate current views	
				$sql_archive = '
					INSERT INTO '.$archived_table_name.' ('.$id_column_name.', num_views, date_occurred)
					SELECT '.
						$views_table_name.'.'.$id_column_name.', '.
						$views_table_name.'.num_views, '.
						($archive_type === 'daily' ? '"'.date('Y-m-d H:i:s', strtotime('this week Sunday')).'" AS date_occurred' : $views_table_name.'.date_occurred').'
					FROM '.$views_table_name.'
					ON DUPLICATE KEY UPDATE '.$archived_table_name.'.num_views='.$archived_table_name.'.num_views + '.$views_table_name.'.num_views
				';
				$stmt_archive = $this->pdo->prepare($sql_archive);
				$stmt_archive->execute();
				
				// Wipe current table so we can start over for the day/week
				$sql_truncate = 'TRUNCATE '.$views_table_name;
				$stmt_truncate = $this->pdo->prepare($sql_truncate);
				$stmt_truncate->execute();
				
			}
			
		}
		
	}
	
}