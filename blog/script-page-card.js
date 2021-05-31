let cardElem = document.querySelector('#card');
let blogID = document.querySelector('[name="blog_id"]').value;

html2canvas(cardElem).then(function(canvas) {
	
	let imageData = canvas.toDataURL('image/webp', 1);
	
	// Save image
	initializeInlineSubmit( $(cardElem), '/blog/function-save_card.php', {
		preparedFormData: {
			'image_data': imageData,
			'blog_id': blogID,
		},
		callbackOnSuccess: function(formElem, returnedData) {
			console.log('Card image saved.');
			console.log(returnedData);
		},
		callbackOnError: function(formElem, returnedData) {
			console.log('Card image not saved.');
			console.log(returnedData);
		}
	});

});