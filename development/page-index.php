<?php

include_once('../votes/function-render_vote.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

script([
	'/about/script-issues.js',
]);

?>

<style>
	/* Spacing for prose columns */
	@media(min-width:700px) {
		.col--prose {
			display: grid;
			grid-template-columns: [left] fit-content(200px) [center] minmax(0, 800px) [right] fit-content(200px);
			justify-content: center;
			margin: 0 calc( -1 * var(--gutter) / 2 );
			width: calc(100% + var(--gutter));
		}
		.col--prose > * {
			padding: 0 calc( var(--gutter) / 2 );
			width: 100%;
		}
	}
	.col--main {
		grid-column: center;
	}
	.col--side {
		background: hsl(var(--background--alt));
		margin-top: -1rem;
		padding: 1rem 0;
	}
	.col--side h2::before,
	.col--side h3::before {
		display: none;
	}
	.col--side .text {
		border-color: transparent;
	}
	
	/* Base sizing for prose text box */
	:root {
		--prose-padding: 1rem;
		--prose-width: 550px;
	}
	.text--prose {
		background-color: transparent;
		border: none;
	}
	.text--prose > p,
	.text--prose > ol,
	.text--prose > ul,
	.text--prose hr {
		max-width: 100%;
		width: var(--prose-width);
	}
	/*.text--prose hr {
		margin-left: calc(-1 * var(--prose-padding));
		width: calc(var(--prose-padding) + var(--prose-width) + var(--prose-padding));
	}*/
	
	/* Prose headings */
	.text--prose h1,
	.text--prose h2,
	.text--prose h3 {
		margin: 1rem 0;
		padding: 0;
	}
	.text--prose ul + h1,
	.text--prose ul + h2,
	.text--prose ul + h3,
	.text--prose ol + h1,
	.text--prose ol + h2,
	.text--prose ol + h3 {
		margin-top: 3rem;
	}
	.text--prose h2::before {
		background: hsl(var(--attention--secondary));
		background-clip: content-box;
		bottom: 0;
		content: "";
		display: inline-block;
		left: -1rem;
		position: absolute;
		top: 0;
		width: 3px;
	}
	.text--prose h3 {
		color: hsl(var(--attention--secondary));
		font-weight: bold;
	}
	
	/* Prose modules */
	.text--prose .module {
		max-width: calc(var(--prose-padding) + 100% + var(--prose-padding));
		padding: 0;
	}
	.text--prose .module--release {
		width: calc(var(--prose-padding) + var(--prose-width) + var(--prose-padding));
	}
	.text--prose .release-card__container {
		overflow: visible;
	}
	.text--prose .release-card__artist-image {
		display: none;
	}
	.text--prose .release-card__left {
		box-shadow: none;
	}
	
	/* Prose image styling */
	.module--image {
		overflow: hidden;
	}
	.module--image a {
		width: 100%;
	}
	.module--image::after {
		background: linear-gradient(hsla(var(--background),0), hsla(var(--background),1));
		content: "";
		display: block;
		height: 1.5rem;
		position: absolute;
		top: calc(500px - 1.5rem);
		width: 100%;
	}
	.module--image img {
		max-height: 500px;
		object-fit: cover;
		vertical-align: middle;
		width: 100%;
	}
	.module--portrait {
		width: calc(550px + 2rem);
	}
	.module--portrait::after {
		top: calc(700px - 1.5rem);
	}
	.module--portrait img {
		max-height: 700px;
	}
	
	/* Gallery base styling */
	.module--gallery-wrapper {
		background: hsl(var(--background));
		margin: 3rem -1rem;
	}
	.module--gallery-wrapper::before {
		background: hsl(var(--background));
		border-radius: 0 0 5px 0;
		color: hsl(var(--text--secondary));
		content: "gallery";
		font-family: var(--font--secondary);
		font-size: .8rem;
		left: 0;
		letter-spacing: 1px;
		line-height: 1;
		padding: 1rem 0.5rem 0.5rem 1rem;
		position: absolute;
		text-transform: uppercase;
		top: 0;
		z-index: 2;
	}
	.module--gallery-wrapper::after {
		background:
			linear-gradient( to right, hsla(var(--background),1) 0.5rem, hsla(var(--background),0) ),
			linear-gradient( to left, hsla(var(--background),1) 0.5rem, hsla(var(--background),0) );
		background-position: left, right;
		background-repeat: no-repeat;
		background-size: 1rem 100%;
		bottom: 1rem;
		content: "";
		display: block;
		left: 0;
		pointer-events: none;
		position: absolute;
		right: 0;
		top: 0;
		z-index: 1;
	}
	.module--gallery {
		margin: 0 1px;
		overflow-x: auto;
		padding: 1rem 0;
		white-space: nowrap;
	}
	.module--gallery .module--image {
		display: inline-block;
		margin: 0;
		margin-right: 1rem;
		vertical-align: top;
	}
	.module--gallery .module--image img {
		height: 500px;
		object-fit: fill;
		width: auto;
		max-width: none;
		max-height: none;
	}
	.module--gallery .module--image::after {
		display: none;
	}
	.module--gallery .module--image:first-of-type {
		margin-left: 1rem;
	}
	.module--gallery p {
		text-align: left;
	}
	
	/* Scrollbars */
	.any--scrollbar {
		scrollbar-width: 10px;
		scrollbar-color: black transparent;
	}
	.any--scrollbar::-webkit-scrollbar {
		height: 10px;
		background: transparent;
	}
	.any--scrollbar::-webkit-scrollbar-thumb {
		border-radius: 5px;
		background-color: rgba(0,0,0,0);
		opacity: 0;
		transition: background-color 0.1s linear;
	}
	.any--scrollbar:hover::-webkit-scrollbar-thumb {
		background-color: rgba(0,0,0,0.5);
	}
	.any--scrollbar::-webkit-scrollbar-track {
		background: transparent;
	}
</style>

<div class="col c1">
	<div class="col--prose">
	
		<!-- Updates -->
		<div class="col--main">
			<h2><?= lang('Latest update', 'サイト更新', 'div'); ?></h2>

			<div class="text text--prose">
				
				<?= $entry['content']; ?>
				
				<a class="a--outlined a--padded" href="<?= '/development/'.$entry['id'].'/#comments'; ?>" style="margin-top:3rem;">
					<?= lang('add comment', 'コメントする', 'hidden'); ?>
				</a>
				
			</div>
		</div>

		<!-- Issues -->
		<div class="col--side">

				<?php
					if($_SESSION['is_boss']) {
						?>
							<input class="issues__options-checkbox input__choice" id="show_controls" type="checkbox" />
							<label class="issues__options-button input__button" for="show_controls"></label>
						<?php
					}
				?>

			<h2>
				<?= lang('Issues', '問題', 'div'); ?>
			</h2>

			<!--<div class="text text--outlined any--weaken">
				<?php if( $_SESSION['is_vip'] ): ?>
					<a href="https://patreon.com/vkgy" target="_blank">VIP members</a> can upvote issues and help decide which ones take priority.
				<?php else: ?>
					Please upvote any issues that you agree with, to help us prioritize development.
				<?php endif; ?>
			</div>-->

			<ul class="text text--outlined issues__container">

				<?php foreach($issues as $issue): ?>
					<li>
						<form class="issue__container any--flex">

							<!-- Completion toggle -->
							<?php if($_SESSION['is_boss']): ?>
								<input name="id" value="<?= $issue['id']; ?>" hidden />
								<input class="issue__completed input__choice" id="<?= 'issue-'.$issue['id']; ?>" name="is_completed" type="checkbox" value="1" <?= $issue['is_completed'] ? 'checked' : null; ?> />
								<label class="issue__completed-label input__checkbox symbol__unchecked" for="<?= 'issue-'.$issue['id']; ?>">done?</label>
							<?php endif; ?>

							<div class="issue__text <?= $issue['is_completed'] ? 'issue--completed' : null; ?>">
								<span class="any__note"><?= '#'.$issue['id']; ?></span>
								<?= $issue['title']; ?>
							</div>

							<div class="issue__vote">
								<?php
									$item_type = 'development';

									$item_id = $issue['id'];
									$issue['votes'] = $vote->access_vote([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'basics' ])[0];

									echo render_component($vote_template, [
										'item_id' => $item_id,
										'item_type' => $item_type,
										'upvote_is_checked' => $issue['votes']['user_score'] > 0 ? 'checked' : null,
										'downvote_is_checked' => $issue['votes']['user_score'] < 0 ? 'checked' : null,
										'score' => $issue['votes']['score'] ?: 0,
									]);
								?>
							</div>

						</form>
					</li>
				<?php endforeach; ?>

			</ul>

			<style>
				.issues__options-button {
					float: right;
					z-index: 1;
				}
				.issues__options-button::before {
					content: "edit";
				}
				.issues__options-checkbox:checked + .issues__options-button::before {
					content: "hide";
				}

				.issue__container {
					justify-content: space-between;
				}

				.issue--completed, .issue__completed:checked ~ .issue__text {
					opacity: 0.75;
					text-decoration: line-through;
				}
				.issue__text {
					margin-right: auto;
					padding-bottom: 0;
				}
				.issue__text .any__note {
					text-decoration: inherit;
				}
				.issue__completed-label {
					box-shadow: 0 0 0.5rem 0.5rem hsl(var(--background--secondary));
					display: none;
					margin: 0;
					position: absolute;
					right: 0;
					top: 50%;
					transform: translateY(-50%);
					transition: opacity 0.1s linear;
					z-index: 1;
				}
				.issues__options-checkbox:checked ~ .issues__container .issue__completed-label {
					display: initial;
				}
				/*.issue__completed + .symbol__unchecked::before {
					margin-right: 0 !important;
				}
				.issue__completed ~ [data-role="status"] {
					margin-left: 1rem;
				}
				.issue__completed ~ [data-role="status"]::before {
					opacity: 1;
				}
				.issue__completed ~ [data-role="status"]:not([class*="symbol"]) {
					display: none;
				}*/
			</style>

	<style>
		[data-role="result"]:empty {
			display: none;
		}
	</style>

			<?php include('partial-add_issue.php'); ?>

		</div>
	
	</div>
</div>

<div class="row">
	<?php include('../main/partial-patreon.php'); ?>
</div>