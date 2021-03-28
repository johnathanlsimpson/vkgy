<?php

include_once('../php/include.php');

//$migration = '20201115-development.php';
//$migration = '20210326-artist_years.php';

$migration_file = '../migrations/'.$migration;

if($_SESSION['username'] === 'inartistic') {
	
	if($migration && file_exists($migration_file)) {
		
		include_once($migration_file);
		
		if( is_array($sql) || strlen($sql) ) {
			
			$sql = is_array($sql) ? $sql : [ $sql ];
			
			if( $sql && is_array($sql) && !empty($sql) ) {
				
				foreach($sql as $sql_line) {
					
					$stmt = $pdo->prepare($sql_line);
					
					if($stmt->execute()) {
						echo $sql_line.'<br />';
						echo 'Migration completed.<br /><br />';
					}
					else {
						echo $sql_line.'<br />';
						echo 'Something went wrong.<br /><br />';
					}
					
				}
				
			}
			
		}
		
		else {
			echo 'No sql set.';
		}
		
	}
	
}