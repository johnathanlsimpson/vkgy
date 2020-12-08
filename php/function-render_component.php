<?php

// Helper to clear {variables} and print template
function clean_template($template) {
	return preg_replace('/'.'\{.+?\}'.'/', '', $template);
}

// Populate a <template> element with data
function render_component($component_template, $replacement_data = []) {
	if($component_template && is_array($replacement_data)) {
		ob_start();
		
		foreach($replacement_data as $key => $value) {
			$replacement_data['{'.$key.'}'] = $value;
			unset($replacement_data[$key]);
		}
		
		echo str_replace(
			array_keys($replacement_data),
			$replacement_data,
			$component_template
		);
		
		$output = ob_get_clean();
		$output = preg_replace('/'.'{.+?}'.'/', '', $output);
		$output = preg_replace('/'.'<!--.+?-->'.'/', '', $output);
		
		return $output;
	}
}