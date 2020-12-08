<?php

include_once('../php/include.php');
include_once('../php/function-render_component.php');

style([
	'/votes/style-partial-vote.css',
]);

script([
	'/votes/script-vote.js',
]);

?>

<template id="template-vote">
	<?php ob_start(); ?>
		
		<span class="vote__container {direction_class} any--weaken-color" data-item-id="{item_id}" data-item-type="{item_type}">
			
			<label class="vote__label vote--upvote input__checkbox">
				<input class="vote__vote input__choice" type="checkbox" {upvote_is_checked} />
				<span class="vote__arrow symbol__up-caret symbol--standalone"></span>
			</label>
			
			<span class="vote__score any--weaken-size" data-score="{score}"></span>
			
			<label class="vote__label vote--downvote input__checkbox">
				<input class="vote__vote input__choice" type="checkbox" {downvote_is_checked} />
				<span class="vote__arrow symbol__down-caret symbol--standalone"></span>
			</label>
			
		</span>
		
	<?php $vote_template = ob_get_clean(); ?>
	<?= clean_template($vote_template); ?>
</template>