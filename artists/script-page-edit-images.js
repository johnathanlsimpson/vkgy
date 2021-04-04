let musicianElems = document.querySelectorAll('.musician__default');

musicianElems.forEach(function(musicianElem) {
	musicianElem.addEventListener('change', function(event) {
		
		let labelElem = musicianElem.closest('.musician__label');
		
		initializeInlineSubmit($(labelElem), '/artists/function-edit_musician_images.php', {
			'preparedFormData': {
				artist_id: musicianElem.dataset.artist,
				musician_id: musicianElem.dataset.musician,
				image_id: musicianElem.value
			},
			'callbackOnSuccess': function(event, returnedData) {
			},
			'callbackOnError': function(event, returnedData) {
			}
		});
		
		document.querySelectorAll('.musician__default:not(:checked) + .symbol__unchecked').forEach(function(defaultElem) {
			defaultElem.classList.remove('symbol__success', 'symbol__error', 'loading', 'symbol__loading');
		});
		
	});
});