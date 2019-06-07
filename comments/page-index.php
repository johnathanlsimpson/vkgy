<?php

$page_header = lang('Recent discussions', '最近のコメント', ['container' => 'div']);

include('../comments/partial-pagination.php');

include('../comments/partial-comments.php');
render_default_comment_section('none', 0, $comments, $markdown_parser);