<?php

// Helper to clear {variables} and print template
function clean_template($template) {
	return preg_replace('/'.'\{[^ ].*?[^ ]\}'.'/', '', $template);
}

// Populate a <template> element with data
function render_component($component_template, $replacement_data = []) {
	if($component_template && is_array($replacement_data)) {
		ob_start();
		
		foreach($replacement_data as $key => $value) {
			
			// Handle checkboxes and radios
			if( strpos($key, 'checked_') === 0 ) {
				
				// Replace {checked_item_id} with 3, in case we want the actual value somewhere
				$replacement_data['{'.$key.'}'] = $value;
				
				// Find {checked_item_id:3} and replace with 'checked'
				unset($replacement_data[$key]);
				$key = $key.':'.$value;
				$value = 'checked';
				
			}
			
			// Handle selects
			elseif( strpos($key, 'selected_') === 0 ) {
				
				// Find {selected_item_id:3} and replace with 'selected'
				unset($replacement_data[$key]);
				$key = $key.':'.$value;
				$value = 'selected';
				
			}
			
			$replacement_data['{'.$key.'}'] = $value;
			unset($replacement_data[$key]);
		}
		
		echo str_replace(
			array_keys($replacement_data),
			$replacement_data,
			$component_template
		);
		
		$output = ob_get_clean();
		//$output = preg_replace('/'.'{.+?}'.'/', '', $output);
		$output = clean_template($output);
		$output = preg_replace('/'.'<!--.+?-->'.'/', '', $output);
		
		return $output;
	}
}