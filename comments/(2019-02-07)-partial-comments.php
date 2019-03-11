<?php
	script([
		'/comments/script-update.js',
	]);
	
	style([
		'/comments/style-partial-comments.css',
	]);
	
	include_once('../comments/partial-thread.php');
	include_once('../comments/partial-comment.php');
	include_once('../comments/partial-commentate.php');
?>

<div class="col c1">
	<div>
		<span id="comments"></span>
		<h2>
			<div class="any--en">
				Comments
			</div>
			<div class="any--jp any--weaken">
				<?php echo sanitize('コメント'); ?>
			</div>
		</h2>
		
		<!-- Add comment -->
		<?php
			ob_start();
			
			$replacements = [
				'{comment_id}' => null,
				'{parent_id}' => null,
				'{item_type}' => $entry['type'],
				'{item_id}' => $entry['id'],
				'{thread_id}' => null,
				'{content}' => null,
				'{button_text}' => 'Add',
				'{name}' => null,
			];
			
			echo str_replace(
				array_keys($replacements),
				$replacements,
				$commentate_template
			);
			
			$commentate = ob_get_clean();
			echo preg_replace('/'.'<!--.+?-->'.'/', '', str_replace('{comments}', $commentate, $comment_thread_template));
		?>
		
		<!-- Other comments -->
		<?php
			if(is_array($entry['comments']) && !empty($entry['comments'])) {
				foreach($entry['comments'] as $comment_thread) {
					ob_start();
					foreach($comment_thread as $comment) {
						
						$replacements = [
							'{comment_id}' => $comment['id'],
							'{avatar_url}' => '/usericons/avatar-'.($comment['user']['username'] ?: 'anonymous').'.png',
							'{user_url}' => '/users/'.($comment['user']['username'] ?: 'anonymous').'/',
							'{username}' => $comment['user']['username'] ?: 'anonymous',
							'{date_occurred}' => $comment['date_occurred'],
							'{markdown}' => !$comment['is_deleted'] ? base64_encode($comment['content']) : null,
							'{is_user}' => ($_COOKIE['anonymous_id'] === $comment['anonymous_id']) || ($_SESSION['loggedIn'] && $_SESSION['userID'] === $comment['user']['id']) ? '1' : '0',
							'{is_admin}' => $_SESSION['admin'] ? '1' : '0',
							'{item_type}' => $entry['type'],
							'{item_id}' => $entry['id'],
							'{content}' => !$comment['is_deleted'] && ($comment['is_approved'] || (!$comment['is_approved'] && ($_SESSION['admin'] || $_COOKIE['anonymous_id'] === $comment['anonymous_id']))) ? $markdown_parser->parse_markdown($comment['content']) : '',
							'{is_approved}' => $comment['is_approved'] ? '1' : '0',
							'{is_deleted}' => $comment['is_deleted'] ? '1' : '0',
							'{name}' => !$comment['is_deleted'] && $comment['name'] ? sanitize($comment['name']) : null,
						];
						
						echo str_replace(
							array_keys($replacements),
							$replacements,
							$comment_template
						);
					}
					
					$comments = ob_get_clean();
					echo preg_replace('/'.'<!--.+?-->'.'/', '', str_replace('{comments}', $comments, $comment_thread_template));
				}
			}
		?>
	</div>
</div>