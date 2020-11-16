<?php

$active_page = '/about/development/';

?>

<div class="col c2">
	
	<!-- Current entry -->
	<div>
		
		<div class="text--centered">
			<h2>
				<div class="h5">
					<?= $entry['date_occurred']; ?>
					by <?= $access_user->render_username($entry['user']); ?>
				</div>
				<?= $entry['is_issue'] ? 'Issue <span class="any__note">#'.$entry['id'].'</span>' : $entry['title']; ?>
			</h2>
		</div>
		
		<div class="text text--centered">
			<?= $markdown_parser->parse_markdown($entry['is_issue'] ? $entry['title'] : $entry['content']); ?>
		</div>
		
	</div>
	
	<!-- Other entries -->
	<div>
		<?php
			include('../comments/partial-comments.php');
			render_default_comment_section('development', $entry['id'], $entry['comments'], $markdown_parser);
		?>
	</div>
	
</div>
