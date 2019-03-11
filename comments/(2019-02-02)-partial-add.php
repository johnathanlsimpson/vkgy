<?php
	$user_avatar_url  = '/usericons/avatar-';
	$user_avatar_url .= $_SESSION['loggedIn'] && file_exists('../usericons/avatar-'.$_SESSION['username'].'.png') ? $_SESSION['username'] : 'anonymous';
	$user_avatar_url .= '.png';
?>

<!-- Template: Individual comment -->
<template id="comment-template">
	<?php
		ob_start();
		
		?>
			<!-- Individual comment container -->
			<div class="comment__container any--flex" data-id="{id}">
				
				<!-- Left side, avatar -->
				<div class="comment__avatar" style="background-image: url({avatar_url});">
					<a class="comment__avatar-link" href="{user_url}"></a>
				</div>
				
				<!-- Right side, everything else -->
				<div class="comment__comment">
					
					<!-- Top, comment header -->
					<div class="any--flex comment__head">
						<div class="comment__authored any--weaken-color">
							<h5 class="comment__date">{date_occurred}</h5>
							<a class="user comment__user a--inherit" href="{user_url}">{username}</a>
						</div>
						
						<div class="comment__controls" data-is-admin="{is_admin}" data-is-user="{is_user}">
							<a class="any__note comment__edit symbol__edit" rel="nofollow">Edit</a>
							<a class="any__note comment__delete symbol__trash" rel="nofollow">Delete</a>
						</div>
					</div>
					
					<!-- Bottom, actual comment -->
					<div class="comment__content">{content}</div>
					
					<!-- Bottom, reply link -->
					<a class="a--padded a--outlined comment__reply symbol__arrow-right-circled" rel="nofollow">Reply to this thread</a>
					
				</div>
			</div>
		<?php
		
		$comment_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $comment_template);
	?>
</template>

<!-- Template: Add/edit comment form -->
<template id="add-comment-template">
	<?php
		ob_start();
		
		?>
			<form action="/comments/function-update.php" class="commentate__container" enctype="multipart/form-data" method="post" name="form__commentate">
				
				<!-- Hidden inputs -->
				<input class="any--hidden" name="id" type="hidden" value="{id}" hidden />
				<input class="any--hidden" name="item_id" type="hidden" value="{item_id}" hidden />
				<input class="any--hidden" name="item_type" type="hidden" value="{item_type}" hidden />
				<input class="any--hidden" name="parent_id" type="hidden" value="{parent_id}" hidden />
				
				<!-- Where you leave your comment -->
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<label class="input__label">Comment</label>
						<textarea class="commentate__content input__textarea any--flex-grow" name="content" placeholder="your comment...&#10;<?php echo sanitize('あなたのコメント...'); ?>">{content}</textarea>
					</div>
				</div>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<button class="input__button any--flex-grow" name="submit" type="submit">{button_text} comment <?php echo sanitize('(コメントする)'); ?></button>
					</div>
				</div>
				
				<!-- This section shows when you're not signed in -->
				<div class="commentate__anonymous any--signed-out-only">
					<hr />
					
					<!-- Notice -->
					<div class="text text--outlined text--notice">
						<span class="symbol__help"></span>
						<span class="any--en">Your comment will be posted anonymously. Would you like to sign in or add a handle name?<br /></span>
						<span class="any--jp any--weaken"><?php echo sanitize('コメントは匿名で投稿されます。 サインインしますか、それともハンドル名を追加しますか?'); ?></span>
					</div>
					
					<!-- Introduce yourself by setting handlename or signing in -->
					<div class="any--flex commentate__introduce">
						
						<!-- Set your handlename -->
						<div class="any--flex-grow commentate__name">
							<h4>
								Add handlename
							</h4>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Name</label>
									<input class="any--flex-grow" name="name" placeholder="your name <?php echo sanitize('(ハンドル名)'); ?>" value="{name}" />
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<button class="input__button any--flex-grow" name="add-name" type="submit">Add name <?php echo sanitize('(名を追加します)'); ?></button>
								</div>
							</div>
						</div>
						
						<!-- Sign in -->
						<div class="any--flex-grow commentate__sign-in">
							<h4>
								Sign in
							</h4>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">username</label>
									<input class="any--flex-grow" name="username" placeholder="username <?php echo sanitize('(ユーザ名)'); ?>"></textarea>
								</div>
								<div class="input__group any--flex-grow">
									<label class="input__label">password</label>
									<input class="any--flex-grow" name="password" placeholder="password <?php echo sanitize('(パスワード)'); ?>" type="password">
								</div>
							</div>
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<button class="input__button any--flex-grow" name="sign-in" type="submit">Sign in <?php echo sanitize('(サインイン)'); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
				
			</form>
		<?php
		
		$commentate_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $commentate_template);
	?>
</template>

<!-- Test area -->
<div class="col c1">
	<div>
		<h2>
			<div class="any--en">
				Comments
			</div>
			<div class="any--jp any--weaken">
				<?php echo sanitize('コメント'); ?>
			</div>
		</h2>
		
		<div class="text any--flex-grow">
			<!-- Add comment -->
			<?php
				echo str_replace(
					[
						'{id}',
						'{parent_id}',
						'{item_type}',
						'{item_id}',
						'{content}',
						'{button_text}',
						'{name}',
					],
					[
						null,
						null,
						'blog',
						1234,
						null,
						'Add',
						null,
					],
					$commentate_template
				);
			?>
		</div>
			
			<!-- Sample comment -->
			<?php
				foreach($entry['comments'] as $comment_thread) {
					?>
						<div class="comment__thread text">
							<?php
								foreach($comment_thread as $comment) {
									echo str_replace(
										[
											'{id}',
											'{avatar_url}',
											'{user_url}',
											'{username}',
											'{date_occurred}',
											'{content}',
											'{is_user}',
											'{is_admin}',
										],
										[
											$comment['id'],
											'/usericons/avatar-'.$comment['user']['username'].'.png',
											'/users/'.$comment['user']['username'].'/',
											$comment['user']['username'],
											$comment['date_occurred'],
											$comment['content'],
											$_SESSION['userID'] === $comment['user']['id'] ? '1' : '0',
											$_SESSION['admin'] ? '1' : '0',
										],
										$comment_template
									);
								}
								
								echo str_replace(
									[
										'{id}',
										'{avatar_url}',
										'{user_url}',
										'{username}',
										'{date_occurred}',
										'{content}',
										'{is_user}',
										'{is_admin}',
									],
									[
										1234,
										$user_avatar_url,
										'/users/'.$_SESSION['username'].'/',
										$_SESSION['username'],
										date('Y-m-d H:i:s'),
										'Blah blah testing...',
										'0',
										'0',
									],
									$comment_template
								);
							?>
						</div>
					<?php
				}
				
				/*echo str_replace(
					[
						'{id}',
						'{avatar_url}',
						'{user_url}',
						'{username}',
						'{date_occurred}',
						'{content}',
					],
					[
						1234,
						'/usericons/avatar-suji.png',
						'/users/'.$_SESSION['username'].'/',
						$_SESSION['username'],
						date('Y-m-d H:i:s'),
						'<p>Blah blah testing...</p><p>cats</p>',
					],
					$comment_template
				);
				
				echo str_replace(
					[
						'{id}',
						'{avatar_url}',
						'{user_url}',
						'{username}',
						'{date_occurred}',
						'{content}',
					],
					[
						1234,
						'/usericons/avatar-anonymous.png',
						'/users/'.$_SESSION['username'].'/',
						$_SESSION['username'],
						date('Y-m-d H:i:s'),
						'Blah blah testing...',
					],
					$comment_template
				);*/
			?>
		
	</div>
</div>

<style>
	.comment__container {
		background-image: linear-gradient(var(--background--bold), var(--background--bold));
		background-position: calc(50px / 2);
		background-repeat: no-repeat;
		background-size: 2px 100%;
		padding-bottom: 1.5rem;
	}
	.comment__container:last-of-type {
		background-image: none;
		padding-bottom: 0;
	}
	.comment__comment {
		flex: 1;
	}
	.comment__head {
		align-items: flex-start;
		justify-content: space-between;
		flex-wrap: wrap;
		padding-bottom: 1rem;
	}
	.comment__avatar.comment__avatar {
		background-color: var(--background--faint);
		background-position: 0;
		background-repeat: no-repeat;
		background-size: contain;
		border-radius: 50%;
		float: none;
		height: 50px;
		margin: 0;
		margin-right: 1rem;
		width: 50px;
	}
	.comment__avatar-link {
		border-radius: 50%;
		display: inline-block;
		height: 100%;
		width: 100%;
	}
	.comment__avatar-link:hover {
		background: var(--background--faint);
		opacity: 0.25;
	}
	.comment__authored {
		margin-right: 1rem;
	}
	/**/
	.comment__controls * {
		margin: 0;
	}
	.comment__content {
		border-bottom: 1px dotted var(--background--bold);
		padding-bottom: 1rem;
	}
	.comment__container:last-of-type .comment__content {
		border-bottom: none;
		padding-bottom: 0;
	}
	.comment__reply {
		display: none;
	}
	.comment__container:last-of-type .comment__content + .comment__reply {
		display: inline-block;
		margin-top: 1rem;
	}
	.comment__edit, .comment__delete  {
		display: none;
	}
	.comment__controls[data-is-admin="1"] .comment__delete,
	.comment__controls[data-is-user="1"] .comment__edit,
	.comment__controls[data-is-user="1"] .comment__delete {
		display: inline-block;
	}
	
	.comment__content + .commentate__container {
		border-top: 1px dotted var(--background--bold);
		margin-top: 1rem;
		padding-top: 1rem;
	}
</style>

<script>
	var commentateTemplate = document.querySelector('#add-comment-template');
	var commentTemplate = document.querySelector('#comment-template');
	var currentUsername = '<?php echo $_SESSION['loggedIn'] ? $_SESSION['username'] : 'anonymous'; ?>';
	
	function populateComment(commentElem, commentData) {
		commentElem.querySelector('.comment__user').href      = '/users/' + (commentData.username ? commentData.username : 'anonymous') + '/';
		commentElem.querySelector('.comment__user').innerHTML = commentData.username ? commentData.username : 'anonymous';
		commentElem.querySelector('.comment__date').innerHTML = commentData.dateOccurred ? commentData.dateOccurred : new Date(Date.now()).toISOString().substring(0, 19).replace('T', ' ');
		commentElem.querySelector('[name=parent_id]').value   = commentData.parentId ? commentData.parentId : null;
		
		return commentElem;
	}
	
	function editInline(targetElem) {
		
	}
	
	function replyInline(replyButton) {
		var threadContainer = replyButton.parentNode.parentNode.parentNode;
		var newCommentTemplate = document.importNode(commentTemplate.content, true);
		var newCommentContent = newCommentTemplate.querySelector('.comment__content');
		var newCommentateTemplate = document.importNode(commentateTemplate.content, true);
		
		var newCommentData = {
			'username' : currentUsername,
			'parent_id' : threadContainer.querySelector('.comment__container:first-of-type').dataset.parentId,
		};
		
		newCommentContent.replaceWith(newCommentateTemplate);
		newCommentTemplate = populateComment(newCommentTemplate, newCommentData);
		
		threadContainer.appendChild(newCommentTemplate);
		threadContainer.querySelector('.commentate__content').focus();
	}
	
	function initReplyButton(targetElem = null) {
		if(targetElem) {
			var targetElems = [ targetElem ];
		}
		else {
			var targetElems = document.querySelectorAll('.comment__reply');
		}
		
		targetElems.forEach(function(elem) {
			elem.addEventListener('click', function() {
				replyInline(elem);
			});
		});
	}
	
	initReplyButton();
	
	/*var y = document.querySelectorAll('.comment__reply');
	y.forEach(function(elem) {
		elem.addEventListener('click', function() {
			replyInline(elem);
		});
	});*/
	
	//var x = document.getElementById('sample-comment');
	//x.classList.add('show');
	
	//var commentTemplate = document.querySelector('#comment-template');
	
	//document.querySelector('#sample-comment').appendChild(document.importNode(commentTemplate.content, true));
</script>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	