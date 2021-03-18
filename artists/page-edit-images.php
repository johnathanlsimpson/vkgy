<?php

include('../artists/head.php');
include_once('../php/function-render_component.php');

script([
	"/scripts/external/script-autosize.js",
	"/scripts/external/script-selectize.js",
	'/scripts/external/script-tribute.js',
	'/scripts/external/script-inputmask.js',
	
	"/scripts/script-initDelete.js",
	"/scripts/script-initSelectize.js",
]);

style([
	'/style/external/style-tribute.css',
	"/style/external/style-selectize.css",
	"/style/style-selectize.css",
]);

?>
						
						<div class="col c1">
							<div>
								<h2>
									Edit image gallery
								</h2>
								<?php
									include('../images/function-render_image_section.php');
									render_image_section($artist['images'], [
										'item_type' => 'artist',
										'item_id' => $artist['id'],
										'item_name' => $artist['quick_name'],
										'description' => $artist['quick_name'].' group photo',
										'default_id' => $artist['image_id'],
										'hide_blog' => true,
										'hide_labels' => true,
										'hide_markdown' => true,
									]);
								?>
							</div>
						</div>