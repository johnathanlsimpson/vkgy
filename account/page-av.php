<?php

include_once('../php/include.php');

// Capture parameters
parse_str($_SERVER['QUERY_STRING'], $parameters);
if(is_array($parameters) && !empty($parameters)) {
	$parameters = array_keys($parameters);
	$username = sanitize($parameters[0]);
	$theme = $parameters[1] === 'dark' ? 'dark' : 'light';
}
$username = strlen($username) && preg_match('/'.'^[A-z0-9-_\.]+$'.'/', $username) ? $username : 'anonymous';

// Set image variables
$image_width = 200;
$image_height = 200;
	
// Create base image
$image = imagecreatetruecolor($image_width, $image_height);
imagealphablending($image, true);
	
// Set image colors, fill
if($theme === 'light') {
	$color["bg"] = imagecolorallocate($image, 230,230,230);
}
elseif($theme === 'dark') {
	$color["bg"] = imagecolorallocate($image, 16,16,19);
}
imagefill($image,0,0,$color["bg"]);
	
// Add user avatar and logo
$avatar_img = '../usericons/avatar-'.$username.'.png';
$avatar_img = file_exists($avatar_img) ? $avatar_img : '../usericons/avatar-anonymous.png';
$avatar_img = imagecreatefrompng($avatar_img);
imagecopy($image, $avatar_img, 0, 0, 0, 0, 200, 200);

// Display image
header("Content-Type: image/jpeg");
imagejpeg($image, null, 100);