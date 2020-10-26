<?php

include_once('../php/class-parse_markdown.php');
$markdown_parser = new parse_markdown($pdo);

?>

<div class="col c4-AAAB">
	
	<div>
		
		<h2>
			
			<?= $list['name']; ?>
			
		</h2>
		
		<ul class="text">
			
			<?php foreach($list['items'] as $item): ?>
			<li>
				<?= $item['content']; ?>
			</li>
			<?php endforeach; ?>
			
		</ul>
		
	</div>
	
</div>