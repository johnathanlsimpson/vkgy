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
			'blog'   => 'blog',
			'video'  => 'videos',
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
		$allowed_archive_types = [
			'daily'   => 'weekly',
			'weekly'  => '',
			'monthly' => 'weekly'
		];
		$id_column = $item_type.'_id';
		$current_table_name = 'views_'.$archive_type.'_'.$this->allowed_item_types[$item_type];
		$archived_table_name = 'views_'.$allowed_archive_types[$archive_type].'_'.$this->allowed_item_types[$item_type];
		$date_occurred = date('Y-m').'-01';
		
		// Make sure archive type is allowed
		if( in_array($archive_type, array_keys($allowed_archive_types)) ) {
			
			// Make sure item type provided and allowed
			if( strlen($item_type) && in_array($item_type, array_keys($this->allowed_item_types)) ) {
				
				// Daily aggregation
				if($archive_type === 'daily') {
					$sql_archive = "
						INSERT INTO $archived_table_name ($id_column, num_views)
						SELECT $id_column, num_views FROM $current_table_name
						ON DUPLICATE KEY UPDATE $archived_table_name.num_views = $archived_table_name.num_views + $current_table_name.num_views
					";
					$sql_delete = "TRUNCATE $current_table_name";
				}
				
				// Permanent archive
				elseif($archive_type === 'monthly') {
					$sql_archive = "
						INSERT INTO $current_table_name ($id_column, num_views, date_occurred)
						SELECT $id_column, num_views, '$date_occurred' AS date_occurred FROM $archived_table_name WHERE num_views > 0 OR past_views > 0 OR past_past_views > 0
						ON DUPLICATE KEY UPDATE $current_table_name.num_views = $current_table_name.num_views + $archived_table_name.num_views
					";
					echo $sql_archive;
				}
				
				// Weekly aggregation
				elseif($archive_type === 'weekly') {
					$sql_archive = "
						UPDATE $current_table_name
						SET past_past_views = past_views, past_views = num_views, num_views = 0
					";
					$sql_delete = "
						DELETE FROM $current_table_name
						WHERE num_views = 0 AND past_views = 0 AND past_past_views = 0
					";
				}
				
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