<!-- Template: Individual comment -->
<template id="comment-template">
	<?php
		ob_start();
		
		?>
			<!-- Individual comment container -->
			<div class="comment__container any--flex" id="comment-{comment_id}" data-is-liked="{is_liked}" data-num-likes="{num_likes}" data-comment-id="{comment_id}" data-item-type="{item_type}" data-item-id="{item_id}" data-is-deleted="{is_deleted}" data-is-approved="{is_approved}" data-is-admin="{is_admin}" data-is-user="{is_user}">
				
				<!-- Empty container to display like decoration -->
				<span class="comment__likes">
					<?php
						for($i=0; $i<15; $i++) {
							?>
								<span class="comment__star symbol__star--<?php echo rand(0,1) ? 'full' : 'empty'; ?>"></span>
							<?php
						}
					?>
				</span>
				
				<!-- Left side, avatar -->
				<div class="comment__avatar" style="background-image: url({avatar_url}?<?php echo date('YmdH'); ?>);">
					<a class="comment__avatar-link" href="{user_url}"></a>
				</div>
				
				<!-- Right side, everything else -->
				<div class="comment__comment">
					
					<!-- Top, comment header -->
					<div class="any--flex comment__head">
						<div class="comment__authored any--weaken-color">
							<h5 class="comment__date">{date_occurred}</h5>
							<a class="user comment__user a--inherit" href="{user_url}">{username}</a>
							<span class="comment__name any--weaken-color">{name}</span>
						</div>
						
						<div class="comment__controls">
							<button class="any__note comment__like symbol__star--empty" type="button">Like</button>
							<button class="any__note comment__approve symbol__like" type="button">Approve</button>
							<button class="any__note comment__edit symbol__edit" type="button">Edit</button>
							<button class="any__note comment__delete symbol__trash" type="button">Delete</button>
						</div>
					</div>
					
					<!-- Bottom, moderation notice -->
					<div class="comment__moderation text text--outlined text--compact text--error symbol__error">
						This comment is awaiting approval.
					</div>
					
					<!-- Button, deleted notice -->
					<span class="comment__deleted any__note">comment deleted</span>
					
					<!-- Bottom, actual comment -->
					<div class="comment__content" data-markdown="{markdown}">{content}</div>
					
					<!-- Bottom, reply link -->
					<a class="a--padded a--outlined comment__reply symbol__arrow-right-circled" rel="nofollow">Reply <span class="any--weaken-size any--jp"><?php echo sanitize('(リプライ)'); ?></span></a>
					
				</div>
			</div>
		<?php
		
		$comment_template = ob_get_clean();
		echo preg_replace('/'.'\{.+?\}'.'/', '', $comment_template);
	?>
</template>