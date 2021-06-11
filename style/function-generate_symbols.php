<?php

// Get list of symbol SVGs
$symbol_files = scandir( '../style/symbols/' );
$symbol_files = array_slice( $symbol_files, 2 );

// Turn files into list of allowed symbols
foreach( $symbol_files as $file ) {
	$allowed_symbols[] = str_replace('.svg', '', $file);
}

// Manually duplicate certain selectors for convenience
$dupe_selectors = [
	'artist'            => [ '.artist:not(.artist--no-symbol)' ],
	'crown'             => [ '.user', '.symbol__user', '.user[data-icon="crown"]', ],
	'flower'            => [ '.user[data-icon="flower"]' ],
	'heart'             => [ '.user[data-icon="heart"]' ],
	'moon'              => [ '.user[data-icon="moon"]' ],
	'star'              => [ '.user[data-icon="star"]', '.symbol__star--full' ],
	'label'             => [ '.symbol__company' ],
	'caret'             => [ '.symbol__next', '.symbol__previous' ],
	'shuffle'           => [ '.symbol__random' ],
	'help'              => [ '.symbol__info', ],
	'loading'           => [ '.symbol__loading.symbol__loading.symbol__loading.symbol__loading.symbol__loading.symbol__loading.symbol__loading' ],
	'checkbox'          => [ '.input__checkbox .symbol__unchecked' ],
	'checkbox--checked' => [ '.input__checkbox .input__choice:checked + .symbol__unchecked', '.symbol--orphan:checked   ~ .symbol--parent .symbol--orphan.input__checkbox   .symbol__unchecked', '.symbol--orphan-a:checked ~ .symbol--parent .symbol--orphan-a.input__checkbox .symbol__unchecked', '.symbol--orphan-b:checked ~ .symbol--parent .symbol--orphan-b.input__checkbox .symbol__unchecked', ],
	'radio'             => [ '.input__radio .symbol__unchecked' ],
	'radio--checked'    => [ '.success', '.symbol__success', '.input__radio .input__choice:checked + .symbol__unchecked', '.symbol--orphan:checked   ~ .symbol--parent .symbol--orphan.input__radio   .symbol__unchecked', '.symbol--orphan-a:checked ~ .symbol--parent .symbol--orphan-a.input__radio .symbol__unchecked', '.symbol--orphan-b:checked ~ .symbol--parent .symbol--orphan-b.input__radio .symbol__unchecked', '.symbol--orphan-c:checked ~ .symbol--parent .symbol--orphan-c.input__radio .symbol__unchecked', '.symbol--orphan-d:checked ~ .symbol--parent .symbol--orphan-d.input__radio .symbol__unchecked', ],
];

// Auto apply direction transforms to these
$direction_selectors = [
	'left' => [ '.symbol__previous' ],
];

// Loop through allowed symbols and build mask images for them
foreach($allowed_symbols as $symbol) {
	
	// symbol::before
	$selector = '.symbol__'.$symbol.'::before';
	
	// Add symbol::before to styles that affect all symbols
	$selectors = [ $selector ];
	$all_selectors[] = $selector;
	
	// May have dupe selectors for a certain symbol (e.g. .user and .symbol__crown should show same thing)
	if( isset($dupe_selectors[ $symbol ]) ) {
		foreach( $dupe_selectors[ $symbol ] as $dupe_selector ) {
			$dupe_selector = $dupe_selector.'::before';
			$selectors[] = $dupe_selector;
			$all_selectors[] = $dupe_selector;
		}
	}
	
	// Get latest SVG
	$url = '/style/symbols/'.$symbol.'.svg';
	$url .= '?'.date( 'YmdHis', filemtime('..'.$url) );
	
	$styles[] = implode(', ', $selectors).' {
		-webkit-mask-image: url('.$url.');
		mask-image: url('.$url.');
	}';
	
}

// Save stylsheet content into var then replace actual stylesheet
ob_start();
?>
	<style>
		
		/* This should be updated from function-generate_symbols */
		/* ====================================================== */
		
		/* Hide disallowed symbols */
		[class*="symbol__"]::before {
			visibility: hidden;
		}
		
		/* All symbols */
		[class*="symbol__"]::before, <?= implode(', ', $all_selectors); ?> {
			background: currentcolor;
			content: "";
			display: inline-block;
			height: var(--symbol-size);
			margin-right: 0.3ch;
			-webkit-mask-size: var(--symbol-size);
			mask-size: var(--symbol-size);
			opacity: 0.5;
			width: var(--symbol-size);
		}
		
		/* Utilities */
		.symbol--standalone::before {
			margin-right: 0;
			opacity: 1;
		}
		.symbol--down::before {
			transform: rotate(90deg);
		}
		.symbol--left::before, <?= implode('::before, ', $direction_selectors['left']).'::before'; ?> {
			transform: rotate(180deg);
		}
		.symbol--up::before {
			transform: rotate(270deg);
		}
		
		/* Un-hide allowed symbols */
		<?= implode(', ', $all_selectors); ?> {
			visibility: visible;
		}
		
		/* Each symbol */
		<?= implode("\n", $styles); ?>
		
	</style>
<?php

// Wrapped stylesheet within <style> just for syntax highlighting, so remove that here
$stylesheet = ob_get_clean();
$stylesheet = str_replace( ['<style>','</style>'], '', $stylesheet );

echo file_put_contents('../style/style-symbols.css', $stylesheet) ? 'Symbol stylesheet generated.' : 'Something went wrong.';