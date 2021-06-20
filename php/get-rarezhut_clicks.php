<?php

include_once('../php/include.php');

$allowed_users = [
	'biopanda',
	'inartistic',
];

if( in_array( $_SESSION['username'], $allowed_users ) ) {
	
	$rarezhut_id = 3;
	
	$sql_views = 'SELECT SUBSTRING(date_occurred,1,10) AS date_occurred, outbound_url, current_page, location_in_page FROM views_outbound WHERE platform_id=? ORDER BY date_occurred DESC';
	$stmt_views = $pdo->prepare($sql_views);
	$stmt_views->execute([ $rarezhut_id ]);
	$rslt_views = $stmt_views->fetchAll();
	
	echo '<table>';
	
	foreach($rslt_views as $view_key => $view) {
		
		if( $view_key === 0 ) {
			
			echo '<tr>';
			
			foreach( $view as $key => $value ) {
				echo '<th>'.$key.'</th>';
			}
			
			echo '</tr>';
			
		}
		
		echo '<tr>';
		
		foreach( $view as $key => $value ) {
			echo '<td>'.$value.'</td>';
		}
		
		echo '</tr>';
		
	}
	
	echo '</table>';
	
}