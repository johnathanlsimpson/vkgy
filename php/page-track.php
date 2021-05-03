<?php

include_once('../php/include.php');

// Set up defaults
$allowed_platforms = [
	'none',
	'amazon',
	'cdjapan',
	'rarezhut'
];

// Tokens for tracking affiliate links
$affiliate_tokens = [
	'amazon' => 'vkgy0c-22',
	'cdjapan' => 'PytJTGW7Lok/6128/A549875',
	'rarezhut' => 'vkgy'
];

// Templates for links
$link_templates = [
	'amazon' => 'https://www.amazon.co.jp/s/ref=as_li_ss_tl?k={default_term}&tag={affiliate_token}',
	'cdjapan' => 'https://www.cdjapan.co.jp/aff/click.cgi/{affiliate_token}/searches?term.media_format=&f=all&q={favored_term}',
	'rarezhut' => 'https://magento.rarezhut.net/catalogsearch/result/?q={default_term}&utm_source={affiliate_token}',
];

// Get possible variables and clean
$platform         = friendly($_GET['p']);
$default_term     = urlencode( urldecode( $_GET['d'] ) );
$favored_term     = strlen($_GET['f']) ? urlencode( urldecode( $_GET['f'] ) ) : $default_term;
$location_in_page = sanitize( urldecode($_GET['l']) ) ?: null;

// Make platform-specific change to product number for cdjapan
if( $platform === 'cdjapan' && strlen($favored_term) ) {
	$favored_term = preg_replace('/'.'\-0+'.'/', '-', $favored_term);
}

// Set default for preferred term since we just do simple find and replace later
$favored_term = strlen($favored_term) ? $favored_term : $default_term;
	
// Make sure we have correct platform
if( strlen($platform) && in_array( $platform, $allowed_platforms ) ) {
	
	// Make sure we have something to search for
	if( strlen($default_term) ) {
		
		// Set up specific vars
		$current_page    = sanitize( urldecode( $_GET['c'] ) ) ?: null;
		$platform_id     = array_search( $platform, $allowed_platforms );
		$affiliate_token = $affiliate_tokens[ $platform ];
		$link_template   = $link_templates[ $platform ];
		$user_agent      = sanitize( $_SERVER['HTTP_USER_AGENT'] );
		$is_bot          = $user_agent && preg_match('/'.'bot|crawl|slurp|spider|mediapartners'.'/i', $_SERVER['HTTP_USER_AGENT']);
		
		// Set up the actual url
		$outbound_url = str_replace(
			[ '{default_term}', '{favored_term}', '{affiliate_token}' ],
			[ $default_term, $favored_term, $affiliate_token ],
			$link_template
		);
		
		// Save info if not bot
		if( !$is_bot ) {
			$sql_track = 'INSERT INTO views_outbound (platform_id, outbound_url, current_page, location_in_page, user_agent) VALUES (?, ?, ?, ?, ?)';
			$stmt_track = $pdo->prepare($sql_track);
			$stmt_track->execute([ $platform_id, $outbound_url, $current_page, $location_in_page, $user_agent ]);
		}
		
	}
	else {
		$outbound_url = '/';
	}
	
}
else {
	$outbound_url = '/';
}

header('Location: '.$outbound_url);