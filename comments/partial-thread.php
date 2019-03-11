<!-- Template: Comment thread container -->
<template id="comment-thread-template">
	<?php
		ob_start();
		
		?>
			<!-- Comment thread container -->
			<div class="comment__thread text">
				{comments}
			</div>
		<?php
		
		$comment_thread_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $comment_thread_template);
	?>
</template>