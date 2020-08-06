<?php

include_once('../php/include.php');

https://stackoverflow.com/questions/2344383/what-is-the-best-way-to-put-a-translation-system-in-php-website

lang(inputstring [ variables ]) {
	// check	for file in current dir
	
	^ prob easier for folder but maybe page is better?
	
	// get current lang from cookie or session or variable
	
	// convert file to array
	
	// get translated string from array
	
	// insert variables
	
	// fall back to eng?
	
	$file_contents = get_contents( 'my_page.de' );
	$lang = unserialize( gzuncompress( $file_contents ) );
}

lang('blah blah');

generate_lang() {
	
	// get all trans for certain page

	// check if document exists
	
	// open document
	
	// replace contents
	
	^ will that cause a page to render wrong while saving?
		
		$lang = array(
    'hello' => 'Hallo',
    'good_morning' => 'Guten Tag',
    'logout_message' = > 'We are sorry to see you go, come again!'    
);

$storage_lang = gzcompress( serialize( $lang ) );

// WRITE THIS INTO A FILE SUCH AS 'my_page.de'
	
}