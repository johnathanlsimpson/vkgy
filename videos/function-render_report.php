<?php

include_once('../php/include.php');
include_once('../php/function-render_component.php');
include_once('../php/class-access_list.php');

style([
	'/videos/style-partial-report.css',
]);

script([
	'/videos/script-report.js',
]);

$allowed_report_types = [
	'no problem',
	'from new user',
	'unofficial source',
	'dead link',
	//'extreme content',
	//'incorrect'
];

// Report container
ob_start();
?>
	<div class="report__wrapper">
		
		<!-- Toggle button for dropdown -->
		<label class="report__open input__button input__checkbox" for="report-open">
			<input class="input__choice" id="report-open" type="checkbox" />
			<span class="symbol__error">report</span>
			<span class="symbol__down-caret symbol--standalone"></span>
		</label>
		
		<!-- Container with dropdown template for tippy -->
		<template id="template-report-container">
			<ul class="report__container">
				{report_items}
			</ul>
		</template>
		
	</div>
<?php
$report_dropdown_template = ob_get_clean();

// List item
ob_start();
?>
	<li class="report__item any--weaken">
		<label class="report__button input__radio" data-item-id="{item_id}">
			
			<input class="report__choice input__choice" name="report_type" type="radio" value="{report_type}" {checked} />
			<span class="symbol__unchecked" data-role="status">{report_name}</span>
			
		</label>
	</li>
<?php
$report_item_template = ob_get_clean();

function render_report_dropdown($item_data) {
	
	global $pdo;
	global $allowed_report_types;
	global $report_button_template, $report_item_template, $report_container_template, $report_dropdown_template;
	
	// For each list, save li > button component
	foreach($allowed_report_types as $report_key => $report_name) {
		
		$report_items[] = render_component($report_item_template, [
			'item_id'       => $item_data['item_id'],
			'report_name'   => $report_name,
			'report_type'   => $report_key,
			'checked'       => $item_data['is_flagged'] == $report_key ? 'checked' : null,
		]);
	}
	
	// Now get the dropdown toggle button, put the lists container within that, and put each list item in that container
	$report_dropdown = render_component($report_dropdown_template, [
		'report_items' => implode("\n", $report_items),
	]);
	
	// Return output
	return $report_dropdown;
	
}