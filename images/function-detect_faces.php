<?php

function detect_faces($image) {
	
	$curl = curl_init();
	
	$accuracy_boost = 4;
	
	$api_url = 'https://face-detection6.p.rapidapi.com/img/face';
	$api_host = 'face-detection6.p.rapidapi.com';
	$api_key = '7428f07834msh3c52d201a82eb6dp1e0738jsn4385bf72f0a8';
	$post_fields = json_encode([
		'url' => $image,
		'accuracy_boost' => $accuracy_boost
	]);
	
	curl_setopt_array($curl, [
		CURLOPT_URL => $api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
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
		return $error;
	}
	else {
		
		$response = json_decode($response, true);
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
		
		return json_encode($response);
		
	}
	
}