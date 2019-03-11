<?php
	
	//echo range
	//echo $_SESSION['username'] === 'inartistic' ? password_hash('!vFP5XuKvrbq%Hmg', PASSWORD_DEFAULT) : null;
	
	/*$s = 'SELECT id, edit_history FROM labels';
	$t = $pdo->prepare($s);
	$t->execute();
	$r = $t->fetchAll();
	$num_r = count($r);
	$z = $num_r;
	
	echo '<pre>';
	echo 'INSERT INTO edits_labels (label_id, date_occurred, user_id) VALUES '."\n";
	for($i=0; $i<$num_r; $i++) {
		$r[$i]['edit_history'] = str_replace(["\r\n", ")2", "()"], ["\n", ")\n2", "(0)"], $r[$i]['edit_history']);
		//$r[$i]['edit_history'] .= ' (0)';
		//$r[$i]['edit_history'] = str_replace([") (0)", "\r\n", ' (', ')'], [")", "\n", ',', ''], $r[$i]['edit_history']);
		
		$x = explode("\n", $r[$i]['edit_history']);
		$x = array_filter($x);
		$z = $z + count($x);
		
		foreach($x as $y) {
			
			//if($y >= '2019') {
			
			echo '(';
			echo $r[$i]['id'];
			echo ',';
			echo '"';
			echo substr($y, -1) === ')' ? str_replace([' (', ')'], ['",', ''], $y) : $y.'",0';
			//echo substr($y, -3) === ':00' ? ',0' : null;
			echo '),';
			echo "\n";
			//if($y < '2018-12-14') {
				//echo '('.$r[$i]['id'].','.$y."),\n";
			//}
			//}
		}
	}
	//echo $z;
	echo '</pre>';*/
	
	/*$s2 = "LOAD DATA LOCAL INFILE 'https://vk.gy/test/musicians.csv'
	INTO TABLE edits_musicians
	FIELDS TERMINATED BY ','
	ENCLOSED BY '\"'
	LINES TERMINATED BY '\n'
	IGNORE 1 ROWS";
	$t2 = $pdo->prepare($s2);
	if($t2->execute()) { echo 'y'; } else { echo 'no'.print_r($pdo->errorInfo(), true); }*/
	
					/*$sql_artists_added = '
						SELECT COUNT(user_edits.artist_id) AS num_artists_added, users.username
						FROM (SELECT artist_id, MIN(date_occurred) as min_date_occurred FROM edits_artists GROUP BY artist_id) AS grouped_edits
						INNER JOIN edits_artists AS user_edits
						ON user_edits.artist_id=grouped_edits.artist_id AND user_edits.date_occurred=grouped_edits.min_date_occurred
						LEFT JOIN users ON users.id=user_edits.user_id
						GROUP BY user_edits.user_id
						ORDER BY num_artists_added DESC
					';*/
					/*$sql_artists_added = '
						SELECT COUNT(user_edits.musician_id) AS num_musicians_added, users.username
						FROM (SELECT musician_id, MIN(date_occurred) as min_date_occurred FROM edits_musicians GROUP BY musician_id) AS grouped_edits
						INNER JOIN edits_musicians AS user_edits
						ON user_edits.musician_id=grouped_edits.musician_id AND user_edits.date_occurred=grouped_edits.min_date_occurred
						LEFT JOIN users ON users.id=user_edits.user_id
						GROUP BY user_edits.user_id
						ORDER BY num_musicians_added DESC
					';*/
					//$stmt_artists_added = $pdo->prepare($sql_artists_added);
					//$stmt_artists_added->execute();
					//$num_artists_added = $stmt_artists_added->fetchAll();

//echo '<pre>'.print_r($num_artists_added, true).'</pre>';




//$s = 'SELECT users.username, COUNT(artists.id) AS num_artists FROM edits_artists LEFT JOIN users ON users.id=edits_artists.user_id LEFT JOIN artists ON artists.id=edits_artists.artist_id GROUP BY edits_artists.user_id ORDER BY num_artists DESC';
					//$t = $pdo->prepare($s);
					//$t->execute();
					//$r = $t->fetchAll();
					
					//echo '<pre>'.print_r($r, true).'</pre>';

/*SELECT article, dealer, price
FROM   shop s1
WHERE  price=(SELECT MAX(s2.price)
              FROM shop s2
              WHERE s1.article = s2.article)
ORDER BY article;



SELECT s1.article, s1.dealer, s1.price
FROM shop s1
LEFT JOIN shop s2 ON s1.article = s2.article AND s1.price < s2.price
WHERE s2.article IS NULL
ORDER BY s1.article;*/
					
					//echo '<pre>'.print_r($rslt_comments, true).'</pre>';
	if($_SESSION['username'] === 'inartistic') {
		//$limit_num = 500;
		$start = 2500;
		
		//$sql_musicians = 'SELECT id, edit_history FROM musicians OFFSET '.$start;
		//$stmt_musicians = $pdo->prepare($sql_musicians);
		//$stmt_musicians->execute();
		//$rslt_musicians = $stmt_musicians->fetchAll();
		
		////$sql_insert = 'INSERT INTO edits_musicians (musician_id, user_id, date_occurred) VALUES (?, ?, ?)';
		//$stmt_insert = $pdo->prepare($sql_insert);
		
		//echo '<textarea>';
		foreach($rslt_musicians as $a) {
			/*$musician_id = $a['id'];
			$edits = explode("\n", $a['edit_history']);
			$edits = array_filter($edits);
			$num_edits = count($edits);
			
			for($i=0; $i<$num_edits; $i++) {
				$date = substr($edits[$i], 0, 19);
				$user_id = substr($edits[$i], 21, -1);
				
				$stmt_insert->execute([ $musician_id, $user_id, $date ]);
				
				//$s = "INSERT INTO edits_musicians (musician_id, user_id, date_occurred) VALUES ('{$musician_id}', '{$user_id}', '{$date}');\n";
			}*/
		}
		//echo '</textarea>';
	}
?>