<?php

$active_page = '/about/development/';

?>

<div class="col c2">
	
	<!-- Current entry -->
	<div>
		
		
	
		<!-- Updates -->
		<div class="col--main">
			<h2>
				<div class="h5">
					<?= $entry['date_occurred']; ?>
					by <?= $access_user->render_username($entry['user']); ?>
				</div>
				<?= $entry['is_issue'] ? 'Issue <span class="any__note">#'.$entry['id'].'</span>' : $entry['title']; ?>
			</h2>
			<!--<h2><?= lang('Latest update', 'サイト更新', 'div'); ?></h2>-->

			<div class="text text--prose">
				
				<?= $entry['content']; ?>
				
			</div>
		</div>
		
		
		<?php /*<div class="text--centered">
			<h2>
				<div class="h5">
					<?= $entry['date_occurred']; ?>
					by <?= $access_user->render_username($entry['user']); ?>
				</div>
				<?= $entry['is_issue'] ? 'Issue <span class="any__note">#'.$entry['id'].'</span>' : $entry['title']; ?>
			</h2>
		</div>
		
		<div class="text text--centered">
			<?php
				
				// Parse Markdown
				$content = $markdown_parser->parse_markdown($entry['is_issue'] ? $entry['title'] : $entry['content']);
				
				// If regular dev entry, do some additional formatting
				if( !$entry['is_issue'] ) {
					
					// Format change type
					$content = preg_replace_callback('/'.'\[(Addition|Bug fix|Change|Feature)\]'.'/', function($matches) {
						return '<span class="any__note">'.strtolower($matches[1]).'</span>';
					}, $content);
					
					// Format affected folder
					$content = preg_replace('/'.'\<code\>([A-z]+)\<\/code\>'.'/', '<span class="any--weaken">$1</span>', $content);
					
				}
				
				echo $content;
				
			?>
		</div>*/ ?>
		
	</div>
	
	<!-- Other entries -->
	<div>
		<?php
			include('../comments/partial-comments.php');
			render_default_comment_section('development', $entry['id'], $entry['comments'], $markdown_parser);
		?>
	</div>
	
</div>



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