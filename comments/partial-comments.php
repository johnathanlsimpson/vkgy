<?php
	script([
		'/scripts/external/script-tribute.js',
		'/scripts/script-autosize.js',
		'/scripts/script-initTribute.js',
		'/scripts/script-signIn.js',
		'/comments/script-update.js',
	]);
	
	style([
		'/style/external/style-tribute.css',
		'/comments/style-partial-comments.css',
	]);
	
	include_once('../php/class-parse_markdown.php');
	$markdown_parser = new parse_markdown($pdo);
	
	function render_comment_component($component_template, $replacement_data) {
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
			$output = preg_replace('/'.'<!--.+?-->'.'/', '', $output);
			
			return $output;
		}
	}
	
	function render_default_comment_section($item_type, $item_id, $comments, $markdown_parser) {
		include('../comments/partial-thread.php');
		include('../comments/partial-comment.php');
		include('../comments/partial-commentate.php');
		
		// Echo current username, to be used when making new comment
		if($_SESSION['is_signed_in']) {
			?>
				<template id="comment-current-user"><?php echo $_SESSION['username']; ?></span></template>
			<?php
		}
		
		?>
			<div class="col c1">
				<div>
					<span id="comments"></span>
					<h2 class="h1">
						<?= lang('Comments', 'コメント', ['container' => 'div']); ?>
					</h2>
				</div>
			</div>
			
			<div class="col c2">
				<div class="comments__left">
					<!-- Add comment -->
					<?php
						echo render_comment_component($comment_thread_template, [
							'comments' => render_comment_component($commentate_template, [
									'comment_id' => null,
									'parent_id' => null,
									'item_type' => $item_type,
									'item_id' => $item_id,
									'item_url' => null,
									'thread_id' => null,
									'content' => null,
									'button_text' => 'Add',
									'name' => null,
									'username' => '******',
								])
						]);
					?>
				</div>
				<div>
					
					<!-- Other comments -->
					<?php
						if(is_array($comments) && !empty($comments)) {
							foreach($comments as $comment_thread) {
								$rendered_comments = [];
								
								foreach($comment_thread as $comment) {
									$rendered_comments[] = render_comment_component($comment_template, [
										'comment_id' => $comment['id'],
										'avatar_url' => '/usericons/avatar-'.($comment['user']['username'] ?: 'anonymous').'.png',
										'user_icon' => $comment['user']['icon'],
										'user_url' => $comment['user']['url'],
										'user_is_vip' => $comment['user']['is_vip'],
										'user_is_moderator' => $comment['user']['is_moderator'],
										'username' => $comment['user']['username'] ?: 'anonymous',
										'date_occurred' => $comment['date_occurred'],
										'markdown' => !$comment['is_deleted'] ? base64_encode(str_replace("  \n", "\n", $comment['content'])) : null, // Replace space+space linebreak with normal linebreak; this is added automatically to allow single linebreaks
										'is_user' => ($comment['anonymous_id'] && $_SESSION['anonymous_id'] === $comment['anonymous_id']) || ($_SESSION['is_signed_in'] && $_SESSION['user_id'] === $comment['user']['id']) ? '1' : '0',
										'is_admin' => $_SESSION['can_approve_data'] ? '1' : '0',
										'num_likes' => $comment['num_likes'] ?: '0',
										'is_liked' => is_numeric($_SESSION['user_id']) && $comment['liked_by_user_id'] === $_SESSION['user_id'] ? '1' : '0',
										'item_type' => $comment['item_type'],
										'item_id' => $comment['item_id'],
										'item_url' => $item_type === 'none' ? $comment['item_url'] : null,
										'content' => !$comment['is_deleted'] && ($comment['is_approved'] || (!$comment['is_approved'] && ($_SESSION['can_approve_data'] || $_COOKIE['anonymous_id'] === $comment['anonymous_id']))) ? $markdown_parser->parse_markdown($comment['content']) : '',
										'content' => !$comment['is_deleted'] && ($comment['is_approved'] || (!$comment['is_approved'] && ($_SESSION['can_approve_data'] || $_SESSION['anonymous_id'] === $comment['anonymous_id']))) ? $markdown_parser->parse_markdown($comment['content']) : '',
										'is_approved' => $comment['is_approved'] ? '1' : '0',
										'is_deleted' => $comment['is_deleted'] ? '1' : '0',
										'name' => !$comment['is_deleted'] && $comment['name'] ? sanitize($comment['name']) : null,
									]);
								}
								
								echo render_comment_component($comment_thread_template, [
									'comments' => implode("\n", $rendered_comments),
								]);
							}
						}
						else {
							echo '<div class="text text--outlined">No comments yet.</div>';
						}
					?>
				</div>
			</div>
		<?php
	}