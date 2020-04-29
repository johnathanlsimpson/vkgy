<?php

include_once('../php/include.php');

$access_points = new access_points($pdo);

if($_SESSION['can_add_livehouses']) {
	
	if(is_array($_POST) && !empty($_POST)) {
		
		foreach($_POST['name'] as $key => $name) {
			
			$name = sanitize($name);
			
			if(strlen($name)) {
				
				$romaji = sanitize($_POST['romaji'][$key]) ?: null;
				$friendly = friendly($romaji ?: $name);
				
				// Check validity of friendly name
				if(strlen($friendly) && $friendly != '-') {
					
					// Make sure name is available
					$sql_name = 'SELECT name, romaji, friendly FROM magazines WHERE friendly=? LIMIT 1';
					$stmt_name = $pdo->prepare($sql_name);
					$stmt_name->execute([ $friendly ]);
					$rslt_name = $stmt_name->fetch();
					
					$name_is_taken = $rslt_name['friendly'] ? true : false;
					
					if(!$name_is_taken) {
						
						// Main query
						$sql_add = 'INSERT INTO magazines (name, romaji, friendly) VALUES (?, ?, ?)';
						$stmt_add = $pdo->prepare($sql_add);
						
						if($stmt_add->execute([ $name, $romaji, $friendly ])) {
							$output['status'] = $output['status'] ?: 'success';
							$output['result'][] = 'Added <a href="/magazines/'.$friendly.'/">'.($romaji ?: $name).'</a>'.(strlen($romaji) ? ' ('.$name.')' : null).'.';
							$output['keys'][] = $key;
							
							// Grab ID
							$magazine_id = $pdo->lastInsertID();
							
							// Update edit table
							$sql_edit = 'INSERT INTO edits_magazines (magazine_id, user_id, content) VALUES (?, ?, ?)';
							$stmt_edit = $pdo->prepare($sql_edit);
							$stmt_edit->execute([ $magazine_id, $_SESSION['user_id'], 'Created.' ]);
							
							// Award point
							$output['points'] += $access_points->award_points([ 'point_type' => 'added-magazine' ]);
							$points++;
							
						}
						else {
							$output['result'][] = 'Couldn\'t add <strong>'.($romaji ?: $name).'</strong>.';
						}
						
					}
					else {
						$output['result'][] = 'The name <strong>'.($romaji ?: $name).'</strong> is already being used by <a href="/magazines/'.$rslt_name['friendly'].'/">'.($rslt_name['romaji'] ?: $rslt_name['name']).'</a>'.(strlen($rslt_name['romaji']) ? ' ('.$rslt_name['name'].')' : null).'. Please use a different name.';
					}
					
				}
				else {
					$output['result'][] = 'For <strong>'.($romaji ?: $name).'</strong>, the automatically-generated URL name is <span class="any__note">-</span>, which is too short. Please specify an alternate name in the <span class="any__note">romaji</span> field.';
				}
				
			}
			else {
				//$output['result'][] = 'No name specified.';
			}
			
		}
		
	}
	else {
		//$output['result'][] = 'No data passed.';
	}
	
}
else {
	$output['result'][] = 'Sorry, you don\'t have permission to add magazines.';
}

$output['status'] = $output['status'] ?: 'error';
$output['result'] = is_array($output['result']) ? implode('<br />', $output['result']) : null;
$output['points'] = $points;

echo json_encode($output);