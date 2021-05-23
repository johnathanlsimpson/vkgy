<?php

include_once('../php/include.php');

function generate_card( $blog_id ) {
	
	global $pdo;
	
	// Make sure we have an ID
	if( is_numeric($blog_id) ) {
		
		// Get blog friendly
		$sql_friendly = 'SELECT friendly FROM blog WHERE id=? LIMIT 1';
		$stmt_friendly = $pdo->prepare($sql_friendly);
		$stmt_friendly->execute([ $blog_id ]);
		$friendly = $stmt_friendly->fetchColumn();
		
		// Make sure we got blog
		if( strlen($friendly) ) {
			
			// Set card URL
			$card_url = 'https://vk.gy/blog/page-card.php?entry='.$friendly;
			
			// Init GrabzIt
			include_once('../php/external/function-init_grabzit.php');
			
			// Init options
			$options = new \GrabzIt\GrabzItImageOptions();
			
			// Make it grab intrinsic dimensions
			$options->setWidth(-1);
			$options->setHeight(-1);
			$options->setBrowserHeight(-1);
			
			// Output format
			$options->setFormat('jpg');
			
			// Element on page that will be grabbed
			$options->setTargetElement('#card');
			
			// Grab from this URL
			$grabzit->URLToImage($card_url, $options);
			
			// Save here
			if( $grabzit->SaveTo('../images/blog_images/'.$blog_id.'.jpg') ) {
				return true;
			}
			
		}
		
	}
	
}