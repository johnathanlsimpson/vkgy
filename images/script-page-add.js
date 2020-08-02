lookForSelectize();

let numElem = document.querySelector('.image__count');
let artistIdElems = document.querySelectorAll('[name="image_artist_id"]');
artistIdElems.forEach(function(artistIdElem) {
	artistIdElem.addEventListener('change', function(event) {
		
		let artistId = artistIdElem.value;
		let artistName = artistIdElem.querySelector('option:checked').textContent;
		let parentElem = artistIdElem.closest('.image__template');
		let id = parentElem.querySelector('[name="image_id"]').value;
		
		initializeInlineSubmit($(parentElem), '/images/function-edit_queued_image.php', {
			preparedFormData  : {
				id: id,
				artist_id: artistId,
				artist_name: artistName
			},
			callbackOnSuccess : function(event, returnedData) {
				
				let siblingElem = parentElem.nextElementSibling;
				siblingElem.querySelector('.image__image').focus();
				
				if(returnedData.status === 'success') {
					parentElem.style.opacity = 0.1;
					numElem.dataset.num--;
				}
				
			}
		});
		
	});
});