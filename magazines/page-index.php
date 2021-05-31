<?php

// ========================================================
// Page setup
// ========================================================

$page_header = lang('Magazines', '雑誌', 'div');

$page_title = 'Magazines (雑誌)';

?>

<div class="col c1">
	
	<h2>
		<?= lang('List of vkei magazines', 'ビジュアル系雑誌の一覧', 'div'); ?>
	</h2>
	
	<ul class="text">
		
		<?php foreach($magazines as $magazine): ?>
			<li>
				<a href="<?= $magazine['url']; ?>"><?= $magazine['romaji'] ? lang($magazine['romaji'], $magazine['name'], 'parentheses') : $magazine['name']; ?></a>
			</li>
		<?php endforeach; ?>
		
	</ul>
	
</div>