<?php

$page_header = '<h1>'.lang('Visual Kei Interviews', 'ヴィジュアル系インタビュー', 'div').'</h1>';

$page_title = 'Interview List | インタビュー一覧';

style([
	'/blog/style-page-interviews.css',
]);

?>

<div class="col c1">
	<div class="interview__wrapper">
	<?php
		// Loop through entries and attach Japanese versions to English counterparts
		/*for($i=0; $i<$num_entries; $i++) {
			
			// If 日本語 in title, assume JP versions
			if( strpos($entries[$i]['title'], sanitize('日本語')) ) {
				
				// Assuming we won't post two interviews on same day, check previous and next entry to see if posted same day
				if( substr($entries[$i - 1]['date_occurred'], 0, 10) == substr($entries[$i]['date_occurred'], 0, 10) ) {
					$english_key = $i - 1;
				}
				elseif( substr($entries[$i + 1]['date_occurred'], 0, 10) == substr($entries[$i]['date_occurred'], 0, 10) ) {
					$english_key = $i + 1;
				}
				
				// Move JP info to EN post
				if(is_numeric($english_key)) {
					
					$entries[$english_key]['jp_title'] = $entries[$i]['title'];
					$entries[$english_key]['jp_friendly'] = $entries[$i]['friendly'];
					$entries[$english_key]['jp_content'] = $entries[$i]['content'];
					
				}
				
				// Unset JP post and $english_key
				unset($entries[$i], $english_key);
				
			}
			
		}*/
		
		// Pop off first entry so we can highlight it, then shuffle rest
		//$entries = array_values($entries);
		//$first_entry = $entries[0];
		//unset($entries[0]);
		//shuffle($entries);
		//array_unshift($entries, $first_entry);
		
		//echo $_SESSION['username'] === 'inartistic' ? '<pre>'.print_r($entries, true).'</pre>' : null;
		
		// Loop back through entries and render
		foreach($entries as $entry_key => $entry) {
			
			//$entry['content'] = $markdown_parser->parse_markdown($entry['content']);
			//$entry['jp_content'] = $markdown_parser->parse_markdown($entry['jp_content']);
			
			if(is_array($entry['artist']) && !empty($entry['artist'])) {
				$band_romaji = $entry['artist']['romaji'];
				$band_name = $entry['artist']['name'];
			}
			elseif(strpos($entry['title'], 'interview') !== false) {
				foreach($entry['translations'] as $translation) {
					if($translation['language'] === 'ja') {
						$band_name = explode(sanitize('へのインタビュー'), $translation['title'])[0];
					}
				}
				$band_romaji = strpos($entry['title'], 'band ') > 0 ? end(explode('band ', $entry['title'])) : end(explode('with ', $entry['title']));
				
				if(!$band_name && $band_romaji) {
					$band_name = $band_romaji;
					unset($band_romaji);
				}
				elseif($band_name && $band_romaji && $band_romaji == $band_name) {
					unset($band_romaji);
				}
				
			}
			/*elseif(is_array($entry['translations']) && !empty($entry['translations'])) {
				$band_romaji = strpos($entry['title'], 'band ') > 0 ? end(explode('band ', $entry['title'])) : end(explode('with ', $entry['title']));
				$band_romaji = $band_romaji && $band_romaji != $band_name
			}*/
			
			//$band_name = strlen($band_name) ? $band_name : 
			
			
			?>
				<div class="interview__container">
					
					<div class="interview__top any--flex">
						
						<h1 class="interview__title">
							<a href="<?= '/blog/'.$entry['friendly'].'/'; ?>">
								<?= $band_romaji && $band_romaji != $band_name ? lang($band_romaji, $band_name, 'div') : ($band_name ?: $entry['title']); ?>
							</a>
						</h1>
						
						<?= $_SESSION['can_add_data'] ? '<a class="a--padded interview__link symbol__edit" href="/blog/'.$entry['friendly'].'/edit/"><span>Edit</span></a>' : null; ?>
						<a class="a--outlined a--padded interview__link" href="<?= '/blog/'.$entry['friendly'].'/'; ?>">Eng<span>lish</span></a>
						
						<?php
							if(is_array($entry['translations']) && !empty($entry['translations'])) {
								foreach($entry['translations'] as $translation) {
									if($translation['language'] != 'en') {
										echo '<a class="a--outlined a--padded interview__link" href="/blog/'.$translation['friendly'].'/">'.['ja' => '日本語版'][$translation['language']].'</a>';
									}
								}
							}
						?>
					</div>
					
					<div class="text interview__content <?= $entry_key === 0 ? 'interview--latest' : null; ?> ">
						<a class="interview__image-link" href="<?= '/blog/'.$entry['friendly'].'/'; ?>">
							<img class="interview__image" src="<?= '/images/'.$entry['image']['id'].'-'.$entry['friendly'].'.large.'.$entry['image']['extension']; ?>" />
						</a>
					</div>
					
				</div>
			<?php
			
			unset($band_name, $band_romaji);
		}
	?>
	</div>
</div>