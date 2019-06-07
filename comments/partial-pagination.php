<?php

subnav([
	[
		'text' => $page > 1 ? 'Page '.($page - 1) : 'Page 1',
		'url' => $page > 1 ? '/comments/&page='.($page - 1) : null,
		'position' => 'left',
	],
	[
		'text' => 'Results '.($offset + 1).' to '.($offset + $num_comments),
		'position' => 'center',
	],
	[
		'text' => $num_total_pages > $page ? 'Page '.($page + 1) : 'Page '.($page ?: 1),
		'url' => $num_total_pages > $page ? '/comments/&page='.($page + 1) : null,
		'position' => 'right',
	],
], 'directional');