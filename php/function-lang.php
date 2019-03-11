<?php
function lang($english_text, $japanese_text, $options = []) {
	// Set default options
	$default_options = [
		'container' => null,
		'primary_container' => 'span',
		'primary_class' => null,
		'primary_attributes' => null,
		'secondary_container' => 'span',
		'secondary_class' => 'any--weaken',
		'secondary_attributes' => null,
	];
	$options = is_array($options) ? $options : [];
	$options = array_merge($default_options, $options);
	
	if(strlen($options['container'])) {
		$options['primary_container'] = $options['container'];
		$options['secondary_container'] = $options['container'];
	}
	
	// Clean text
	$english_text = sanitize($english_text);
	$japanese_text = sanitize($japanese_text);
	
	// Output html
	$output  =
		'<'.$options['primary_container'].' '.$options['primary_attributes'].' class="any--en '.$options['primary_class'].'">'.
		$english_text.
		'</'.$options['primary_container'].'>';
	$output .=
		'<'.$options['secondary_container'].' '.$options['secondary_attributes'].' class="any--ja '.$options['secondary_class'].'">'.
		$japanese_text.
		'</'.$options['secondary_container'].'>';
	
	return $output;
}