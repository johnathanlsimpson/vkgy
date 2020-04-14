// Get inputs so we can clear them if successful
let nameElems = document.querySelectorAll('[name="name[]"]');
let romajiElems = document.querySelectorAll('[name="romaji[]"]');

// Submit additions
initializeInlineSubmit($('[name=add-magazine]'), '/magazines/function-add.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElem, returnedData) {
		
		if(returnedData.keys && returnedData.keys.length) {
			returnedData.keys.forEach(function(index) {
				nameElems[index].value = null;
				romajiElems[index].value = null;
			});
		}
		
	}
});

	/*initializeInlineSubmit($("[name=add]"), "/releases/function-add.php",{
		showEditLink : true,
		callbackOnSuccess : function(formElement, returnedData) {
			var e = new Event('item-id-updated');
			e.details = {
				'id' : returnedData.id
			};
			document.dispatchEvent(e);
		}
	});*/