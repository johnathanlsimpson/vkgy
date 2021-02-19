<?php

include_once('../php/include.php');

$image_url = $_POST['image_url'];

if( strlen($image_url) ) {
	
	// Higher accuracy = longer loading; for vkei images, 4 seems to work best so far
	$accuracy_boost = 4;
	
	// Set other vars
	$api_url = 'https://face-detection6.p.rapidapi.com/img/face';
	$api_host = 'face-detection6.p.rapidapi.com';
	$api_key = '7428f07834msh3c52d201a82eb6dp1e0738jsn4385bf72f0a8';
	$post_fields = json_encode([
		'url' => $image_url,
		'accuracy_boost' => $accuracy_boost
	]);
	
	// Send image and get results via curl
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $post_fields,
		CURLOPT_HTTPHEADER => [
			'content-type: application/json',
			'x-rapidapi-host: '.$api_host,
			'x-rapidapi-key: '.$api_key
		],
	]);
	
	$response = curl_exec($curl);
	$error = curl_error($curl);
	curl_close($curl);
	
	if($error) {
		$output['result'] = $error;
	}
	
	// If no errors, format response
	else {
		
		$response2 = $response;
		$response = json_decode($response, true);
		
		// Make sure some faces were returned, then clean them up
		if( is_array($response) && is_array($response['detected_faces']) ) {
			
			$response = $response['detected_faces'];
			
			// Only want to save the coordinate data
			foreach($response as $response_key => $face) {
				
				$face = $face['BoundingBox'];
				
				$response[$response_key] = [
					'start_x' => $face['startX'],
					'start_y' => $face['startY'],
					'end_x' => $face['endX'],
					'end_y' => $face['endY'],
				];
				
			}
			
			$output['status'] = 'success';
			$output['result'] = json_encode($response);
			
		}
		else {
			
			$output['result'] = 'No faces detected.'.print_r($response2, true).print_r($_POST, true);
			
		}
		
	}
	
}
else {
	$output['result'] = 'No image specified.';
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);