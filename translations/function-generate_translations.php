<?php

include_once('../php/include.php');

// Update translations for that particular page
function generate_translation_file($folder, $language, $pdo) {
	
	if(strlen($folder) && file_exists('../'.$folder)) {
		
		$sql_translations = '
			SELECT
				translations.*,
				translations_proposals.content AS translation
			FROM
				translations
			LEFT JOIN
				translations_proposals ON translations_proposals.id=translations.'.$language.'_id
			WHERE
				translations.folder=?
		';
		$stmt_translations = $pdo->prepare($sql_translations);
		$stmt_translations->execute([ $folder ]);
		$rslt_translations = $stmt_translations->fetchAll();
		
		$translations = [];
		
		if(is_array($rslt_translations) && !empty($rslt_translations)) {
			foreach($rslt_translations as $translation) {
				$translations[ $translation['content'] ] = $translation['translation'] ?: null;
			}
		}
		
		$translation_file = gzcompress( serialize( $translations ) );
		$filename = '../'.$folder.'/lang.'.$language;
		file_put_contents( $filename, $translation_file );
		
	}
	
}