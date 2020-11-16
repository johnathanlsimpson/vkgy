<?php

$markdown_parser = new parse_markdown($pdo);

$page_header = lang('VIP members', 'VIPメンバー', 'div');

?>

<?php if( $_SESSION['is_vip'] ): ?>
	<div class="col c1 any--margin">
		<div class="vip__content">
			
			<div class="text text--outlined text--notice">
				Thanks for being a VIP member &hearts; Feel free to leave random comments about... whatever, below! Non-VIP members won't be able to see these.
			</div>
			
			<h1 class="vip__wall">
				<?= lang('VIP wall', 'VIPの掲示板', 'div'); ?>
			</h1>
			
			<?php
				$vip_comments = $access_comment->access_comment([ 'id' => 0, 'type' => 'vip', 'get' => 'all' ]);
				include('../comments/partial-comments.php');
				render_default_comment_section('vip', 0, $vip_comments, $markdown_parser);
			?>
			
		</div>
	</div>
<?php else: ?>
	<div class="col c1 any--margin">
		<div class="vip__content">
			
			<h2>
				<?= lang('About VIP membership', 'VIP会員について', 'div'); ?>
			</h2>
			
			<div class="text">
				
				<p>
					Our little vkei library currently contains <strong>over 2 million records</strong>, attracting 100+ thousand views per month. This kind of scale brings significant recurring costs&mdash;and we're only able to afford them because of users like you.
				</p>
				
				<p>
					VIP membership is granted to users who donate $5/month (or more) through <strong><a href="https://patreon.com/vkgy" target="_blank">vkgy's Patreon</a></strong>. If you find that kind of value in vkgy, please consider supporting us. Several nice benefits are included (below).
				</p>
				
				<p class="any--weaken-color">
					Want to support us but can't spare $5 right now? Even $1/month helps us out. Or, just be active around vkgy and tell your friends about us. Every bit of participation helps grow!
				</p>
				
			</div>
			
			<h2>
				<?= lang('Benefits', '会員制のメリット', 'div'); ?>
			</h2>
			
			<ul class="vip__benefits ul--bulleted ul--inline">
				<li>No ads</li>
				<li><span class="patreon__badge">VIP</span> badge on username</li>
				<li>Custom symbol on <span class="symbol__user-moon">username</span></li>
				<li>Beta access to features</li>
				<li>Feature requests prioritized</li>
				<li>Full resolution images</li>
				<li>Exclusive avatar items</li>
				<li>Exclusive Discord channel</li>
				<li><span class="any__note">+ more</span></li>
			</ul>
			
			<a class="patreon__button any--margin" href="https://patreon.com/vkgy" target="_blank">
				Become a Patron
			</a>
			
		</div>
	</div>
<?php endif; ?>

<style>
	.vip__content {
		margin-left: auto;
		margin-right: auto;
		max-width: 100%;
		width: 600px;
	}
	.vip__wall {
		margin-bottom: 1rem;
	}
	.vip__benefits {
		line-height: 2rem;
	}
	.vip__benefits li {
		margin-left: 1rem;
	}
	#comments + h2 {
		display: none;
	}
</style>

<?php include('../main/partial-patreon.php'); ?>