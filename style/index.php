<?php

$symbols = array_slice( scandir('../style/symbols/'), 2 );

?>

<div class="col c1">
	
	<h3>
		Symbols
	</h3>
	
	<div class="text any--flex style__symbols">
		<?php foreach( $symbols as $symbol ): ?>
			<div class="<?= 'symbol__'.substr( $symbol, 0, -4 ); ?> symbol--standalone style__symbol">
				<div style="font-size:1rem;"><?= substr( $symbol, 0, -4 ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	
</div>

<style>
	.style__symbols {
		flex-wrap: wrap;
		font-size: 2rem;
		overflow: hidden;
		padding-bottom: 0;
		padding-top: 0;
		text-align: center;
	}
	.style__symbol {
		border-top: 1px dotted hsl(var(--text--secondary));
		flex-grow: 1;
		margin-top: -1px;
		padding: 1rem;
		padding-top: calc(1rem + 1px);
		width: 100px;
	}
	.symbol__loading::before {
		animation: none;
		opacity: 1 !important;
	}
</style>