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
		'secondary_parentheses' => false,
	];
	
	// Frequently used option sets
	if($options === 'hidden') {
		$options = [
			'container' => 'span',
			'secondary_class' => 'any--hidden',
		];
	}
	elseif($options === 'div') {
		$options = [
			'container' => 'div',
			'secondary_class' => 'any--weaken',
		];
	}
	elseif($options === 'parentheses') {
		$options = [
			'container' => 'span',
			'secondary_class' => 'any--weaken',
			'secondary_parentheses' => true,
		];
	}
	
	// Merge default & sets options
	$options = is_array($options) ? $options : [];
	$options = array_merge($default_options, $options);
	
	// If primary/secondary container not specified, get from generic
	if(strlen($options['container'])) {
		$options['primary_container'] = $options['container'];
		$options['secondary_container'] = $options['container'];
	}
	
	// Clean text
	$english_text = sanitize($english_text, "allowhtml");
	$japanese_text = sanitize($japanese_text, "allowhtml");
	
	// Output html
	$output  =
		'<'.$options['primary_container'].' '.$options['primary_attributes'].' class="any--en '.$options['primary_class'].'">'.
			$english_text.
		'</'.$options['primary_container'].'>';
	$output .=
		'<'.$options['secondary_container'].' '.$options['secondary_attributes'].' class="any--ja '.$options['secondary_class'].'">'.
			($options['secondary_parentheses'] ? ' <span class="any--en">(</span>' : null).
				$japanese_text.
			($options['secondary_parentheses'] ? '<span class="any--en">)</span>' : null).
		'</'.$options['secondary_container'].'>';
	
	return $output;
}