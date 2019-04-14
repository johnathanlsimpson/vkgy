<?php
function render_documentation($page) {
	global $pdo;
	global $markdown_parser;
	
	$chunks = explode("\n\n\n", $page);
	
	foreach($chunks as $chunk_key => $chunk) {
		// Temporarily replace notes with placeholders
		preg_match_all('/'.'\'\'(.+?),,'.'/s', $chunk, $note_matches, PREG_SET_ORDER);
		foreach($note_matches as $note_match_key => $note_match) {
			$chunk = str_replace($note_match[0], "''".$note_match_key.",,", $chunk);
		}
		
		preg_match_all('/'.'{(.+?)}'.'/s', $chunk, $code_matches, PREG_SET_ORDER);
		foreach($code_matches as $code_match_key => $code_match) {
			$chunk = str_replace($code_match[0], '{'.$code_match_key.'}', $chunk);
		}
		
		preg_match_all('/'.'\[\[(.+?)\]\]'.'/s', $chunk, $raw_matches, PREG_SET_ORDER);
		foreach($raw_matches as $raw_match_key => $raw_match) {
			$chunk = str_replace($raw_match[0], '[['.$raw_match_key.']]', $chunk);
		}
		
		// Initial Markdown parse
		$chunk = $markdown_parser->parse_markdown($chunk);
		
		// Format code examples and insert back into document
		foreach($code_matches as $code_match_key => $code_match) {
			$new_code = $markdown_parser->validate_markdown($code_match[1]);
			$new_code = $markdown_parser->parse_markdown($new_code);
			$new_code = str_replace('&#92;n', '<br />', $new_code);
			$new_code =
				'<span class="any__note">'.str_replace('\n', '<br />', $code_match[1]).'</span> '.
				'<span class="symbol__next symbol--standalone"></span> '.
				'<span class="documentation__example">'.$new_code.'</span>';
			$chunk = str_replace('{'.$code_match_key.'}', $new_code, $chunk);
		}
		
		// Format plain notes and insert back into document
		foreach($note_matches as $note_match_key => $note_match) {
			$new_note = '<span class="any__note">'.str_replace('\n', '<br />', $note_match[1]).'</span>';
			$chunk = str_replace("&#39;&#39;".$note_match_key.',,', $new_note, $chunk);
		}
		
		// Unescape raw code and insert into document
		foreach($raw_matches as $raw_match_key => $raw_match) {
			$new_raw = str_replace(['&#60;', '&#34;', '&#62;'], ['<', '"', '>'], $raw_match[1]);
			$new_raw = '<span class="documentation__raw">'.$new_raw.'</span>';
			$chunk = str_replace("[[".$raw_match_key.']]', $new_raw, $chunk);
		}
		
		// Cleanup the document a bit
		$chunk = str_replace('<span class="any__note">→</span>', '<span class="symbol__next symbol--standalone"></span>', $chunk);
		$chunk = str_replace(["</p>\n<p>", '<p>', '</p>'], ["<br /><br />\n", '', ''], $chunk);
		$chunk = str_replace('</h2>', '</summary><div class="text text--outlined documentation__container">', $chunk).'</div>';
		$chunk = '<details class="documentation__details">'.str_replace('<h2>', '<summary class="h2 documentation__summary">', $chunk).'</details>';
		
		$chunks[$chunk_key] = $chunk;
	}
	
	return implode("\n\n", $chunks);
}