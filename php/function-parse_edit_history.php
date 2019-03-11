<?php
	include_once("../php/include.php");
	
	function parse_edit_history($edit_history, $pdo) {
		$access_user = new access_user($pdo);
		
		if(!empty($edit_history)) {
			$edit_history = str_replace(["\r\n", "\r"], "\n", $edit_history);
			$edit_history = explode("\n", $edit_history);
			
			if(is_array($edit_history)) {
				$edit_history = array_filter($edit_history);
				
				foreach($edit_history as $line) {
					if(preg_match("/"."^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) \((\d+)\)$"."/", $line, $match)) {
						$date = $match[1];
						$time = $match[2];
						$user_id = $match[3];
						
						$output[] = [
							"date_time" => $date." ".$time,
							"y" => substr($date, 0, 4),
							"m" => substr($date, 5, 2),
							"d" => substr($date, 8, 2),
							"user" => $access_user->access_user(["id" => $user_id, "get" => "name"])
						];
					}
				}
				
				if(is_array($output)) {
					rsort($output);
				}
			}
		}
		
		return $output;
	}
?>