<div class="col c1">
	<h1>
		<?php echo lang('Recent discussions', '最近のコメント', ['container' => 'div']); ?>
	</h1>
</div>

<?php
	include('../comments/partial-comments.php');
	render_default_comment_section('none', 0, $comments, $markdown_parser);
?>