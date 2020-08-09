<?php

include_once('../php/include.php');

$folder = friendly($_POST['folder']);
$content = sanitize($_POST['content']);
$context = sanitize($_POST['context']);
$id = is_numeric($_POST['id']) ? $_POST['id'] : null;

if($_SESSION['is_moderator']) {
	
	if(strlen($folder) && strlen($content)) {
		
		// If editing
		if(is_numeric($id)) {
			
			$sql_update = 'UPDATE translations SET content=?, context=?, folder=? WHERE id=? LIMIT 1';
			$stmt_update = $pdo->prepare($sql_update);
			if($stmt_update->execute([ $content, $context, $folder, $id ])) {
				
				$output['status'] = 'success';
				
			}
			
		}
		
		// If adding new
		else {
			
			// Double check that doesn't exist
			$sql_check = 'SELECT 1 FROM translations WHERE folder=? AND content=? LIMIT 1';
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $folder, $content ]);
			$rslt_check = $stmt_check->fetchColumn();
			
			// Add new
			if(!$rslt_check) {
				
				$sql_add = 'INSERT INTO translations (folder, content, context) VALUES (?, ?, ?)';
				$stmt_add = $pdo->prepare($sql_add);
				
				if($stmt_add->execute([ $folder, $content, $context ])) {
					
					$output['status'] = 'success';
					
				}
				
			}
			
		}
		
	}
	
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);