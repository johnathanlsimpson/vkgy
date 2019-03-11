<?php

if($_SESSION['username'] === 'inartistic') {
	$s = 'SELECT user_id, content FROM users_avatars';
	$t = $pdo->prepare($s);
	$t->execute();
	$r = $t->fetchAll();
	
	$replacements = [
		'caramel' => 'brown-medium',
		'red' => 'red-medium',
		'pink' => 'pink-pastel',
		'blonde' => 'blonde-medium',
		'green' => 'green-dark',
		'blue' => 'blue-medium',
		'maroon' => 'red-dark',
		'blush' => 'pink-pastel',
		'purple' => 'purple-dark',
		'green' => 'green-dark',
		'blue' => 'blue-medium',
		'pink' => 'pink-pastel',
	];
	$searches = array_keys($replacements);
	
	for($i=0; $i<count($r); $i++) {
		$content = (array) json_decode($r[$i]['content']);
		
		//echo $r[$i]['user_id'];
		//echo '<pre>'.print_r($content, true).'</pre>';
		
		foreach($content as $key => $value) {
			if(substr($key, -6) === '-color' && in_array($value, $searches)) {
				$content[$key] = $replacements[$value];
			}
			elseif($key === 'hair__base' && $value === 'elegant') {
				$content[$key] = 'curls';
			}
		}
		
		$content['hair__left'] = $content['hair__base'];
		$content['hair__right'] = $content['hair__base'];
		$content['hair__left-color'] = $content['hair__base-color'];
		$content['hair__right-color'] = $content['hair__base-color'];
		unset($content['hair__base'], $content['hair__base-color']);
		
		//echo $r[$i]['user_id'];
		//echo '<pre>'.print_r($content, true).'</pre>';
		//echo json_encode($content);
		//echo '<br /><br />';
		//echo $r[$i]['content'];
		
		//echo '<hr />';
		
		//if($r[$i]['user_id'] === '1') {
			$sql_update = 'UPDATE users_avatars SET content=? WHERE user_id=? LIMIT 1';
			$stmt_update = $pdo->prepare($sql_update);
			if($stmt_update->execute([ json_encode($content), $r[$i]['user_id'] ])) {
				echo 'y';
			}
			else {
				echo 'n';
			}
		//}
	}
}


/*

caramel->brown-medium
red->red-medium
pink->pink-pastel
blonde->blonde-medium
green->green-dark
blue->blue-medium

maroon->red-dark
blush->pink-pastel
purple->purple-dark
nude->nude
green->green-dark
blue->blue-medium
pink->pink-pastel

*/