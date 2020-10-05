<?php

include_once('../php/include.php');

//$migration = '20201003-videos.php';
$migration_file = '../migrations/'.$migration;

if($_SESSION['username'] === 'inartistic') {
	
	if($migration && file_exists($migration_file)) {
		
		include_once($migration_file);
		
		if($sql) {
			
			$stmt = $pdo->prepare($sql);
			
			if($stmt->execute()) {
				echo 'Migration completed.';
			}
			else {
				echo 'Something went wrong.';
			}
			
		}
		
	}
	
}