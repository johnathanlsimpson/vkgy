<template id="point-template">
	<a class="point__container h5" href="<?= '/users/'.$_SESSION['username'].'/'; ?>">
		<span class="point__value">0</span>
		<span class="symbol__point point__symbol"></span>
	</a>
</template>