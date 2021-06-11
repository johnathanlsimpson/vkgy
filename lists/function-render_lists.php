<?php

include_once('../php/include.php');
include_once('../php/function-render_component.php');
include_once('../php/class-access_list.php');

style([
	'/lists/style-partial-lists.css',
]);

script([
	'/lists/script-list.js',
]);

$allowed_item_types = array_keys(access_list::$allowed_item_types);

?>
	<template id="template-list-button">
		<?php
			ob_start();
			?>
				<label class="list__button {private_class} input__checkbox" data-list-id="{list_id}" data-item-id="{item_id}" data-item-type="{item_type}">
					<input class="list__choice input__choice" type="checkbox" {checked} />
					<span class="symbol__unchecked" data-role="status">{list_name}</span>
				</label>
				<a class="list__arrow symbol__arrow" href="{list_url}"></a>
			<?php
			$list_button_template = ob_get_clean();
			echo preg_replace('/'.'\{.+?\}'.'/', '', $list_button_template);
		?>
	</template>
<?php



?>
	<template id="template-lists-item">
		<?php
			ob_start();
			?>
				<li class="lists__item any--weaken">
					{list_button}
				</li>
			<?php
			$lists_item_template = ob_get_clean();
			echo preg_replace('/'.'\{.+?\}'.'/', '', $lists_item_template);
		?>
	</template>
<?php



?>
	<template data-contains="list-items"><?php
		
		// Get all user's lists and attach all items--we'll use this to set 'checked' for each listbutton
		$sql_lists = '
			SELECT lists.id AS list_id, lists_items.item_id, lists_items.item_type
			FROM lists
			LEFT JOIN lists_items ON lists_items.list_id=lists.id
			WHERE lists.user_id=? AND lists_items.list_id IS NOT NULL';
		$stmt_lists = $pdo->prepare($sql_lists);
		$stmt_lists->execute([ $_SESSION['user_id'] ]);
		$rslt_lists = $stmt_lists->fetchAll();
		
		// Save the results as an array so we can spit it out to json and parse it later
		foreach($rslt_lists as $list_item) {
			
			// Get text name for item type
			$item_type = $allowed_item_types[ $list_item['item_type'] ];
			
			// Save any items
			$list_items[ $list_item['list_id'] ][ $item_type ][ $list_item['item_id'] ] = 1;
			
		}
		
		echo json_encode($list_items);
		
	?></template>
<?php



?>
	<template id="template-lists-container">
		<?php
			ob_start();
			?>
				<ul class="lists__container">
					{lists_items}
					
					<li class="lists__item">
						<form action="/lists/function-update_list.php" class="lists__new" enctype="multipart/form-data" method="post" name="add_list">
							
							<input name="item_id" value="{item_id}" hidden />
							<input name="item_type" value="{item_type}" hidden />
							
							<label class="input__label">new list</label>
							<input class="lists__name any--flex-grow" name="name" placeholder="name" />
							<button class="lists__add symbol__plus symbol--standalone" name="submit" type="submit"></button>
							<span class="lists__status" data-role="status"></span>
							
							<div class="lists__result text text--outlined text--error text--compact" data-role="result"></div>
							
						</form>
					</li>
					
				</ul>
			<?php
			$lists_container_template = ob_get_clean();
			
			// Given item ID and item type, get all lists, and whether or not that item is currently in the list
			$sql_lists = '
				SELECT
					lists.*
				FROM
					lists
				WHERE
					lists.user_id=?';
			$stmt_lists = $pdo->prepare($sql_lists);
			$stmt_lists->execute([ $_SESSION['user_id'] ]);
			$rslt_lists = $stmt_lists->fetchAll();
			
			// For each list, save li > button component
			if( is_array($rslt_lists) && !empty($rslt_lists) ) {
				foreach($rslt_lists as $list) {
					$lists_items[] = render_component($lists_item_template, [
						'list_button' => render_component($list_button_template, [
							'list_id'       => $list['id'],
							'list_name'     => $list['name'],
							'list_url'      => '/lists/'.$list['id'].'/'.($list['friendly'] ? $list['friendly'].'/' : null),
							'item_id'       => $item_data['item_id'],
							'item_type'     => $item_data['item_type'],
							'checked'       => $list['is_listed'] ? 'checked' : null,
							'private_class' => $list['is_private'] ? 'list--private symbol__locked' : null,
						]),
					]);
				}
			}
			else {
				$lists_items = [];
			}
				
				echo render_component($lists_container_template, [
					'lists_items' => implode("\n", $lists_items),
				]);
			
		?>
	</template>
<?php



?>
	<template id="template-lists-dropdown">
		<?php
			ob_start();
			?>
				<div class="lists__wrapper" data-item-id="{item_id}" data-item-type="{item_type}">
					
					<!-- Toggle button for dropdown -->
					<input class="lists__choice input__choice" type="checkbox" />
					<label class="lists__open input__button input__checkbox">
						<span class="symbol__list">add to list</span>
						<span class="symbol__triangle symbol--down symbol--standalone"></span>
					</label>
					
					<!-- Container with dropdown template for tippy -->
					{lists_container}
					
				</div>
			<?php
			$lists_dropdown_template = ob_get_clean();
			echo preg_replace('/'.'\{.+?\}'.'/', '', $lists_dropdown_template);
		?>
	</template>
<?php



function render_lists_dropdown($item_data) {
	
	if( !$_SESSION['is_signed_in'] ) {
		return '<span class="any--weaken"><a class="a--inherit" href="/account/">sign in</a> to add this to a list</span>';
	}
	
	global $pdo;
	global $allowed_item_types;
	global $list_button_template, $lists_item_template, $lists_container_template, $lists_dropdown_template;
	
	// Now get the dropdown toggle button, put the lists container within that, and put each list item in that container
	$lists_dropdown = render_component($lists_dropdown_template, $item_data);
	
	// Return output
	return $lists_dropdown;
	
}