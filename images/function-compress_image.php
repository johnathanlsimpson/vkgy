<?php

include_once('../php/include.php');
include_once('../php/external/class-tinify.php');

session_write_close();

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

// Get ID and extension
$id = $_POST['image_id'];
$extension = friendly($_POST['image_extension']);
$filename = '/images/image_files/'.$id.'.'.$extension;
$thumbnail = '/images/'.$id.'.thumbnail.'.$extension;

// Check ID and extension
if( is_numeric($id) && strlen($extension) && in_array($extension, $allowed_extensions) ) {
	
	// Make sure file exists
	if( file_exists('..'.$filename) ) {
		
		// There's an error with Tinify with certain images, but will have to fix later
		//unset($needs_compression);
		$needs_compression = true;
		
		// Compress
		if($needs_compression && $extension !== 'gif') {
			
			// Try Tinify
			try {
				
				// Get file, which has been uploaded to temporary directory, and send to Tinify
				if($source = \Tinify\fromFile('..'.$filename)) {
					
					// Grab compressed file and move to image directory
					if($source->toFile('..'.$filename)) {
						
						$output['status'] = 'success';
						$output['image_style'] = 'background-image:url('.$thumbnail.');';
						
					}
					else {
						$output['result'] = 'Couldn\'t save.';
					}
					
				}
				
			}
			
			// If Tinify error
			catch(\Tinify\Exception $e) {
				
				$output['result'] = 'Couldn\'t compress. '.'..'.$filename.' '.print_r($e, true);
				error_log(date('Y-m-d H:i:s').' Unable to compress file with Tinify: '.$filename."\n".print_r($e, true)."\n\n", 3, 'error_tinify_log.log');
				
			}
			
		}
		
	}
	else {
		$output['result'] = 'File doesn\t exist.';
	}
	
}
else {
	$output['result'] = 'No ID or extension: '.$id.' * '.$extension;
}

$output['status'] = $output['status'] ?: 'error';

echo json_encode($output);